<?php
// /api/chat/session.php - ОБНОВЛЕННАЯ ВЕРСИЯ с поддержкой очистки

define('SECURE_ACCESS', true);

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

class ChatSessionAPI {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'POST':
                return $this->handlePost();
            case 'GET':
                return $this->getSession();
            case 'PUT':
                return $this->updateSession();
            default:
                return $this->error('Метод не підтримується', 405);
        }
    }
    
    private function handlePost() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error('Невірний JSON формат');
        }
        
        $action = $input['action'] ?? 'create';
        
        switch ($action) {
            case 'create':
                return $this->createSession();
            case 'user_disconnect':
                return $this->handleUserDisconnect();
            default:
                return $this->createSession();
        }
    }
    
    private function handleUserDisconnect() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $session_id = $input['session_id'] ?? null;
            $session_key = $input['session_key'] ?? null;
            
            if (!$session_id && !$session_key) {
                return $this->success('Відключення обробено');
            }
            
            // Находим сессию
            $session = null;
            if ($session_id) {
                $session = $this->getSessionById($session_id);
            } elseif ($session_key) {
                $session = $this->getSessionByKey($session_key);
            }
            
            if ($session) {
                // Добавляем системное сообщение о отключении пользователя
                $this->addSystemMessage($session['id'], 'Користувач відключився від чату');
                
                // Обновляем время последней активности
                $this->updateSessionActivity($session['id']);
                
                // Если сессия была в ожидании и пользователь отключился, помечаем как неактивную
                if ($session['status'] === 'waiting') {
                    $stmt = $this->pdo->prepare("
                        UPDATE chat_sessions 
                        SET status = 'inactive', updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$session['id']]);
                }
            }
            
            return $this->success('Відключення обробено');
            
        } catch (Exception $e) {
            error_log("User disconnect error: " . $e->getMessage());
            return $this->success('Відключення обробено'); // Всегда успех для disconnect
        }
    }
    
    private function createSession() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('Невірний JSON формат');
            }
            
            $subject = trim($input['subject'] ?? 'Загальне питання');
            $guest_name = trim($input['guest_name'] ?? 'Гість');
            $guest_email = trim($input['guest_email'] ?? '');
            $priority = $input['priority'] ?? 'normal';
            $user_id = $_SESSION['user_id'] ?? null;
            
            // Валідація
            if (strlen($subject) > 255) {
                return $this->error('Тема занадто довга');
            }
            
            if (!in_array($priority, ['low', 'normal', 'high', 'urgent'])) {
                $priority = 'normal';
            }
            
            // Проверяем, нет ли уже активной сессии для этого пользователя
            if ($user_id) {
                $existing = $this->getActiveSession($user_id);
                if ($existing && $existing['status'] !== 'closed') {
                    // Возвращаем существующую сессию
                    return $this->success('Активна сесія знайдена', $existing);
                }
            }
            
            // Генерируем ключ сессии
            $session_key = $this->generateSessionKey();
            
            // Создаем сессию
            $stmt = $this->pdo->prepare("
                INSERT INTO chat_sessions 
                (user_id, session_key, guest_name, guest_email, status, priority, subject, created_at, updated_at) 
                VALUES (?, ?, ?, ?, 'waiting', ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $user_id,
                $session_key,
                $guest_name,
                $guest_email,
                $priority,
                $subject
            ]);
            
            $session_id = $this->pdo->lastInsertId();
            
            // Сохраняем ключ сессии
            $_SESSION['chat_session_key'] = $session_key;
            
            // Получаем созданную сессию
            $session = $this->getSessionById($session_id);
            
            // Отправляем системное сообщение
            $this->addSystemMessage($session_id, 'Чат створено! Очікуйте підключення оператора...');
            
            return $this->success('Сесія створена', $session);
            
        } catch (Exception $e) {
            error_log("Chat session create error: " . $e->getMessage());
            return $this->error('Помилка створення сесії');
        }
    }
    
    private function getSession() {
        try {
            $session_key = $_GET['session_key'] ?? $_SESSION['chat_session_key'] ?? null;
            $user_id = $_SESSION['user_id'] ?? null;
            
            if (!$session_key && !$user_id) {
                return $this->error('Сесія не знайдена');
            }
            
            $session = null;
            
            if ($session_key) {
                $session = $this->getSessionByKey($session_key);
            } elseif ($user_id) {
                $session = $this->getActiveSession($user_id);
            }
            
            if (!$session) {
                return $this->error('Сесія не знайдена');
            }
            
            // Проверяем, не закрыта ли сессия и не слишком ли старая
            if ($this->shouldResetSession($session)) {
                $this->resetOldSession($session['id']);
                return $this->error('Сесія застаріла');
            }
            
            // Получаем сообщения
            $messages = $this->getSessionMessages($session['id']);
            $session['messages'] = $messages;
            
            return $this->success('Сесія знайдена', $session);
            
        } catch (Exception $e) {
            error_log("Chat session get error: " . $e->getMessage());
            return $this->error('Помилка отримання сесії');
        }
    }
    
    private function shouldResetSession($session) {
        // Сессия должна быть сброшена если:
        // 1. Она закрыта
        if ($session['status'] === 'closed') {
            return true;
        }
        
        // 2. Прошло более 2 часов с последней активности
        $updated_at = strtotime($session['updated_at']);
        $now = time();
        if (($now - $updated_at) > (2 * 60 * 60)) { // 2 часа
            return true;
        }
        
        // 3. Сессия в ожидании более 30 минут
        if ($session['status'] === 'waiting') {
            $created_at = strtotime($session['created_at']);
            if (($now - $created_at) > (30 * 60)) { // 30 минут
                return true;
            }
        }
        
        return false;
    }
    
    private function resetOldSession($session_id) {
        try {
            // Закрываем старую сессию
            $stmt = $this->pdo->prepare("
                UPDATE chat_sessions 
                SET status = 'expired', closed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$session_id]);
            
            // Добавляем системное сообщение
            $this->addSystemMessage($session_id, 'Сесія закрита через неактивність');
            
        } catch (Exception $e) {
            error_log("Reset old session error: " . $e->getMessage());
        }
    }
    
    private function updateSession() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $session_id = $input['session_id'] ?? null;
            $action = $input['action'] ?? null;
            
            if (!$session_id || !$action) {
                return $this->error('session_id і action обов\'язкові');
            }
            
            $session = $this->getSessionById($session_id);
            if (!$session) {
                return $this->error('Сесія не знайдена');
            }
            
            switch ($action) {
                case 'close':
                    return $this->closeSession($session_id);
                case 'assign_operator':
                    $operator_id = $input['operator_id'] ?? null;
                    return $this->assignOperator($session_id, $operator_id);
                case 'reset_session':
                    return $this->resetSession($session_id);
                default:
                    return $this->error('Невідома дія');
            }
            
        } catch (Exception $e) {
            error_log("Chat session update error: " . $e->getMessage());
            return $this->error('Помилка оновлення сесії');
        }
    }
    
    private function resetSession($session_id) {
        try {
            // Мягкий сброс сессии - помечаем как неактивную
            $stmt = $this->pdo->prepare("
                UPDATE chat_sessions 
                SET status = 'reset', updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$session_id]);
            
            return $this->success('Сесія скинута');
            
        } catch (Exception $e) {
            error_log("Reset session error: " . $e->getMessage());
            return $this->error('Помилка скидання сесії');
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
    
    private function getActiveSession($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT cs.*, so.name as operator_name, so.avatar as operator_avatar
            FROM chat_sessions cs
            LEFT JOIN support_operators so ON cs.operator_id = so.id
            WHERE cs.user_id = ? AND cs.status IN ('waiting', 'active')
            ORDER BY cs.created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    private function getSessionMessages($session_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                cm.*,
                so.name as sender_name,
                so.avatar as sender_avatar
            FROM chat_messages cm
            LEFT JOIN support_operators so ON cm.sender_id = so.id AND cm.sender_type = 'operator'
            WHERE cm.session_id = ?
            ORDER BY cm.created_at ASC
        ");
        
        $stmt->execute([$session_id]);
        return $stmt->fetchAll();
    }
    
    private function addSystemMessage($session_id, $message) {
        $stmt = $this->pdo->prepare("
            INSERT INTO chat_messages 
            (session_id, sender_type, message, message_type, created_at) 
            VALUES (?, 'system', ?, 'system', NOW())
        ");
        
        $stmt->execute([$session_id, $message]);
    }
    
    private function updateSessionActivity($session_id) {
        $stmt = $this->pdo->prepare("
            UPDATE chat_sessions 
            SET updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$session_id]);
    }
    
    private function closeSession($session_id) {
        $stmt = $this->pdo->prepare("
            UPDATE chat_sessions 
            SET status = 'closed', closed_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$session_id]);
        
        $this->addSystemMessage($session_id, 'Чат закрито оператором');
        
        return $this->success('Сесія закрита');
    }
    
    private function assignOperator($session_id, $operator_id) {
        if (!$operator_id) {
            return $this->error('operator_id обов\'язковий');
        }
        
        // Проверяем существование оператора
        $stmt = $this->pdo->prepare("SELECT name FROM support_operators WHERE id = ?");
        $stmt->execute([$operator_id]);
        $operator = $stmt->fetch();
        
        if (!$operator) {
            return $this->error('Оператор не знайдений');
        }
        
        // Назначаем оператора
        $stmt = $this->pdo->prepare("
            UPDATE chat_sessions 
            SET operator_id = ?, status = 'active', updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$operator_id, $session_id]);
        
        $this->addSystemMessage($session_id, "Оператор {$operator['name']} підключився до чату");
        
        return $this->success('Оператор призначений');
    }
    
    private function generateSessionKey() {
        return bin2hex(random_bytes(16)) . '-' . time();
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
    $api = new ChatSessionAPI($pdo);
    $api->handleRequest();
} catch (Throwable $e) {
    error_log("Chat Session API Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Внутрішня помилка сервера'
    ], JSON_UNESCAPED_UNICODE);
}