<?php
/**
 * VDS/VPS Order API Endpoint
 * Handles VPS order processing and FossBilling integration
 */

// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Headers для API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обробка OPTIONS запиту
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Функції-заглушки
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return trim(strip_tags($input));
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return !empty($token); // Заглушка
    }
}

try {
    // Підключення конфігурації
    if (file_exists('../../includes/config.php')) {
        require_once '../../includes/config.php';
    }
    
    // Підключення БД
    if (file_exists('../../includes/db_connect.php')) {
        require_once '../../includes/db_connect.php';
    }
    
} catch (Exception $e) {
    // Ігноруємо помилки підключення для API
}

/**
 * Головна функція обробки запитів
 */
function handleVPSOrder() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'POST':
            return processOrder();
        case 'GET':
            return getOrderStatus();
        default:
            return sendError('Method not allowed', 405);
    }
}

/**
 * Обробка створення замовлення VPS
 */
function processOrder() {
    try {
        // Отримання та валідація даних
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        // Валідація обов'язкових полів
        $requiredFields = ['plan_id', 'plan_name', 'billing_period', 'price'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                return sendError("Missing required field: {$field}", 400);
            }
        }
        
        // Санітизація даних
        $orderData = [
            'plan_id' => sanitizeInput($input['plan_id']),
            'plan_name' => sanitizeInput($input['plan_name']),
            'billing_period' => sanitizeInput($input['billing_period']),
            'price' => floatval($input['price']),
            'service_type' => 'vps',
            'specifications' => $input['specifications'] ?? [],
            'fossbilling' => $input['fossbilling'] ?? []
        ];
        
        // Валідація ціни
        if ($orderData['price'] <= 0) {
            return sendError('Invalid price', 400);
        }
        
        // Валідація періоду біллінгу
        if (!in_array($orderData['billing_period'], ['monthly', 'yearly'])) {
            return sendError('Invalid billing period', 400);
        }
        
        // Генерація унікального ID замовлення
        $orderId = generateOrderId();
        
        // 
        // ====== ІНТЕГРАЦІЯ З FOSSBILLING ======
        // Тут буде код інтеграції з FossBilling API
        //
        
        // 1. Створення продукту в FossBilling (якщо не існує)
        $fossBillingProduct = createFossBillingProduct($orderData);
        
        // 2. Створення замовлення в FossBilling
        $fossBillingOrder = createFossBillingOrder($orderId, $orderData);
        
        // 3. Збереження замовлення в локальній БД
        $localOrderId = saveOrderToDatabase($orderId, $orderData, $fossBillingOrder);
        
        // 4. Генерація посилання на оплату
        $paymentLink = generatePaymentLink($fossBillingOrder, $orderData);
        
        // Відповідь з даними замовлення
        return sendSuccess([
            'order_id' => $orderId,
            'local_order_id' => $localOrderId,
            'fossbilling_order_id' => $fossBillingOrder['id'] ?? null,
            'payment_link' => $paymentLink,
            'order_data' => $orderData,
            'status' => 'pending',
            'expires_at' => date('Y-m-d H:i:s', time() + 3600), // 1 година на оплату
            'message' => 'Замовлення створено успішно'
        ]);
        
    } catch (Exception $e) {
        error_log("VPS Order Error: " . $e->getMessage());
        return sendError('Internal server error', 500);
    }
}

/**
 * Отримання статусу замовлення
 */
function getOrderStatus() {
    $orderId = $_GET['order_id'] ?? '';
    
    if (empty($orderId)) {
        return sendError('Order ID required', 400);
    }
    
    try {
        // Пошук замовлення в БД
        $order = getOrderFromDatabase($orderId);
        
        if (!$order) {
            return sendError('Order not found', 404);
        }
        
        // Перевірка статусу в FossBilling
        $fossBillingStatus = getFossBillingOrderStatus($order['fossbilling_order_id']);
        
        // Оновлення локального статусу
        if ($fossBillingStatus && $fossBillingStatus !== $order['status']) {
            updateOrderStatus($orderId, $fossBillingStatus);
            $order['status'] = $fossBillingStatus;
        }
        
        return sendSuccess([
            'order_id' => $orderId,
            'status' => $order['status'],
            'order_data' => json_decode($order['order_data'], true),
            'created_at' => $order['created_at'],
            'updated_at' => $order['updated_at']
        ]);
        
    } catch (Exception $e) {
        error_log("Get Order Status Error: " . $e->getMessage());
        return sendError('Internal server error', 500);
    }
}

/**
 * Створення продукту в FossBilling
 */
function createFossBillingProduct($orderData) {
    // 
    // ====== ЗАГЛУШКА ДЛЯ FOSSBILLING API ======
    // Тут буде реальний API запит до FossBilling
    //
    
    /*
    Приклад реального коду:
    
    $fossBillingAPI = new FossBilling\Api($config['fossbilling']);
    
    $productData = [
        'type' => 'hosting',
        'title' => $orderData['plan_name'],
        'price' => $orderData['price'],
        'setup_price' => 0,
        'period' => $orderData['billing_period'],
        'config' => json_encode($orderData['specifications']),
        'stock_control' => false,
        'active' => true
    ];
    
    return $fossBillingAPI->admin_product_create($productData);
    */
    
    // ТИМЧАСОВА ЗАГЛУШКА
    return [
        'id' => 'vps_plan_' . $orderData['plan_id'],
        'title' => $orderData['plan_name'],
        'status' => 'created'
    ];
}

