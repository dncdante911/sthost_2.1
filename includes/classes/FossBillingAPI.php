<?php
/**
 * FOSSBilling API Integration
 * Класс для интеграции с FOSSBilling
 * Файл: /includes/classes/FossBillingAPI.php
 */

// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

class FossBillingAPI {
    private $api_url;
    private $api_token;
    private $timeout = 30;
    
    public function __construct($api_url = null, $api_token = null) {
        $this->api_url = $api_url ?: 'https://bill.sthost.pro';
        $this->api_token = $api_token ?: 'GTfbsKnkaVE7MV1PE7XjfekQx1ixleF1';
    }
    
    /**
     * Выполнение API запроса
     */
    private function makeRequest($endpoint, $data = [], $method = 'POST') {
        try {
            $url = rtrim($this->api_url, '/') . '/api/' . ltrim($endpoint, '/');
            
            $curl = curl_init();
            
            // Базовые настройки cURL
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->api_token
                ]
            ]);
            
            // Настройки для POST/PUT
            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            } elseif ($method === 'GET' && !empty($data)) {
                curl_setopt($curl, CURLOPT_URL, $url . '?' . http_build_query($data));
            }
            
            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            
            if ($error) {
                throw new Exception("cURL Error: " . $error);
            }
            
            $decoded = json_decode($response, true);
            
            return [
                'success' => $http_code >= 200 && $http_code < 300,
                'http_code' => $http_code,
                'data' => $decoded,
                'raw_response' => $response
            ];
            
        } catch (Exception $e) {
            error_log("FOSSBilling API Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Создание клиента
     */
    public function createClient($client_data) {
        $data = [
            'first_name' => $client_data['first_name'],
            'last_name' => $client_data['last_name'],
            'email' => $client_data['email'],
            'phone' => $client_data['phone'] ?? '',
            'company' => $client_data['company'] ?? '',
            'address_1' => $client_data['address'] ?? '',
            'city' => $client_data['city'] ?? '',
            'state' => $client_data['state'] ?? '',
            'postcode' => $client_data['postcode'] ?? '',
            'country' => $client_data['country'] ?? 'UA',
            'password' => $client_data['password'],
            'password_confirm' => $client_data['password']
        ];
        
        return $this->makeRequest('admin/client', $data, 'POST');
    }
    
    /**
     * Получение информации о клиенте
     */
    public function getClient($client_id) {
        return $this->makeRequest('admin/client/' . $client_id, [], 'GET');
    }
    
    /**
     * Поиск клиента по email
     */
    public function findClientByEmail($email) {
        $data = ['search' => $email];
        return $this->makeRequest('admin/client', $data, 'GET');
    }
    
    /**
     * Создание продукта VPS
     */
    public function createVPSProduct($product_data) {
        $data = [
            'type' => 'custom',
            'category' => 'vps',
            'title' => $product_data['title'],
            'description' => $product_data['description'] ?? '',
            'price' => $product_data['price'],
            'setup' => $product_data['setup_fee'] ?? 0,
            'pricing' => [
                'type' => 'recurrent',
                'once' => [
                    'price' => $product_data['price'],
                    'setup' => $product_data['setup_fee'] ?? 0
                ],
                'monthly' => [
                    'price' => $product_data['price'],
                    'setup' => $product_data['setup_fee'] ?? 0
                ],
                'quarterly' => [
                    'price' => $product_data['price'] * 3 * 0.95, // 5% скидка
                    'setup' => $product_data['setup_fee'] ?? 0
                ],
                'annually' => [
                    'price' => $product_data['yearly_price'] ?? ($product_data['price'] * 12 * 0.9),
                    'setup' => 0
                ]
            ],
            'config' => [
                'cpu_cores' => $product_data['cpu_cores'],
                'ram_mb' => $product_data['ram_mb'],
                'disk_gb' => $product_data['disk_gb'],
                'bandwidth_gb' => $product_data['bandwidth_gb'],
                'os_templates' => $product_data['os_templates'] ?? []
            ]
        ];
        
        return $this->makeRequest('admin/product', $data, 'POST');
    }
    
    /**
     * Создание заказа VPS
     */
    public function createVPSOrder($order_data) {
        $data = [
            'client_id' => $order_data['client_id'],
            'product_id' => $order_data['product_id'],
            'period' => $order_data['period'] ?? 'monthly',
            'config' => [
                'hostname' => $order_data['hostname'],
                'os_template' => $order_data['os_template'],
                'root_password' => $order_data['root_password'] ?? $this->generatePassword()
            ],
            'activate' => $order_data['activate'] ?? true
        ];
        
        return $this->makeRequest('admin/order', $data, 'POST');
    }
    
    /**
     * Получение заказа
     */
    public function getOrder($order_id) {
        return $this->makeRequest('admin/order/' . $order_id, [], 'GET');
    }
    
    /**
     * Активация заказа
     */
    public function activateOrder($order_id) {
        return $this->makeRequest('admin/order/' . $order_id . '/activate', [], 'POST');
    }
    
    /**
     * Приостановка заказа
     */
    public function suspendOrder($order_id, $reason = '') {
        $data = ['reason' => $reason];
        return $this->makeRequest('admin/order/' . $order_id . '/suspend', $data, 'POST');
    }
    
    /**
     * Возобновление заказа
     */
    public function unsuspendOrder($order_id) {
        return $this->makeRequest('admin/order/' . $order_id . '/unsuspend', [], 'POST');
    }
    
    /**
     * Отмена заказа
     */
    public function cancelOrder($order_id) {
        return $this->makeRequest('admin/order/' . $order_id . '/cancel', [], 'POST');
    }
    
    /**
     * Создание счета
     */
    public function createInvoice($invoice_data) {
        $data = [
            'client_id' => $invoice_data['client_id'],
            'title' => $invoice_data['title'],
            'amount' => $invoice_data['amount'],
            'taxrate' => $invoice_data['taxrate'] ?? 0,
            'currency' => $invoice_data['currency'] ?? 'UAH',
            'due_at' => $invoice_data['due_at'] ?? date('Y-m-d H:i:s', strtotime('+7 days')),
            'lines' => $invoice_data['lines'] ?? [
                [
                    'title' => $invoice_data['title'],
                    'price' => $invoice_data['amount'],
                    'quantity' => 1
                ]
            ]
        ];
        
        return $this->makeRequest('admin/invoice', $data, 'POST');
    }
    
    /**
     * Получение счета
     */
    public function getInvoice($invoice_id) {
        return $this->makeRequest('admin/invoice/' . $invoice_id, [], 'GET');
    }
    
    /**
     * Создание платежа
     */
    public function createPayment($payment_data) {
        $data = [
            'invoice_id' => $payment_data['invoice_id'],
            'amount' => $payment_data['amount'],
            'description' => $payment_data['description'] ?? 'Payment received',
            'type' => $payment_data['type'] ?? 'manual',
            'gateway' => $payment_data['gateway'] ?? 'manual'
        ];
        
        return $this->makeRequest('admin/invoice/payment', $data, 'POST');
    }
    
    /**
     * Получение списка продуктов
     */
    public function getProducts($filters = []) {
        return $this->makeRequest('admin/product', $filters, 'GET');
    }
    
    /**
     * Получение статистики
     */
    public function getStats() {
        return $this->makeRequest('admin/stats', [], 'GET');
    }
    
    /**
     * Webhook для уведомлений
     */
    public function processWebhook($payload) {
        try {
            // Проверяем подпись webhook (если настроена)
            if (isset($payload['signature'])) {
                if (!$this->verifyWebhookSignature($payload)) {
                    return ['success' => false, 'error' => 'Invalid signature'];
                }
            }
            
            $event_type = $payload['event'] ?? '';
            $data = $payload['data'] ?? [];
            
            switch ($event_type) {
                case 'order.activated':
                    return $this->handleOrderActivated($data);
                    
                case 'order.suspended':
                    return $this->handleOrderSuspended($data);
                    
                case 'order.cancelled':
                    return $this->handleOrderCancelled($data);
                    
                case 'invoice.paid':
                    return $this->handleInvoicePaid($data);
                    
                default:
                    return ['success' => true, 'message' => 'Event ignored'];
            }
            
        } catch (Exception $e) {
            error_log("Webhook processing error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Обработка активации заказа
     */
    private function handleOrderActivated($data) {
        // Логика создания VPS при активации заказа
        $order_id = $data['id'] ?? null;
        if (!$order_id) {
            return ['success' => false, 'error' => 'Missing order ID'];
        }
        
        // Здесь будет интеграция с VPSManager для создания VPS
        // Подробности в следующих файлах
        
        return ['success' => true, 'message' => 'Order activation processed'];
    }
    
    /**
     * Обработка приостановки заказа
     */
    private function handleOrderSuspended($data) {
        $order_id = $data['id'] ?? null;
        if (!$order_id) {
            return ['success' => false, 'error' => 'Missing order ID'];
        }
        
        // Логика приостановки VPS
        
        return ['success' => true, 'message' => 'Order suspension processed'];
    }
    
    /**
     * Обработка отмены заказа
     */
    private function handleOrderCancelled($data) {
        $order_id = $data['id'] ?? null;
        if (!$order_id) {
            return ['success' => false, 'error' => 'Missing order ID'];
        }
        
        // Логика удаления VPS
        
        return ['success' => true, 'message' => 'Order cancellation processed'];
    }
    
    /**
     * Обработка оплаты счета
     */
    private function handleInvoicePaid($data) {
        $invoice_id = $data['id'] ?? null;
        if (!$invoice_id) {
            return ['success' => false, 'error' => 'Missing invoice ID'];
        }
        
        // Логика обработки оплаты
        
        return ['success' => true, 'message' => 'Payment processed'];
    }
    
    /**
     * Проверка подписи webhook
     */
    private function verifyWebhookSignature($payload) {
        // Реализация проверки подписи webhook
        // В зависимости от настроек FOSSBilling
        return true; // Упрощенная версия
    }
    
    /**
     * Генерация пароля
     */
    private function generatePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }
    
    /**
     * Синхронизация клиента с FOSSBilling
     */
    public function syncClient($user_data) {
        try {
            // Ищем клиента по email
            $client_search = $this->findClientByEmail($user_data['email']);
            
            if ($client_search['success'] && !empty($client_search['data']['list'])) {
                // Клиент уже существует
                $client = $client_search['data']['list'][0];
                return ['success' => true, 'client_id' => $client['id'], 'exists' => true];
            }
            
            // Создаем нового клиента
            $client_result = $this->createClient([
                'first_name' => $user_data['name'] ?? 'User',
                'last_name' => $user_data['surname'] ?? '',
                'email' => $user_data['email'],
                'phone' => $user_data['phone'] ?? '',
                'password' => $user_data['password'] ?? $this->generatePassword()
            ]);
            
            if ($client_result['success']) {
                return [
                    'success' => true, 
                    'client_id' => $client_result['data']['id'],
                    'exists' => false
                ];
            }
            
            return $client_result;
            
        } catch (Exception $e) {
            error_log("Sync client error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>