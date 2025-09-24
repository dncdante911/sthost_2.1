// Файл: /api/create_order.php
<?php
define('SECURE_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/fossbilling_client.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    $fossbilling = new FOSSBillingClient();
    
    // Создание или получение клиента
    $clientData = [
        'email' => $input['client_data']['email'] ?? 'guest@sthost.pro',
        'first_name' => $input['client_data']['name'] ?? 'Guest',
        'last_name' => 'User',
        'company' => '',
        'phone' => $input['client_data']['phone'] ?? '',
        'country' => 'UA',
        'password' => bin2hex(random_bytes(8))
    ];
    
    $client = $fossbilling->createClient($clientData);
    
    if (!$client['result']) {
        throw new Exception('Client creation failed');
    }
    
    // Создание заказа
    global $service_types_map;
    $product_id = $service_types_map[$input['service_type']] ?? 1;
    
    $orderData = [
        'client_id' => $client['result']['id'],
        'product_id' => $product_id,
        'period' => $input['billing_period'] ?? '1M',
        'price' => $input['price'],
        'title' => $input['plan_name'],
        'config' => json_encode($input)
    ];
    
    $order = $fossbilling->createOrder($orderData);
    
    if (!$order['result']) {
        throw new Exception('Order creation failed');
    }
    
    // Возврат URL для оплаты
    echo json_encode([
        'success' => true,
        'order_id' => $order['result']['id'],
        'client_id' => $client['result']['id'],
        'payment_url' => $fossbilling->getPaymentUrl($order['result']['id'])
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>