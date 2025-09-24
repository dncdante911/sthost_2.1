<?php
/**
 * Site Check API
 * API для проверки доступности сайтов
 * Файл: /api/tools/site-check.php
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Подключение конфигурации
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Настройка заголовков для API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Обработка OPTIONS запроса
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Класс для проверки сайтов
class SiteCheckAPI {
    private $db;
    private $locations;
    private $maxTimeout;
    private $userAgent;
    
    public function __construct($db) {
        $this->db = $db;
        $this->maxTimeout = 30; // секунд
        $this->userAgent = 'StormHosting-SiteChecker/1.0 (+https://stormhosting.ua)';
        
        // Доступные локации для проверки
        $this->locations = [
            'kyiv' => ['name' => 'Київ, Україна', 'proxy' => null],
            'frankfurt' => ['name' => 'Франкфурт, Німеччина', 'proxy' => null],
            'london' => ['name' => 'Лондон, Великобританія', 'proxy' => null],
            'nyc' => ['name' => 'Нью-Йорк, США', 'proxy' => null],
            'singapore' => ['name' => 'Сінгапур', 'proxy' => null],
            'tokyo' => ['name' => 'Токіо, Японія', 'proxy' => null]
        ];
    }
    
    /**
     * Основной метод для проверки сайта
     */
    public function checkSite($url, $locations = ['kyiv']) {
        try {
            // Валидация URL
            $url = $this->validateUrl($url);
            
            // Проверка rate limit
            $this->checkRateLimit();
            
            $results = [
                'url' => $url,
                'timestamp' => date('c'),
                'general' => $this->getGeneralInfo($url),
                'locations' => [],
                'ssl' => null,
                'headers' => null
            ];
            
            // Проверка с разных локаций
            foreach ($locations as $location) {
                if (isset($this->locations[$location])) {
                    $locationResult = $this->checkFromLocation($url, $location);
                    $results['locations'][] = $locationResult;
                }
            }
            
            // SSL проверка
            if (strpos($url, 'https://') === 0) {
                $results['ssl'] = $this->checkSSL($url);
            }
            
            // HTTP заголовки
            $results['headers'] = $this->getHttpHeaders($url);
            
            // Сохранение результатов в БД
            $this->saveResults($results);
            
            return $results;
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Валидация URL
     */
    private function validateUrl($url) {
        // Добавляем протокол если его нет
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }
        
        // Валидация URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Некоректний URL');
        }
        
        // Проверка домена
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || !checkdnsrr($host, 'A')) {
            throw new Exception('Домен не існує або недоступний');
        }
        
        return $url;
    }
    
    /**
     * Проверка rate limit
     */
    private function checkRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM site_check_logs 
            WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 60) { // 60 запросов в час
            throw new Exception('Перевищено ліміт запитів. Спробуйте пізніше.');
        }
    }
    
    /**
     * Получение общей информации о сайте
     */
    private function getGeneralInfo($url) {
        $host = parse_url($url, PHP_URL_HOST);
        
        return [
            'url' => $url,
            'host' => $host,
            'ip' => gethostbyname($host),
            'check_time' => date('c')
        ];
    }
    
    /**
     * Проверка сайта с определенной локации
     */
    private function checkFromLocation($url, $location) {
        $startTime = microtime(true);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->maxTimeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_FRESH_CONNECT => true,
            // Включаем детальную информацию о времени
            CURLOPT_CERTINFO => true,
            // Настройки для получения дополнительной информации
            CURLINFO_HEADER_OUT => true
        ]);
        
        // Если есть прокси для данной локации
        if ($this->locations[$location]['proxy']) {
            curl_setopt($ch, CURLOPT_PROXY, $this->locations[$location]['proxy']);
        }
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        $endTime = microtime(true);
        $totalTime = round(($endTime - $startTime) * 1000); // в миллисекундах
        
        $result = [
            'location' => $location,
            'location_name' => $this->locations[$location]['name'],
            'response_time' => $totalTime,
            'status_code' => $info['http_code'] ?? null,
            'status_text' => $this->getHttpStatusText($info['http_code'] ?? 0),
            'dns_time' => isset($info['namelookup_time']) ? round($info['namelookup_time'] * 1000) : null,
            'connect_time' => isset($info['connect_time']) ? round($info['connect_time'] * 1000) : null,
            'error' => $error ?: null
        ];
        
        // Дополнительная информация если запрос успешен
        if (!$error && $info['http_code'] > 0) {
            $result['content_length'] = $info['size_download'] ?? 0;
            $result['content_type'] = $info['content_type'] ?? null;
            $result['server_ip'] = $info['primary_ip'] ?? null;
        }
        
        return $result;
    }
    
    /**
     * Проверка SSL сертификата
     */
    private function checkSSL($url) {
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?: 443;
        
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $socket = @stream_socket_client(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if (!$socket) {
            return [
                'valid' => false,
                'error' => "Неможливо з'єднатися з SSL: {$errstr}"
            ];
        }
        
        $cert = stream_context_get_params($socket);
        fclose($socket);
        
        if (!isset($cert['options']['ssl']['peer_certificate'])) {
            return [
                'valid' => false,
                'error' => 'SSL сертифікат не знайдено'
            ];
        }
        
        $certData = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        
        $validFrom = date('Y-m-d H:i:s', $certData['validFrom_time_t']);
        $validTo = date('Y-m-d H:i:s', $certData['validTo_time_t']);
        $daysUntilExpiry = round(($certData['validTo_time_t'] - time()) / 86400);
        
        $altNames = [];
        if (isset($certData['extensions']['subjectAltName'])) {
            $altNames = array_map(function($name) {
                return trim(str_replace('DNS:', '', $name));
            }, explode(',', $certData['extensions']['subjectAltName']));
        }
        
        return [
            'valid' => $daysUntilExpiry > 0,
            'issuer' => $certData['issuer']['CN'] ?? 'Невідомий',
            'subject' => $certData['subject']['CN'] ?? $host,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'days_until_expiry' => $daysUntilExpiry,
            'alt_names' => $altNames,
            'signature_algorithm' => $certData['signatureTypeSN'] ?? null
        ];
    }
    
    /**
     * Получение HTTP заголовков
     */
    private function getHttpHeaders($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $headerData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if (!$headerData) {
            return [];
        }
        
        $headers = [];
        $headerLines = explode("\r\n", $headerData);
        
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        
        return $headers;
    }
    
    /**
     * Получение текста HTTP статуса
     */
    private function getHttpStatusText($code) {
        $statusTexts = [
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            304 => 'Not Modified',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout'
        ];
        
        return $statusTexts[$code] ?? 'Unknown Status';
    }
    
    /**
     * Сохранение результатов в БД
     */
    private function saveResults($results) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO site_check_logs 
                (url, ip_address, user_agent, results_json, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $results['url'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                json_encode($results, JSON_UNESCAPED_UNICODE)
            ]);
            
        } catch (PDOException $e) {
            // Логируем ошибку, но не прерываем работу
            error_log('Site Check DB Error: ' . $e->getMessage());
        }
    }
}

