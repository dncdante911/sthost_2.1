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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Валидируем входные данные
$required_fields = ['plan_id', 'os_template_id', 'hostname'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

$planId = (int)$_POST['plan_id'];
$osTemplateId = (int)$_POST['os_template_id'];
$hostname = trim($_POST['hostname']);
$domainName = trim($_POST['domain_name'] ?? '');

// Валидация hostname
if (!preg_match('/^[a-zA-Z0-9-]+$/', $hostname)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid hostname. Only letters, numbers and hyphens allowed.'
    ]);
    exit;
}

if (strlen($hostname) < 3 || strlen($hostname) > 63) {
    echo json_encode([
        'success' => false, 
        'message' => 'Hostname must be between 3 and 63 characters long.'
    ]);
    exit;
}

// Валидация доменного имени (если указано)
if (!empty($domainName) && !filter_var($domainName, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid domain name format.'
    ]);
    exit;
}

try {
    // Проверяем лимиты пользователя (можно добавить в будущем)
    $stmt = $pdo->prepare("SELECT COUNT(*) as vps_count FROM vps_instances WHERE user_id = ? AND status != 'destroyed'");
    $stmt->execute([$user_id]);
    $currentVPSCount = $stmt->fetchColumn();
    
    // Для примера, лимит 10 VPS на пользователя
    if ($currentVPSCount >= 10) {
        echo json_encode([
            'success' => false, 
            'message' => 'You have reached the maximum number of VPS instances (10).'
        ]);
        exit;
    }
    
    // Проверяем существование плана
    $stmt = $pdo->prepare("SELECT * FROM vps_plans WHERE id = ? AND is_active = 1");
    $stmt->execute([$planId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$plan) {
        echo json_encode([
            'success' => false, 
            'message' => 'Selected VPS plan not found or inactive.'
        ]);
        exit;
    }
    
    // Проверяем существование шаблона ОС
    $stmt = $pdo->prepare("SELECT * FROM vps_os_templates WHERE id = ? AND is_active = 1");
    $stmt->execute([$osTemplateId]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$template) {
        echo json_encode([
            'success' => false, 
            'message' => 'Selected OS template not found or inactive.'
        ]);
        exit;
    }
    
    // Создаем VPS Manager
    $vpsManager = new VPSManager();
    
    // Логируем начало операции создания
    $stmt = $pdo->prepare("
        INSERT INTO security_logs (ip_address, user_id, action, details, severity) 
        VALUES (?, ?, 'vps_create_attempt', ?, 'low')
    ");
    $stmt->execute([
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $user_id,
        json_encode([
            'hostname' => $hostname,
            'plan_id' => $planId,
            'os_template_id' => $osTemplateId
        ])
    ]);
    
    // Создаем VPS
    $result = $vpsManager->createVPS($user_id, $planId, $osTemplateId, $hostname, $domainName ?: null);
    
    if ($result['success']) {
        // Логируем успешное создание
        $stmt = $pdo->prepare("
            INSERT INTO security_logs (ip_address, user_id, action, details, severity) 
            VALUES (?, ?, 'vps_created', ?, 'low')
        ");
        $stmt->execute([
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $user_id,
            json_encode([
                'vps_id' => $result['vps_id'],
                'hostname' => $hostname,
                'ip_address' => $result['ip_address']
            ])
        ]);
        
        // Отправляем email пользователю (заглушка)
        sendVPSCreationEmail(
            $_SESSION['user_email'],
            $_SESSION['user_name'],
            $hostname,
            $result['ip_address'],
            $result['root_password']
        );
        
        // Удаляем пароль из ответа для безопасности
        unset($result['root_password']);
        unset($result['vnc_password']);
    } else {
        // Логируем ошибку создания
        $stmt = $pdo->prepare("
            INSERT INTO security_logs (ip_address, user_id, action, details, severity) 
            VALUES (?, ?, 'vps_create_failed', ?, 'medium')
        ");
        $stmt->execute([
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $user_id,
            json_encode([
                'hostname' => $hostname,
                'error' => $result['message']
            ])
        ]);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log('VPS Creation API Error: ' . $e->getMessage());
    
    // Логируем критическую ошибку
    $stmt = $pdo->prepare("
        INSERT INTO security_logs (ip_address, user_id, action, details, severity) 
        VALUES (?, ?, 'vps_create_error', ?, 'high')
    ");
    $stmt->execute([
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $user_id,
        $e->getMessage()
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred while creating VPS. Please contact support.'
    ]);
}

/**
 * Отправка email с данными созданного VPS
 */
function sendVPSCreationEmail($email, $userName, $hostname, $ipAddress, $rootPassword) {
    // В реальном проекте здесь будет отправка email через SMTP
    // Для демонстрации просто логируем
    
    $subject = "VPS Created Successfully - $hostname";
    $message = "
    Dear $userName,
    
    Your VPS has been created successfully!
    
    VPS Details:
    - Hostname: $hostname
    - IP Address: $ipAddress
    - Root Password: $rootPassword
    
    You can access your VPS via SSH:
    ssh root@$ipAddress
    
    VNC Console is also available in your client panel.
    
    Best regards,
    StormHosting UA Team
    ";
    
    // Здесь бы был код отправки email
    error_log("VPS Creation Email sent to $email: $subject");
    
    return true;
}
?>