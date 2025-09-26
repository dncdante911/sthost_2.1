<?php
/**
 * VPS Management System
 * Подключение всех необходимых классов для работы с VPS
 */

// Проверяем, что файл подключен корректно
if (!defined('SECURE_ACCESS')) {
    die('Direct access not allowed');
}

// Подключаем классы управления VPS
if (!class_exists('LibvirtManager')) {
    // В реальной установке libvirt-php может отсутствовать
    // Поэтому создаем фолбэк через SSH команды
    
    /**
     * LibvirtManager - класс для управления VPS через SSH команды
     * (Fallback версия когда libvirt-php недоступен)
     */
    class LibvirtManager {
        private $host;
        private $port;
        private $username;
        private $password;
        
        public function __construct($host = '195.22.131.11', $port = 22, $username = 'root', $password = '0607Dm$157') {
            $this->host = $host;
            $this->port = $port;
            $this->username = $username;
            $this->password = $password;
        }
        
        /**
         * Выполнение SSH команды
         */
        private function executeSSHCommand($command) {
            try {
                $connection = ssh2_connect($this->host, $this->port);
                
                if (!$connection) {
                    throw new Exception("Cannot connect to SSH server {$this->host}:{$this->port}");
                }
                
                if (!ssh2_auth_password($connection, $this->username, $this->password)) {
                    throw new Exception("SSH authentication failed");
                }
                
                $stream = ssh2_exec($connection, $command);
                stream_set_blocking($stream, true);
                
                $output = stream_get_contents($stream);
                $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
                $error = stream_get_contents($errorStream);
                
                fclose($stream);
                fclose($errorStream);
                
                return [
                    'success' => empty($error),
                    'output' => $output,
                    'error' => $error
                ];
                
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'output' => '',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        /**
         * Подключение к libvirt (через SSH)
         */
        public function connect() {
            $result = $this->executeSSHCommand('virsh version');
            return $result['success'];
        }
        
        /**
         * Отключение
         */
        public function disconnect() {
            // В SSH варианте нет постоянного соединения
            return true;
        }
        
        /**
         * Получение списка всех VM
         */
        public function listAllDomains() {
            $result = $this->executeSSHCommand('virsh list --all --uuid --name');
            
            if (!$result['success']) {
                return [];
            }
            
            $domains = [];
            $lines = explode("\n", trim($result['output']));
            
            foreach ($lines as $line) {
                $parts = explode(" ", trim($line));
                if (count($parts) >= 2) {
                    $domains[] = [
                        'uuid' => $parts[0],
                        'name' => $parts[1],
                        'state' => $this->getDomainStateByName($parts[1])
                    ];
                }
            }
            
            return $domains;
        }
        
        /**
         * Создание VPS
         */
        public function createVPS($config) {
            try {
                // Создаем диск из шаблона
                $diskPath = "/var/lib/libvirt/images/vps/{$config['name']}.qcow2";
                $templatePath = "/var/lib/libvirt/images/templates/{$config['template_name']}.qcow2";
                
                // Создаем диск
                $createDiskCmd = "qemu-img create -f qcow2 -b {$templatePath} {$diskPath} {$config['storage_gb']}G";
                $result = $this->executeSSHCommand($createDiskCmd);
                
                if (!$result['success']) {
                    throw new Exception("Failed to create disk: " . $result['error']);
                }
                
                // Генерируем XML конфигурацию
                $xml = $this->generateVMXML($config);
                $xmlFile = "/tmp/{$config['name']}.xml";
                
                // Сохраняем XML во временный файл
                $saveXmlCmd = "cat > {$xmlFile} << 'EOF'\n{$xml}\nEOF";
                $result = $this->executeSSHCommand($saveXmlCmd);
                
                if (!$result['success']) {
                    throw new Exception("Failed to save XML config");
                }
                
                // Определяем VM
                $result = $this->executeSSHCommand("virsh define {$xmlFile}");
                if (!$result['success']) {
                    throw new Exception("Failed to define VM: " . $result['error']);
                }
                
                // Запускаем VM
                $result = $this->executeSSHCommand("virsh start {$config['name']}");
                if (!$result['success']) {
                    throw new Exception("Failed to start VM: " . $result['error']);
                }
                
                // Получаем UUID
                $result = $this->executeSSHCommand("virsh domuuid {$config['name']}");
                $uuid = trim($result['output']);
                
                // Удаляем временный файл
                $this->executeSSHCommand("rm {$xmlFile}");
                
                return [
                    'success' => true,
                    'uuid' => $uuid,
                    'name' => $config['name'],
                    'message' => 'VPS created successfully'
                ];
                
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        /**
         * Запуск VPS
         */
        public function startVPS($vmName) {
            $result = $this->executeSSHCommand("virsh start {$vmName}");
            
            return [
                'success' => $result['success'],
                'message' => $result['success'] ? 'VPS started successfully' : $result['error']
            ];
        }
        
        /**
         * Остановка VPS
         */
        public function stopVPS($vmName) {
            $result = $this->executeSSHCommand("virsh shutdown {$vmName}");
            
            if (!$result['success']) {
                // Принудительная остановка
                $result = $this->executeSSHCommand("virsh destroy {$vmName}");
            }
            
            return [
                'success' => $result['success'],
                'message' => $result['success'] ? 'VPS stopped successfully' : $result['error']
            ];
        }
        
        /**
         * Перезагрузка VPS
         */
        public function rebootVPS($vmName) {
            $result = $this->executeSSHCommand("virsh reboot {$vmName}");
            
            return [
                'success' => $result['success'],
                'message' => $result['success'] ? 'VPS rebooted successfully' : $result['error']
            ];
        }
        
        /**
         * Удаление VPS
         */
        public function deleteVPS($vmName) {
            try {
                // Останавливаем VM если работает
                $this->executeSSHCommand("virsh destroy {$vmName}");
                
                // Удаляем конфигурацию
                $result = $this->executeSSHCommand("virsh undefine {$vmName}");
                
                if (!$result['success']) {
                    throw new Exception("Failed to undefine VM: " . $result['error']);
                }
                
                // Удаляем диск
                $diskPath = "/var/lib/libvirt/images/vps/{$vmName}.qcow2";
                $this->executeSSHCommand("rm -f {$diskPath}");
                
                return [
                    'success' => true,
                    'message' => 'VPS deleted successfully'
                ];
                
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        /**
         * Получение информации о VPS
         */
        public function getVPSInfo($vmName) {
            $result = $this->executeSSHCommand("virsh dominfo {$vmName}");
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['error']
                ];
            }
            
            // Получаем UUID
            $uuidResult = $this->executeSSHCommand("virsh domuuid {$vmName}");
            $uuid = trim($uuidResult['output']);
            
            // Получаем состояние
            $state = $this->getDomainStateByName($vmName);
            
            return [
                'success' => true,
                'name' => $vmName,
                'uuid' => $uuid,
                'state' => $state,
                'info' => $result['output']
            ];
        }
        
        /**
         * Получение статистики VPS
         */
        public function getVPSStats($vmName) {
            $result = $this->executeSSHCommand("virsh domstats {$vmName}");
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['error']
                ];
            }
            
            // Парсим статистику
            $stats = [];
            $lines = explode("\n", $result['output']);
            
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $stats[trim($key)] = trim($value);
                }
            }
            
            return [
                'success' => true,
                'cpu_time' => $stats['cpu.time'] ?? 0,
                'memory_used' => $stats['balloon.current'] ?? 0,
                'memory_max' => $stats['balloon.maximum'] ?? 0,
                'vcpus' => $stats['vcpu.maximum'] ?? 1,
                'state' => $this->getDomainStateByName($vmName)
            ];
        }
        
        /**
         * Получение VNC информации
         */
        public function getVNCInfo($vmName) {
            $result = $this->executeSSHCommand("virsh vncdisplay {$vmName}");
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => 'VNC not available: ' . $result['error']
                ];
            }
            
            $vncDisplay = trim($result['output']);
            
            if (empty($vncDisplay)) {
                return [
                    'success' => false,
                    'message' => 'VNC not configured for this VM'
                ];
            }
            
            // VNC display обычно в формате :1, :2, etc.
            $port = 5900 + (int)str_replace(':', '', $vncDisplay);
            
            return [
                'success' => true,
                'host' => $this->host,
                'port' => $port,
                'display' => $vncDisplay
            ];
        }
        
        /**
         * Получение состояния домена по имени
         */
        private function getDomainStateByName($vmName) {
            $result = $this->executeSSHCommand("virsh domstate {$vmName}");
            
            if (!$result['success']) {
                return 'unknown';
            }
            
            return trim($result['output']);
        }
        
        /**
         * Создание диска из шаблона
         */
        public function createDiskFromTemplate($templateName, $newDiskPath, $sizeGB) {
            $templatePath = "/var/lib/libvirt/images/templates/{$templateName}";
            $command = "qemu-img create -f qcow2 -b {$templatePath} {$newDiskPath} {$sizeGB}G";
            
            $result = $this->executeSSHCommand($command);
            
            if (!$result['success']) {
                throw new Exception("Failed to create disk from template: " . $result['error']);
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
        
        /**
         * Генерация XML конфигурации для VM
         */
        private function generateVMXML($config) {
            $vncPort = rand(5900, 5999);
            $vncPassword = substr(bin2hex(random_bytes(4)), 0, 8);
            $macAddress = $this->generateMacAddress();
            
            $xml = <<<XML
<domain type='kvm'>
    <name>{$config['name']}</name>
    <memory unit='MiB'>{$config['ram_mb']}</memory>
    <currentMemory unit='MiB'>{$config['ram_mb']}</currentMemory>
    <vcpu placement='static'>{$config['cpu_cores']}</vcpu>
    <os>
        <type arch='x86_64' machine='pc-q35-4.2'>hvm</type>
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
            <driver name='qemu' type='qcow2' cache='writeback'/>
            <source file='/var/lib/libvirt/images/vps/{$config['name']}.qcow2'/>
            <target dev='vda' bus='virtio'/>
            <address type='pci' domain='0x0000' bus='0x04' slot='0x00' function='0x0'/>
        </disk>
        <controller type='usb' index='0' model='qemu-xhci' ports='15'>
            <address type='pci' domain='0x0000' bus='0x02' slot='0x00' function='0x0'/>
        </controller>
        <controller type='sata' index='0'>
            <address type='pci' domain='0x0000' bus='0x00' slot='0x1f' function='0x2'/>
        </controller>
        <controller type='pci' index='0' model='pcie-root'/>
        <controller type='pci' index='1' model='pcie-root-port'>
            <model name='pcie-root-port'/>
            <target chassis='1' port='0x10'/>
            <address type='pci' domain='0x0000' bus='0x00' slot='0x02' function='0x0' multifunction='on'/>
        </controller>
        <interface type='network'>
            <mac address='{$macAddress}'/>
            <source network='vps-network'/>
            <model type='virtio'/>
            <address type='pci' domain='0x0000' bus='0x01' slot='0x00' function='0x0'/>
        </interface>
        <serial type='pty'>
            <target type='isa-serial' port='0'>
                <model name='isa-serial'/>
            </target>
        </serial>
        <console type='pty'>
            <target type='serial' port='0'/>
        </console>
        <input type='tablet' bus='usb'>
            <address type='usb' bus='0' port='1'/>
        </input>
        <input type='mouse' bus='ps2'/>
        <input type='keyboard' bus='ps2'/>
        <graphics type='vnc' port='{$vncPort}' autoport='yes' listen='0.0.0.0' passwd='{$vncPassword}'>
            <listen type='address' address='0.0.0.0'/>
        </graphics>
        <sound model='ich7'>
            <address type='pci' domain='0x0000' bus='0x00' slot='0x1b' function='0x0'/>
        </sound>
        <video>
            <model type='qxl' ram='65536' vram='65536' vgamem='16384' heads='1' primary='yes'/>
            <address type='pci' domain='0x0000' bus='0x00' slot='0x01' function='0x0'/>
        </video>
        <memballoon model='virtio'>
            <address type='pci' domain='0x0000' bus='0x05' slot='0x00' function='0x0'/>
        </memballoon>
    </devices>
</domain>
XML;
            
            return $xml;
        }
        
        /**
         * Генерация MAC адреса
         */
        private function generateMacAddress() {
            $mac = array(
                0x52, 0x54, 0x00, // QEMU/KVM prefix
                mt_rand(0x00, 0x7f),
                mt_rand(0x00, 0xff),
                mt_rand(0x00, 0xff)
            );
            
            return implode(':', array_map(function($byte) {
                return sprintf('%02x', $byte);
            }, $mac));
        }
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
                
                // Конфигурация для создания VM
                $vmConfig = [
                    'name' => $vmName,
                    'cpu_cores' => $plan['cpu_cores'],
                    'ram_mb' => $plan['ram_mb'],
                    'storage_gb' => $plan['storage_gb'],
                    'template_name' => $template['template_name'],
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
        try {
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
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Получение информации о VPS
     */
    public function getVPSInfo($vpsId, $userId) {
        try {
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
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
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
        try {
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
        } catch (Exception $e) {
            error_log('Failed to log VPS operation: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Обновление статуса операции
     */
    private function updateOperationStatus($operationId, $status, $errorMessage = null) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE vps_operations_log 
                SET status = ?, error_message = ?, completed_at = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([$status, $errorMessage, $operationId]);
        } catch (Exception $e) {
            error_log('Failed to update operation status: ' . $e->getMessage());
        }
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