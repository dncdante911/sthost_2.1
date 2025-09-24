<?php
// Диагностический файл для отладки StormHosting UA

// Включаем отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>StormHosting UA - Диагностика системы</h1>";

// Проверяем версию PHP
echo "<h2>PHP Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Проверяем необходимые расширения
echo "<h2>Required PHP Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'curl', 'openssl'];

foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✅ Loaded' : '❌ Missing';
    echo "<p><strong>{$ext}:</strong> {$status}</p>";
}

// Проверяем файлы конфигурации
echo "<h2>Configuration Files</h2>";
$config_files = [
    'config.php',
    'includes/db_connect.php',
    'lang/ua.php',
    'pages/home.php',
    '.htaccess'
];

foreach ($config_files as $file) {
    $status = file_exists($file) ? '✅ Exists' : '❌ Missing';
    $size = file_exists($file) ? ' (' . filesize($file) . ' bytes)' : '';
    echo "<p><strong>{$file}:</strong> {$status}{$size}</p>";
}

// Проверяем права доступа к директориям
echo "<h2>Directory Permissions</h2>";
$directories = [
    'assets/',
    'assets/css/',
    'assets/js/',
    'assets/images/',
    'includes/',
    'lang/',
    'pages/'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? 'Writable' : 'Read-only';
        echo "<p><strong>{$dir}:</strong> ✅ Exists (Permissions: {$perms}, {$writable})</p>";
    } else {
        echo "<p><strong>{$dir}:</strong> ❌ Missing</p>";
    }
}

// Проверяем переменные сервера
echo "<h2>Important Server Variables</h2>";
$server_vars = ['DOCUMENT_ROOT', 'REQUEST_URI', 'HTTP_HOST', 'SERVER_NAME'];

foreach ($server_vars as $var) {
    $value = $_SERVER[$var] ?? 'Not set';
    echo "<p><strong>{$var}:</strong> {$value}</p>";
}

// Простой тест подключения к БД
echo "<h2>Database Connection Test</h2>";

try {
    // Пытаемся подключиться к БД
    $host = 'localhost';
    $dbname = 'sthostsitedb';
    $username = 'sthostdb';
    $password = '3344Frz!q0607Dm$157';
    
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p><strong>Database Connection:</strong> ✅ Success</p>";
    
    // Проверяем несколько таблиц
    $tables = ['users', 'domain_zones', 'hosting_plans'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
            $result = $stmt->fetch();
            echo "<p><strong>Table {$table}:</strong> ✅ Exists ({$result['count']} records)</p>";
        } catch (Exception $e) {
            echo "<p><strong>Table {$table}:</strong> ❌ Error: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p><strong>Database Connection:</strong> ❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><em>Note: Это нормально, если база данных еще не создана.</em></p>";
}

// Проверяем сессии
echo "<h2>Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['test'] = 'Session working';
echo "<p><strong>Sessions:</strong> " . ($_SESSION['test'] === 'Session working' ? '✅ Working' : '❌ Not working') . "</p>";

// Проверяем возможность записи файлов
echo "<h2>File Write Test</h2>";
$test_file = 'test_write.txt';
try {
    file_put_contents($test_file, 'Test write');
    if (file_exists($test_file)) {
        echo "<p><strong>File Write:</strong> ✅ Working</p>";
        unlink($test_file); // удаляем тестовый файл
    } else {
        echo "<p><strong>File Write:</strong> ❌ Failed</p>";
    }
} catch (Exception $e) {
    echo "<p><strong>File Write:</strong> ❌ Error: " . $e->getMessage() . "</p>";
}

// Информация об ошибках PHP
echo "<h2>PHP Error Log</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $recent_errors = tail($error_log, 10);
    echo "<pre>" . htmlspecialchars($recent_errors) . "</pre>";
} else {
    echo "<p>Error log not found or not configured.</p>";
}

// Тест простого роутинга
echo "<h2>Routing Test</h2>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Query String:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'None') . "</p>";

// Функция для чтения последних строк файла
function tail($filename, $lines = 10) {
    if (!file_exists($filename)) {
        return "File not found";
    }
    
    $file = file($filename);
    if (count($file) < $lines) {
        return implode('', $file);
    }
    
    return implode('', array_slice($file, -$lines));
}

echo "<h2>Recommended Next Steps</h2>";
echo "<ol>";
echo "<li>Если база данных не подключается - создайте БД и пользователя согласно config.php</li>";
echo "<li>Импортируйте schema.sql в созданную базу данных</li>";
echo "<li>Убедитесь что все файлы загружены в правильную структуру папок</li>";
echo "<li>Проверьте права доступа к файлам (644 для файлов, 755 для папок)</li>";
echo "<li>После исправления ошибок, удалите этот debug.php файл</li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>Generated at: " . date('Y-m-d H:i:s') . "</em></p>";
?>