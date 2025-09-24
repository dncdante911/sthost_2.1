<?php
define('SECURE_ACCESS', true);
require_once '../includes/config.php';

// Обработка webhook от FOSSBilling
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data && isset($data['event'])) {
    switch ($data['event']) {
        case 'order_paid':
            // Заказ оплачен - активировать услугу
            activateService($data['order_id']);
            break;
            
        case 'order_canceled':
            // Заказ отменен
            cancelService($data['order_id']);
            break;
    }
}

function activateService($orderId) {
    // Логика активации услуги
    error_log("Order {$orderId} activated");
}

function cancelService($orderId) {
    // Логика отмены услуги  
    error_log("Order {$orderId} canceled");
}

echo 'OK';
?>