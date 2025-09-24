<?php
// Простейший тест PHP
echo "PHP работает!<br>";
echo "Версия PHP: " . phpversion() . "<br>";
echo "Время: " . date('Y-m-d H:i:s') . "<br>";

// Проверим основные функции
if (function_exists('session_start')) {
    echo "✅ Sessions работают<br>";
} else {
    echo "❌ Sessions не работают<br>";
}

if (function_exists('json_encode')) {
    echo "✅ JSON работает<br>";
} else {
    echo "❌ JSON не работает<br>";
}

// Попробуем запустить сессию
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "✅ Сессия запущена<br>";
} catch (Exception $e) {
    echo "❌ Ошибка сессии: " . $e->getMessage() . "<br>";
}

// Проверим права записи
if (is_writable('.')) {
    echo "✅ Директория доступна для записи<br>";
} else {
    echo "❌ Нет прав на запись<br>";
}

echo "<hr>";
echo "Если вы видите этот текст - PHP работает нормально<br>";
echo "HTTP 500 ошибка скорее всего в другом месте<br>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>StormHosting UA - Test</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>StormHosting UA - Диагностика</h1>
    <p>Если HTML отображается - проблема не в основном PHP</p>
    
    <h2>Информация о сервере:</h2>
    <ul>
        <li>Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></li>
        <li>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></li>
        <li>Request URI: <?php echo $_SERVER['REQUEST_URI'] ?? 'Unknown'; ?></li>
        <li>Script Name: <?php echo $_SERVER['SCRIPT_NAME'] ?? 'Unknown'; ?></li>
    </ul>
    
    <h2>Следующие шаги:</h2>
    <ol>
        <li>Если этот файл работает - проблема в сложном коде</li>
        <li>Проверьте логи ошибок сервера</li>
        <li>Попробуйте загрузить простую HTML страницу</li>
    </ol>
</body>
</html>