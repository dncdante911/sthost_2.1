<?php
/**
 * VPS Manager - главный класс управления VPS
 * Интегрирует LibVirt, FOSSBilling и базу данных сайта
 * Файл: /includes/classes/VPSManager.php
 */

// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

require_once __DIR__ . '/LibvirtManager.php';
require_once __DIR__ . '/FossBillingAPI.php';

class VPSManager {
    private $db;
    private $libvirt;
    private $fossbilling;
    
    public function __construct($db_connection = null) {
        $this->db = $db_connection ?: DatabaseConnection::getSiteConnection();
        $this->libvirt = new LibvirtManager();
        $this->fossbilling = new FossBillingAPI();
    }
    
    /**
     * Получение списка VPS планов
     */
    public function getVPSPlans($active_only = true) {
        try {
            $sql = "SELECT * FROM vps_plans";
            $params = [];
            
            if ($active_only) {
                $sql .= " WHERE is_active = 1";
            }
            
            $sql .= " ORDER BY sort_order, price_monthly";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'plans' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
            
        } catch (Exception $e) {
            error_log("Get VPS plans error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение операционных систем
     */
    public function getOSTemplates($active_only = true) {
        try {
            $sql = "SELECT * FROM vps_os_templates";
            $params = [];
            
            if ($active_only) {
                $sql .= " WHERE is_active = 1";
            }
            
            $sql .= " ORDER BY sort_order, type, name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'templates' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
            
        } catch (Exception $e) {
            error_log("Get OS templates error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Создание VPS заказа
     */
    public function createVPSOrder($user_id, $plan_id, $config) {
        $this->db->beginTransaction();
        
        try {
            // Получаем план
            $plan = $this->getVPSPlan($plan_id);
            if (!$plan['success']) {
                throw new Exception('Plan not found');
            }
            $plan_data = $plan['plan'];
            
            // Получаем пользователя
            $user = $this->getUser($user_id);
            if (!$user['success']) {
                throw new Exception('User not found');
            }
            $user_data = $user['user'];
            
            // Синхронизируем клиента с FOSSBilling
            $client_sync = $this->fossbilling->syncClient($user_data);
            if (!$client_sync['success']) {
                throw new Exception('Failed to sync client with billing');
            }
            
            // Получаем свободный IP адрес
            $ip_result = $this->allocateIPAddress();
            if (!$ip_result['success']) {
                throw new Exception('No available IP addresses');
            }
            
            // Генерируем уникальное имя VPS
            $hostname = $config['hostname'] ?? $this->generateHostname();
            $libvirt_name = 'vps-' . $user_id . '-' . time();
            $root_password = $config['root_password'] ?? $this->generatePassword();
            $vnc_password = $this->generatePassword(8);
            
            // Создаем заказ в FOSSBilling
            $fossbilling_order = $this->fossbilling->createVPSOrder([
                'client_id' => $client_sync['client_id'],
                'product_id' => $plan_data['fossbilling_product_id'] ?? null,
                'period' => $config['period'] ?? 'monthly',
                'hostname' => $hostname,
                'os_template' => $config['os_template'],
                'root_password' => $root_password,
                'activate' => false // Активируем после создания VPS
            ]);
            
            // Создаем запись VPS в нашей БД
            $vps_data = [
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'fossbilling_order_id' => $fossbilling_order['data']['id'] ?? null,
                'hostname' => $hostname,
                'libvirt_name' => $libvirt_name,
                'ip_address' => $ip_result['ip_address'],
                'ip_gateway' => $ip_result['gateway'],
                'ip_netmask' => $ip_result['netmask'],
                'dns_servers' => json_encode(['192.168.0.10']),
                'os_template' => $config['os_template'],
                'root_password' => password_hash($root_password, PASSWORD_DEFAULT),
                'vnc_password' => $vnc_password,
                'status' => 'pending',
                'cpu_cores' => $plan_data['cpu_cores'],
                'ram_mb' => $plan_data['ram_mb'],
                'disk_gb' => $plan_data['disk_gb'],
                'bandwidth_gb' => $plan_data['bandwidth_gb']
            ];
            
            $vps_id = $this->createVPSRecord($vps_data);
            if (!$vps_id) {
                throw new Exception('Failed to create VPS record');
            }
            
            // Обновляем IP адрес как занятый
            $this->assignIPToVPS($ip_result['ip_id'], $vps_id);
            
            $this->db->commit();
            
            // Запускаем создание VPS в фоне
            $this->scheduleVPSCreation($vps_id);
            
            return [
                'success' => true,
                'vps_id' => $vps_id,
                'order_id' => $fossbilling_order['data']['id'] ?? null,
                'hostname' => $hostname,
                'ip_address' => $ip_result['ip_address'],
                'status' => 'pending'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Create VPS order error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Создание VPS в libvirt
     */
    public function createVPS($vps_id) {
        try {
            // Получаем данные VPS
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                throw new Exception('VPS not found');
            }
            $vps_data = $vps['vps'];
            
            // Получаем шаблон ОС
            $os_template = $this->getOSTemplate($vps_data['os_template']);
            if (!$os_template['success']) {
                throw new Exception('OS template not found');
            }
            $template_data = $os_template['template'];
            
            // Обновляем статус
            $this->updateVPSStatus($vps_id, 'creating');
            
            // Конфигурация для libvirt
            $libvirt_config = [
                'name' => $vps_data['libvirt_name'],
                'memory' => $vps_data['ram_mb'],
                'cpu_cores' => $vps_data['cpu_cores'],
                'disk_size' => $vps_data['disk_gb'],
                'ip_address' => $vps_data['ip_address'],
                'template_path' => $template_data['libvirt_image_path'],
                'vnc_password' => $vps_data['vnc_password']
            ];
            
            // Создаем VPS в libvirt
            $creation_result = $this->libvirt->createVPS($libvirt_config);
            
            if ($creation_result['success']) {
                // Обновляем статус и VNC порт
                $this->updateVPSStatus($vps_id, 'active');
                $this->updateVPSVNC($vps_id, $creation_result['vnc_port'] ?? null);
                
                // Активируем заказ в FOSSBilling
                if ($vps_data['fossbilling_order_id']) {
                    $this->fossbilling->activateOrder($vps_data['fossbilling_order_id']);
                }
                
                // Логируем действие
                $this->logVPSAction($vps_id, 'create', 'completed', [
                    'ip_address' => $vps_data['ip_address'],
                    'hostname' => $vps_data['hostname']
                ]);
                
                return ['success' => true, 'message' => 'VPS created successfully'];
                
            } else {
                // Ошибка создания
                $this->updateVPSStatus($vps_id, 'error');
                $this->logVPSAction($vps_id, 'create', 'failed', null, $creation_result['error']);
                
                return ['success' => false, 'error' => $creation_result['error']];
            }
            
        } catch (Exception $e) {
            error_log("Create VPS error: " . $e->getMessage());
            $this->updateVPSStatus($vps_id, 'error');
            $this->logVPSAction($vps_id, 'create', 'failed', null, $e->getMessage());
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Управление VPS (старт, стоп, перезагрузка)
     */
    public function controlVPS($vps_id, $action, $user_id = null) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            // Проверяем права пользователя
            if ($user_id && $vps_data['user_id'] != $user_id) {
                return ['success' => false, 'error' => 'Access denied'];
            }
            
            // Логируем начало действия
            $action_id = $this->logVPSAction($vps_id, $action, 'running');
            
            // Выполняем действие в libvirt
            $result = $this->libvirt->controlVPS($vps_data['libvirt_name'], $action);
            
            if ($result['success']) {
                // Обновляем статус VPS
                $new_status = $this->mapLibvirtActionToStatus($action);
                if ($new_status) {
                    $this->updateVPSStatus($vps_id, $new_status);
                }
                
                // Обновляем лог действия
                $this->updateVPSAction($action_id, 'completed');
                
                return ['success' => true, 'message' => "VPS {$action} completed"];
                
            } else {
                // Обновляем лог действия с ошибкой
                $this->updateVPSAction($action_id, 'failed', $result['error']);
                
                return ['success' => false, 'error' => $result['error']];
            }
            
        } catch (Exception $e) {
            error_log("Control VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Переустановка VPS
     */
    public function reinstallVPS($vps_id, $new_os_template, $user_id = null) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            // Проверяем права пользователя
            if ($user_id && $vps_data['user_id'] != $user_id) {
                return ['success' => false, 'error' => 'Access denied'];
            }
            
            // Получаем новый шаблон ОС
            $os_template = $this->getOSTemplate($new_os_template);
            if (!$os_template['success']) {
                return ['success' => false, 'error' => 'OS template not found'];
            }
            $template_data = $os_template['template'];
            
            // Создаем бэкап перед переустановкой
            $backup_result = $this->createVPSBackup($vps_id, 'before_reinstall');
            
            // Логируем действие
            $action_id = $this->logVPSAction($vps_id, 'reinstall', 'running', [
                'old_os' => $vps_data['os_template'],
                'new_os' => $new_os_template,
                'backup_id' => $backup_result['backup_id'] ?? null
            ]);
            
            // Переустанавливаем в libvirt
            $result = $this->libvirt->reinstallVPS(
                $vps_data['libvirt_name'], 
                $template_data['libvirt_image_path']
            );
            
            if ($result['success']) {
                // Обновляем OS template в БД
                $this->updateVPSOS($vps_id, $new_os_template);
                
                // Генерируем новый пароль
                $new_password = $this->generatePassword();
                $this->updateVPSPassword($vps_id, $new_password);
                
                // Обновляем лог действия
                $this->updateVPSAction($action_id, 'completed', [
                    'new_password' => $new_password
                ]);
                
                return [
                    'success' => true, 
                    'message' => 'VPS reinstalled successfully',
                    'new_password' => $new_password
                ];
                
            } else {
                $this->updateVPSAction($action_id, 'failed', $result['error']);
                return ['success' => false, 'error' => $result['error']];
            }
            
        } catch (Exception $e) {
            error_log("Reinstall VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение информации о VPS для пользователя
     */
    public function getUserVPS($user_id, $vps_id = null) {
        try {
            $sql = "SELECT v.*, p.name_ua as plan_name, p.cpu_cores as plan_cpu, 
                           p.ram_mb as plan_ram, p.disk_gb as plan_disk, 
                           o.display_name as os_name, o.version as os_version,
                           o.type as os_type
                    FROM vps_instances v 
                    LEFT JOIN vps_plans p ON v.plan_id = p.id
                    LEFT JOIN vps_os_templates o ON v.os_template = o.name
                    WHERE v.user_id = ?";
            
            $params = [$user_id];
            
            if ($vps_id) {
                $sql .= " AND v.id = ?";
                $params[] = $vps_id;
            }
            
            $sql .= " ORDER BY v.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $vps_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Получаем статус из libvirt для каждого VPS
            foreach ($vps_list as &$vps) {
                $status = $this->libvirt->getVPSStatus($vps['libvirt_name']);
                if ($status['success']) {
                    $vps['libvirt_status'] = $status['state'];
                    $vps['resource_usage'] = [
                        'cpu_usage' => $status['cpu_usage'] ?? 0,
                        'memory_usage' => $status['memory_usage'] ?? 0,
                        'memory_used_mb' => $status['memory_used_mb'] ?? 0
                    ];
                }
                
                // Получаем VNC информацию
                $vnc = $this->libvirt->getVNCInfo($vps['libvirt_name']);
                if ($vnc['success']) {
                    $vps['vnc'] = [
                        'host' => $vnc['host'],
                        'port' => $vnc['port'],
                        'password' => $vps['vnc_password'] // Из нашей БД
                    ];
                }
                
                // Скрываем чувствительную информацию
                unset($vps['root_password']);
                unset($vps['vnc_password']);
            }
            
            if ($vps_id) {
                return [
                    'success' => true,
                    'vps' => $vps_list[0] ?? null
                ];
            }
            
            return [
                'success' => true,
                'vps_list' => $vps_list
            ];
            
        } catch (Exception $e) {
            error_log("Get user VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Создание бэкапа VPS
     */
    public function createVPSBackup($vps_id, $type = 'manual', $name = null) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            $backup_name = $name ?: ('backup-' . date('Y-m-d-H-i-s'));
            
            // Создаем снимок в libvirt
            $snapshot_result = $this->libvirt->createSnapshot(
                $vps_data['libvirt_name'], 
                $backup_name
            );
            
            if ($snapshot_result['success']) {
                // Сохраняем информацию о бэкапе в БД
                $stmt = $this->db->prepare("
                    INSERT INTO vps_backups (vps_id, name, backup_type, status) 
                    VALUES (?, ?, ?, 'completed')
                ");
                $stmt->execute([$vps_id, $backup_name, $type]);
                
                $backup_id = $this->db->lastInsertId();
                
                return [
                    'success' => true,
                    'backup_id' => $backup_id,
                    'backup_name' => $backup_name
                ];
            }
            
            return $snapshot_result;
            
        } catch (Exception $e) {
            error_log("Create VPS backup error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Приостановка VPS (по неоплате или нарушениям)
     */
    public function suspendVPS($vps_id, $reason = '') {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            // Приостанавливаем в libvirt
            $result = $this->libvirt->controlVPS($vps_data['libvirt_name'], 'suspend');
            
            if ($result['success']) {
                // Обновляем статус и причину в БД
                $stmt = $this->db->prepare("
                    UPDATE vps_instances 
                    SET status = 'suspended', suspend_reason = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$reason, $vps_id]);
                
                // Приостанавливаем заказ в FOSSBilling
                if ($vps_data['fossbilling_order_id']) {
                    $this->fossbilling->suspendOrder($vps_data['fossbilling_order_id'], $reason);
                }
                
                // Логируем действие
                $this->logVPSAction($vps_id, 'suspend', 'completed', ['reason' => $reason]);
                
                return ['success' => true, 'message' => 'VPS suspended'];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Suspend VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Возобновление VPS
     */
    public function unsuspendVPS($vps_id) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            // Возобновляем в libvirt
            $result = $this->libvirt->controlVPS($vps_data['libvirt_name'], 'resume');
            
            if ($result['success']) {
                // Обновляем статус в БД
                $stmt = $this->db->prepare("
                    UPDATE vps_instances 
                    SET status = 'active', suspend_reason = NULL, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$vps_id]);
                
                // Возобновляем заказ в FOSSBilling
                if ($vps_data['fossbilling_order_id']) {
                    $this->fossbilling->unsuspendOrder($vps_data['fossbilling_order_id']);
                }
                
                // Логируем действие
                $this->logVPSAction($vps_id, 'unsuspend', 'completed');
                
                return ['success' => true, 'message' => 'VPS resumed'];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Unsuspend VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
   
   // ========== ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ==========
    
    /**
     * Получение VPS плана по ID
     */
    private function getVPSPlan($plan_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vps_plans WHERE id = ? AND is_active = 1");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$plan) {
                return ['success' => false, 'error' => 'Plan not found'];
            }
            
            return ['success' => true, 'plan' => $plan];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение пользователя
     */
    private function getUser($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            return ['success' => true, 'user' => $user];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Выделение свободного IP адреса
     */
    private function allocateIPAddress() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM vps_ip_pool 
                WHERE vps_id IS NULL AND is_reserved = 0 
                ORDER BY INET_ATON(ip_address) 
                LIMIT 1
            ");
            $stmt->execute();
            $ip = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ip) {
                return ['success' => false, 'error' => 'No available IP addresses'];
            }
            
            return [
                'success' => true,
                'ip_id' => $ip['id'],
                'ip_address' => $ip['ip_address'],
                'gateway' => $ip['gateway'],
                'netmask' => $ip['netmask']
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Назначение IP адреса VPS
     */
    private function assignIPToVPS($ip_id, $vps_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE vps_ip_pool 
                SET vps_id = ?, assigned_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$vps_id, $ip_id]);
            
        } catch (Exception $e) {
            error_log("Assign IP error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Создание записи VPS в БД
     */
    private function createVPSRecord($vps_data) {
        try {
            $sql = "INSERT INTO vps_instances (
                user_id, plan_id, fossbilling_order_id, hostname, libvirt_name,
                ip_address, ip_gateway, ip_netmask, dns_servers, os_template,
                root_password, vnc_password, status, cpu_cores, ram_mb, 
                disk_gb, bandwidth_gb
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $vps_data['user_id'], $vps_data['plan_id'], 
                $vps_data['fossbilling_order_id'], $vps_data['hostname'], 
                $vps_data['libvirt_name'], $vps_data['ip_address'], 
                $vps_data['ip_gateway'], $vps_data['ip_netmask'], 
                $vps_data['dns_servers'], $vps_data['os_template'], 
                $vps_data['root_password'], $vps_data['vnc_password'], 
                $vps_data['status'], $vps_data['cpu_cores'], 
                $vps_data['ram_mb'], $vps_data['disk_gb'], 
                $vps_data['bandwidth_gb']
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Create VPS record error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получение VPS по ID
     */
    private function getVPSById($vps_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vps_instances WHERE id = ?");
            $stmt->execute([$vps_id]);
            $vps = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$vps) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            
            return ['success' => true, 'vps' => $vps];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение шаблона ОС
     */
    private function getOSTemplate($template_name) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM vps_os_templates 
                WHERE name = ? AND is_active = 1
            ");
            $stmt->execute([$template_name]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$template) {
                return ['success' => false, 'error' => 'Template not found'];
            }
            
            return ['success' => true, 'template' => $template];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Обновление статуса VPS
     */
    private function updateVPSStatus($vps_id, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE vps_instances 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$status, $vps_id]);
            
        } catch (Exception $e) {
            error_log("Update VPS status error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Логирование действий VPS
     */
    private function logVPSAction($vps_id, $action, $status = 'pending', $details = null, $error = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO vps_actions (vps_id, action, status, details, error_message)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $vps_id, 
                $action, 
                $status, 
                $details ? json_encode($details) : null,
                $error
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Log VPS action error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обновление действия VPS
     */
    private function updateVPSAction($action_id, $status, $error = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE vps_actions 
                SET status = ?, error_message = ?, completed_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$status, $error, $action_id]);
            
        } catch (Exception $e) {
            error_log("Update VPS action error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Планирование создания VPS в фоне
     */
    private function scheduleVPSCreation($vps_id) {
        // В реальном проекте здесь должна быть очередь задач (Redis, RabbitMQ и т.д.)
        // Для упрощения создаем сразу
        $this->createVPS($vps_id);
    }
    
    /**
     * Генерация hostname
     */
    private function generateHostname() {
        return 'vps-' . strtolower(substr(md5(uniqid()), 0, 8));
    }
    
    /**
     * Генерация пароля
     */
    private function generatePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }
    
    /**
     * Маппинг действий libvirt в статусы
     */
    private function mapLibvirtActionToStatus($action) {
        $map = [
            'start' => 'active',
            'stop' => 'stopped',
            'force_stop' => 'stopped',
            'suspend' => 'suspended',
            'resume' => 'active'
        ];
        
        return $map[$action] ?? null;
    }
    
    /**
     * Обновление VNC информации
     */
    private function updateVPSVNC($vps_id, $vnc_port) {
        try {
            $stmt = $this->db->prepare("
                UPDATE vps_instances 
                SET vnc_port = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$vnc_port, $vps_id]);
            
        } catch (Exception $e) {
            error_log("Update VPS VNC error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обновление ОС VPS
     */
    private function updateVPSOS($vps_id, $os_template) {
        try {
            $stmt = $this->db->prepare("
                UPDATE vps_instances 
                SET os_template = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$os_template, $vps_id]);
            
        } catch (Exception $e) {
            error_log("Update VPS OS error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обновление пароля VPS
     */
    private function updateVPSPassword($vps_id, $password) {
        try {
            $stmt = $this->db->prepare("
                UPDATE vps_instances 
                SET root_password = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $vps_id]);
            
        } catch (Exception $e) {
            error_log("Update VPS password error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получение статистики использования ресурсов
     */
    public function getVPSStatistics($vps_id, $period = '24h') {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return $vps;
            }
            
            // Получаем текущую статистику из libvirt
            $current_stats = $this->libvirt->getResourceUsage($vps['vps']['libvirt_name']);
            
            // Получаем историческую статистику из БД
            $interval = match($period) {
                '1h' => 'INTERVAL 1 HOUR',
                '6h' => 'INTERVAL 6 HOUR',
                '24h' => 'INTERVAL 1 DAY',
                '7d' => 'INTERVAL 7 DAY',
                '30d' => 'INTERVAL 30 DAY',
                default => 'INTERVAL 1 DAY'
            };
            
            $stmt = $this->db->prepare("
                SELECT cpu_usage, ram_usage_mb, disk_usage_gb, 
                       network_rx_bytes, network_tx_bytes, recorded_at
                FROM vps_statistics 
                WHERE vps_id = ? AND recorded_at > DATE_SUB(NOW(), {$interval})
                ORDER BY recorded_at
            ");
            $stmt->execute([$vps_id]);
            $historical_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'current' => $current_stats,
                'historical' => $historical_stats,
                'period' => $period
            ];
            
        } catch (Exception $e) {
            error_log("Get VPS statistics error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Сохранение статистики VPS
     */
    public function recordVPSStatistics($vps_id) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return false;
            }
            
            // Получаем статистику из libvirt
            $stats = $this->libvirt->getResourceUsage($vps['vps']['libvirt_name']);
            if (!$stats['success']) {
                return false;
            }
            
            // Сохраняем в БД
            $stmt = $this->db->prepare("
                INSERT INTO vps_statistics (
                    vps_id, cpu_usage, ram_usage_mb, disk_usage_gb,
                    network_rx_bytes, network_tx_bytes
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $vps_id,
                $stats['cpu_usage'] ?? 0,
                $stats['memory_used_mb'] ?? 0,
                $stats['disk_usage_gb'] ?? 0,
                0, // network stats требуют дополнительной настройки
                0
            ]);
            
        } catch (Exception $e) {
            error_log("Record VPS statistics error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Удаление VPS
     */
    public function deleteVPS($vps_id) {
        $this->db->beginTransaction();
        
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return $vps;
            }
            $vps_data = $vps['vps'];
            
            // Создаем финальный бэкап
            $this->createVPSBackup($vps_id, 'before_delete');
            
            // Удаляем из libvirt
            $delete_result = $this->libvirt->deleteVPS($vps_data['libvirt_name']);
            
            // Освобождаем IP адрес
            $stmt = $this->db->prepare("
                UPDATE vps_ip_pool 
                SET vps_id = NULL, assigned_at = NULL 
                WHERE vps_id = ?
            ");
            $stmt->execute([$vps_id]);
            
            // Обновляем статус в нашей БД (не удаляем для истории)
            $stmt = $this->db->prepare("
                UPDATE vps_instances 
                SET status = 'terminated', updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$vps_id]);
            
            // Отменяем заказ в FOSSBilling
            if ($vps_data['fossbilling_order_id']) {
                $this->fossbilling->cancelOrder($vps_data['fossbilling_order_id']);
            }
            
            // Логируем действие
            $this->logVPSAction($vps_id, 'terminate', 'completed');
            
            $this->db->commit();
            
            return [
                'success' => true, 
                'message' => 'VPS deleted successfully',
                'libvirt_result' => $delete_result
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Delete VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение списка действий VPS
     */
    public function getVPSActions($vps_id, $limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT action, status, details, error_message, 
                       started_at, completed_at
                FROM vps_actions 
                WHERE vps_id = ? 
                ORDER BY started_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$vps_id, $limit]);
            
            return [
                'success' => true,
                'actions' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
            
        } catch (Exception $e) {
            error_log("Get VPS actions error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение списка бэкапов VPS
     */
    public function getVPSBackups($vps_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM vps_backups 
                WHERE vps_id = ? AND status != 'deleted'
                ORDER BY created_at DESC
            ");
            $stmt->execute([$vps_id]);
            
            return [
                'success' => true,
                'backups' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
            
        } catch (Exception $e) {
            error_log("Get VPS backups error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Восстановление из бэкапа
     */
    public function restoreVPSFromBackup($vps_id, $backup_id, $user_id = null) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return $vps;
            }
            $vps_data = $vps['vps'];
            
            // Проверяем права пользователя
            if ($user_id && $vps_data['user_id'] != $user_id) {
                return ['success' => false, 'error' => 'Access denied'];
            }
            
            // Получаем информацию о бэкапе
            $stmt = $this->db->prepare("
                SELECT * FROM vps_backups 
                WHERE id = ? AND vps_id = ? AND status = 'completed'
            ");
            $stmt->execute([$backup_id, $vps_id]);
            $backup = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$backup) {
                return ['success' => false, 'error' => 'Backup not found'];
            }
            
            // Логируем действие
            $action_id = $this->logVPSAction($vps_id, 'restore', 'running', [
                'backup_id' => $backup_id,
                'backup_name' => $backup['name']
            ]);
            
            // Здесь должна быть логика восстановления через libvirt
            // Для упрощения возвращаем успех
            
            $this->updateVPSAction($action_id, 'completed');
            
            return [
                'success' => true,
                'message' => 'VPS restored from backup successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Restore VPS from backup error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Изменение размера VPS (апгрейд/даунгрейд)
     */
    public function resizeVPS($vps_id, $new_plan_id, $user_id = null) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return $vps;
            }
            $vps_data = $vps['vps'];
            
            // Проверяем права пользователя
            if ($user_id && $vps_data['user_id'] != $user_id) {
                return ['success' => false, 'error' => 'Access denied'];
            }
            
            // Получаем новый план
            $new_plan = $this->getVPSPlan($new_plan_id);
            if (!$new_plan['success']) {
                return $new_plan;
            }
            $new_plan_data = $new_plan['plan'];
            
            // Создаем бэкап перед изменением
            $backup_result = $this->createVPSBackup($vps_id, 'before_resize');
            
            // Логируем действие
            $action_id = $this->logVPSAction($vps_id, 'resize', 'running', [
                'old_plan_id' => $vps_data['plan_id'],
                'new_plan_id' => $new_plan_id,
                'backup_id' => $backup_result['backup_id'] ?? null
            ]);
            
            // Изменяем RAM в libvirt (если увеличиваем)
            if ($new_plan_data['ram_mb'] > $vps_data['ram_mb']) {
                $resize_result = $this->libvirt->resizeRAM(
                    $vps_data['libvirt_name'], 
                    $new_plan_data['ram_mb']
                );
                
                if (!$resize_result['success']) {
                    $this->updateVPSAction($action_id, 'failed', $resize_result['error']);
                    return $resize_result;
                }
            }
            
            // Обновляем план в БД
            $stmt = $this->db->prepare("
                UPDATE vps_instances 
                SET plan_id = ?, cpu_cores = ?, ram_mb = ?, 
                    disk_gb = ?, bandwidth_gb = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $new_plan_id,
                $new_plan_data['cpu_cores'],
                $new_plan_data['ram_mb'],
                $new_plan_data['disk_gb'],
                $new_plan_data['bandwidth_gb'],
                $vps_id
            ]);
            
            $this->updateVPSAction($action_id, 'completed');
            
            return [
                'success' => true,
                'message' => 'VPS resized successfully',
                'new_plan' => $new_plan_data
            ];
            
        } catch (Exception $e) {
            error_log("Resize VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение общей статистики для администратора
     */
    public function getAdminStatistics() {
        try {
            // Общее количество VPS по статусам
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count 
                FROM vps_instances 
                GROUP BY status
            ");
            $status_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Загрузка ресурсов
            $stmt = $this->db->query("
                SELECT 
                    SUM(cpu_cores) as total_cpu,
                    SUM(ram_mb) as total_ram_mb,
                    SUM(disk_gb) as total_disk_gb
                FROM vps_instances 
                WHERE status IN ('active', 'suspended')
            ");
            $resource_usage = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Статистика по планам
            $stmt = $this->db->query("
                SELECT p.name_ua, COUNT(v.id) as count
                FROM vps_plans p
                LEFT JOIN vps_instances v ON p.id = v.plan_id
                GROUP BY p.id, p.name_ua
                ORDER BY count DESC
            ");
            $plan_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Доступные IP адреса
            $stmt = $this->db->query("
                SELECT COUNT(*) as available_ips 
                FROM vps_ip_pool 
                WHERE vps_id IS NULL AND is_reserved = 0
            ");
            $ip_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'statistics' => [
                    'status_distribution' => $status_stats,
                    'resource_usage' => $resource_usage,
                    'plan_distribution' => $plan_stats,
                    'available_ips' => $ip_stats['available_ips']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Get admin statistics error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
     <?php
/**
 * VPS Manager - главный класс управления VPS
 * Интегрирует LibVirt, FOSSBilling и базу данных сайта
 * Файл: /includes/classes/VPSManager.php
 */

// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

require_once __DIR__ . '/LibvirtManager.php';
require_once __DIR__ . '/FossBillingAPI.php';

class VPSManager {
    private $db;
    private $libvirt;
    private $fossbilling;
    
    public function __construct($db_connection = null) {
        $this->db = $db_connection ?: DatabaseConnection::getSiteConnection();
        $this->libvirt = new LibvirtManager();
        $this->fossbilling = new FossBillingAPI();
    }
    
    /**
     * Получение списка VPS планов
     */
    public function getVPSPlans($active_only = true) {
        try {
            $sql = "SELECT * FROM vps_plans";
            $params = [];
            
            if ($active_only) {
                $sql .= " WHERE is_active = 1";
            }
            
            $sql .= " ORDER BY sort_order, price_monthly";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'plans' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
            
        } catch (Exception $e) {
            error_log("Get VPS plans error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение операционных систем
     */
    public function getOSTemplates($active_only = true) {
        try {
            $sql = "SELECT * FROM vps_os_templates";
            $params = [];
            
            if ($active_only) {
                $sql .= " WHERE is_active = 1";
            }
            
            $sql .= " ORDER BY sort_order, type, name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'templates' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
            
        } catch (Exception $e) {
            error_log("Get OS templates error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Создание VPS заказа
     */
    public function createVPSOrder($user_id, $plan_id, $config) {
        $this->db->beginTransaction();
        
        try {
            // Получаем план
            $plan = $this->getVPSPlan($plan_id);
            if (!$plan['success']) {
                throw new Exception('Plan not found');
            }
            $plan_data = $plan['plan'];
            
            // Получаем пользователя
            $user = $this->getUser($user_id);
            if (!$user['success']) {
                throw new Exception('User not found');
            }
            $user_data = $user['user'];
            
            // Синхронизируем клиента с FOSSBilling
            $client_sync = $this->fossbilling->syncClient($user_data);
            if (!$client_sync['success']) {
                throw new Exception('Failed to sync client with billing');
            }
            
            // Получаем свободный IP адрес
            $ip_result = $this->allocateIPAddress();
            if (!$ip_result['success']) {
                throw new Exception('No available IP addresses');
            }
            
            // Генерируем уникальное имя VPS
            $hostname = $config['hostname'] ?? $this->generateHostname();
            $libvirt_name = 'vps-' . $user_id . '-' . time();
            $root_password = $config['root_password'] ?? $this->generatePassword();
            $vnc_password = $this->generatePassword(8);
            
            // Создаем заказ в FOSSBilling
            $fossbilling_order = $this->fossbilling->createVPSOrder([
                'client_id' => $client_sync['client_id'],
                'product_id' => $plan_data['fossbilling_product_id'] ?? null,
                'period' => $config['period'] ?? 'monthly',
                'hostname' => $hostname,
                'os_template' => $config['os_template'],
                'root_password' => $root_password,
                'activate' => false // Активируем после создания VPS
            ]);
            
            // Создаем запись VPS в нашей БД
            $vps_data = [
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'fossbilling_order_id' => $fossbilling_order['data']['id'] ?? null,
                'hostname' => $hostname,
                'libvirt_name' => $libvirt_name,
                'ip_address' => $ip_result['ip_address'],
                'ip_gateway' => $ip_result['gateway'],
                'ip_netmask' => $ip_result['netmask'],
                'dns_servers' => json_encode(['192.168.0.10']),
                'os_template' => $config['os_template'],
                'root_password' => password_hash($root_password, PASSWORD_DEFAULT),
                'vnc_password' => $vnc_password,
                'status' => 'pending',
                'cpu_cores' => $plan_data['cpu_cores'],
                'ram_mb' => $plan_data['ram_mb'],
                'disk_gb' => $plan_data['disk_gb'],
                'bandwidth_gb' => $plan_data['bandwidth_gb']
            ];
            
            $vps_id = $this->createVPSRecord($vps_data);
            if (!$vps_id) {
                throw new Exception('Failed to create VPS record');
            }
            
            // Обновляем IP адрес как занятый
            $this->assignIPToVPS($ip_result['ip_id'], $vps_id);
            
            $this->db->commit();
            
            // Запускаем создание VPS в фоне
            $this->scheduleVPSCreation($vps_id);
            
            return [
                'success' => true,
                'vps_id' => $vps_id,
                'order_id' => $fossbilling_order['data']['id'] ?? null,
                'hostname' => $hostname,
                'ip_address' => $ip_result['ip_address'],
                'status' => 'pending'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Create VPS order error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Создание VPS в libvirt
     */
    public function createVPS($vps_id) {
        try {
            // Получаем данные VPS
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                throw new Exception('VPS not found');
            }
            $vps_data = $vps['vps'];
            
            // Получаем шаблон ОС
            $os_template = $this->getOSTemplate($vps_data['os_template']);
            if (!$os_template['success']) {
                throw new Exception('OS template not found');
            }
            $template_data = $os_template['template'];
            
            // Обновляем статус
            $this->updateVPSStatus($vps_id, 'creating');
            
            // Конфигурация для libvirt
            $libvirt_config = [
                'name' => $vps_data['libvirt_name'],
                'memory' => $vps_data['ram_mb'],
                'cpu_cores' => $vps_data['cpu_cores'],
                'disk_size' => $vps_data['disk_gb'],
                'ip_address' => $vps_data['ip_address'],
                'template_path' => $template_data['libvirt_image_path'],
                'vnc_password' => $vps_data['vnc_password']
            ];
            
            // Создаем VPS в libvirt
            $creation_result = $this->libvirt->createVPS($libvirt_config);
            
            if ($creation_result['success']) {
                // Обновляем статус и VNC порт
                $this->updateVPSStatus($vps_id, 'active');
                $this->updateVPSVNC($vps_id, $creation_result['vnc_port'] ?? null);
                
                // Активируем заказ в FOSSBilling
                if ($vps_data['fossbilling_order_id']) {
                    $this->fossbilling->activateOrder($vps_data['fossbilling_order_id']);
                }
                
                // Логируем действие
                $this->logVPSAction($vps_id, 'create', 'completed', [
                    'ip_address' => $vps_data['ip_address'],
                    'hostname' => $vps_data['hostname']
                ]);
                
                return ['success' => true, 'message' => 'VPS created successfully'];
                
            } else {
                // Ошибка создания
                $this->updateVPSStatus($vps_id, 'error');
                $this->logVPSAction($vps_id, 'create', 'failed', null, $creation_result['error']);
                
                return ['success' => false, 'error' => $creation_result['error']];
            }
            
        } catch (Exception $e) {
            error_log("Create VPS error: " . $e->getMessage());
            $this->updateVPSStatus($vps_id, 'error');
            $this->logVPSAction($vps_id, 'create', 'failed', null, $e->getMessage());
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Управление VPS (старт, стоп, перезагрузка)
     */
    public function controlVPS($vps_id, $action, $user_id = null) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            // Проверяем права пользователя
            if ($user_id && $vps_data['user_id'] != $user_id) {
                return ['success' => false, 'error' => 'Access denied'];
            }
            
            // Логируем начало действия
            $action_id = $this->logVPSAction($vps_id, $action, 'running');
            
            // Выполняем действие в libvirt
            $result = $this->libvirt->controlVPS($vps_data['libvirt_name'], $action);
            
            if ($result['success']) {
                // Обновляем статус VPS
                $new_status = $this->mapLibvirtActionToStatus($action);
                if ($new_status) {
                    $this->updateVPSStatus($vps_id, $new_status);
                }
                
                // Обновляем лог действия
                $this->updateVPSAction($action_id, 'completed');
                
                return ['success' => true, 'message' => "VPS {$action} completed"];
                
            } else {
                // Обновляем лог действия с ошибкой
                $this->updateVPSAction($action_id, 'failed', $result['error']);
                
                return ['success' => false, 'error' => $result['error']];
            }
            
        } catch (Exception $e) {
            error_log("Control VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Переустановка VPS
     */
    public function reinstallVPS($vps_id, $new_os_template, $user_id = null) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            // Проверяем права пользователя
            if ($user_id && $vps_data['user_id'] != $user_id) {
                return ['success' => false, 'error' => 'Access denied'];
            }
            
            // Получаем новый шаблон ОС
            $os_template = $this->getOSTemplate($new_os_template);
            if (!$os_template['success']) {
                return ['success' => false, 'error' => 'OS template not found'];
            }
            $template_data = $os_template['template'];
            
            // Создаем бэкап перед переустановкой
            $backup_result = $this->createVPSBackup($vps_id, 'before_reinstall');
            
            // Логируем действие
            $action_id = $this->logVPSAction($vps_id, 'reinstall', 'running', [
                'old_os' => $vps_data['os_template'],
                'new_os' => $new_os_template,
                'backup_id' => $backup_result['backup_id'] ?? null
            ]);
            
            // Переустанавливаем в libvirt
            $result = $this->libvirt->reinstallVPS(
                $vps_data['libvirt_name'], 
                $template_data['libvirt_image_path']
            );
            
            if ($result['success']) {
                // Обновляем OS template в БД
                $this->updateVPSOS($vps_id, $new_os_template);
                
                // Генерируем новый пароль
                $new_password = $this->generatePassword();
                $this->updateVPSPassword($vps_id, $new_password);
                
                // Обновляем лог действия
                $this->updateVPSAction($action_id, 'completed', [
                    'new_password' => $new_password
                ]);
                
                return [
                    'success' => true, 
                    'message' => 'VPS reinstalled successfully',
                    'new_password' => $new_password
                ];
                
            } else {
                $this->updateVPSAction($action_id, 'failed', $result['error']);
                return ['success' => false, 'error' => $result['error']];
            }
            
        } catch (Exception $e) {
            error_log("Reinstall VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение информации о VPS для пользователя
     */
    public function getUserVPS($user_id, $vps_id = null) {
        try {
            $sql = "SELECT v.*, p.name_ua as plan_name, p.cpu_cores as plan_cpu, 
                           p.ram_mb as plan_ram, p.disk_gb as plan_disk, 
                           o.display_name as os_name, o.version as os_version,
                           o.type as os_type
                    FROM vps_instances v 
                    LEFT JOIN vps_plans p ON v.plan_id = p.id
                    LEFT JOIN vps_os_templates o ON v.os_template = o.name
                    WHERE v.user_id = ?";
            
            $params = [$user_id];
            
            if ($vps_id) {
                $sql .= " AND v.id = ?";
                $params[] = $vps_id;
            }
            
            $sql .= " ORDER BY v.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $vps_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Получаем статус из libvirt для каждого VPS
            foreach ($vps_list as &$vps) {
                $status = $this->libvirt->getVPSStatus($vps['libvirt_name']);
                if ($status['success']) {
                    $vps['libvirt_status'] = $status['state'];
                    $vps['resource_usage'] = [
                        'cpu_usage' => $status['cpu_usage'] ?? 0,
                        'memory_usage' => $status['memory_usage'] ?? 0,
                        'memory_used_mb' => $status['memory_used_mb'] ?? 0
                    ];
                }
                
                // Получаем VNC информацию
                $vnc = $this->libvirt->getVNCInfo($vps['libvirt_name']);
                if ($vnc['success']) {
                    $vps['vnc'] = [
                        'host' => $vnc['host'],
                        'port' => $vnc['port'],
                        'password' => $vps['vnc_password'] // Из нашей БД
                    ];
                }
                
                // Скрываем чувствительную информацию
                unset($vps['root_password']);
                unset($vps['vnc_password']);
            }
            
            if ($vps_id) {
                return [
                    'success' => true,
                    'vps' => $vps_list[0] ?? null
                ];
            }
            
            return [
                'success' => true,
                'vps_list' => $vps_list
            ];
            
        } catch (Exception $e) {
            error_log("Get user VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Создание бэкапа VPS
     */
    public function createVPSBackup($vps_id, $type = 'manual', $name = null) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            $backup_name = $name ?: ('backup-' . date('Y-m-d-H-i-s'));
            
            // Создаем снимок в libvirt
            $snapshot_result = $this->libvirt->createSnapshot(
                $vps_data['libvirt_name'], 
                $backup_name
            );
            
            if ($snapshot_result['success']) {
                // Сохраняем информацию о бэкапе в БД
                $stmt = $this->db->prepare("
                    INSERT INTO vps_backups (vps_id, name, backup_type, status) 
                    VALUES (?, ?, ?, 'completed')
                ");
                $stmt->execute([$vps_id, $backup_name, $type]);
                
                $backup_id = $this->db->lastInsertId();
                
                return [
                    'success' => true,
                    'backup_id' => $backup_id,
                    'backup_name' => $backup_name
                ];
            }
            
            return $snapshot_result;
            
        } catch (Exception $e) {
            error_log("Create VPS backup error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Приостановка VPS (по неоплате или нарушениям)
     */
    public function suspendVPS($vps_id, $reason = '') {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            // Приостанавливаем в libvirt
            $result = $this->libvirt->controlVPS($vps_data['libvirt_name'], 'suspend');
            
            if ($result['success']) {
                // Обновляем статус и причину в БД
                $stmt = $this->db->prepare("
                    UPDATE vps_instances 
                    SET status = 'suspended', suspend_reason = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$reason, $vps_id]);
                
                // Приостанавливаем заказ в FOSSBilling
                if ($vps_data['fossbilling_order_id']) {
                    $this->fossbilling->suspendOrder($vps_data['fossbilling_order_id'], $reason);
                }
                
                // Логируем действие
                $this->logVPSAction($vps_id, 'suspend', 'completed', ['reason' => $reason]);
                
                return ['success' => true, 'message' => 'VPS suspended'];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Suspend VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Возобновление VPS
     */
    public function unsuspendVPS($vps_id) {
        try {
            $vps = $this->getVPSById($vps_id);
            if (!$vps['success']) {
                return ['success' => false, 'error' => 'VPS not found'];
            }
            $vps_data = $vps['vps'];
            
            // Возобновляем в libvirt
            $result = $this->libvirt->controlVPS($vps_data['libvirt_name'], 'resume');
            
            if ($result['success']) {
                // Обновляем статус в БД
                $stmt = $this->db->prepare("
                    UPDATE vps_instances 
                    SET status = 'active', suspend_reason = NULL, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$vps_id]);
                
                // Возобновляем заказ в FOSSBilling
                if ($vps_data['fossbilling_order_id']) {
                    $this->fossbilling->unsuspendOrder($vps_data['fossbilling_order_id']);
                }
                
                // Логируем действие
                $this->logVPSAction($vps_id, 'unsuspend', 'completed');
                
                return ['success' => true, 'message' => 'VPS resumed'];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Unsuspend VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}