<?php
/**
 * IP Check API Configuration
 * Файл: /includes/ip-check-config.php
 * 
 * Конфигурация API ключей для внешних сервисов
 * Скопируйте этот файл и настройте свои API ключи
 */

// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// API ключи для внешних сервисов (замените на свои)
define('ABUSEIPDB_API_KEY', ''); // https://www.abuseipdb.com/api
define('VIRUSTOTAL_API_KEY', ''); // https://www.virustotal.com/gui/join-us
define('OPENWEATHERMAP_API_KEY', ''); // https://openweathermap.org/api

// Настройки кэширования
define('IP_CACHE_ENABLED', true);
define('IP_CACHE_TTL', 3600); // 1 час в секундах
define('GEOLOCATION_CACHE_TTL', 86400); // 24 часа
define('BLACKLIST_CACHE_TTL', 1800); // 30 минут

// Настройки Rate Limiting
define('IP_CHECK_RATE_LIMIT', 100); // запросов в час на IP
define('IP_CHECK_DAILY_LIMIT', 1000); // запросов в день на IP

// Настройки безопасности
define('ENABLE_BLACKLIST_CHECK', true);
define('ENABLE_THREAT_INTEL', true);
define('ENABLE_WEATHER_CHECK', true);
define('ENABLE_DISTANCE_CALC', true);

// Максимальное количество одновременных RBL проверок
define('MAX_CONCURRENT_RBL_CHECKS', 10);

// Таймауты для внешних API (в секундах)
define('GEOLOCATION_TIMEOUT', 10);
define('RBL_TIMEOUT', 3);
define('THREAT_INTEL_TIMEOUT', 15);
define('WEATHER_TIMEOUT', 8);

// Fallback сервисы геолокации
$GEOLOCATION_SERVICES = [
    'primary' => 'ipapi.co',
    'fallback' => 'ip-api.com',
    'backup' => 'ipinfo.io'
];

// Список RBL серверов (можно настроить приоритеты)
$RBL_PROVIDERS = [
    'zen.spamhaus.org' => [
        'name' => 'Spamhaus ZEN',
        'priority' => 1,
        'timeout' => 3
    ],
    'bl.spamcop.net' => [
        'name' => 'SpamCop',
        'priority' => 2,
        'timeout' => 3
    ],
    'b.barracudacentral.org' => [
        'name' => 'Barracuda',
        'priority' => 2,
        'timeout' => 3
    ],
    'dnsbl.sorbs.net' => [
        'name' => 'SORBS',
        'priority' => 3,
        'timeout' => 3
    ],
    'cbl.abuseat.org' => [
        'name' => 'Composite Blocking List',
        'priority' => 1,
        'timeout' => 3
    ]
];

// Логирование
define('ENABLE_IP_CHECK_LOGGING', true);
define('LOG_FAILED_REQUESTS', true);
define('LOG_SUSPICIOUS_ACTIVITY', true);

// Уведомления администратора
define('ADMIN_EMAIL', 'admin@stormhosting.ua');
define('NOTIFY_HIGH_RISK_IPS', false);
define('NOTIFY_RATE_LIMIT_EXCEEDED', false);

/**
 * Функция для получения конфигурации RBL провайдеров
 */
function getRBLProviders() {
    global $RBL_PROVIDERS;
    return $RBL_PROVIDERS;
}

/**
 * Функция для получения сервисов геолокации
 */
function getGeolocationServices() {
    global $GEOLOCATION_SERVICES;
    return $GEOLOCATION_SERVICES;
}

/**
 * Проверка доступности API ключей
 */
function checkAPIKeysAvailability() {
    $status = [
        'abuseipdb' => !empty(ABUSEIPDB_API_KEY),
        'virustotal' => !empty(VIRUSTOTAL_API_KEY),
        'openweathermap' => !empty(OPENWEATHERMAP_API_KEY)
    ];
    
    return $status;
}

/**
 * Получение конфигурации для фронтенда
 */
function getPublicConfig() {
    return [
        'features' => [
            'blacklist_check' => ENABLE_BLACKLIST_CHECK,
            'threat_intel' => ENABLE_THREAT_INTEL,
            'weather_check' => ENABLE_WEATHER_CHECK,
            'distance_calc' => ENABLE_DISTANCE_CALC
        ],
        'limits' => [
            'rate_limit' => IP_CHECK_RATE_LIMIT,
            'daily_limit' => IP_CHECK_DAILY_LIMIT
        ],
        'timeouts' => [
            'geolocation' => GEOLOCATION_TIMEOUT,
            'rbl' => RBL_TIMEOUT,
            'threat_intel' => THREAT_INTEL_TIMEOUT
        ]
    ];
}

// Инициализация логгера если нужно
if (ENABLE_IP_CHECK_LOGGING && !function_exists('logIPCheckActivity')) {
    function logIPCheckActivity($level, $message, $context = []) {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/ip-check.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        if (is_writable($logDir)) {
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = $context ? ' ' . json_encode($context) : '';
            $logEntry = "[{$timestamp}] {$level}: {$message}{$contextStr}\n";
            
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        }
    }
}
?>