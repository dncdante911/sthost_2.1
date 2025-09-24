<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Настройка заголовков
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Обработка OPTIONS запроса
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Начинаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Функция для отправки ответа
function sendResponse($success, $data = [], $message = '') {
    $response = ['success' => $success];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    if (!empty($message)) {
        $response['message'] = $message;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Генерация CSRF токена
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Валидация CSRF токена
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Генерируем новый токен
        $token = generateCSRFToken();
        
        sendResponse(true, [
            'csrf_token' => $token,
            'session_id' => session_id()
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Проверяем токен
        $token = $_POST['csrf_token'] ?? $_POST['token'] ?? '';
        
        if (validateCSRFToken($token)) {
            sendResponse(true, ['valid' => true], 'Токен валідний');
        } else {
            sendResponse(false, ['valid' => false], 'Невалідний CSRF токен');
        }
    } else {
        http_response_code(405);
        sendResponse(false, [], 'Метод не дозволений');
    }
    
} catch (Exception $e) {
    error_log('CSRF Token API error: ' . $e->getMessage());
    sendResponse(false, [], 'Помилка сервера');
}
?>