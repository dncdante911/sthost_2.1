<?php
define('SECURE_ACCESS', true);
// /chat/notify-support.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Базова конфігурація
define('LOG_FILE', $_SERVER['DOCUMENT_ROOT'] . '/logs/chat_messages.log');
define('URGENT_LOG_FILE', $_SERVER['DOCUMENT_ROOT'] . '/logs/urgent_messages.log');

// Створюємо папку для логів якщо не існує
$logDir = dirname(LOG_FILE);
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// Отримуємо дані
$message = $_POST['message'] ?? '';
$subject = $_POST['subject'] ?? 'Нове повідомлення';
$urgent = ($_POST['urgent'] ?? '0') === '1';
$page = $_POST['page'] ?? '';
$time = $_POST['time'] ?? date('Y-m-d H:i:s');
$user_agent = $_POST['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '';

// Валідація
if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Повідомлення не може бути порожнім']);
    exit;
}

// Обмеження довжини повідомлення
if (strlen($message) > 1000) {
    $message = substr($message, 0, 1000) . '...';
}

// Підготовка даних для збереження
$logData = [
    'time' => $time,
    'subject' => $subject,
    'message' => $message,
    'urgent' => $urgent,
    'page' => $page,
    'user_agent' => $user_agent,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];

// Форматування для логу
$logEntry = date('Y-m-d H:i:s') . ' | ' . 
            ($urgent ? 'URGENT' : 'NORMAL') . ' | ' . 
            $subject . ' | ' . 
            $message . ' | ' . 
            $page . ' | ' . 
            $logData['ip'] . PHP_EOL;

// Збереження в основний лог
file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);

// Збереження термінових повідомлень в окремий файл
if ($urgent) {
    file_put_contents(URGENT_LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

// Відправка email повідомлення (опціонально)
if ($urgent) {
    sendUrgentNotification($logData);
}

// Збереження в базу даних (якщо доступна)
saveToDatabaseIfAvailable($logData);

// Відповідь
echo json_encode([
    'success' => true, 
    'message' => 'Повідомлення отримано',
    'urgent' => $urgent
]);

// Функція відправки терміново повідомлення
function sendUrgentNotification($data) {
    $to = 'support@sthost.pro'; // Замініть на вашу email адресу
    $subject = '[URGENT] Термінове звернення - StormHosting';
    
    $body = "ТЕРМІНОВЕ ЗВЕРНЕННЯ КЛІЄНТА!\n\n";
    $body .= "Час: " . $data['time'] . "\n";
    $body .= "Сторінка: " . $data['page'] . "\n";
    $body .= "IP: " . $data['ip'] . "\n\n";
    $body .= "Повідомлення:\n" . $data['message'] . "\n\n";
    $body .= "Відповідайте негайно!\n";
    
    $headers = "From: noreply@sthost.pro\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Відправка email (якщо налаштовано)
    if (function_exists('mail')) {
        mail($to, $subject, $body, $headers);
    }
}

// Функція збереження в базу даних
function saveToDatabaseIfAvailable($data) {
    try {
        // Підключення до БД (якщо доступно)
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php')) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
            
            // Створення таблиці якщо не існує
            $createTable = "
                CREATE TABLE IF NOT EXISTS chat_messages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    time DATETIME,
                    subject VARCHAR(255),
                    message TEXT,
                    urgent BOOLEAN DEFAULT 0,
                    page VARCHAR(500),
                    user_agent TEXT,
                    ip VARCHAR(45),
                    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
            
            if (isset($pdo)) {
                $pdo->exec($createTable);
                
                // Вставка повідомлення
                $stmt = $pdo->prepare("
                    INSERT INTO chat_messages 
                    (time, subject, message, urgent, page, user_agent, ip) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $data['time'],
                    $data['subject'],
                    $data['message'],
                    $data['urgent'] ? 1 : 0,
                    $data['page'],
                    $data['user_agent'],
                    $data['ip']
                ]);
            }
        }
    } catch (Exception $e) {
        // Тихо ігноруємо помилки БД, логи все одно збережуться
        error_log("Chat DB Error: " . $e->getMessage());
    }
}

// Функція отримання статистики (для API)
function getChatStats() {
    $stats = [
        'urgent' => 0,
        'total' => 0,
        'today' => 0
    ];
    
    try {
        if (file_exists(LOG_FILE)) {
            $lines = file(LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $stats['total'] = count($lines);
            
            $today = date('Y-m-d');
            foreach ($lines as $line) {
                if (strpos($line, $today) === 0) {
                    $stats['today']++;
                }
                if (strpos($line, 'URGENT') !== false) {
                    $stats['urgent']++;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Stats Error: " . $e->getMessage());
    }
    
    return $stats;
}

// API для отримання статистики (GET запит)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['stats'])) {
    echo json_encode(getChatStats());
    exit;
}
?>