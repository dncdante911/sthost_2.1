<?php
//echo "CONFIG LOADED<br>";
//exit;
// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Основные настройки сайта
define('SITE_NAME', 'StormHosting UA');
define('SITE_URL', 'https://sthost.pro');
define('SITE_EMAIL', 'info@sthost.pro');
define('TELEGRAM_BOT_TOKEN', 'YOUR_TELEGRAM_BOT_TOKEN');
define('TELEGRAM_CHAT_ID', 'YOUR_TELEGRAM_CHAT_ID');

// Конфігурація підключення до БД сайту
$host = 'localhost';
$dbname_site = 'sthostsitedb';
$db_userconnect_site = 'sthostdb';
$db_passwd_site = '3344Frz@q0607Dm$157';

// Конфігурація підключення до БД FOSSbill
$dbname_fossbill = 'admin_fossbill';
$db_userconnect_fossbill = 'admin_fossbill';
$db_passwd_fossbill = '0607Dm$157';

// Настройки безопасности
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600); // 1 час
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 минут

// Функция генерации CSRF токена
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Функция проверки CSRF токена
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Функция очистки ввода
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function t($key, $default = '') {
    global $translations;
    return $translations[$key] ?? $default;
}

// Функция валидации email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Функция защиты от XSS
function escapeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Языковые настройки
define('DEFAULT_LANG', 'ua');
$available_languages = ['ua', 'en', 'ru'];

// API настройки
define('WHOIS_API_KEY', 'YOUR_WHOIS_API_KEY');
define('SITE_CHECK_API_KEY', 'YOUR_SITE_CHECK_API_KEY');

// Платежные системы
define('LIQPAY_PUBLIC_KEY', 'YOUR_LIQPAY_PUBLIC_KEY');
define('LIQPAY_PRIVATE_KEY', 'YOUR_LIQPAY_PRIVATE_KEY');

// ispmanager настройки
define('ISPMANAGER_URL', 'https://cp.sthost.pro');
define('ISPMANAGER_USER', 'root');
define('ISPMANAGER_PASS', '0607Dm$157');

// Libvirt настройки
define('LIBVIRT_HOST', '192.168.0.4');
define('LIBVIRT_USER', 'dncdante');
define('LIBVIRT_URI', 'qemu+ssh://dncdante@localhost/system');

// VPS настройки
define('VPS_TEMPLATES_PATH', '/var/lib/libvirt/images/templates/');
define('VPS_IMAGES_PATH', '/var/lib/libvirt/images/');

// FOSSBilling настройки
define('FOSSBILLING_URL', 'https://bill.sthost.pro');
define('FOSSBILLING_API_TOKEN', 'GTfbsKnkaVE7MV1PE7XjfekQx1ixleF1');

// Настройки почты
define('SMTP_HOST', 'mail.sthost.pro');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'support@sthost.pro');
define('SMTP_PASSWORD', '0607Dm$157');

// Настройки капчи
define('RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY');
define('RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY');

// Временная зона
date_default_timezone_set('Europe/Kiev');

// Настройки сессий с защитой
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

// Запуск сессий
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Регенерация ID сессии для защиты от фиксации
//if (!isset($_SESSION['initiated'])) {
//    session_regenerate_id(true);
//    $_SESSION['initiated'] = true;
//}

// Установка языка по умолчанию
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

// Защита от DDoS - простая проверка частоты запросов
function checkRateLimit() {
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    if (!isset($_SESSION['rate_limit'][$client_ip])) {
        $_SESSION['rate_limit'][$client_ip] = ['count' => 1, 'time' => $current_time];
        return true;
    }
    
    $rate_data = $_SESSION['rate_limit'][$client_ip];
    
    // Сброс счетчика каждую минуту
    if ($current_time - $rate_data['time'] > 60) {
        $_SESSION['rate_limit'][$client_ip] = ['count' => 1, 'time' => $current_time];
        return true;
    }
    
    // Максимум 100 запросов в минуту
    if ($rate_data['count'] > 100) {
        http_response_code(429);
        die('Too many requests');
    }
    
    $_SESSION['rate_limit'][$client_ip]['count']++;
    return true;
}

checkRateLimit();
?>