<?php
/**
 * VPS Control API
 * API для управления VPS (старт, стоп, перезагрузка и т.д.)
 * Файл: /api/vps/control.php
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Настройка заголовков
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Обработка OPTIONS запроса
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не дозволений']);
    exit;
}

// Начинаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем авторизацию
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Необхідна авторизація']);
    exit;
}

// Подключение к БД
try {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
    }
    
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
        $pdo = DatabaseConnection::getSiteConnection();
    } else {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=sthostsitedb;charset=utf8mb4",
            "sthostdb",
            "3344Frz@q0607Dm\$157",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Помилка підключення до бази даних']);
    exit;
}

// Подключаем VPS Manager
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/classes/VPSManager.php';

// Функция для отправки ответа
function sendResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Rate limiting для действий
function checkActionRateLimit($pdo, $user_id, $action) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM vps_actions va
            JOIN vps_instances vi ON va.vps_id = vi.id
            WHERE vi.user_id = ? AND va.action = ? AND va.started_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute([$user_id, $action]);
        $result = $stmt->fetch();
        
        // Максимум 10 действий одного типа за 5 минут
        return $result['count'] < 10;
        
    } catch (Exception $e) {
        return true; // В случае ошибки разрешаем
    }
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Получаем данные запроса
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendResponse(false, 'Неправильні дані запиту');
    }
    
    $vps_id = $input['vps_id'] ?? null;
    $action = $input['action'] ?? null;
    
    // Валидация входных данных
    if (!$vps_id || !is_numeric($vps_id)) {
        sendResponse(false, 'Неправильний ID VPS');
    }
    
    $allowed_actions = ['start', 'stop', 'restart', 'force_stop', 'suspend', 'resume'];
    if (!$action || !in_array($action, $allowed_actions)) {
        sendResponse(false, 'Неправильна дія');
    }
    
    // Проверяем rate limiting
    if (!checkActionRateLimit($pdo, $user_id, $action)) {
        sendResponse(false, 'Забагато дій за останні 5 хвилин. Спробуйте пізніше.');
    }
    
    // Создаем VPS Manager
    $vpsManager = new VPSManager($pdo);
    
    // Выполняем действие
    $result = $vpsManager->controlVPS($vps_id, $action, $user_id);
    
    if ($result['success']) {
        // Логируем успешное действие
        $stmt = $pdo->prepare("
            INSERT INTO user_activity (user_id, action, details, ip_address, user_agent) 
            VALUES (?, 'vps_action', ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            json_encode([
                'vps_id' => $vps_id,
                'action' => $action,
                'success' => true
            ]),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        sendResponse(true, $result['message'] ?? 'Дію виконано успішно');
        
    } else {
        // Логируем неудачное действие
        $stmt = $pdo->prepare("
            INSERT INTO user_activity (user_id, action, details, ip_address, user_agent) 
            VALUES (?, 'vps_action_failed', ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            json_encode([
                'vps_id' => $vps_id,
                'action' => $action,
                'error' => $result['error']
            ]),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        sendResponse(false, $result['error'] ?? 'Помилка при виконанні дії');
    }
    
} catch (Exception $e) {
    error_log("VPS control API error: " . $e->getMessage());
    sendResponse(false, 'Внутрішня помилка сервера');
}
?>