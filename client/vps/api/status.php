<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/client/vps/includes/VPSManager.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем ID VPS
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid VPS ID']);
    exit;
}

$vpsId = (int)$_GET['id'];

try {
    // Проверяем, что VPS принадлежит пользователю
    $stmt = $pdo->prepare("SELECT * FROM vps_instances WHERE id = ? AND user_id = ?");
    $stmt->execute([$vpsId, $user_id]);
    $vps = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vps) {
        echo json_encode(['success' => false, 'message' => 'VPS not found or access denied']);
        exit;
    }
    
    // Создаем VPS Manager
    $vpsManager = new VPSManager();
    
    // Получаем актуальную информацию о VPS
    $vpsInfo = $vpsManager->getVPSInfo($vpsId, $user_id);
    
    if ($vpsInfo['success']) {
        $data = $vpsInfo['data'];
        
        // Получаем статистику если VPS запущен
        $stats = null;
        if ($data['power_state'] === 'running') {
            $statsResult = $vpsManager->getVPSStats($vpsId, $user_id);
            if ($statsResult['success']) {
                $stats = $statsResult;
            }
        }
        
        // Формируем ответ
        $response = [
            'success' => true,
            'data' => [
                'id' => $data['id'],
                'hostname' => $data['hostname'],
                'status' => $data['status'],
                'power_state' => $data['power_state'],
                'ip_address' => $data['ip_address'],
                'cpu_cores' => $data['cpu_cores'],
                'ram_mb' => $data['ram_mb'],
                'storage_gb' => $data['storage_gb'],
                'plan_name' => $data['plan_name'],
                'os_name' => $data['os_name'],
                'created_at' => $data['created_at'],
                'current_state' => $data['current_state'] ?? $data['power_state'],
                'stats' => $stats,
                'last_updated' => date('Y-m-d H:i:s')
            ]
        ];
        
        // Обновляем время последней проверки статуса в БД
        $stmt = $pdo->prepare("UPDATE vps_instances SET last_stats_update = NOW() WHERE id = ?");
        $stmt->execute([$vpsId]);
        
    } else {
        $response = $vpsInfo;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('VPS Status API Error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred while getting VPS status. Please contact support.'
    ]);
}
?>