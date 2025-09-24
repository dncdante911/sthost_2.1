<?php
// /api/chat/messages.php - ИСПРАВЛЕННАЯ ВЕРСИЯ

define('SECURE_ACCESS', true);

// Установка правильной кодировки
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

// Подключение к БД
try {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
    }
    
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
        $pdo = DatabaseConnection::getSiteConnection();
    } else {
        // Прямое подключение
        $pdo = new PDO(
            "mysql:host=localhost;dbname=sthostsitedb;charset=utf8mb4",
            "sthostdb",
            "3344Frz@q0607Dm\$157",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
    }
    
    // Принудительно устанавливаем кодировку
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Помилка підключення до бази даних'], JSON_UNESCAPED_UNICODE);
    exit;
}

class ChatMessagesAPI {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'POST':
                return $this->sendMessage();
            case 'GET':
                return $this->getMessages();
            case 'PUT':
                return $this->markAsRead();
            default:
                return $this->error('Метод не підтримується', 405);
        }
    }
    
    private function sendMessage() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('Невірний JSON формат');
            }
            
            $session_id = $input['session_id'] ?? null;
            $message = trim($input['message'] ?? '');
            $sender_type = $input['sender_type'] ?? 'user';
            $sender_id = $input['sender_id'] ?? null;
            
            // Валідація
            if (empty($message)) {
                return $this->error('Повідомлення не може бути порожнім');
            }
            
            if (strlen($message) > 2000) {
                return $this->error('Повідомлення занадто довге (макс. 2000 символів)');
            }
            
            // Находим сессию
            $session = null;
            if ($session_id) {
                $session = $this->getSessionById($session_id);
            } else {
                // Пытаемся найти по ключу сессии
                $session_key = $_SESSION['chat_session_key'] ?? null;
                if ($session_key) {
                    $session = $this->getSessionByKey($session_key);
                }
            }
            
            if (!$session) {
                return $this->error('Сесія не знайдена');
            }
            
            // Проверка прав доступа
            if (!$this->canSendMessage($session, $sender_type, $sender_id)) {
                return $this->error('Немає прав для відправки повідомлення');
            }
            
            // Очистка сообщения
            $message = $this->sanitizeMessage($message);
            
            // Сохранение сообщения
            $stmt = $this->pdo->prepare("
                INSERT INTO chat_messages 
                (session_id, sender_type, sender_id, message, message_type, created_at) 
                VALUES (?, ?, ?, ?, 'text', NOW())
            ");
            
            $stmt->execute([
                $session['id'],
                $sender_type,
                $sender_id,
                $message
            ]);
            
            $message_id = $this->pdo->lastInsertId();
            
            // Обновляем время последней активности сессии
            $this->updateSessionActivity($session['id']);
            
            // Получаем созданное сообщение
            $new_message = $this->getMessageById($message_id);
            
            return $this->success('Повідомлення відправлено', $new_message);
            
        } catch (Exception $e) {
            error_log("Chat message send error: " . $e->getMessage());
            return $this->error('Помилка відправки повідомлення');
        }
    }
    
    private function getMessages() {
        try {
            $session_id = $_GET['session_id'] ?? null;
            $last_message_id = (int)($_GET['last_message_id'] ?? 0);
            
            if (!$session_id) {
                return $this->error('session_id обов\'язковий');
            }
            
            // Проверяем сессию
            $session = $this->getSessionById($session_id);
            if (!$session) {
                return $this->error('Сесія не знайдена');
            }
            
            // Получаем новые сообщения
            $stmt = $this->pdo->prepare("
                SELECT 
                    cm.*,
                    so.name as sender_name,
                    so.avatar as sender_avatar
                FROM chat_messages cm
                LEFT JOIN support_operators so ON cm.sender_id = so.id AND cm.sender_type = 'operator'
                WHERE cm.session_id = ? AND cm.id > ?
                ORDER BY cm.created_at ASC
            ");
            
            $stmt->execute([$session_id, $last_message_id]);
            $messages = $stmt->fetchAll();
            
            return $this->success('Повідомлення отримано', [
                'messages' => $messages,
                'session' => $session
            ]);
            
        } catch (Exception $e) {
            error_log("Chat messages get error: " . $e->getMessage());
            return $this->error('Помилка отримання повідомлень');
        }
    }
    
    private function markAsRead() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $session_id = $input['session_id'] ?? null;
            $message_ids = $input['message_ids'] ?? [];
            $reader_type = $input['reader_type'] ?? 'user';
            
            if (!$session_id || empty($message_ids)) {
                return $this->error('session_id и message_ids обов\'язкові');
            }
            
            $placeholders = implode(',', array_fill(0, count($message_ids), '?'));
            $params = array_merge([$session_id], $message_ids);
            
            $stmt = $this->pdo->prepare("
                UPDATE chat_messages 
                SET is_read = 1 
                WHERE session_id = ? AND id IN ($placeholders)
            ");
            
            $stmt->execute($params);
            
            return $this->success('Повідомлення позначено як прочитане');
            
        } catch (Exception $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return $this->error('Помилка позначення як прочитане');
        }
    }
    
    // Helper methods
    
    private function getSessionById($session_id) {
        $stmt = $this->pdo->prepare("
            SELECT cs.*, so.name as operator_name, so.avatar as operator_avatar
            FROM chat_sessions cs
            LEFT JOIN support_operators so ON cs.operator_id = so.id
            WHERE cs.id = ?
        ");
        
        $stmt->execute([$session_id]);
        return $stmt->fetch();
    }
    
    private function getSessionByKey($session_key) {
        $stmt = $this->pdo->prepare("
            SELECT cs.*, so.name as operator_name, so.avatar as operator_avatar
            FROM chat_sessions cs
            LEFT JOIN support_operators so ON cs.operator_id = so.id
            WHERE cs.session_key = ?
        ");
        
        $stmt->execute([$session_key]);
        return $stmt->fetch();
    }
    
    private function getMessageById($message_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                cm.*,
                so.name as sender_name,
                so.avatar as sender_avatar
            FROM chat_messages cm
            LEFT JOIN support_operators so ON cm.sender_id = so.id AND cm.sender_type = 'operator'
            WHERE cm.id = ?
        ");
        
        $stmt->execute([$message_id]);
        return $stmt->fetch();
    }
    
    private function canSendMessage($session, $sender_type, $sender_id) {
        // Проверка закрытой сессии
        if ($session['status'] === 'closed') {
            return false;
        }
        
        switch ($sender_type) {
            case 'user':
                // Пользователь может писать в свою сессию
                $user_id = $_SESSION['user_id'] ?? null;
                return $session['user_id'] === $user_id || 
                       $session['session_key'] === ($_SESSION['chat_session_key'] ?? null);
                
            case 'operator':
                // Оператор должен быть назначен или иметь права админа
                return $this->isOperatorAuthorized($sender_id, $session['id']);
                
            default:
                return false;
        }
    }
    
    private function isOperatorAuthorized($operator_id, $session_id) {
        $stmt = $this->pdo->prepare("
            SELECT 1 FROM support_operators so
            WHERE so.id = ? AND (
                so.role IN ('admin', 'supervisor') OR
                EXISTS (
                    SELECT 1 FROM chat_sessions cs 
                    WHERE cs.id = ? AND cs.operator_id = ?
                )
            )
        ");
        
        $stmt->execute([$operator_id, $session_id, $operator_id]);
        return $stmt->fetchColumn() !== false;
    }
    
    private function sanitizeMessage($message) {
        // Очищение и фильтрация сообщения
        $message = strip_tags($message);
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        // Разрешаем некоторые эмодзи и специальные символы
        $message = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}\p{So}]/u', '', $message);
        
        return $message;
    }
    
    private function updateSessionActivity($session_id) {
        $stmt = $this->pdo->prepare("
            UPDATE chat_sessions 
            SET updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$session_id]);
    }
    
    private function success($message, $data = null) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    private function error($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Обработка запроса
try {
    $api = new ChatMessagesAPI($pdo);
    $api->handleRequest();
} catch (Throwable $e) {
    error_log("Chat API Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Внутрішня помилка сервера'
    ], JSON_UNESCAPED_UNICODE);
}