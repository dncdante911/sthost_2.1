<?php
// /api/chat/operators.php - API для операторов

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

class OperatorsAPI {
    private $pdo;
    private $operator_id;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->operator_id = $_SESSION['operator_id'] ?? null;
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? null;
            
            switch ($action) {
                case 'login':
                    return $this->login();
                case 'logout':
                    return $this->logout();
                default:
                    return $this->error('Невідома дія');
            }
        } elseif ($method === 'GET') {
            $action = $_GET['action'] ?? null;
            
            switch ($action) {
                case 'get_sessions':
                    return $this->getSessions();
                case 'get_stats':
                    return $this->getStats();
                default:
                    return $this->error('Невідома дія');
            }
        }
        
        return $this->error('Метод не підтримується', 405);
    }
    
    private function login() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $name = trim($input['name'] ?? '');
            $password = $input['password'] ?? '';
            
            if (empty($name) || empty($password)) {
                return $this->error('Необхідно вказати ім\'я та пароль');
            }
            
            // Простая защита паролем (в реальном проекте используйте хеширование)
            if ($password !== 'stormoperator123') {
                return $this->error('Невірний пароль');
            }
            
            $operator_id = $this->createOrGetOperator($name);
            if ($operator_id) {
                $_SESSION['operator_id'] = $operator_id;
                $this->operator_id = $operator_id;
                $this->setOperatorOnline($operator_id, true);
                
                return $this->success('Авторизацію виконано', [
                    'operator_id' => $operator_id,
                    'name' => $name
                ]);
            }
            
            return $this->error('Помилка авторизації');
            
        } catch (Exception $e) {
            error_log("Operator login error: " . $e->getMessage());
            return $this->error('Помилка авторизації');
        }
    }
    
    private function logout() {
        if ($this->operator_id) {
            $this->setOperatorOnline($this->operator_id, false);
            unset($_SESSION['operator_id']);
        }
        
        return $this->success('Вихід виконано');
    }
    
    private function getSessions() {
        try {
            if (!$this->operator_id) {
                return $this->error('Необхідна авторизація');
            }
            
            $filter = $_GET['filter'] ?? 'all';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(50, max(10, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            
            $where_conditions = [];
            $params = [];
            
            switch ($filter) {
                case 'waiting':
                    $where_conditions[] = "cs.status = 'waiting'";
                    break;
                case 'active':
                    $where_conditions[] = "cs.status = 'active'";
                    break;
                case 'my':
                    $where_conditions[] = "cs.operator_id = ?";
                    $params[] = $this->operator_id;
                    break;
                case 'urgent':
                    $where_conditions[] = "cs.priority = 'urgent'";
                    break;
            }
            
            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);
            
            $sql = "
                SELECT 
                    cs.*,
                    so.name as operator_name,
                    so.avatar as operator_avatar,
                    (SELECT COUNT(*) FROM chat_messages cm WHERE cm.session_id = cs.id AND cm.is_read = 0) as unread_count
                FROM chat_sessions cs
                LEFT JOIN support_operators so ON cs.operator_id = so.id
                {$where_clause}
                ORDER BY 
                    CASE WHEN cs.priority = 'urgent' THEN 1 ELSE 2 END,
                    cs.updated_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $sessions = $stmt->fetchAll();
            
            // Получаем общее количество
            $count_sql = "SELECT COUNT(*) FROM chat_sessions cs {$where_clause}";
            $count_params = array_slice($params, 0, -2); // Убираем limit и offset
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($count_params);
            $total = $count_stmt->fetchColumn();
            
            return $this->success('Сесії отримано', [
                'sessions' => $sessions,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (Exception $e) {
            error_log("Get sessions error: " . $e->getMessage());
            return $this->error('Помилка отримання сесій');
        }
    }
    
    private function getStats() {
        try {
            if (!$this->operator_id) {
                return $this->error('Необхідна авторизація');
            }
            
            // Статистика для текущего оператора
            $stats = [];
            
            // Активные сессии
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM chat_sessions WHERE status = 'active'");
            $stmt->execute();
            $stats['active_sessions'] = $stmt->fetchColumn();
            
            // Ожидающие сессии
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM chat_sessions WHERE status = 'waiting'");
            $stmt->execute();
            $stats['waiting_sessions'] = $stmt->fetchColumn();
            
            // Мои сессии
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM chat_sessions WHERE operator_id = ?");
            $stmt->execute([$this->operator_id]);
            $stats['my_sessions'] = $stmt->fetchColumn();
            
            // Сегодняшние сессии
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM chat_sessions WHERE DATE(created_at) = CURDATE()");
            $stmt->execute();
            $stats['today_sessions'] = $stmt->fetchColumn();
            
            // Средняя статистика оператора
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_handled,
                    AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(closed_at, NOW()))) as avg_duration
                FROM chat_sessions 
                WHERE operator_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute([$this->operator_id]);
            $operator_stats = $stmt->fetch();
            
            $stats['total_handled'] = $operator_stats['total_handled'];
            $stats['avg_duration'] = round($operator_stats['avg_duration'], 1);
            
            return $this->success('Статистика отримана', $stats);
            
        } catch (Exception $e) {
            error_log("Get stats error: " . $e->getMessage());
            return $this->error('Помилка отримання статистики');
        }
    }
    
    // Helper methods
    
    private function createOrGetOperator($name) {
        // Сначала ищем существующего
        $stmt = $this->pdo->prepare("SELECT id FROM support_operators WHERE name = ?");
        $stmt->execute([$name]);
        $operator_id = $stmt->fetchColumn();
        
        if ($operator_id) {
            return $operator_id;
        }
        
        // Создаем нового
        $stmt = $this->pdo->prepare("
            INSERT INTO support_operators (name, email, role, created_at) 
            VALUES (?, ?, 'operator', NOW())
        ");
        $stmt->execute([$name, $name . '@sthost.pro']);
        $operator_id = $this->pdo->lastInsertId();
        
        // Создаем запись статуса
        $stmt = $this->pdo->prepare("
            INSERT INTO operator_status (operator_id, is_online, max_sessions) 
            VALUES (?, 0, 5)
        ");
        $stmt->execute([$operator_id]);
        
        return $operator_id;
    }
    
    private function setOperatorOnline($operator_id, $is_online, $status_message = 'Доступний', $max_sessions = 5) {
        // Обновляем или создаем статус
        $stmt = $this->pdo->prepare("
            INSERT INTO operator_status (operator_id, is_online, status_message, max_sessions, last_activity) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                is_online = VALUES(is_online),
                status_message = VALUES(status_message),
                max_sessions = VALUES(max_sessions),
                last_activity = VALUES(last_activity)
        ");
        $stmt->execute([$operator_id, $is_online ? 1 : 0, $status_message, $max_sessions]);
        
        // Обновляем последнюю активность оператора
        $stmt = $this->pdo->prepare("
            UPDATE support_operators 
            SET last_activity = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$operator_id]);
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
    $api = new OperatorsAPI($pdo);
    $api->handleRequest();
} catch (Throwable $e) {
    error_log("Operators API Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Внутрішня помилка сервера'
    ], JSON_UNESCAPED_UNICODE);
}