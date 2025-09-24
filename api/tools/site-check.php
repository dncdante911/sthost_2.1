<?php
/**
 * Site Check API с подключением к БД
 * Файл: /api/tools/site-check.php
 */

// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Защита от прямого доступа
define('SECURE_ACCESS', true);

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

// Подключение к базе данных
function getDatabaseConnection() {
    $host = 'localhost';
    $dbname = 'sthostsitedb';
    $username = 'sthostdb'; // или ваш пользователь БД
    $password = '3344Frz@q0607Dm$157';     // или ваш пароль БД
    
    try {
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        return null;
    }
}

// Функция для логирования ошибок
function logError($message) {
    error_log('[Site Check API] ' . $message);
}

// Функция для отправки JSON ответа
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Функция проверки rate limit
function checkRateLimit($pdo, $ip) {
    if (!$pdo) return true; // Если нет подключения к БД, пропускаем проверку
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM site_check_logs 
            WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        return $result['count'] < 60; // 60 запросов в час
    } catch (PDOException $e) {
        logError('Rate limit check failed: ' . $e->getMessage());
        return true; // В случае ошибки разрешаем запрос
    }
}

// Функция сохранения в БД
function saveToDatabase($pdo, $url, $ip, $userAgent, $results) {
    if (!$pdo) return; // Если нет подключения к БД, не сохраняем
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO site_check_logs 
            (url, ip_address, user_agent, results_json, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $url,
            $ip,
            $userAgent,
            json_encode($results, JSON_UNESCAPED_UNICODE)
        ]);
    } catch (PDOException $e) {
        logError('Database save failed: ' . $e->getMessage());
    }
}

try {
    // Проверяем метод запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(['error' => 'Метод не дозволений'], 405);
    }
    
    // Подключаемся к БД
    $pdo = getDatabaseConnection();
    
    // Проверяем rate limit
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($pdo, $clientIp)) {
        sendJsonResponse(['error' => 'Перевищено ліміт запитів. Спробуйте через годину.'], 429);
    }
    
    // Получаем данные из POST
    $url = trim($_POST['url'] ?? '');
    $locationsJson = $_POST['locations'] ?? '[]';
    
    if (empty($url)) {
        sendJsonResponse(['error' => 'URL не вказано'], 400);
    }
    
    // Валидация URL
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        sendJsonResponse(['error' => 'Некоректний URL'], 400);
    }
    
    // Парсинг локаций
    $locations = json_decode($locationsJson, true);
    if (!is_array($locations) || empty($locations)) {
        $locations = ['kyiv'];
    }
    
    // Ограничиваем количество локаций
    if (count($locations) > 4) {
        $locations = array_slice($locations, 0, 4);
    }
    
    // Доступные локации
    $availableLocations = [
        'kyiv' => 'Київ, Україна',
        'frankfurt' => 'Франкфурт, Німеччина', 
        'london' => 'Лондон, Великобританія',
        'nyc' => 'Нью-Йорк, США',
        'singapore' => 'Сінгапур',
        'tokyo' => 'Токіо, Японія'
    ];
    
    // Функция проверки сайта
    function checkSiteFromLocation($url, $location, $locationName) {
        $startTime = microtime(true);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'StormHosting-SiteChecker/1.0 (+https://stormhosting.ua)',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_FRESH_CONNECT => true
        ]);
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000);
        
        return [
            'location' => $location,
            'location_name' => $locationName,
            'response_time' => $responseTime,
            'status_code' => $info['http_code'] ?? 0,
            'status_text' => getHttpStatusText($info['http_code'] ?? 0),
            'dns_time' => isset($info['namelookup_time']) ? round($info['namelookup_time'] * 1000) : 0,
            'connect_time' => isset($info['connect_time']) ? round($info['connect_time'] * 1000) : 0,
            'error' => $error ?: null,
            'content_length' => $info['size_download'] ?? 0,
            'content_type' => $info['content_type'] ?? null,
            'server_ip' => $info['primary_ip'] ?? null
        ];
    }
    
    // Функция получения текста HTTP статуса
    function getHttpStatusText($code) {
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
    
    // Функция проверки SSL
    function checkSSL($url) {
        if (strpos($url, 'https://') !== 0) {
            return null;
        }
        
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
            10,
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
            'valid_from' => date('Y-m-d H:i:s', $certData['validFrom_time_t']),
            'valid_to' => date('Y-m-d H:i:s', $certData['validTo_time_t']),
            'days_until_expiry' => $daysUntilExpiry,
            'alt_names' => $altNames,
            'signature_algorithm' => $certData['signatureTypeSN'] ?? null
        ];
    }
    
    // Функция получения HTTP заголовков
    function getHttpHeaders($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'StormHosting-SiteChecker/1.0',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $headerData = curl_exec($ch);
        $info = curl_getinfo($ch);
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
        
        // Добавляем дополнительную информацию
        if (isset($info['content_type'])) {
            $headers['Content-Type'] = $info['content_type'];
        }
        if (isset($info['size_download'])) {
            $headers['Content-Length'] = $info['size_download'];
        }
        
        return $headers;
    }
    
    // Выполняем проверки
    $host = parse_url($url, PHP_URL_HOST);
    $ip = gethostbyname($host);
    
    $results = [
        'url' => $url,
        'timestamp' => date('c'),
        'general' => [
            'url' => $url,
            'host' => $host,
            'ip' => $ip,
            'check_time' => date('c'),
            'server' => null,
            'content_length' => 0
        ],
        'locations' => [],
        'ssl' => null,
        'headers' => null
    ];
    
    // Проверяем с каждой локации
    foreach ($locations as $location) {
        if (isset($availableLocations[$location])) {
            $locationResult = checkSiteFromLocation($url, $location, $availableLocations[$location]);
            $results['locations'][] = $locationResult;
            
            // Обновляем общую информацию из первого успешного ответа
            if (!$results['general']['server'] && !empty($locationResult['content_type'])) {
                $results['general']['content_type'] = $locationResult['content_type'];
                $results['general']['content_length'] = $locationResult['content_length'];
            }
        }
    }
    
    // SSL проверка для HTTPS сайтов
    if (strpos($url, 'https://') === 0) {
        $results['ssl'] = checkSSL($url);
    }
    
    // HTTP заголовки (ограничиваем количество для экономии места)
    $allHeaders = getHttpHeaders($url);
    $results['headers'] = array_slice($allHeaders, 0, 15, true);
    
    // Добавляем информацию о сервере из заголовков
    if (isset($allHeaders['Server'])) {
        $results['general']['server'] = $allHeaders['Server'];
    }
    
    // Сохраняем в базу данных
    saveToDatabase($pdo, $url, $clientIp, $_SERVER['HTTP_USER_AGENT'] ?? '', $results);
    
    // Возвращаем результаты
    sendJsonResponse($results);
    
} catch (Exception $e) {
    logError('Exception: ' . $e->getMessage());
    sendJsonResponse([
        'error' => 'Помилка сервера: ' . $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ], 500);
    
} catch (Error $e) {
    logError('Fatal Error: ' . $e->getMessage());
    sendJsonResponse([
        'error' => 'Критична помилка: ' . $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()), 
            'line' => $e->getLine()
        ]
    ], 500);
}
?>