<?php
// /api/chat/files.php - API для файлового менеджера чата

define('SECURE_ACCESS', true);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS');
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

// Создаем таблицу файлов если её нет
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chat_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            original_name VARCHAR(255) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_url VARCHAR(500) NOT NULL,
            file_type VARCHAR(100) NOT NULL,
            file_size INT NOT NULL,
            session_id INT NULL,
            uploaded_by VARCHAR(100) DEFAULT 'operator',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session_id (session_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) {
    error_log('Table creation error: ' . $e->getMessage());
}

class ChatFilesAPI {
    private $pdo;
    private $uploadDir;
    private $baseUrl;
    private $maxFileSize = 10485760; // 10MB
    private $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'text/plain',
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip', 'application/x-rar-compressed'
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/chat/';
        $this->baseUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/uploads/chat/';
        
        // Создаем директорию если её нет
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'POST':
                return $this->handlePost();
            case 'GET':
                return $this->handleGet();
            case 'DELETE':
                return $this->handleDelete();
            default:
                return $this->error('Метод не підтримується', 405);
        }
    }
    
    private function handlePost() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'upload';
        
        switch ($action) {
            case 'upload':
                return $this->uploadFile();
            default:
                return $this->error('Невідома дія');
        }
    }
    
    private function handleGet() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'list':
                return $this->listFiles();
            case 'get':
                return $this->getFile();
            default:
                return $this->error('Невідома дія');
        }
    }
    
    private function handleDelete() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? 'delete';
        
        switch ($action) {
            case 'delete':
                return $this->deleteFile();
            default:
                return $this->error('Невідома дія');
        }
    }
    
    private function uploadFile() {
        try {
            if (!isset($_FILES['file'])) {
                return $this->error('Файл не знайдено');
            }
            
            $file = $_FILES['file'];
            
            // Проверяем ошибки загрузки
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return $this->error('Помилка завантаження файлу: ' . $this->getUploadError($file['error']));
            }
            
            // Проверяем размер файла
            if ($file['size'] > $this->maxFileSize) {
                return $this->error('Файл занадто великий. Максимальний розмір: 10MB');
            }
            
            // Проверяем тип файла
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($fileType, $this->allowedTypes)) {
                return $this->error('Непідтримуваний тип файлу: ' . $fileType);
            }
            
            // Генерируем безопасное имя файла
            $originalName = $file['name'];
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = $this->generateSafeFileName($originalName, $extension);
            $filePath = $this->uploadDir . $fileName;
            $fileUrl = $this->baseUrl . $fileName;
            
            // Перемещаем файл
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return $this->error('Помилка збереження файлу');
            }
            
            // Сохраняем информацию в БД
            $stmt = $this->pdo->prepare("
                INSERT INTO chat_files 
                (original_name, file_name, file_path, file_url, file_type, file_size, uploaded_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'operator', NOW())
            ");
            
            $stmt->execute([
                $originalName,
                $fileName,
                $filePath,
                $fileUrl,
                $fileType,
                $file['size']
            ]);
            
            $fileId = $this->pdo->lastInsertId();
            
            // Возвращаем информацию о файле
            $fileInfo = [
                'id' => $fileId,
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_url' => $fileUrl,
                'file_type' => $fileType,
                'file_size' => $file['size'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->success('Файл завантажено успішно', $fileInfo);
            
        } catch (Exception $e) {
            error_log('Upload file error: ' . $e->getMessage());
            return $this->error('Помилка завантаження файлу');
        }
    }
    
    private function listFiles() {
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(50, max(10, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            
            $search = $_GET['search'] ?? '';
            $type = $_GET['type'] ?? 'all';
            
            $whereConditions = [];
            $params = [];
            
            if ($search) {
                $whereConditions[] = "original_name LIKE ?";
                $params[] = "%{$search}%";
            }
            
            if ($type !== 'all') {
                switch ($type) {
                    case 'image':
                        $whereConditions[] = "file_type LIKE 'image/%'";
                        break;
                    case 'document':
                        $whereConditions[] = "(file_type LIKE '%pdf%' OR file_type LIKE '%document%' OR file_type LIKE '%text%')";
                        break;
                    case 'archive':
                        $whereConditions[] = "(file_type LIKE '%zip%' OR file_type LIKE '%rar%')";
                        break;
                }
            }
            
            $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
            
            // Получаем файлы
            $sql = "
                SELECT id, original_name, file_name, file_url, file_type, file_size, 
                       uploaded_by, created_at
                FROM chat_files 
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $files = $stmt->fetchAll();
            
            // Получаем общее количество
            $countSql = "SELECT COUNT(*) FROM chat_files {$whereClause}";
            $countParams = array_slice($params, 0, -2);
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetchColumn();
            
            // Получаем статистику
            $statsStmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_files,
                    SUM(file_size) as total_size
                FROM chat_files
            ");
            $statsStmt->execute();
            $stats = $statsStmt->fetch();
            
            return $this->success('Файли отримано', [
                'files' => $files,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            error_log('List files error: ' . $e->getMessage());
            return $this->error('Помилка отримання списку файлів');
        }
    }
    
    private function getFile() {
        try {
            $fileId = $_GET['file_id'] ?? null;
            
            if (!$fileId) {
                return $this->error('ID файлу не вказано');
            }
            
            $stmt = $this->pdo->prepare("
                SELECT id, original_name, file_name, file_url, file_type, file_size, 
                       uploaded_by, created_at
                FROM chat_files 
                WHERE id = ?
            ");
            
            $stmt->execute([$fileId]);
            $file = $stmt->fetch();
            
            if (!$file) {
                return $this->error('Файл не знайдено');
            }
            
            return $this->success('Файл знайдено', $file);
            
        } catch (Exception $e) {
            error_log('Get file error: ' . $e->getMessage());
            return $this->error('Помилка отримання файлу');
        }
    }
    
    private function deleteFile() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $fileId = $input['file_id'] ?? null;
            
            if (!$fileId) {
                return $this->error('ID файлу не вказано');
            }
            
            // Получаем информацию о файле
            $stmt = $this->pdo->prepare("
                SELECT file_path FROM chat_files WHERE id = ?
            ");
            $stmt->execute([$fileId]);
            $file = $stmt->fetch();
            
            if (!$file) {
                return $this->error('Файл не знайдено');
            }
            
            // Удаляем файл с диска
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            // Удаляем запись из БД
            $stmt = $this->pdo->prepare("DELETE FROM chat_files WHERE id = ?");
            $stmt->execute([$fileId]);
            
            return $this->success('Файл видалено');
            
        } catch (Exception $e) {
            error_log('Delete file error: ' . $e->getMessage());
            return $this->error('Помилка видалення файлу');
        }
    }
    
    private function generateSafeFileName($originalName, $extension) {
        // Убираем опасные символы и создаем уникальное имя
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $baseName = substr($baseName, 0, 50); // Ограничиваем длину
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        return $baseName . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Файл перевищує максимальний розмір',
            UPLOAD_ERR_FORM_SIZE => 'Файл перевищує максимальний розмір форми',
            UPLOAD_ERR_PARTIAL => 'Файл завантажено частково',
            UPLOAD_ERR_NO_FILE => 'Файл не було завантажено',
            UPLOAD_ERR_NO_TMP_DIR => 'Відсутня тимчасова папка',
            UPLOAD_ERR_CANT_WRITE => 'Помилка запису файлу',
            UPLOAD_ERR_EXTENSION => 'Завантаження зупинено розширенням'
        ];
        
        return $errors[$errorCode] ?? 'Невідома помилка';
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
    $api = new ChatFilesAPI($pdo);
    $api->handleRequest();
} catch (Throwable $e) {
    error_log("Chat Files API Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Внутрішня помилка сервера'
    ], JSON_UNESCAPED_UNICODE);
}