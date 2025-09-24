<?php
// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

class DatabaseConnection {
    private static $site_connection = null;
    private static $fossbilling_connection = null;
    
    // Подключение к основной БД сайта
    public static function getSiteConnection() {
        if (self::$site_connection === null) {
            try {
                global $host, $dbname_site, $db_userconnect_site, $db_passwd_site;
                
                $dsn = "mysql:host={$host};dbname={$dbname_site};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                self::$site_connection = new PDO($dsn, $db_userconnect_site, $db_passwd_site, $options);
                
                // Логируем успешное подключение
                self::logSecurityEvent('database_connect', 'Успешное подключение к основной БД', 'low');
                
            } catch (PDOException $e) {
                // Логируем ошибку подключения
                self::logSecurityEvent('database_error', 'Ошибка подключения к БД: ' . $e->getMessage(), 'critical');
                throw new Exception('Database connection failed');
            }
        }
        
        return self::$site_connection;
    }
    
    // Подключение к БД FOSSBilling
    public static function getFossBillingConnection() {
        if (self::$fossbilling_connection === null) {
            try {
                global $host, $dbname_fossbill, $db_userconnect_fossbill, $db_passwd_fossbill;
                
                $dsn = "mysql:host={$host};dbname={$dbname_fossbill};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                self::$fossbilling_connection = new PDO($dsn, $db_userconnect_fossbill, $db_passwd_fossbill, $options);
                
                // Логируем успешное подключение
                self::logSecurityEvent('database_connect', 'Успешное подключение к БД FOSSBilling', 'low');
                
            } catch (PDOException $e) {
                // Логируем ошибку подключения
                self::logSecurityEvent('database_error', 'Ошибка подключения к БД FOSSBilling: ' . $e->getMessage(), 'critical');
                throw new Exception('FOSSBilling database connection failed');
            }
        }
        
        return self::$fossbilling_connection;
    }
    
    // Безопасное выполнение запросов с подготовленными выражениями
    public static function executeQuery($sql, $params = [], $connection_type = 'site') {
        try {
            $pdo = ($connection_type === 'fossbilling') ? 
                   self::getFossBillingConnection() : 
                   self::getSiteConnection();
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt;
            
        } catch (PDOException $e) {
            self::logSecurityEvent('database_query_error', 
                'Ошибка выполнения запроса: ' . $e->getMessage() . ' SQL: ' . $sql, 
                'medium');
            throw new Exception('Query execution failed');
        }
    }
    
    // Получение одной записи
    public static function fetchOne($sql, $params = [], $connection_type = 'site') {
        $stmt = self::executeQuery($sql, $params, $connection_type);
        return $stmt->fetch();
    }
    
    // Получение всех записей
    public static function fetchAll($sql, $params = [], $connection_type = 'site') {
        $stmt = self::executeQuery($sql, $params, $connection_type);
        return $stmt->fetchAll();
    }
    
    // Вставка записи с возвратом ID
    public static function insert($sql, $params = [], $connection_type = 'site') {
        $stmt = self::executeQuery($sql, $params, $connection_type);
        $pdo = ($connection_type === 'fossbilling') ? 
               self::getFossBillingConnection() : 
               self::getSiteConnection();
        return $pdo->lastInsertId();
    }
    
    // Обновление/удаление с возвратом количества затронутых строк
    public static function execute($sql, $params = [], $connection_type = 'site') {
        $stmt = self::executeQuery($sql, $params, $connection_type);
        return $stmt->rowCount();
    }
    