// Обработка запроса
try {
    // Проверка метода запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не дозволений', 405);
    }
    
    // Проверка CSRF токена (если включена защита)
    if (defined('CSRF_PROTECTION') && CSRF_PROTECTION) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            throw new Exception('Недійсний CSRF токен', 403);
        }
    }
    
    // Получение данных запроса
    $url = $_POST['url'] ?? '';
    $locationsJson = $_POST['locations'] ?? '[]';
    
    if (empty($url)) {
        throw new Exception('URL не вказано');
    }
    
    // Парсинг локаций
    $locations = json_decode($locationsJson, true);
    if (!is_array($locations) || empty($locations)) {
        $locations = ['kyiv']; // по умолчанию
    }
    
    // Ограничение количества локаций
    if (count($locations) > 4) {
        $locations = array_slice($locations, 0, 4);
    }
    
    // Создание экземпляра API и выполнение проверки
    $api = new SiteCheckAPI($pdo);
    $results = $api->checkSite($url, $locations);
    
    // Возврат результатов
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Обработка ошибок
    $statusCode = $e->getCode() ?: 500;
    http_response_code($statusCode);
    
    echo json_encode([
        'error' => $e->getMessage(),
        'status_code' => $statusCode,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
}

// SQL для создания таблицы логов (выполнить отдельно в БД)
/*
CREATE TABLE IF NOT EXISTS site_check_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(512) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    results_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, created_at),
    INDEX idx_url (url(100)),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/
?>