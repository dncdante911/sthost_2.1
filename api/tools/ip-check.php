<?php
/**
 * IP Check API
 * API для проверки IP адресов с геолокацией, черными списками и анализом угроз
 * Файл: /api/tools/ip-check.php
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
    $username = 'sthostdb'; // измените на ваши данные
    $password = '3344Frz@q0607Dm$157';     // измените на ваши данные
    
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
    error_log('[IP Check API] ' . $message);
}

// Функция для отправки JSON ответа
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Функция проверки rate limit
function checkRateLimit($pdo, $ip) {
    if (!$pdo) return true;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM ip_check_logs 
            WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        return $result['count'] < 100; // 100 запросов в час
    } catch (PDOException $e) {
        logError('Rate limit check failed: ' . $e->getMessage());
        return true;
    }
}

// Функция сохранения в БД
function saveToDatabase($pdo, $checkedIp, $clientIp, $userAgent, $results) {
    if (!$pdo) return;
    
    try {
        // Проверяем существование таблицы и создаем если нужно
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ip_check_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                checked_ip VARCHAR(45) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                results_json JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip_time (ip_address, created_at),
                INDEX idx_checked_ip (checked_ip)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $stmt = $pdo->prepare("
            INSERT INTO ip_check_logs 
            (checked_ip, ip_address, user_agent, results_json, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $checkedIp,
            $clientIp,
            $userAgent,
            json_encode($results, JSON_UNESCAPED_UNICODE)
        ]);
    } catch (PDOException $e) {
        logError('Database save failed: ' . $e->getMessage());
    }
}

// Класс для проверки IP
class IPCheckAPI {
    private $pdo;
    private $blacklists;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Список RBL серверов для проверки
        $this->blacklists = [
            'zen.spamhaus.org' => 'Spamhaus ZEN',
            'bl.spamcop.net' => 'SpamCop',
            'b.barracudacentral.org' => 'Barracuda',
            'dnsbl.sorbs.net' => 'SORBS',
            'spam.dnsbl.sorbs.net' => 'SORBS Spam',
            'http.dnsbl.sorbs.net' => 'SORBS HTTP',
            'socks.dnsbl.sorbs.net' => 'SORBS SOCKS',
            'misc.dnsbl.sorbs.net' => 'SORBS Misc',
            'zombie.dnsbl.sorbs.net' => 'SORBS Zombie',
            'dul.dnsbl.sorbs.net' => 'SORBS DUL',
            'dnsbl-1.uceprotect.net' => 'UCEPROTECT Level 1',
            'dnsbl-2.uceprotect.net' => 'UCEPROTECT Level 2',
            'dnsbl-3.uceprotect.net' => 'UCEPROTECT Level 3',
            'pbl.spamhaus.org' => 'Spamhaus PBL',
            'sbl.spamhaus.org' => 'Spamhaus SBL',
            'css.spamhaus.org' => 'Spamhaus CSS',
            'xbl.spamhaus.org' => 'Spamhaus XBL',
            'cbl.abuseat.org' => 'Composite Blocking List',
            'psbl.surriel.com' => 'Passive Spam Block List',
            'ubl.unsubscore.com' => 'Lashback UBL'
        ];
    }
    
    /**
     * Основной метод проверки IP
     */
    public function checkIP($ip, $options = []) {
        try {
            // Валидация IP
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new Exception('Некоректний IP адрес');
            }
            
            $results = [
                'ip' => $ip,
                'timestamp' => date('c'),
                'general' => $this->getGeneralInfo($ip),
                'location' => null,
                'network' => null,
                'blacklists' => null,
                'threats' => null,
                'distance' => null,
                'weather' => null
            ];
            
            // Получаем геолокацию
            $results['location'] = $this->getGeolocation($ip);
            
            // Получаем сетевую информацию
            $results['network'] = $this->getNetworkInfo($ip);
            
            // Проверка черных списков
            if ($options['checkBlacklists'] ?? true) {
                $results['blacklists'] = $this->checkBlacklists($ip);
            }
            
            // Анализ угроз
            if ($options['checkThreatIntel'] ?? true) {
                $results['threats'] = $this->checkThreats($ip);
            }
            
            // Расчет расстояния
            if (($options['checkDistance'] ?? true) && isset($options['userLocation'])) {
                $userLocation = json_decode($options['userLocation'], true);
                if ($userLocation && $results['location']) {
                    $results['distance'] = $this->calculateDistance(
                        $userLocation['lat'], 
                        $userLocation['lng'],
                        $results['location']['latitude'],
                        $results['location']['longitude']
                    );
                }
            }
            
            // Погода в регионе
            if ($results['location']) {
                $results['weather'] = $this->getWeather(
                    $results['location']['latitude'],
                    $results['location']['longitude']
                );
            }
            
            return $results;
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение общей информации об IP
     */
    private function getGeneralInfo($ip) {
        return [
            'ip' => $ip,
            'is_valid' => filter_var($ip, FILTER_VALIDATE_IP) !== false,
            'ip_type' => $this->getIPType($ip),
            'check_time' => date('c')
        ];
    }
    
    /**
     * Определение типа IP адреса
     */
    private function getIPType($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return 'Публічна IPv4';
            } else {
                return 'Приватна IPv4';
            }
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'IPv6';
        }
        return 'Невідомий';
    }
    
    /**
     * Получение геолокации через внешний API
     */
    private function getGeolocation($ip) {
        try {
            // Используем ipapi.co (бесплатный сервис)
            $url = "https://ipapi.co/{$ip}/json/";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'StormHosting-IPChecker/1.0',
                CURLOPT_FOLLOWLOCATION => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                
                if ($data && !isset($data['error'])) {
                    return [
                        'country' => $data['country_name'] ?? 'Невідомо',
                        'country_code' => $data['country_code'] ?? '',
                        'region' => $data['region'] ?? 'Невідомо',
                        'city' => $data['city'] ?? 'Невідомо',
                        'postal' => $data['postal'] ?? '',
                        'latitude' => (float)($data['latitude'] ?? 0),
                        'longitude' => (float)($data['longitude'] ?? 0),
                        'timezone' => $data['timezone'] ?? ''
                    ];
                }
            }
            
            // Fallback к ip-api.com
            return $this->getGeolocationFallback($ip);
            
        } catch (Exception $e) {
            logError('Geolocation fallback error: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Получение сетевой информации
     */
    private function getNetworkInfo($ip) {
        try {
            // Получаем информацию через ip-api.com
            $url = "http://ip-api.com/json/{$ip}?fields=isp,org,as,proxy,hosting";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'StormHosting-IPChecker/1.0'
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && $data['status'] === 'success') {
                    // Извлекаем ASN из поля 'as'
                    $asn = '';
                    if (isset($data['as'])) {
                        preg_match('/AS(\d+)/', $data['as'], $matches);
                        $asn = $matches[1] ?? '';
                    }
                    
                    return [
                        'isp' => $data['isp'] ?? 'Невідомо',
                        'org' => $data['org'] ?? 'Невідомо',
                        'asn' => $asn,
                        'connection_type' => $this->getConnectionType($data),
                        'usage_type' => $this->getUsageType($data),
                        'is_proxy' => $data['proxy'] ?? false
                    ];
                }
            }
            
        } catch (Exception $e) {
            logError('Network info error: ' . $e->getMessage());
        }
        
        return [
            'isp' => 'Невідомо',
            'org' => 'Невідомо',
            'asn' => '',
            'connection_type' => 'Невідомо',
            'usage_type' => 'Невідомо',
            'is_proxy' => false
        ];
    }
    
    /**
     * Определение типа соединения
     */
    private function getConnectionType($data) {
        $isp = strtolower($data['isp'] ?? '');
        
        if (strpos($isp, 'mobile') !== false || strpos($isp, 'cellular') !== false) {
            return 'Мобільний';
        } elseif (strpos($isp, 'fiber') !== false || strpos($isp, 'ftth') !== false) {
            return 'Оптоволокно';
        } elseif (strpos($isp, 'cable') !== false) {
            return 'Кабельний';
        } elseif (strpos($isp, 'dsl') !== false || strpos($isp, 'adsl') !== false) {
            return 'DSL';
        } elseif ($data['hosting'] ?? false) {
            return 'Хостинг/Датацентр';
        }
        
        return 'Широкосмуговий';
    }
    
    /**
     * Определение типа использования
     */
    private function getUsageType($data) {
        if ($data['hosting'] ?? false) {
            return 'Комерційний хостинг';
        } elseif ($data['proxy'] ?? false) {
            return 'Проксі/VPN';
        }
        
        return 'Домашнє використання';
    }
    
    /**
     * Проверка IP в черных списках
     */
    private function checkBlacklists($ip) {
        $results = [];
        
        // Для IPv6 пропускаем RBL проверки (большинство RBL не поддерживают IPv6)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            foreach ($this->blacklists as $rbl => $name) {
                $results[] = [
                    'name' => $name,
                    'listed' => false,
                    'checked' => false,
                    'reason' => 'IPv6 не підтримується'
                ];
            }
            return $results;
        }
        
        // Реверсируем IP для RBL запросов
        $reversedIP = $this->reverseIP($ip);
        
        foreach ($this->blacklists as $rbl => $name) {
            $hostname = $reversedIP . '.' . $rbl;
            
            try {
                // Проверяем с таймаутом
                $result = $this->dnsLookupWithTimeout($hostname, 2);
                
                $results[] = [
                    'name' => $name,
                    'listed' => $result !== false,
                    'checked' => true,
                    'response' => $result ?: null
                ];
                
            } catch (Exception $e) {
                $results[] = [
                    'name' => $name,
                    'listed' => false,
                    'checked' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Реверс IP для RBL запросов
     */
    private function reverseIP($ip) {
        $parts = explode('.', $ip);
        return implode('.', array_reverse($parts));
    }
    
    /**
     * DNS lookup с таймаутом
     */
    private function dnsLookupWithTimeout($hostname, $timeout = 2) {
        $old = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', $timeout);
        
        $result = gethostbyname($hostname);
        
        ini_set('default_socket_timeout', $old);
        
        // Если результат равен исходному хосту, значит не найден
        return ($result !== $hostname) ? $result : false;
    }
    
    /**
     * Анализ угроз (упрощенная версия)
     */
    private function checkThreats($ip) {
        try {
            // Проверяем несколько известных источников угроз
            $threats = [
                'risk_level' => 'Низький',
                'confidence' => 0,
                'categories' => [],
                'last_seen' => null
            ];
            
            // Проверка через AbuseIPDB API (нужен API ключ)
            // $this->checkAbuseIPDB($ip, $threats);
            
            // Проверка через VirusTotal API (нужен API ключ)
            // $this->checkVirusTotal($ip, $threats);
            
            // Простая эвристическая проверка
            $this->performHeuristicCheck($ip, $threats);
            
            return $threats;
            
        } catch (Exception $e) {
            logError('Threat analysis error: ' . $e->getMessage());
            return [
                'risk_level' => 'Невідомо',
                'confidence' => 0,
                'categories' => [],
                'last_seen' => null
            ];
        }
    }
    
    /**
     * Эвристическая проверка угроз
     */
    private function performHeuristicCheck($ip, &$threats) {
        // Проверяем известные диапазоны TOR выходных узлов
        if ($this->isTorExitNode($ip)) {
            $threats['categories'][] = 'TOR';
            $threats['risk_level'] = 'Середній';
            $threats['confidence'] = 70;
        }
        
        // Проверяем известные диапазоны VPN/прокси
        if ($this->isKnownVPN($ip)) {
            $threats['categories'][] = 'VPN/Proxy';
            $threats['risk_level'] = 'Низький';
            $threats['confidence'] = 50;
        }
    }
    
    /**
     * Проверка на TOR выходной узел (упрощенная)
     */
    private function isTorExitNode($ip) {
        // Это упрощенная проверка, в реальности нужно загружать список TOR узлов
        $torRanges = [
            '185.220.', '199.87.', '176.10.', '185.100.'
        ];
        
        foreach ($torRanges as $range) {
            if (strpos($ip, $range) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Проверка на известные VPN/прокси (упрощенная)
     */
    private function isKnownVPN($ip) {
        // Проверяем некоторые известные диапазоны VPN провайдеров
        $vpnRanges = [
            '104.28.', '172.67.', '104.16.' // Cloudflare
        ];
        
        foreach ($vpnRanges as $range) {
            if (strpos($ip, $range) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Расчет расстояния между координатами
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        if ($lat2 == 0 || $lng2 == 0) {
            return null;
        }
        
        $earthRadius = 6371; // км
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return [
            'km' => round($distance),
            'miles' => round($distance * 0.621371),
            'flight_time' => $this->estimateFlightTime($distance)
        ];
    }
    
    /**
     * Оценка времени полета
     */
    private function estimateFlightTime($distanceKm) {
        $avgSpeed = 900; // км/ч средняя скорость самолета
        $hours = $distanceKm / $avgSpeed;
        
        if ($hours < 1) {
            return round($hours * 60) . ' хв';
        } elseif ($hours < 24) {
            return round($hours, 1) . ' год';
        } else {
            return round($hours / 24, 1) . ' дн';
        }
    }
    
    /**
     * Получение погоды (упрощенная версия)
     */
    private function getWeather($lat, $lng) {
        try {
            // Можно использовать OpenWeatherMap API (нужен API ключ)
            // Пока возвращаем заглушку
            return [
                'temperature' => rand(15, 25),
                'description' => 'Хмарно',
                'condition' => 'cloudy',
                'humidity' => rand(40, 80),
                'wind_speed' => rand(2, 10),
                'visibility' => rand(5, 15)
            ];
            
        } catch (Exception $e) {
            logError('Weather error: ' . $e->getMessage());
            return null;
        }
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
    $ip = trim($_POST['ip'] ?? '');
    $optionsJson = $_POST['options'] ?? '{}';
    $userLocationJson = $_POST['user_location'] ?? null;
    
    if (empty($ip)) {
        sendJsonResponse(['error' => 'IP адреса не вказана'], 400);
    }
    
    // Парсинг опций
    $options = json_decode($optionsJson, true) ?: [];
    
    // Добавляем локацию пользователя
    if ($userLocationJson) {
        $options['userLocation'] = $userLocationJson;
    }
    
    // Создание экземпляра API и выполнение проверки
    $api = new IPCheckAPI($pdo);
    $results = $api->checkIP($ip, $options);
    
    // Сохраняем в базу данных
    saveToDatabase($pdo, $ip, $clientIp, $_SERVER['HTTP_USER_AGENT'] ?? '', $results);
    
    // Возврат результатов
    sendJsonResponse($results);
    
} catch (Exception $e) {
    // Обработка ошибок
    $statusCode = $e->getCode() ?: 500;
    logError('Exception in main handler: ' . $e->getMessage());
    
    sendJsonResponse([
        'error' => $e->getMessage(),
        'status_code' => $statusCode,
        'timestamp' => date('c'),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ], $statusCode);
    
} catch (Error $e) {
    // Обработка фатальных ошибок
    logError('Fatal Error: ' . $e->getMessage());
    
    sendJsonResponse([
        'error' => 'Критична помилка: ' . $e->getMessage(),
        'status_code' => 500,
        'timestamp' => date('c'),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ], 500);
}

// Дополнительные функции для расширения функционала

/**
 * Функция для проверки через AbuseIPDB API
 * (требует API ключ)
 */
function checkAbuseIPDB($ip, &$threats, $apiKey = null) {
    if (!$apiKey) return;
    
    try {
        $url = 'https://api.abuseipdb.com/api/v2/check';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Key: ' . $apiKey,
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => http_build_query([
                'ipAddress' => $ip,
                'maxAgeInDays' => 90,
                'verbose' => ''
            ])
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            
            if ($data && isset($data['data'])) {
                $result = $data['data'];
                
                if ($result['abuseConfidencePercentage'] > 25) {
                    $threats['risk_level'] = 'Високий';
                    $threats['confidence'] = $result['abuseConfidencePercentage'];
                    $threats['categories'] = array_merge($threats['categories'], ['Abuse']);
                    $threats['last_seen'] = $result['lastReportedAt'] ?? null;
                }
            }
        }
        
    } catch (Exception $e) {
        logError('AbuseIPDB error: ' . $e->getMessage());
    }
}

/**
 * Функция для проверки через VirusTotal API
 * (требует API ключ)
 */
function checkVirusTotal($ip, &$threats, $apiKey = null) {
    if (!$apiKey) return;
    
    try {
        $url = "https://www.virustotal.com/vtapi/v2/ip-address/report";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => http_build_query([
                'apikey' => $apiKey,
                'ip' => $ip
            ])
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            
            if ($data && $data['response_code'] === 1) {
                $positives = $data['detected_communicating_samples'] ?? [];
                $detected_urls = $data['detected_urls'] ?? [];
                
                if (count($positives) > 0 || count($detected_urls) > 0) {
                    $threats['risk_level'] = 'Високий';
                    $threats['confidence'] = max($threats['confidence'], 80);
                    $threats['categories'] = array_merge($threats['categories'], ['Malware']);
                }
            }
        }
        
    } catch (Exception $e) {
        logError('VirusTotal error: ' . $e->getMessage());
    }
}

/**
 * Функция для получения погоды через OpenWeatherMap API
 * (требует API ключ)
 */
function getWeatherData($lat, $lng, $apiKey = null) {
    if (!$apiKey) {
        // Возвращаем заглушку без API ключа
        return [
            'temperature' => rand(15, 25),
            'description' => 'Хмарно',
            'condition' => 'cloudy',
            'humidity' => rand(40, 80),
            'wind_speed' => rand(2, 10),
            'visibility' => rand(5, 15)
        ];
    }
    
    try {
        $url = "https://api.openweathermap.org/data/2.5/weather";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query([
                'lat' => $lat,
                'lon' => $lng,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'uk'
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            
            if ($data && isset($data['main'])) {
                return [
                    'temperature' => round($data['main']['temp']),
                    'description' => $data['weather'][0]['description'] ?? 'Невідомо',
                    'condition' => $data['weather'][0]['main'] ?? 'unknown',
                    'humidity' => $data['main']['humidity'] ?? 0,
                    'wind_speed' => round($data['wind']['speed'] ?? 0),
                    'visibility' => round(($data['visibility'] ?? 0) / 1000, 1)
                ];
            }
        }
        
    } catch (Exception $e) {
        logError('Weather API error: ' . $e->getMessage());
    }
    
    return null;
}

/**
 * Расширенная проверка TOR узлов
 * (загружает актуальный список с Tor Project)
 */
function checkTorExitNodes($ip) {
    try {
        // Простая проверка по известным паттернам
        // В продакшене можно загружать актуальный список
        $torExitListUrl = 'https://check.torproject.org/torbulkexitlist';
        
        // Для демонстрации используем кэшированную проверку
        $cacheFile = sys_get_temp_dir() . '/tor_exit_nodes.txt';
        $cacheTime = 3600; // 1 час
        
        if (!file_exists($cacheFile) || (time() - filemtime($cacheFile)) > $cacheTime) {
            // Загружаем новый список (с таймаутом)
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'StormHosting-IPChecker/1.0'
                ]
            ]);
            
            $torList = @file_get_contents($torExitListUrl, false, $context);
            if ($torList) {
                file_put_contents($cacheFile, $torList);
            }
        }
        
        if (file_exists($cacheFile)) {
            $torNodes = file_get_contents($cacheFile);
            return strpos($torNodes, $ip) !== false;
        }
        
    } catch (Exception $e) {
        logError('TOR check error: ' . $e->getMessage());
    }
    
    return false;
}