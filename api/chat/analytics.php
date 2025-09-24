<?php
// /api/chat/analytics.php - API для аналитики чата

define('SECURE_ACCESS', true);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

class ChatAnalyticsAPI {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function handleRequest() {
        $action = $_GET['action'] ?? null;
        
        switch ($action) {
            case 'get_analytics':
                return $this->getAnalytics();
            case 'get_operators':
                return $this->getOperators();
            case 'get_detailed_stats':
                return $this->getDetailedStats();
            default:
                return $this->error('Невідома дія');
        }
    }
    
    private function getAnalytics() {
        try {
            $period = $_GET['period'] ?? 'week';
            $operator_id = $_GET['operator'] !== 'all' ? $_GET['operator'] : null;
            $status = $_GET['status'] !== 'all' ? $_GET['status'] : null;
            
            $dateRange = $this->getDateRange($period);
            
            $analytics = [
                'stats' => $this->getMainStats($dateRange, $operator_id, $status),
                'charts' => $this->getChartsData($dateRange, $operator_id, $status),
                'operators' => $this->getOperatorPerformance($dateRange),
                'recent_chats' => $this->getRecentChats(20)
            ];
            
            return $this->success('Аналітика отримана', $analytics);
            
        } catch (Exception $e) {
            error_log("Analytics error: " . $e->getMessage());
            return $this->error('Помилка отримання аналітики');
        }
    }
    
    private function getMainStats($dateRange, $operator_id = null, $status = null) {
        $whereConditions = ["cs.created_at >= ?", "cs.created_at <= ?"];
        $params = [$dateRange['start'], $dateRange['end']];
        
        if ($operator_id) {
            $whereConditions[] = "cs.operator_id = ?";
            $params[] = $operator_id;
        }
        
        if ($status) {
            $whereConditions[] = "cs.status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Общая статистика
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_chats,
                COUNT(CASE WHEN status = 'closed' THEN 1 END) as completed_chats,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_chats,
                COUNT(CASE WHEN status = 'waiting' THEN 1 END) as waiting_chats,
                AVG(CASE 
                    WHEN closed_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, created_at, closed_at) 
                    ELSE NULL 
                END) as avg_duration
            FROM chat_sessions cs 
            WHERE {$whereClause}
        ");
        
        $stmt->execute($params);
        $stats = $stmt->fetch();
        
        // Среднее время ответа оператора
        $stmt = $this->pdo->prepare("
            SELECT AVG(response_time) as avg_response_time
            FROM (
                SELECT 
                    TIMESTAMPDIFF(MINUTE, cs.created_at, MIN(cm.created_at)) as response_time
                FROM chat_sessions cs
                JOIN chat_messages cm ON cs.id = cm.session_id
                WHERE {$whereClause} AND cm.sender_type = 'operator'
                GROUP BY cs.id
            ) as response_times
        ");
        
        $stmt->execute($params);
        $responseTime = $stmt->fetch();
        
        // Количество активных операторов
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT operator_id) as active_operators
            FROM chat_sessions cs
            WHERE {$whereClause} AND operator_id IS NOT NULL
        ");
        
        $stmt->execute($params);
        $operators = $stmt->fetch();
        
        // Вычисляем изменения по сравнению с предыдущим периодом
        $prevDateRange = $this->getPreviousDateRange($dateRange);
        $prevStats = $this->getPreviousStats($prevDateRange, $operator_id, $status);
        
        return [
            'total_chats' => $stats['total_chats'],
            'completed_chats' => $stats['completed_chats'],
            'active_chats' => $stats['active_chats'],
            'waiting_chats' => $stats['waiting_chats'],
            'avg_response_time' => round($responseTime['avg_response_time'] ?? 0) . 'хв',
            'active_operators' => $operators['active_operators'],
            'avg_duration' => round($stats['avg_duration'] ?? 0) . 'хв',
            'chats_change' => $this->calculateChange($stats['total_chats'], $prevStats['total_chats']),
            'completed_change' => $this->calculateChange($stats['completed_chats'], $prevStats['completed_chats']),
            'response_time_change' => $this->calculateChange($responseTime['avg_response_time'], $prevStats['avg_response_time']),
            'operators_change' => $this->calculateChange($operators['active_operators'], $prevStats['active_operators'])
        ];
    }
    
    private function getChartsData($dateRange, $operator_id = null, $status = null) {
        // Данные для графика динамики чатов
        $chatsTimeline = $this->getChatsTimeline($dateRange, $operator_id, $status);
        
        // Данные для круговой диаграммы статусов
        $statusDistribution = $this->getStatusDistribution($dateRange, $operator_id);
        
        return [
            'chats_timeline' => $chatsTimeline,
            'status_distribution' => $statusDistribution
        ];
    }
    
