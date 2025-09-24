<?php
/**
 * Registration API
 * Файл: /api/auth/register.php
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

// Rate limiting
function checkRateLimit($pdo, $ip) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM registration_attempts 
            WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        return $result['count'] < 5; // 5 попыток в час
    } catch (Exception $e) {
        return true; // В случае ошибки разрешаем
    }
}

// Логирование попыток регистрации
function logRegistrationAttempt($pdo, $ip, $email, $success) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO registration_attempts (ip_address, email, success, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$ip, $email, $success ? 1 : 0]);
    } catch (Exception $e) {
        error_log('Failed to log registration attempt: ' . $e->getMessage());
    }
}

// Создание таблиц если не существуют
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(50),
            password_hash VARCHAR(255) NOT NULL,
            email_verified BOOLEAN DEFAULT FALSE,
            verification_token VARCHAR(64),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_verification_token (verification_token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS registration_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255),
            success BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_time (ip_address, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS csrf_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(64) NOT NULL,
            user_session VARCHAR(128) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) {
    error_log('Failed to create tables: ' . $e->getMessage());
}

// Получаем IP адрес
$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];

// Проверяем rate limit
if (!checkRateLimit($pdo, $client_ip)) {
    sendResponse(false, 'Занадто багато спроб реєстрації. Спробуйте через годину.');
}

// Получаем данные из POST
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$agree_terms = isset($_POST['agree_terms']);

// Валидация
$errors = [];

// Проверка имени
if (empty($full_name)) {
    $errors['full_name'] = 'Вкажіть повне ім\'я';
} elseif (strlen($full_name) < 2) {
    $errors['full_name'] = 'Ім\'я повинно містити мінімум 2 символи';
} elseif (strlen($full_name) > 255) {
    $errors['full_name'] = 'Ім\'я занадто довге';
}

// Проверка email
if (empty($email)) {
    $errors['email'] = 'Вкажіть email адресу';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Невірний формат email';
} elseif (strlen($email) > 255) {
    $errors['email'] = 'Email занадто довгий';
}

// Проверка телефона (опционально)
if (!empty($phone)) {
    $phone = preg_replace('/[^0-9+\-\(\)\s]/', '', $phone);
    if (strlen($phone) < 10 || strlen($phone) > 20) {
        $errors['phone'] = 'Невірний формат телефону';
    }
}

// Проверка пароля
if (empty($password)) {
    $errors['password'] = 'Вкажіть пароль';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Пароль повинен містити мінімум 8 символів';
} elseif (strlen($password) > 128) {
    $errors['password'] = 'Пароль занадто довгий';
} elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
    $errors['password'] = 'Пароль повинен містити великі та малі літери, цифри';
}

// Проверка подтверждения пароля
if ($password !== $password_confirm) {
    $errors['password_confirm'] = 'Паролі не співпадають';
}

// Проверка согласия с условиями
if (!$agree_terms) {
    $errors['agree_terms'] = 'Необхідно погодитися з умовами використання';
}

// Если есть ошибки валидации
if (!empty($errors)) {
    logRegistrationAttempt($pdo, $client_ip, $email, false);
    sendResponse(false, 'Виправте помилки у формі', [], $errors);
}

// Проверяем существование email
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        logRegistrationAttempt($pdo, $client_ip, $email, false);
        sendResponse(false, 'Користувач з таким email вже існує', [], ['email' => 'Email вже зареєстрований']);
    }
} catch (Exception $e) {
    error_log('Email check failed: ' . $e->getMessage());
    sendResponse(false, 'Помилка перевірки email');
}

// Хешируем пароль
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Генерируем токен для подтверждения email
$verification_token = bin2hex(random_bytes(32));

// Регистрируем пользователя
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, phone, password_hash, verification_token, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$full_name, $email, $phone, $password_hash, $verification_token]);
    $user_id = $pdo->lastInsertId();
    
    // Логируем успешную регистрацию
    logRegistrationAttempt($pdo, $client_ip, $email, true);
    
    // В реальном проекте здесь бы отправляли email с подтверждением
    // sendVerificationEmail($email, $verification_token);
    
    // Автоматически логиним пользователя
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_verified'] = false;
    
    sendResponse(true, 'Реєстрація пройшла успішно! Ласкаво просимо!', [
        'user_id' => $user_id,
        'redirect' => '/' // Можно изменить на страницу личного кабинета
    ]);
    
} catch (Exception $e) {
    error_log('Registration failed: ' . $e->getMessage());
    logRegistrationAttempt($pdo, $client_ip, $email, false);
    sendResponse(false, 'Помилка реєстрації. Спробуйте ще раз.');
}
?>