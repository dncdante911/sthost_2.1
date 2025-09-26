<?php
/**
 * LibVirt Manager
 * Класс для управления VPS через libvirt
 * Файл: /includes/classes/LibvirtManager.php
 */

// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

class LibvirtManager {
    private $connection = null;
    private $host = 'localhost';
    private $username = 'root';
    private $ssh_key_path = '/root/.ssh/id_rsa';
    
    public function __construct($host = 'localhost', $username = 'root') {
        $this->host = $host;
        $this->username = $username;
    }
    
    /**
     * Подключение к libvirt
     */
    public function connect() {
        try {
            $uri = "qemu+ssh://{$this->username}@{$this->host}/system";
            $this->connection = libvirt_connect($uri);
            
            if (!$this->connection) {
                throw new Exception('Failed to connect to libvirt: ' . libvirt_get_last_error());
            }
            
            return true;
        } catch (Exception $e) {
            error_log("LibVirt connection error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Отключение от libvirt
     */
    public function disconnect() {
        if ($this->connection) {
            libvirt_connect_close($this->connection);
            $this->connection = null;
        }
    }
    
    /**
     * Создание виртуальной машины
     */
    public function createVPS($config) {
        if (!$this->connection && !$this->connect()) {
            return ['success' => false, 'error' => 'Cannot connect to libvirt'];
        }
        
        try {
            // Генерируем XML конфигурацию
            $xml = $this->generateVMXML($config);
            
            // Создаем домен
            $domain = libvirt_domain_define_xml($this->connection, $xml);
            if (!$domain) {
                throw new Exception('Failed to define VM: ' . libvirt_get_last_error());
            }
            
            // Создаем диск
            if (!$this->createDisk($config)) {
                throw new Exception('Failed to create disk');
            }
            
            // Запускаем VM
            if (!libvirt_domain_create($domain)) {
                throw new Exception('Failed to start VM: ' . libvirt_get_last_error());
            }
            
            return [
                'success' => true, 
                'domain_name' => $config['name'],
                'ip_address' => $config['ip_address']
            ];
            
        } catch (Exception $e) {
            error_log("Create VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Управление состоянием VPS
     */
    public function controlVPS($name, $action) {
        if (!$this->connection && !$this->connect()) {
            return ['success' => false, 'error' => 'Cannot connect to libvirt'];
        }
        
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $name);
            if (!$domain) {
                return ['success' => false, 'error' => 'VM not found'];
            }
            
            switch ($action) {
                case 'start':
                    $result = libvirt_domain_create($domain);
                    break;
                case 'stop':
                    $result = libvirt_domain_shutdown($domain);
                    break;
                case 'force_stop':
                    $result = libvirt_domain_destroy($domain);
                    break;
                case 'restart':
                    libvirt_domain_reboot($domain);
                    $result = true;
                    break;
                case 'suspend':
                    $result = libvirt_domain_suspend($domain);
                    break;
                case 'resume':
                    $result = libvirt_domain_resume($domain);
                    break;
                default:
                    return ['success' => false, 'error' => 'Unknown action'];
            }
            
            return ['success' => $result, 'action' => $action];
            
        } catch (Exception $e) {
            error_log("Control VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение статуса VPS
     */
    public function getVPSStatus($name) {
        if (!$this->connection && !$this->connect()) {
            return ['success' => false, 'error' => 'Cannot connect to libvirt'];
        }
        
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $name);
            if (!$domain) {
                return ['success' => false, 'error' => 'VM not found'];
            }
            
            $info = libvirt_domain_get_info($domain);
            $state_map = [
                VIR_DOMAIN_NOSTATE => 'no_state',
                VIR_DOMAIN_RUNNING => 'active',
                VIR_DOMAIN_BLOCKED => 'blocked',
                VIR_DOMAIN_PAUSED => 'suspended',
                VIR_DOMAIN_SHUTDOWN => 'shutdown',
                VIR_DOMAIN_SHUTOFF => 'stopped',
                VIR_DOMAIN_CRASHED => 'crashed'
            ];
            
            return [
                'success' => true,
                'state' => $state_map[$info['state']] ?? 'unknown',
                'max_memory' => $info['maxMem'] / 1024, // MB
                'memory' => $info['memory'] / 1024, // MB
                'cpu_count' => $info['nrVirtCpu'],
                'cpu_time' => $info['cpuTime']
            ];
            
        } catch (Exception $e) {
            error_log("Get VPS status error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение VNC информации
     */
    public function getVNCInfo($name) {
        if (!$this->connection && !$this->connect()) {
            return ['success' => false, 'error' => 'Cannot connect to libvirt'];
        }
        
        try {
            $domain = libvirt_domain_lookup_by_name($this->connection, $name);
            if (!$domain) {
                return ['success' => false, 'error' => 'VM not found'];
            }
            
            $xml = libvirt_domain_get_xml_desc($domain);
            $doc = new DOMDocument();
            $doc->loadXML($xml);
            
            $xpath = new DOMXPath($doc);
            $graphics = $xpath->query("//graphics[@type='vnc']");
            
            if ($graphics->length > 0) {
                $vnc = $graphics->item(0);
                return [
                    'success' => true,
                    'port' => $vnc->getAttribute('port'),
                    'host' => $this->host,
                    'password' => $vnc->getAttribute('passwd') ?: null
                ];
            }
            
            return ['success' => false, 'error' => 'VNC not configured'];
            
        } catch (Exception $e) {
            error_log("Get VNC info error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Создание диска VPS
     */
    private function createDisk($config) {
        try {
            $template_path = $config['template_path'];
            $disk_path = "/var/lib/libvirt/images/{$config['name']}.qcow2";
            
            // Копируем шаблон
            $cmd = "qemu-img create -f qcow2 -b {$template_path} {$disk_path} {$config['disk_size']}G";
            exec($cmd, $output, $return_code);
            
            if ($return_code !== 0) {
                throw new Exception("Failed to create disk: " . implode("\n", $output));
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Create disk error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Генерация XML конфигурации VM
     */
    private function generateVMXML($config) {
        $vnc_port = $config['vnc_port'] ?? $this->getAvailableVNCPort();
        $vnc_password = $config['vnc_password'] ?? $this->generatePassword(8);
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<domain type="kvm">
    <name>' . htmlspecialchars($config['name']) . '</name>
    <memory unit="MiB">' . $config['memory'] . '</memory>
    <currentMemory unit="MiB">' . $config['memory'] . '</currentMemory>
    <vcpu placement="static">' . $config['cpu_cores'] . '</vcpu>
    <os>
        <type arch="x86_64" machine="pc-i440fx-2.1">hvm</type>
        <boot dev="hd"/>
    </os>
    <features>
        <acpi/>
        <apic/>
        <pae/>
    </features>
    <clock offset="utc"/>
    <on_poweroff>destroy</on_poweroff>
    <on_reboot>restart</on_reboot>
    <on_crash>restart</on_crash>
    <devices>
        <emulator>/usr/bin/kvm</emulator>
        <disk type="file" device="disk">
            <driver name="qemu" type="qcow2"/>
            <source file="/var/lib/libvirt/images/' . $config['name'] . '.qcow2"/>
            <target dev="vda" bus="virtio"/>
        </disk>
        <interface type="bridge">
            <source bridge="br0"/>
            <model type="virtio"/>
            <address type="pci" domain="0x0000" bus="0x00" slot="0x03" function="0x0"/>
        </interface>
        <serial type="pty">
            <target port="0"/>
        </serial>
        <console type="pty">
            <target type="serial" port="0"/>
        </console>
        <graphics type="vnc" port="' . $vnc_port . '" autoport="no" passwd="' . $vnc_password . '"/>
        <video>
            <model type="cirrus"/>
        </video>
    </devices>
</domain>';
        
        return $xml;
    }
    
    /**
     * Получение свободного VNC порта
     */
    private function getAvailableVNCPort() {
        for ($port = 5900; $port < 6000; $port++) {
            if (!$this->isPortInUse($port)) {
                return $port;
            }
        }
        return 5900; // fallback
    }
    
    /**
     * Проверка использования порта
     */
    private function isPortInUse($port) {
        $connection = @fsockopen($this->host, $port);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }
    
    /**
     * Генерация пароля
     */
    private function generatePassword($length = 12) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Переустановка VPS
     */
    public function reinstallVPS($name, $template_path) {
        try {
            // Останавливаем VM
            $this->controlVPS($name, 'force_stop');
            
            // Удаляем старый диск
            $disk_path = "/var/lib/libvirt/images/{$name}.qcow2";
            if (file_exists($disk_path)) {
                unlink($disk_path);
            }
            
            // Создаем новый диск из шаблона
            $cmd = "qemu-img create -f qcow2 -b {$template_path} {$disk_path}";
            exec($cmd, $output, $return_code);
            
            if ($return_code !== 0) {
                throw new Exception("Failed to recreate disk: " . implode("\n", $output));
            }
            
            // Запускаем VM
            $this->controlVPS($name, 'start');
            
            return ['success' => true, 'message' => 'VPS reinstalled successfully'];
            
        } catch (Exception $e) {
            error_log("Reinstall VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Удаление VPS
     */
    public function deleteVPS($name) {
        try {
            if (!$this->connection && !$this->connect()) {
                return ['success' => false, 'error' => 'Cannot connect to libvirt'];
            }
            
            $domain = libvirt_domain_lookup_by_name($this->connection, $name);
            if (!$domain) {
                return ['success' => false, 'error' => 'VM not found'];
            }
            
            // Останавливаем VM если запущен
            $info = libvirt_domain_get_info($domain);
            if ($info['state'] == VIR_DOMAIN_RUNNING) {
                libvirt_domain_destroy($domain);
            }
            
            // Удаляем домен
            libvirt_domain_undefine($domain);
            
            // Удаляем диск
            $disk_path = "/var/lib/libvirt/images/{$name}.qcow2";
            if (file_exists($disk_path)) {
                unlink($disk_path);
            }
            
            return ['success' => true, 'message' => 'VPS deleted successfully'];
            
        } catch (Exception $e) {
            error_log("Delete VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Создание снимка (backup)
     */
    public function createSnapshot($name, $snapshot_name) {
        try {
            if (!$this->connection && !$this->connect()) {
                return ['success' => false, 'error' => 'Cannot connect to libvirt'];
            }
            
            $domain = libvirt_domain_lookup_by_name($this->connection, $name);
            if (!$domain) {
                return ['success' => false, 'error' => 'VM not found'];
            }
            
            $xml = '<domainsnapshot>
                <name>' . $snapshot_name . '</name>
                <description>Backup snapshot created at ' . date('Y-m-d H:i:s') . '</description>
            </domainsnapshot>';
            
            $snapshot = libvirt_domain_snapshot_create_xml($domain, $xml);
            if (!$snapshot) {
                throw new Exception('Failed to create snapshot: ' . libvirt_get_last_error());
            }
            
            return ['success' => true, 'snapshot_name' => $snapshot_name];
            
        } catch (Exception $e) {
            error_log("Create snapshot error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение статистики использования ресурсов
     */
    public function getResourceUsage($name) {
        try {
            if (!$this->connection && !$this->connect()) {
                return ['success' => false, 'error' => 'Cannot connect to libvirt'];
            }
            
            $domain = libvirt_domain_lookup_by_name($this->connection, $name);
            if (!$domain) {
                return ['success' => false, 'error' => 'VM not found'];
            }
            
            $info = libvirt_domain_get_info($domain);
            
            // CPU usage calculation (simplified)
            $cpu_usage = 0;
            if ($info['state'] == VIR_DOMAIN_RUNNING && $info['cpuTime'] > 0) {
                $cpu_usage = min(100, ($info['cpuTime'] / (time() * 1000000000)) * 100);
            }
            
            // Memory usage
            $memory_usage = ($info['memory'] / $info['maxMem']) * 100;
            
            // Disk usage (requires additional tools)
            $disk_usage = $this->getDiskUsage($name);
            
            return [
                'success' => true,
                'cpu_usage' => round($cpu_usage, 2),
                'memory_usage' => round($memory_usage, 2),
                'memory_used_mb' => $info['memory'] / 1024,
                'memory_total_mb' => $info['maxMem'] / 1024,
                'disk_usage_gb' => $disk_usage,
                'uptime' => $this->getUptime($domain)
            ];
            
        } catch (Exception $e) {
            error_log("Get resource usage error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение использования диска
     */
    private function getDiskUsage($name) {
        try {
            $disk_path = "/var/lib/libvirt/images/{$name}.qcow2";
            if (!file_exists($disk_path)) {
                return 0;
            }
            
            $cmd = "qemu-img info {$disk_path} | grep 'disk size'";
            exec($cmd, $output);
            
            if (!empty($output[0])) {
                preg_match('/(\d+(?:\.\d+)?)\s*([KMGT]B?)/', $output[0], $matches);
                if (count($matches) >= 3) {
                    $size = floatval($matches[1]);
                    $unit = strtoupper($matches[2]);
                    
                    switch ($unit) {
                        case 'KB': return $size / 1024 / 1024;
                        case 'MB': return $size / 1024;
                        case 'GB': return $size;
                        case 'TB': return $size * 1024;
                        default: return $size / 1024 / 1024 / 1024; // bytes to GB
                    }
                }
            }
            
            return 0;
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Получение времени работы
     */
    private function getUptime($domain) {
        try {
            // Упрощенная версия - возвращаем 0 если не можем определить
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Изменение размера RAM
     */
    public function resizeRAM($name, $new_memory_mb) {
        try {
            if (!$this->connection && !$this->connect()) {
                return ['success' => false, 'error' => 'Cannot connect to libvirt'];
            }
            
            $domain = libvirt_domain_lookup_by_name($this->connection, $name);
            if (!$domain) {
                return ['success' => false, 'error' => 'VM not found'];
            }
            
            // Устанавливаем новый размер памяти
            $result = libvirt_domain_set_max_memory($domain, $new_memory_mb * 1024);
            if ($result) {
                libvirt_domain_set_memory($domain, $new_memory_mb * 1024);
            }
            
            return ['success' => $result, 'new_memory_mb' => $new_memory_mb];
            
        } catch (Exception $e) {
            error_log("Resize RAM error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение списка всех VPS
     */
    public function listAllVPS() {
        try {
            if (!$this->connection && !$this->connect()) {
                return ['success' => false, 'error' => 'Cannot connect to libvirt'];
            }
            
            $domains = libvirt_list_all_domains($this->connection);
            $vps_list = [];
            
            foreach ($domains as $domain) {
                $name = libvirt_domain_get_name($domain);
                $info = libvirt_domain_get_info($domain);
                
                $state_map = [
                    VIR_DOMAIN_NOSTATE => 'no_state',
                    VIR_DOMAIN_RUNNING => 'active',
                    VIR_DOMAIN_BLOCKED => 'blocked',
                    VIR_DOMAIN_PAUSED => 'suspended',
                    VIR_DOMAIN_SHUTDOWN => 'shutdown',
                    VIR_DOMAIN_SHUTOFF => 'stopped',
                    VIR_DOMAIN_CRASHED => 'crashed'
                ];
                
                $vps_list[] = [
                    'name' => $name,
                    'state' => $state_map[$info['state']] ?? 'unknown',
                    'memory_mb' => $info['memory'] / 1024,
                    'cpu_cores' => $info['nrVirtCpu']
                ];
            }
            
            return ['success' => true, 'vps_list' => $vps_list];
            
        } catch (Exception $e) {
            error_log("List VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>