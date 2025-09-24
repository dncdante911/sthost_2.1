// Файл: /includes/fossbilling_client.php
<?php
class FOSSBillingClient {
    private $api_url;
    private $api_token;
    
    public function __construct() {
        $this->api_url = FOSSBILLING_API_URL;
        $this->api_token = FOSSBILLING_API_TOKEN;
    }
    
    // Создание клиента
    public function createClient($clientData) {
        return $this->makeRequest('admin/client/create', $clientData);
    }
    
    // Создание заказа
    public function createOrder($orderData) {
        return $this->makeRequest('admin/order/create', $orderData);
    }
    
    // Получение URL оплаты
    public function getPaymentUrl($orderId) {
        return FOSSBILLING_URL . '/order/' . $orderId;
    }
    
    private function makeRequest($endpoint, $data) {
        $data['token'] = $this->api_token;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url . '/' . $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
?>