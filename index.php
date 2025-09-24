<?php
// StormHosting UA - Main Index File
// Константа для защиты от прямого доступа
define('SECURE_ACCESS', true);

// Включаем отображение ошибок только для отладки (отключите в продакшене)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Проверяем существование файлов перед подключением
if (!file_exists('includes/config.php')) {
    die('Error: includes/config.php not found. Please create the configuration file.');
}

try {
    // Подключение конфигурации из includes
    require_once 'includes/config.php';
    
    // Подключаем остальные файлы если существуют
    if (file_exists('includes/db_connect.php')) {
        require_once 'includes/db_connect.php';
    }
    
} catch (Exception $e) {
    die('Configuration Error: ' . $e->getMessage());
}

// Получаем текущий язык
$current_lang = $_SESSION['lang'] ?? DEFAULT_LANG;

// Обработка смены языка
if (isset($_POST['change_language']) && isset($_POST['language'])) {
    if (in_array($_POST['language'], $available_languages)) {
        $_SESSION['lang'] = $_POST['language'];
        $current_lang = $_POST['language'];
        // Редирект для очистки POST данных
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Загрузка языкового файла
$lang_file = "lang/{$current_lang}.php";
if (file_exists($lang_file)) {
    require_once $lang_file;
} else {
    // Базовые переводы как fallback
    $lang = [
        'site_name' => 'StormHosting UA',
        'site_slogan' => 'Надійний хостинг для вашого бізнесу',
        'nav_home' => 'Головна',
        'nav_domains' => 'Домени',
        'nav_hosting' => 'Хостинг',
        'nav_vds' => 'VDS/VPS',
        'nav_tools' => 'Інструменти',
        'nav_info' => 'Інформація',
        'nav_contacts' => 'Контакти',
        'hero_title' => 'Професійний хостинг з підтримкою 24/7',
        'hero_subtitle' => 'Швидкі SSD сервери, безкоштовний SSL, миттєва активація',
        'language_ua' => 'Українська',
        'language_en' => 'English',
        'language_ru' => 'Русский'
    ];
}

// Функция для получения переводов
if (!function_exists('t')) {
    function t($key, $default = null) {
        global $lang;
        return $lang[$key] ?? $default ?? $key;
    }
}

// Дополнительные функции если не определены в конфиге
if (!function_exists('formatPrice')) {
    function formatPrice($price, $currency = 'грн') {
        return number_format($price, 0, '.', ' ') . ' ' . $currency;
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd.m.Y') {
        return date($format, strtotime($date));
    }
}

// Простая маршрутизация
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$segments = explode('/', $path);
//$page = $segments[0] ?: 'home';
$page = $_GET['page'] ?? 'home';

// Защита от некорректных символов в URL
if (!preg_match('/^[a-zA-Z0-9\-_\/]*$/', $path)) {
    http_response_code(400);
    die('Invalid URL');
}

// Настройки страницы
$page_title = t('site_name');
$meta_description = 'StormHosting UA - надійний хостинг провайдер України. Хостинг сайтів, VDS/VPS сервери, реєстрація доменів. Підтримка 24/7, SSL сертифікати, 99.9% аптайм.';
$meta_keywords = 'хостинг україна, vps сервер, реєстрація домену, ssl сертифікат, хостинг сайтів, дешевий хостинг';
$canonical_url = SITE_URL . '/' . $path;

// Получение данных для главной страницы
try {
    // Проверяем доступность БД
    if (defined('DB_AVAILABLE') && DB_AVAILABLE && function_exists('db_fetch_all')) {
        // Пытаемся получить данные из БД
        $popular_domains = db_fetch_all(
            "SELECT zone, price_registration FROM domain_zones WHERE is_popular = 1 AND is_active = 1 ORDER BY price_registration ASC LIMIT 6"
        );
        
        $popular_hosting = db_fetch_all(
            "SELECT * FROM hosting_plans WHERE is_active = 1 ORDER BY is_popular DESC, price_monthly ASC LIMIT 3"
        );
        
        $latest_news = db_fetch_all(
            "SELECT id, title_{$current_lang} as title, content_{$current_lang} as content, image, created_at 
             FROM news WHERE is_published = 1 ORDER BY is_featured DESC, created_at DESC LIMIT 4"
        );
    } else {
        throw new Exception('Database not available');
    }
} catch (Exception $e) {
    // Используем статические данные если БД недоступна
    $popular_domains = [
        ['zone' => '.com', 'price_registration' => 350],
        ['zone' => '.net', 'price_registration' => 450],
        ['zone' => '.org', 'price_registration' => 400],
        ['zone' => '.ua', 'price_registration' => 200],
        ['zone' => '.com.ua', 'price_registration' => 150],
        ['zone' => '.info', 'price_registration' => 300]
    ];
    
    $popular_hosting = [
        [
            'id' => 1,
            'name_ua' => 'Базовий',
            'name_en' => 'Basic',
            'name_ru' => 'Базовый',
            'disk_space' => 1024,
            'bandwidth' => 10,
            'databases' => 1,
            'email_accounts' => 5,
            'price_monthly' => 99,
            'price_yearly' => 990,
            'is_popular' => 0
        ],
        [
            'id' => 2,
            'name_ua' => 'Стандарт',
            'name_en' => 'Standard',
            'name_ru' => 'Стандарт',
            'disk_space' => 5120,
            'bandwidth' => 50,
            'databases' => 5,
            'email_accounts' => 20,
            'price_monthly' => 199,
            'price_yearly' => 1990,
            'is_popular' => 1
        ],
        [
            'id' => 3,
            'name_ua' => 'Преміум',
            'name_en' => 'Premium',
            'name_ru' => 'Премиум',
            'disk_space' => 10240,
            'bandwidth' => 100,
            'databases' => 10,
            'email_accounts' => 50,
            'price_monthly' => 399,
            'price_yearly' => 3990,
            'is_popular' => 0
        ]
    ];
    
    $latest_news = [
        [
            'id' => 1,
            'title' => 'Запуск нових тарифних планів',
            'content' => 'Ми раді представити оновлені тарифні плани хостингу з покращеними характеристиками та доступними цінами.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'image' => null
        ],
        [
            'id' => 2,
            'title' => 'Безкоштовні SSL сертифікати',
            'content' => 'Тепер всі наші клієнти отримують безкоштовні SSL сертифікати Let\'s Encrypt для своїх сайтів.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'image' => null
        ],
        [
            'id' => 3,
            'title' => 'Підтримка PHP 8.2',
            'content' => 'Оновили наші сервери до підтримки найновішої версії PHP 8.2 для кращої продуктивності.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
            'image' => null
        ]
    ];
}

// Обработка AJAX запросов
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    $ajax_action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($ajax_action) {
        case 'test_db':
            $db_test = testDatabaseConnection();
            echo json_encode($db_test);
            exit;
            
        case 'check_status':
            echo json_encode([
                'status' => 'ok',
                'db_available' => defined('DB_AVAILABLE') ? DB_AVAILABLE : false,
                'time' => date('Y-m-d H:i:s')
            ]);
            exit;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
            exit;
    }
}

// Определяем какую страницу показать
switch($page) {
    case 'test-db':
        // Страница тестирования БД
        if (function_exists('testDatabaseConnection')) {
            $db_test = testDatabaseConnection();
            echo "<h1>Database Connection Test</h1>";
            if ($db_test['success']) {
                echo "<p style='color: green;'>✅ Database connection successful!</p>";
            } else {
                echo "<p style='color: red;'>❌ Database connection failed:</p>";
                echo "<pre>" . htmlspecialchars($db_test['error']) . "</pre>";
                echo "<h3>Possible solutions:</h3>";
                echo "<ul>";
                echo "<li>Check if database user exists</li>";
                echo "<li>Verify password (check for special characters)</li>";
                echo "<li>Ensure database permissions are correct</li>";
                echo "<li>Run the SQL commands from fix_database_user.sql</li>";
                echo "</ul>";
            }
        } else {
            echo "<p>Database test function not available</p>";
        }
        exit;
        
    case 'home':
    case '':
    default:
        // Подключаем header
        include 'includes/header.php';
        
        // Показываем главную страницу
        include 'pages/home.php';
        break;
}
?>