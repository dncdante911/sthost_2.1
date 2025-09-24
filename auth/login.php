<?php
/**
 * Login API
 * Файл: /api/auth/login.php
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Настройка заголовков
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Обработка OPTIONS запроса
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не дозволений']);
    exit;
}

// Начинаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключение к БД
try {
    // Попытка подключения через includes
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
    }
    
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
        $pdo = DatabaseConnection::getSiteConnection();
    } else {
        // Прямое подключение к БД
        $host = 'localhost';
        $dbname = 'sthostsitedb';
        $username = 'sthostdb';
        $password = '3344Frz@q0607Dm$157';
        
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Помилка підключення до бази даних']);
    exit;
}

// Функция для отправки ответа
function sendResponse($success, $message, $data = [], $errors = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Rate limiting для попыток входа
function checkLoginRateLimit($pdo, $ip, $email = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $params = [$ip];
        
        if ($email) {
            $sql .= " OR (email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE))";
            $params[] = $email;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] < 10; // 10 попыток в 15 минут
    } catch (Exception $e) {
        return true; // В случае ошибки разрешаем
    }
}

// Логирование попыток входа
function logLoginAttempt($pdo, $ip, $email, $success, $user_id = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO login_attempts (ip_address, email, user_id, success, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$ip, $email, $user_id, $success ? 1 : 0]);
    } catch (Exception $e) {
        error_log('Failed to log login attempt: ' . $e->getMessage());
    }
}

// Создание таблицы login_attempts если не существует
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255),
            user_id INT,
            success BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_time (ip_address, created_at),
            INDEX idx_email_time (email, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) {
    error_log('Failed to create login_attempts table: ' . $e->getMessage());
}

// Получаем IP адрес
$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];

// Получаем данные из POST
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// Базовая валидация
$errors = [];

if (empty($email)) {
    $errors['email'] = 'Вкажіть email адресу';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Невірний формат email';
}

if (empty($password)) {
    $errors['password'] = 'Вкажіть пароль';
}

// Если есть ошибки валидации
if (!empty($errors)) {
    logLoginAttempt($pdo, $client_ip, $email, false);
    sendResponse(false, 'Заповніть всі поля правильно', [], $errors);
}

// Проверяем rate limit
if (!checkLoginRateLimit($pdo, $client_ip, $email)) {
    logLoginAttempt($pdo, $client_ip, $email, false);
    sendResponse(false, 'Занадто багато спроб входу. Спробуйте через 15 хвилин.');
}

// Ищем пользователя в базе
try {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, password_hash, email_verified, created_at 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        logLoginAttempt($pdo, $client_ip, $email, false);
        sendResponse(false, 'Невірний email або пароль', [], ['email' => 'Користувач не знайдений']);
    }
    
    // Проверяем пароль
    if (!password_verify($password, $user['password_hash'])) {
        logLoginAttempt($pdo, $client_ip, $email, false, $user['id']);
        sendResponse(false, 'Невірний email або пароль', [], ['password' => 'Невірний пароль']);
    }
    
    // Логируем успешный вход
    logLoginAttempt($pdo, $client_ip, $email, true, $user['id']);
    
    // Создаем сессию
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_verified'] = $user['email_verified'];
    $_SESSION['login_time'] = time();
    
    // Если выбрано "Запомнить меня"
    if ($remember_me) {
        // Генерируем токен для remember me
        $remember_token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 дней
        
        // Сохраняем токен в базе (создадим таблицу если нужно)
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS remember_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    expires_at TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_token (token),
                    INDEX idx_user_id (user_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Удаляем старые токены пользователя
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            
            // Добавляем новый токен
            $stmt = $pdo->prepare("
                INSERT INTO remember_tokens (user_id, token, expires_at) 
                VALUES (?, ?, FROM_UNIXTIME(?))
            ");
            $stmt->execute([$user['id'], $remember_token, $expires]);
            
            // Устанавливаем cookie
            setcookie('remember_token', $remember_token, $expires, '/', '', true, true);
            
        } catch (Exception $e) {
            error_log('Failed to set remember token: ' . $e->getMessage());
        }
    }
    
    // Обновляем время последнего входа
    try {
        $stmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
    } catch (Exception $e) {
        error_log('Failed to update last login: ' . $e->getMessage());
    }
    
    sendResponse(true, 'Успішний вхід в систему!', [
        'user' => [
            'id' => $user['id'],
            'name' => $user['full_name'],
            'email' => $user['email'],
            'verified' => $user['email_verified']
        ],
        'redirect' => '/' // Можно изменить на страницу личного кабинета
    ]);
    
} catch (Exception $e) {
    error_log('Login failed: ' . $e->getMessage());
    logLoginAttempt($pdo, $client_ip, $email, false);
    sendResponse(false, 'Помилка входу в систему. Спробуйте ще раз.');
}
?>