    // Логирование событий безопасности
    private static function logSecurityEvent($action, $details, $severity = 'medium') {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_id = $_SESSION['user_id'] ?? null;
            
            $sql = "INSERT INTO security_logs (ip_address, user_id, action, details, severity) VALUES (?, ?, ?, ?, ?)";
            $params = [$ip, $user_id, $action, $details, $severity];
            
            // Используем прямое подключение, чтобы избежать рекурсии
            if (self::$site_connection !== null) {
                $stmt = self::$site_connection->prepare($sql);
                $stmt->execute($params);
            }
        } catch (Exception $e) {
            // Если не можем залогировать в БД, записываем в файл
            error_log("Security log error: " . $e->getMessage() . " Original event: $action - $details");
        }
    }
    
    // Проверка попыток входа для защиты от брутфорса
    public static function checkLoginAttempts($email, $ip) {
        $sql = "SELECT attempts, locked_until FROM login_attempts WHERE (email = ? OR ip_address = ?) AND locked_until > NOW()";
        $result = self::fetchOne($sql, [$email, $ip]);
        
        if ($result) {
            return false; // Заблокирован
        }
        
        return true; // Можно войти
    }
    
    // Регистрация неудачной попытки входа
    public static function recordFailedLogin($email, $ip) {
        $sql = "INSERT INTO login_attempts (ip_address, email, attempts, locked_until) 
                VALUES (?, ?, 1, NULL) 
                ON DUPLICATE KEY UPDATE 
                attempts = attempts + 1,
                last_attempt = NOW(),
                locked_until = IF(attempts >= ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NULL)";
        
        self::execute($sql, [$ip, $email, MAX_LOGIN_ATTEMPTS - 1]);
        
        self::logSecurityEvent('failed_login', "Неудачная попытка входа для email: $email", 'medium');
    }
    
    // Очистка успешных попыток входа
    public static function clearLoginAttempts($email, $ip) {
        $sql = "DELETE FROM login_attempts WHERE email = ? OR ip_address = ?";
        self::execute($sql, [$email, $ip]);
    }
    
    // Создание/проверка CSRF токена
    public static function createCSRFToken() {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 час
        $session_id = session_id();
        
        $sql = "INSERT INTO csrf_tokens (token, user_session, expires_at) VALUES (?, ?, ?)";
        self::execute($sql, [$token, $session_id, $expires]);
        
        return $token;
    }
    
    public static function validateCSRFToken($token) {
        $sql = "SELECT id FROM csrf_tokens WHERE token = ? AND expires_at > NOW() AND user_session = ?";
        $result = self::fetchOne($sql, [$token, session_id()]);
        
        if ($result) {
            // Удаляем использованный токен
            $sql = "DELETE FROM csrf_tokens WHERE id = ?";
            self::execute($sql, [$result['id']]);
            return true;
        }
        
        self::logSecurityEvent('csrf_validation_failed', "Неверный CSRF токен: $token", 'high');
        return false;
    }
    
    // Получение настроек сайта
    public static function getSiteSetting($key, $default = null) {
        $sql = "SELECT setting_value, setting_type FROM site_settings WHERE setting_key = ?";
        $result = self::fetchOne($sql, [$key]);
        
        if (!$result) {
            return $default;
        }
        
        $value = $result['setting_value'];
        
        switch ($result['setting_type']) {
            case 'boolean':
                return (bool)$value;
            case 'number':
                return is_numeric($value) ? (int)$value : $default;
            case 'json':
                return json_decode($value, true) ?: $default;
            default:
                return $value;
        }
    }
    
    // Установка настроек сайта
    public static function setSiteSetting($key, $value, $type = 'string') {
        if ($type === 'json') {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? '1' : '0';
        }
        
        $sql = "INSERT INTO site_settings (setting_key, setting_value, setting_type) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value),
                setting_type = VALUES(setting_type)";
        
        return self::execute($sql, [$key, $value, $type]);
    }
}

// Функции-обертки для удобства
function db_site() {
    return DatabaseConnection::getSiteConnection();
}

function db_fossbilling() {
    return DatabaseConnection::getFossBillingConnection();
}

function db_query($sql, $params = [], $connection = 'site') {
    return DatabaseConnection::executeQuery($sql, $params, $connection);
}

function db_fetch_one($sql, $params = [], $connection = 'site') {
    return DatabaseConnection::fetchOne($sql, $params, $connection);
}

function db_fetch_all($sql, $params = [], $connection = 'site') {
    return DatabaseConnection::fetchAll($sql, $params, $connection);
}

function db_insert($sql, $params = [], $connection = 'site') {
    return DatabaseConnection::insert($sql, $params, $connection);
}

function db_execute($sql, $params = [], $connection = 'site') {
    return DatabaseConnection::execute($sql, $params, $connection);
}
?>