/**
 * Створення замовлення в FossBilling
 */
function createFossBillingOrder($orderId, $orderData) {
    // 
    // ====== ЗАГЛУШКА ДЛЯ FOSSBILLING API ======
    //
    
    /*
    Реальний код:
    
    $orderData = [
        'product_id' => $orderData['fossbilling']['product_id'],
        'period' => $orderData['billing_period'],
        'client_id' => getCurrentClientId(), // З сесії або автентифікації
        'invoice' => true,
        'config' => $orderData['specifications']
    ];
    
    return $fossBillingAPI->admin_order_create($orderData);
    */
    
    // ТИМЧАСОВА ЗАГЛУШКА
    return [
        'id' => 'fb_order_' . time(),
        'status' => 'pending',
        'invoice_id' => 'inv_' . time(),
        'amount' => $orderData['price']
    ];
}

/**
 * Збереження замовлення в локальну БД
 */
function saveOrderToDatabase($orderId, $orderData, $fossBillingOrder) {
    try {
        global $pdo;
        
        if (!isset($pdo)) {
            // Fallback якщо БД недоступна
            return 'local_' . time();
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO vps_orders (
                order_id, 
                plan_id, 
                plan_name, 
                billing_period, 
                price, 
                order_data, 
                fossbilling_order_id,
                status,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $orderId,
            $orderData['plan_id'],
            $orderData['plan_name'],
            $orderData['billing_period'],
            $orderData['price'],
            json_encode($orderData),
            $fossBillingOrder['id'] ?? null,
            'pending'
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        throw new Exception("Failed to save order");
    }
}

/**
 * Генерація посилання на оплату
 */
function generatePaymentLink($fossBillingOrder, $orderData) {
    // 
    // ====== ЗАГЛУШКА ДЛЯ ПЛАТІЖНИХ СИСТЕМ ======
    // Тут буде інтеграція з LiqPay, WayForPay, Portmone
    //
    
    $paymentData = [
        'amount' => $orderData['price'],
        'currency' => 'UAH',
        'description' => 'VPS ' . $orderData['plan_name'],
        'order_id' => $fossBillingOrder['id'],
        'return_url' => $_SERVER['HTTP_HOST'] . '/billing/success',
        'callback_url' => $_SERVER['HTTP_HOST'] . '/api/vds/payment-callback.php'
    ];
    
    // ТИМЧАСОВА ЗАГЛУШКА
    $paymentLink = '/billing/payment?' . http_build_query($paymentData);
    
    /*
    Реальний код для LiqPay:
    
    $liqpay = new LiqPay($public_key, $private_key);
    $paymentLink = $liqpay->cnb_form_raw([
        'action' => 'pay',
        'amount' => $orderData['price'],
        'currency' => 'UAH',
        'description' => 'VPS ' . $orderData['plan_name'],
        'order_id' => $fossBillingOrder['id'],
        'version' => '3',
        'server_url' => $_SERVER['HTTP_HOST'] . '/api/vds/liqpay-callback.php'
    ]);
    */
    
    return $paymentLink;
}

/**
 * Пошук замовлення в БД
 */
function getOrderFromDatabase($orderId) {
    try {
        global $pdo;
        
        if (!isset($pdo)) {
            return null;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM vps_orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Отримання статусу замовлення з FossBilling
 */
function getFossBillingOrderStatus($fossBillingOrderId) {
    // ЗАГЛУШКА
    return 'pending';
    
    /*
    Реальний код:
    return $fossBillingAPI->admin_order_get(['id' => $fossBillingOrderId])['status'];
    */
}

/**
 * Оновлення статусу замовлення
 */
function updateOrderStatus($orderId, $status) {
    try {
        global $pdo;
        
        if (!isset($pdo)) {
            return false;
        }
        
        $stmt = $pdo->prepare("UPDATE vps_orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
        return $stmt->execute([$status, $orderId]);
        
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Генерація унікального ID замовлення
 */
function generateOrderId() {
    return 'vps_' . date('Ymd') . '_' . uniqid();
}

/**
 * Відправка успішної відповіді
 */
function sendSuccess($data) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => time()
    ]);
    exit;
}

/**
 * Відправка помилки
 */
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'code' => $code,
        'timestamp' => time()
    ]);
    exit;
}

// Обробка запиту
try {
    handleVPSOrder();
} catch (Throwable $e) {
    error_log("VPS API Fatal Error: " . $e->getMessage());
    sendError('Fatal server error', 500);
}

/**
 * SQL для створення таблиці замовлень VPS (виконати в БД)
 * 
 * CREATE TABLE IF NOT EXISTS `vps_orders` (
 *   `id` int(11) NOT NULL AUTO_INCREMENT,
 *   `order_id` varchar(100) NOT NULL UNIQUE,
 *   `plan_id` int(11) NOT NULL,
 *   `plan_name` varchar(100) NOT NULL,
 *   `billing_period` enum('monthly','yearly') NOT NULL,
 *   `price` decimal(10,2) NOT NULL,
 *   `order_data` text,
 *   `fossbilling_order_id` varchar(100),
 *   `status` enum('pending','paid','active','suspended','cancelled') DEFAULT 'pending',
 *   `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
 *   `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 *   PRIMARY KEY (`id`),
 *   KEY `idx_order_id` (`order_id`),
 *   KEY `idx_status` (`status`),
 *   KEY `idx_fossbilling` (`fossbilling_order_id`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
?>