    private function getChatsTimeline($dateRange, $operator_id = null, $status = null) {
        $whereConditions = ["cs.created_at >= ?", "cs.created_at <= ?"];
        $params = [$dateRange['start'], $dateRange['end']];
        
        if ($operator_id) {
            $whereConditions[] = "cs.operator_id = ?";
            $params[] = $operator_id;
        }
        
        if ($status) {
            $whereConditions[] = "cs.status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(cs.created_at) as date,
                COUNT(*) as count
            FROM chat_sessions cs
            WHERE {$whereClause}
            GROUP BY DATE(cs.created_at)
            ORDER BY date ASC
        ");
        
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        $labels = [];
        $values = [];
        
        foreach ($results as $result) {
            $labels[] = date('d.m', strtotime($result['date']));
            $values[] = $result['count'];
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
    
    private function getStatusDistribution($dateRange, $operator_id = null) {
        $whereConditions = ["cs.created_at >= ?", "cs.created_at <= ?"];
        $params = [$dateRange['start'], $dateRange['end']];
        
        if ($operator_id) {
            $whereConditions[] = "cs.operator_id = ?";
            $params[] = $operator_id;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                status,
                COUNT(*) as count
            FROM chat_sessions cs
            WHERE {$whereClause}
            GROUP BY status
        ");
        
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        $labels = [];
        $values = [];
        
        $statusMap = [
            'waiting' => 'В очікуванні',
            'active' => 'Активні',
            'closed' => 'Завершені',
            'transferred' => 'Передані'
        ];
        
        foreach ($results as $result) {
            $labels[] = $statusMap[$result['status']] ?? $result['status'];
            $values[] = $result['count'];
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
    
    private function getOperatorPerformance($dateRange) {
        $stmt = $this->pdo->prepare("
            SELECT 
                so.id,
                so.name,
                so.role,
                os.is_online,
                so.last_activity,
                COUNT(cs.id) as chats_today,
                AVG(CASE 
                    WHEN cs.closed_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, cs.created_at, cs.closed_at) 
                    ELSE NULL 
                END) as avg_time,
                COUNT(CASE WHEN cs.status = 'closed' THEN 1 END) as completed_chats
            FROM support_operators so
            LEFT JOIN operator_status os ON so.id = os.operator_id
            LEFT JOIN chat_sessions cs ON so.id = cs.operator_id 
                AND cs.created_at >= ? AND cs.created_at <= ?
            GROUP BY so.id, so.name, so.role, os.is_online, so.last_activity
            ORDER BY chats_today DESC, completed_chats DESC
        ");
        
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        $operators = $stmt->fetchAll();
        
        // Добавляем рейтинг (простая формула)
        foreach ($operators as &$operator) {
            $operator['avg_time'] = round($operator['avg_time'] ?? 0) . 'хв';
            
            // Рейтинг от 1 до 5 звезд
            $completionRate = $operator['chats_today'] > 0 ? 
                $operator['completed_chats'] / $operator['chats_today'] : 0;
            $operator['rating'] = min(5, max(1, round($completionRate * 5)));
        }
        
        return $operators;
    }
    
    private function getRecentChats($limit = 20) {
        $stmt = $this->pdo->prepare("
            SELECT 
                cs.id,
                cs.guest_name,
                cs.guest_email,
                cs.subject,
                cs.status,
                cs.created_at,
                cs.closed_at,
                so.name as operator_name,
                CASE 
                    WHEN cs.closed_at IS NOT NULL 
                    THEN CONCAT(TIMESTAMPDIFF(MINUTE, cs.created_at, cs.closed_at), 'хв')
                    ELSE 'В процесі'
                END as duration
            FROM chat_sessions cs
            LEFT JOIN support_operators so ON cs.operator_id = so.id
            ORDER BY cs.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    private function getOperators() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, role, email
                FROM support_operators
                ORDER BY name ASC
            ");
            
            $stmt->execute();
            $operators = $stmt->fetchAll();
            
            return $this->success('Оператори отримані', $operators);
            
        } catch (Exception $e) {
            error_log("Get operators error: " . $e->getMessage());
            return $this->error('Помилка отримання операторів');
        }
    }
    
    private function getDetailedStats() {
        try {
            // Детальная статистика для экспорта или подробного анализа
            $stats = [
                'daily_stats' => $this->getDailyStats(30), // За последние 30 дней
                'hourly_distribution' => $this->getHourlyDistribution(),
                'response_times' => $this->getResponseTimeStats(),
                'customer_satisfaction' => $this->getCustomerSatisfactionStats()
            ];
            
            return $this->success('Детальна статистика отримана', $stats);
            
        } catch (Exception $e) {
            error_log("Detailed stats error: " . $e->getMessage());
            return $this->error('Помилка отримання детальної статистики');
        }
    }
    
    private function getDailyStats($days = 30) {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_chats,
                COUNT(CASE WHEN status = 'closed' THEN 1 END) as completed_chats,
                AVG(CASE 
                    WHEN closed_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, created_at, closed_at) 
                    ELSE NULL 
                END) as avg_duration
            FROM chat_sessions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
    
    private function getHourlyDistribution() {
        $stmt = $this->pdo->prepare("
            SELECT 
                HOUR(created_at) as hour,
                COUNT(*) as count
            FROM chat_sessions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY HOUR(created_at)
            ORDER BY hour ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getResponseTimeStats() {
        $stmt = $this->pdo->prepare("
            SELECT 
                so.name as operator_name,
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time
            FROM (
                SELECT 
                    cs.operator_id,
                    TIMESTAMPDIFF(MINUTE, cs.created_at, MIN(cm.created_at)) as response_time
                FROM chat_sessions cs
                JOIN chat_messages cm ON cs.id = cm.session_id
                WHERE cm.sender_type = 'operator' 
                    AND cs.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY cs.id, cs.operator_id
            ) rt
            JOIN support_operators so ON rt.operator_id = so.id
            GROUP BY so.id, so.name
            ORDER BY avg_response_time ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getCustomerSatisfactionStats() {
        // Заглушка для будущей функциональности оценок
        return [
            'avg_rating' => 4.2,
            'total_ratings' => 0,
            'rating_distribution' => [
                '5' => 0,
                '4' => 0,
                '3' => 0,
                '2' => 0,
                '1' => 0
            ]
        ];
    }
    
    // Helper methods
    
    private function getDateRange($period) {
        $end = date('Y-m-d 23:59:59');
        
        switch ($period) {
            case 'today':
                $start = date('Y-m-d 00:00:00');
                break;
            case 'week':
                $start = date('Y-m-d 00:00:00', strtotime('-6 days'));
                break;
            case 'month':
                $start = date('Y-m-d 00:00:00', strtotime('-29 days'));
                break;
            default:
                $start = date('Y-m-d 00:00:00', strtotime('-6 days'));
        }
        
        return ['start' => $start, 'end' => $end];
    }
    
    private function getPreviousDateRange($currentRange) {
        $current_start = strtotime($currentRange['start']);
        $current_end = strtotime($currentRange['end']);
        $duration = $current_end - $current_start;
        
        return [
            'start' => date('Y-m-d H:i:s', $current_start - $duration),
            'end' => date('Y-m-d H:i:s', $current_end - $duration)
        ];
    }
    
    private function getPreviousStats($dateRange, $operator_id = null, $status = null) {
        $whereConditions = ["cs.created_at >= ?", "cs.created_at <= ?"];
        $params = [$dateRange['start'], $dateRange['end']];
        
        if ($operator_id) {
            $whereConditions[] = "cs.operator_id = ?";
            $params[] = $operator_id;
        }
        
        if ($status) {
            $whereConditions[] = "cs.status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_chats,
                COUNT(CASE WHEN status = 'closed' THEN 1 END) as completed_chats,
                COUNT(DISTINCT operator_id) as active_operators
            FROM chat_sessions cs 
            WHERE {$whereClause}
        ");
        
        $stmt->execute($params);
        $stats = $stmt->fetch();
        
        // Среднее время ответа для предыдущего периода
        $stmt = $this->pdo->prepare("
            SELECT AVG(response_time) as avg_response_time
            FROM (
                SELECT 
                    TIMESTAMPDIFF(MINUTE, cs.created_at, MIN(cm.created_at)) as response_time
                FROM chat_sessions cs
                JOIN chat_messages cm ON cs.id = cm.session_id
                WHERE {$whereClause} AND cm.sender_type = 'operator'
                GROUP BY cs.id
            ) as response_times
        ");
        
        $stmt->execute($params);
        $responseTime = $stmt->fetch();
        
        return [
            'total_chats' => $stats['total_chats'],
            'completed_chats' => $stats['completed_chats'], 
            'active_operators' => $stats['active_operators'],
            'avg_response_time' => $responseTime['avg_response_time'] ?? 0
        ];
    }
    
    private function calculateChange($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100);
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
    $api = new ChatAnalyticsAPI($pdo);
    $api->handleRequest();
} catch (Throwable $e) {
    error_log("Chat Analytics API Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Внутрішня помилка сервера'
    ], JSON_UNESCAPED_UNICODE);
}