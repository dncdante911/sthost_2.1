<?php
/**
 * LibvirtManager - класс для управления VPS через libvirt API
 */
class LibvirtManager {
    private $host;
    private $port;
    private $connection;
    
    public function __construct($host = '195.22.131.11', $port = 16509) {
        $this->host = $host;
        $this->port = $port;
    }
    
    /**
     * Подключение к libvirt
     */
    public function connect() {
        try {
            $uri = "qemu+tcp://{$this->host}:{$this->port}/system";
            $this->connection = libvirt_connect($uri, false);
            
            if (!$this->connection) {
                throw new Exception("Failed to connect to libvirt at {$uri}");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("LibvirtManager connect error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Отключение от libvirt
     */
    public function disconnect() {
        if ($this->connection) {
            libvirt_connect_close($this->connection);
        }
    }
    
    /**
     * Получение списка всех VM
     */
    public function listAllDomains() {
        if (!$this->connection) {
            throw new Exception("Not connected to libvirt");
        }
        
        $domains = libvirt_list_all_domains($this->connection);
        $result = [];
        
        foreach ($domains as $domain) {
            $result[] = [
                'name' => libvirt_domain_get_name($domain),
                'uuid' => libvirt_domain_get_uuid_string($domain),
                'state' => $this->getDomainState($domain),
                'info' => libvirt_domain_get_info($domain)
            ];
        }
        
        return $result;
    }
    
    /**
     * Создание VPS
     */
    public function createVPS($config) {
        try {
            if (!$this->connection) {
                $this->connect();
            }
            
            // Генерация XML конфигурации VM
            $xml = $this->generateVMXML($config);
            
            // Создание домена
            $domain = libvirt_domain_define_xml($this->connection, $xml);
            if (!$domain) {
                throw new Exception("Failed to define domain");
            }
            
            // Запуск VM
            if (!libvirt_domain_start($domain)) {
                throw new Exception("Failed to start domain");
            }
            
            return [
                'success' => true,
                'uuid' => libvirt_domain_get_uuid_string($domain),
                'name' => $config['name'],
                'message' => 'VPS created successfully'
            ];
            
        } catch (Exception $e) {
            error_log("LibvirtManager createVPS error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Удаление VPS
     */
    public function deleteVPS($vmName) {
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $vmName);
            if (!$domain) {
                throw new Exception("Domain not found: {$vmName}");
            }
            
            // Остановка если работает
            if (libvirt_domain_is_active($domain)) {
                libvirt_domain_destroy($domain);
            }
            
            // Удаление конфигурации
            if (!libvirt_domain_undefine($domain)) {
                throw new Exception("Failed to undefine domain");
            }
            
            return ['success' => true, 'message' => 'VPS deleted successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Запуск VPS
     */
    public function startVPS($vmName) {
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $vmName);
            if (!$domain) {
                throw new Exception("Domain not found: {$vmName}");
            }
            
            if (libvirt_domain_is_active($domain)) {
                return ['success' => true, 'message' => 'VPS already running'];
            }
            
            if (!libvirt_domain_start($domain)) {
                throw new Exception("Failed to start domain");
            }
            
            return ['success' => true, 'message' => 'VPS started successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Остановка VPS
     */
    public function stopVPS($vmName) {
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $vmName);
            if (!$domain) {
                throw new Exception("Domain not found: {$vmName}");
            }
            
            if (!libvirt_domain_is_active($domain)) {
                return ['success' => true, 'message' => 'VPS already stopped'];
            }
            
            if (!libvirt_domain_shutdown($domain)) {
                // Принудительная остановка если graceful shutdown не работает
                libvirt_domain_destroy($domain);
            }
            
            return ['success' => true, 'message' => 'VPS stopped successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Перезагрузка VPS
     */
    public function rebootVPS($vmName) {
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $vmName);
            if (!$domain) {
                throw new Exception("Domain not found: {$vmName}");
            }
            
            if (!libvirt_domain_reboot($domain)) {
                throw new Exception("Failed to reboot domain");
            }
            
            return ['success' => true, 'message' => 'VPS rebooted successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Получение информации о VPS
     */
    public function getVPSInfo($vmName) {
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $vmName);
            if (!$domain) {
                throw new Exception("Domain not found: {$vmName}");
            }
            
            $info = libvirt_domain_get_info($domain);
            $xml = libvirt_domain_get_xml_desc($domain);
            
            return [
                'success' => true,
                'name' => libvirt_domain_get_name($domain),
                'uuid' => libvirt_domain_get_uuid_string($domain),
                'state' => $this->getDomainState($domain),
                'info' => $info,
                'xml' => $xml
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Получение статистики VPS
     */
    public function getVPSStats($vmName) {
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $vmName);
            if (!$domain) {
                throw new Exception("Domain not found: {$vmName}");
            }
            
            $info = libvirt_domain_get_info($domain);
            
            // Получаем статистику блочных устройств
            $blockStats = libvirt_domain_get_block_info($domain, '/var/lib/libvirt/images/vps/' . $vmName . '.qcow2');
            
            // Получаем сетевую статистику (если доступно)
            $netStats = @libvirt_domain_interface_stats($domain, 'vnet0');
            
            return [
                'success' => true,
                'cpu_time' => $info['cpuTime'],
                'memory_used' => $info['memory'],
                'memory_max' => $info['maxMem'],
                'vcpus' => $info['nrVirtCpu'],
                'state' => $info['state'],
                'disk_stats' => $blockStats,
                'network_stats' => $netStats ?: null
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Получение VNC информации
     */
    public function getVNCInfo($vmName) {
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $vmName);
            if (!$domain) {
                throw new Exception("Domain not found: {$vmName}");
            }
            
            $xml = libvirt_domain_get_xml_desc($domain);
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            
            $graphics = $dom->getElementsByTagName('graphics');
            if ($graphics->length > 0) {
                $vnc = $graphics->item(0);
                return [
                    'success' => true,
                    'port' => $vnc->getAttribute('port'),
                    'host' => $this->host,
                    'password' => $vnc->getAttribute('passwd')
                ];
            }
            
            return ['success' => false, 'message' => 'VNC not configured'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Генерация XML конфигурации для VM
     */
    private function generateVMXML($config) {
        $vncPort = rand(5900, 5999);
        $vncPassword = substr(bin2hex(random_bytes(4)), 0, 8);
        
        $xml = <<<XML
<domain type='kvm'>
    <name>{$config['name']}</name>
    <memory unit='MiB'>{$config['ram_mb']}</memory>
    <currentMemory unit='MiB'>{$config['ram_mb']}</currentMemory>
    <vcpu placement='static'>{$config['cpu_cores']}</vcpu>
    <os>
        <type arch='x86_64' machine='pc-q35-2.12'>hvm</type>
        <boot dev='hd'/>
    </os>
    <features>
        <acpi/>
        <apic/>
        <vmport state='off'/>
    </features>
    <cpu mode='host-passthrough' check='none'/>
    <clock offset='utc'>
        <timer name='rtc' tickpolicy='catchup'/>
        <timer name='pit' tickpolicy='delay'/>
        <timer name='hpet' present='no'/>
    </clock>
    <on_poweroff>destroy</on_poweroff>
    <on_reboot>restart</on_reboot>
    <on_crash>destroy</on_crash>
    <devices>
        <emulator>/usr/bin/qemu-system-x86_64</emulator>
        <disk type='file' device='disk'>
            <driver name='qemu' type='qcow2'/>
            <source file='/var/lib/libvirt/images/vps/{$config['name']}.qcow2'/>
            <target dev='vda' bus='virtio'/>
        </disk>
        <interface type='network'>
            <source network='vps-network'/>
            <model type='virtio'/>
        </interface>
        <console type='pty'>
            <target type='serial' port='0'/>
        </console>
        <graphics type='vnc' port='{$vncPort}' autoport='yes' listen='0.0.0.0' passwd='{$vncPassword}'/>
        <video>
            <model type='cirrus' vram='16384' heads='1' primary='yes'/>
        </video>
        <memballoon model='virtio'/>
    </devices>
</domain>
XML;
        
        return $xml;
    }
    
    /**
     * Получение состояния домена
     */
    private function getDomainState($domain) {
        $states = [
            VIR_DOMAIN_NOSTATE => 'nostate',
            VIR_DOMAIN_RUNNING => 'running', 
            VIR_DOMAIN_BLOCKED => 'blocked',
            VIR_DOMAIN_PAUSED => 'paused',
            VIR_DOMAIN_SHUTDOWN => 'shutdown',
            VIR_DOMAIN_SHUTOFF => 'shutoff',
            VIR_DOMAIN_CRASHED => 'crashed'
        ];
        
        $state = libvirt_domain_get_state($domain);
        return $states[$state[0]] ?? 'unknown';
    }
    
    /**
     * Создание диска из шаблона
     */
    public function createDiskFromTemplate($templateName, $newDiskPath, $sizeGB) {
        $command = sprintf(
            'qemu-img create -f qcow2 -b /var/lib/libvirt/images/templates/%s %s %dG',
            escapeshellarg($templateName),
            escapeshellarg($newDiskPath),
            (int)$sizeGB
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Failed to create disk from template: " . implode("\n", $output));
        }
        
        return true;
    }
    
    /**
     * Получение доступного IP адреса
     */
    public function getAvailableIP() {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT ip_address FROM vps_ip_pool WHERE is_reserved = 0 LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new Exception("No available IP addresses");
        }
        
        return $result['ip_address'];
    }
    
    /**
     * Резервирование IP адреса
     */
    public function reserveIP($ipAddress, $vpsId) {
        global $pdo;
        
        $stmt = $pdo->prepare("UPDATE vps_ip_pool SET is_reserved = 1, vps_id = ?, reserved_at = NOW() WHERE ip_address = ? AND is_reserved = 0");
        return $stmt->execute([$vpsId, $ipAddress]);
    }
    
    /**
     * Освобождение IP адреса
     */
    public function releaseIP($ipAddress) {
        global $pdo;
        
        $stmt = $pdo->prepare("UPDATE vps_ip_pool SET is_reserved = 0, vps_id = NULL, reserved_at = NULL WHERE ip_address = ?");
        return $stmt->execute([$ipAddress]);
    }
}

/**
 * VPSManager - основной класс для управления VPS
 */
class VPSManager {
    private $libvirt;
    private $pdo;
    
    public function __construct() {
        $this->libvirt = new LibvirtManager();
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Создание нового VPS
     */
    public function createVPS($userId, $planId, $osTemplateId, $hostname, $domainName = null) {
        try {
            // Получаем информацию о плане
            $stmt = $this->pdo->prepare("SELECT * FROM vps_plans WHERE id = ? AND is_active = 1");
            $stmt->execute([$planId]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$plan) {
                throw new Exception("VPS plan not found");
            }
            
            // Получаем информацию о шаблоне ОС
            $stmt = $this->pdo->prepare("SELECT * FROM vps_os_templates WHERE id = ? AND is_active = 1");
            $stmt->execute([$osTemplateId]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$template) {
                throw new Exception("OS template not found");
            }
            
            // Проверяем уникальность hostname
            $stmt = $this->pdo->prepare("SELECT id FROM vps_instances WHERE hostname = ?");
            $stmt->execute([$hostname]);
            if ($stmt->fetch()) {
                throw new Exception("Hostname already exists");
            }
            
            // Получаем доступный IP
            $ipAddress = $this->libvirt->getAvailableIP();
            
            // Генерируем имя VM в libvirt
            $vmName = 'vps_' . $userId . '_' . uniqid();
            $rootPassword = $this->generateSecurePassword();
            $vncPassword = substr(bin2hex(random_bytes(4)), 0, 8);
            
            // Начинаем транзакцию
            $this->pdo->beginTransaction();
            
            try {
                // Создаем запись в БД
                $stmt = $this->pdo->prepare("
                    INSERT INTO vps_instances 
                    (user_id, plan_id, os_template_id, hostname, domain_name, libvirt_name, 
                     ip_address, root_password, vnc_password, cpu_cores, ram_mb, storage_gb, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'creating')
                ");
                
                $stmt->execute([
                    $userId, $planId, $osTemplateId, $hostname, $domainName, $vmName,
                    $ipAddress, password_hash($rootPassword, PASSWORD_DEFAULT), $vncPassword,
                    $plan['cpu_cores'], $plan['ram_mb'], $plan['storage_gb']
                ]);
                
                $vpsId = $this->pdo->lastInsertId();
                
                // Резервируем IP
                $this->libvirt->reserveIP($ipAddress, $vpsId);
                
                // Подключаемся к libvirt
                if (!$this->libvirt->connect()) {
                    throw new Exception("Failed to connect to libvirt");
                }
                
                // Создаем диск из шаблона
                $diskPath = "/var/lib/libvirt/images/vps/{$vmName}.qcow2";
                $this->libvirt->createDiskFromTemplate($template['template_name'], $diskPath, $plan['storage_gb']);
                
                // Конфигурация для создания VM
                $vmConfig = [
                    'name' => $vmName,
                    'cpu_cores' => $plan['cpu_cores'],
                    'ram_mb' => $plan['ram_mb'],
                    'disk_path' => $diskPath,
                    'ip_address' => $ipAddress,
                    'vnc_password' => $vncPassword
                ];
                
                // Создаем VM в libvirt
                $result = $this->libvirt->createVPS($vmConfig);
                
                if (!$result['success']) {
                    throw new Exception("Failed to create VPS: " . $result['message']);
                }
                
                // Обновляем запись с UUID
                $stmt = $this->pdo->prepare("UPDATE vps_instances SET libvirt_uuid = ?, status = 'active', power_state = 'running' WHERE id = ?");
                $stmt->execute([$result['uuid'], $vpsId]);
                
                // Логируем операцию
                $this->logOperation($vpsId, $userId, 'create', ['hostname' => $hostname, 'ip' => $ipAddress], 'completed');
                
                $this->pdo->commit();
                $this->libvirt->disconnect();
                
                return [
                    'success' => true,
                    'vps_id' => $vpsId,
                    'hostname' => $hostname,
                    'ip_address' => $ipAddress,
                    'root_password' => $rootPassword,
                    'vnc_password' => $vncPassword,
                    'message' => 'VPS created successfully'
                ];
                
            } catch (Exception $e) {
                $this->pdo->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            // Обновляем статус на ошибку
            if (isset($vpsId)) {
                $stmt = $this->pdo->prepare("UPDATE vps_instances SET status = 'error' WHERE id = ?");
                $stmt->execute([$vpsId]);
                
                $this->logOperation($vpsId, $userId, 'create', null, 'failed', $e->getMessage());
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Получение списка VPS пользователя
     */
    public function getUserVPSList($userId) {
        $stmt = $this->pdo->prepare("
            SELECT vi.*, vp.name_ua as plan_name, vot.display_name_ua as os_name
            FROM vps_instances vi
            LEFT JOIN vps_plans vp ON vi.plan_id = vp.id
            LEFT JOIN vps_os_templates vot ON vi.os_template_id = vot.id
            WHERE vi.user_id = ?
            ORDER BY vi.created_at DESC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Получение информации о VPS
     */
    public function getVPSInfo($vpsId, $userId) {
        $stmt = $this->pdo->prepare("
            SELECT vi.*, vp.name_ua as plan_name, vot.display_name_ua as os_name
            FROM vps_instances vi
            LEFT JOIN vps_plans vp ON vi.plan_id = vp.id
            LEFT JOIN vps_os_templates vot ON vi.os_template_id = vot.id
            WHERE vi.id = ? AND vi.user_id = ?
        ");
        
        $stmt->execute([$vpsId, $userId]);
        $vps = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vps) {
            return ['success' => false, 'message' => 'VPS not found'];
        }
        
        // Получаем актуальную информацию от libvirt
        if ($this->libvirt->connect() && $vps['libvirt_name']) {
            $libvirtInfo = $this->libvirt->getVPSInfo($vps['libvirt_name']);
            if ($libvirtInfo['success']) {
                $vps['libvirt_info'] = $libvirtInfo;
                $vps['current_state'] = $libvirtInfo['state'];
            }
            $this->libvirt->disconnect();
        }
        
        return ['success' => true, 'data' => $vps];
    }
    
    /**
     * Управление питанием VPS
     */
    public function controlVPS($vpsId, $userId, $action) {
        $allowedActions = ['start', 'stop', 'restart'];
        
        if (!in_array($action, $allowedActions)) {
            return ['success' => false, 'message' => 'Invalid action'];
        }
        
        // Получаем VPS
        $vpsInfo = $this->getVPSInfo($vpsId, $userId);
        if (!$vpsInfo['success']) {
            return $vpsInfo;
        }
        
        $vps = $vpsInfo['data'];
        
        if (!$vps['libvirt_name']) {
            return ['success' => false, 'message' => 'VPS not properly configured'];
        }
        
        try {
            // Логируем начало операции
            $operationId = $this->logOperation($vpsId, $userId, $action, null, 'running');
            
            if (!$this->libvirt->connect()) {
                throw new Exception("Failed to connect to libvirt");
            }
            
            $result = null;
            switch ($action) {
                case 'start':
                    $result = $this->libvirt->startVPS($vps['libvirt_name']);
                    $newPowerState = 'running';
                    break;
                case 'stop':
                    $result = $this->libvirt->stopVPS($vps['libvirt_name']);
                    $newPowerState = 'shutoff';
                    break;
                case 'restart':
                    $result = $this->libvirt->rebootVPS($vps['libvirt_name']);
                    $newPowerState = 'running';
                    break;
            }
            
            $this->libvirt->disconnect();
            
            if ($result['success']) {
                // Обновляем состояние в БД
                $stmt = $this->pdo->prepare("UPDATE vps_instances SET power_state = ? WHERE id = ?");
                $stmt->execute([$newPowerState, $vpsId]);
                
                // Обновляем лог операции
                $this->updateOperationStatus($operationId, 'completed');
                
                return ['success' => true, 'message' => $result['message']];
            } else {
                $this->updateOperationStatus($operationId, 'failed', $result['message']);
                return $result;
            }
            
        } catch (Exception $e) {
            if (isset($operationId)) {
                $this->updateOperationStatus($operationId, 'failed', $e->getMessage());
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Получение статистики VPS
     */
    public function getVPSStats($vpsId, $userId) {
        $vpsInfo = $this->getVPSInfo($vpsId, $userId);
        if (!$vpsInfo['success']) {
            return $vpsInfo;
        }
        
        $vps = $vpsInfo['data'];
        
        if (!$this->libvirt->connect() || !$vps['libvirt_name']) {
            return ['success' => false, 'message' => 'Cannot connect to VPS'];
        }
        
        try {
            $stats = $this->libvirt->getVPSStats($vps['libvirt_name']);
            $this->libvirt->disconnect();
            
            if ($stats['success']) {
                // Сохраняем статистику в БД
                $this->saveStats($vpsId, $stats);
            }
            
            return $stats;
            
        } catch (Exception $e) {
            $this->libvirt->disconnect();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Получение VNC информации
     */
    public function getVNCInfo($vpsId, $userId) {
        $vpsInfo = $this->getVPSInfo($vpsId, $userId);
        if (!$vpsInfo['success']) {
            return $vpsInfo;
        }
        
        $vps = $vpsInfo['data'];
        
        if (!$this->libvirt->connect() || !$vps['libvirt_name']) {
            return ['success' => false, 'message' => 'Cannot connect to VPS'];
        }
        
        try {
            $vncInfo = $this->libvirt->getVNCInfo($vps['libvirt_name']);
            $this->libvirt->disconnect();
            
            return $vncInfo;
            
        } catch (Exception $e) {
            $this->libvirt->disconnect();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Логирование операций
     */
    private function logOperation($vpsId, $userId, $operation, $details = null, $status = 'pending', $errorMessage = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO vps_operations_log (vps_id, user_id, operation, operation_details, status, error_message) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $vpsId, $userId, $operation, 
            $details ? json_encode($details) : null, 
            $status, $errorMessage
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Обновление статуса операции
     */
    private function updateOperationStatus($operationId, $status, $errorMessage = null) {
        $stmt = $this->pdo->prepare("
            UPDATE vps_operations_log 
            SET status = ?, error_message = ?, completed_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$status, $errorMessage, $operationId]);
    }
    
    /**
     * Сохранение статистики
     */
    private function saveStats($vpsId, $stats) {
        $stmt = $this->pdo->prepare("
            INSERT INTO vps_stats (vps_id, cpu_percent, ram_used_mb, ram_total_mb, disk_used_gb, disk_total_gb) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $vpsId,
            0, // CPU percent нужно будет рассчитать из cpuTime
            round($stats['memory_used'] / 1024, 2),
            round($stats['memory_max'] / 1024, 2),
            isset($stats['disk_stats']['capacity']) ? round($stats['disk_stats']['capacity'] / 1024 / 1024 / 1024, 2) : 0,
            0 // Общий размер диска
        ]);
    }
    
    /**
     * Генерация безопасного пароля
     */
    private function generateSecurePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 1, $length);
    }
}
?>