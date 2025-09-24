<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Логируем выход из системы
if (isset($_SESSION['user_id'])) {
    DatabaseConnection::insert(
        "INSERT INTO security_logs (ip_address, user_id, action, details, severity) VALUES (?, ?, ?, ?, ?)",
        [
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SESSION['user_id'],
            'user_logout',
            'Користувач вийшов з системи',
            'low'
        ]
    );
}

// Удаляем remember token если есть
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $hashed_token = hash('sha256', $token);
    
    // Удаляем токен из БД
    DatabaseConnection::execute(
        "DELETE FROM remember_tokens WHERE token = ?",
        [$hashed_token]
    );
    
    // Удаляем cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Очищаем сессию
session_unset();
session_destroy();

// Перенаправляем на главную страницу
header('Location: /?logout=success');
exit;
?>