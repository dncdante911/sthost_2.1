<?php
/**
 * VPS Order API
 * Обработка заказов VPS
 * Файл: /api/vps/order.php
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
        // Прямое подключение к БД
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

// Валидация входных данных
function validateOrderData($data) {
    $errors = [];
    
    // Проверяем plan_id
    if (empty($data['plan_id']) || !is_numeric($data['plan_id'])) {
        $errors[] = 'Неправильний ID плану';
    }
    
    // Проверяем hostname
    if (empty($data['hostname'])) {
        $errors[] = 'Необхідно вказати ім\'я хоста';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $data['hostname'])) {
        $errors[] = 'Ім\'я хоста може містити тільки малі літери, цифри та дефіс';
    } elseif (strlen($data['hostname']) > 32) {
        $errors[] = 'Ім\'я хоста не може бути довшим за 32 символи';
    }
    
    // Проверяем период
    if (empty($data['period']) || !in_array($data['period'], ['monthly', 'quarterly', 'annually'])) {
        $errors[] = 'Неправильний період оплати';
    }
    
    // Проверяем ОС
    if (empty($data['os_template'])) {
        $errors[] = 'Необхідно обрати операційну систему';
    }
    
    // Проверяем пароль если указан
    if (!empty($data['root_password']) && strlen($data['root_password']) < 8) {
        $errors[] = 'Пароль повинен містити мінімум 8 символів';
    }
    
    return $errors;
}

// Rate limiting
function checkRateLimit($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM vps_instances 
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        // Максимум 3 заказа VPS в час
        return $result['count'] < 3;
        
    } catch (Exception $e) {
        return true; // В случае ошибки разрешаем
    }
}

// Проверка уникальности hostname
function checkHostnameUnique($pdo, $hostname) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM vps_instances 
            WHERE hostname = ? AND status != 'terminated'
        ");
        $stmt->execute([$hostname]);
        $result = $stmt->fetch();
        
        return $result['count'] == 0;
        
    } catch (Exception $e) {
        return false;
    }
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Получаем данные из POST
    $order_data = [
        'plan_id' => $_POST['plan_id'] ?? null,
        'hostname' => strtolower(trim($_POST['hostname'] ?? '')),
        'period' => $_POST['period'] ?? 'monthly',
        'os_template' => $_POST['os_template'] ?? null,
        'root_password' => $_POST['root_password'] ?? null
    ];
    
    // Валидация данных
    $validation_errors = validateOrderData($order_data);
    if (!empty($validation_errors)) {
        sendResponse(false, implode(', ', $validation_errors));
    }
    
    // Проверяем rate limiting
    if (!checkRateLimit($pdo, $user_id)) {
        sendResponse(false, 'Забагато заказів за останню годину. Спробуйте пізніше.');
    }
    
    // Проверяем уникальность hostname
    if (!checkHostnameUnique($pdo, $order_data['hostname'])) {
        sendResponse(false, 'Ім\'я хоста вже використовується. Оберіть інше.');
    }
    
    // Создаем VPS Manager
    $vpsManager = new VPSManager($pdo);
    
    // Проверяем существование плана
    $plans_result = $vpsManager->getVPSPlans();
    if (!$plans_result['success']) {
        sendResponse(false, 'Помилка при отриманні планів VPS');
    }
    
    $plan_exists = false;
    $selected_plan = null;
    foreach ($plans_result['plans'] as $plan) {
        if ($plan['id'] == $order_data['plan_id']) {
            $plan_exists = true;
            $selected_plan = $plan;
            break;
        }
    }
    
    if (!$plan_exists) {
        sendResponse(false, 'Обраний план VPS не знайдено');
    }
    
    // Проверяем существование ОС
    $os_result = $vpsManager->getOSTemplates();
    if (!$os_result['success']) {
        sendResponse(false, 'Помилка при отриманні шаблонів ОС');
    }
    
    $os_exists = false;
    foreach ($os_result['templates'] as $os) {
        if ($os['name'] === $order_data['os_template']) {
            $os_exists = true;
            break;
        }
    }
    
    if (!$os_exists) {
        sendResponse(false, 'Обрана операційна система не знайдена');
    }
    
    // Подготавливаем конфигурацию для создания VPS
    $vps_config = [
        'hostname' => $order_data['hostname'],
        'period' => $order_data['period'],
        'os_template' => $order_data['os_template']
    ];
    
    if (!empty($order_data['root_password'])) {
        $vps_config['root_password'] = $order_data['root_password'];
    }
    
    // Создаем заказ VPS
    $create_result = $vpsManager->createVPSOrder($user_id, $order_data['plan_id'], $vps_config);
    
    if ($create_result['success']) {
        // Логируем успешный заказ
        $stmt = $pdo->prepare("
            INSERT INTO user_activity (user_id, action, details, ip_address, user_agent) 
            VALUES (?, 'vps_order_created', ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            json_encode([
                'vps_id' => $create_result['vps_id'],
                'plan_name' => $selected_plan['name_ua'],
                'hostname' => $order_data['hostname']
            ]),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        sendResponse(true, 'VPS успішно замовлено!', [
            'vps_id' => $create_result['vps_id'],
            'hostname' => $create_result['hostname'],
            'ip_address' => $create_result['ip_address'],
            'status' => $create_result['status'],
            'redirect_url' => '/client/vps/' . $create_result['vps_id']
        ]);
        
    } else {
        // Логируем неудачный заказ
        $stmt = $pdo->prepare("
            INSERT INTO user_activity (user_id, action, details, ip_address, user_agent) 
            VALUES (?, 'vps_order_failed', ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            json_encode([
                'error' => $create_result['error'],
                'hostname' => $order_data['hostname']
            ]),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        sendResponse(false, $create_result['error'] ?? 'Помилка при створенні VPS');
    }
    
} catch (Exception $e) {
    error_log("VPS order API error: " . $e->getMessage());
    sendResponse(false, 'Внутрішня помилка сервера. Спробуйте пізніше.');
}
?>