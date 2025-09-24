<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Заголовки для AJAX
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Только POST запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не дозволений']);
    exit;
}

$response = ['success' => false, 'message' => '', 'errors' => []];

try {
    // Получение и валидация данных
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $language = in_array($_POST['language'] ?? 'ua', ['ua', 'en', 'ru']) ? $_POST['language'] : 'ua';
    $accept_terms = !empty($_POST['accept_terms']);
    $marketing_emails = !empty($_POST['marketing_emails']);
    
    // Валидация
    if (!$email) {
        $response['errors']['email'] = 'Введіть коректну email адресу';
    }
    
    if (strlen($password) < 8) {
        $response['errors']['password'] = 'Пароль повинен містити мінімум 8 символів';
    }
    
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
        $response['errors']['password'] = 'Пароль повинен містити великі і малі літери та цифри';
    }
    
    if ($password !== $password_confirm) {
        $response['errors']['password_confirm'] = 'Паролі не співпадають';
    }
    
    if (empty($full_name) || strlen($full_name) < 2) {
        $response['errors']['full_name'] = 'Введіть повне ім\'я (мінімум 2 символи)';
    }
    
    if (!empty($phone) && !preg_match('/^\+?[0-9\s\-\(\)]{10,15}$/', $phone)) {
        $response['errors']['phone'] = 'Введіть коректний номер телефону';
    }
    
    if (!$accept_terms) {
        $response['errors']['accept_terms'] = 'Необхідно прийняти умови використання';
    }
    
    // Проверка существования пользователя
    if (empty($response['errors'])) {
        $existing_user = DatabaseConnection::fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if ($existing_user) {
            $response['errors']['email'] = 'Користувач з таким email вже існує';
        }
    }
    
    // Если есть ошибки валидации, возвращаем их
    if (!empty($response['errors'])) {
        $response['message'] = 'Будь ласка, виправте помилки у формі';
        echo json_encode($response);
        exit;
    }
    
    // Хешируем пароль
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Начинаем транзакцию
    $pdo = DatabaseConnection::getSiteConnection();
    $pdo->beginTransaction();
    
    try {
        // Создаем пользователя в основной БД
        $user_id = DatabaseConnection::insert(
            "INSERT INTO users (email, password, full_name, phone, language, registration_date, is_active, marketing_emails) VALUES (?, ?, ?, ?, ?, NOW(), 1, ?)",
            [$email, $password_hash, $full_name, $phone, $language, $marketing_emails ? 1 : 0]
        );
        
        // Создаем клиента в FOSSBilling (заглушка)
        $fossbilling_client_id = createFOSSBillingClient($email, $password, $full_name, $phone);
        
        // Обновляем запись пользователя с ID клиента FOSSBilling
        if ($fossbilling_client_id) {
            DatabaseConnection::execute(
                "UPDATE users SET fossbilling_client_id = ? WHERE id = ?",
                [$fossbilling_client_id, $user_id]
            );
        }
        
        // Создаем аккаунт в ispmanager (заглушка)
        $ispmanager_created = createISPManagerAccount($email, $password, $full_name);
        
        // Логируем успешную регистрацию
        DatabaseConnection::insert(
            "INSERT INTO security_logs (ip_address, user_id, action, details, severity) VALUES (?, ?, ?, ?, ?)",
            [
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $user_id,
                'user_registration',
                "Успішна реєстрація користувача. FOSSBilling ID: $fossbilling_client_id, ISPManager: " . ($ispmanager_created ? 'створено' : 'помилка'),
                'low'
            ]
        );
        
        // Подтверждаем транзакцию
        $pdo->commit();
        
        // Автоматически авторизуем пользователя
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_language'] = $language;
        $_SESSION['is_logged_in'] = true;
        $_SESSION['fossbilling_client_id'] = $fossbilling_client_id;
        
        // Отправляем welcome email (заглушка)
        sendWelcomeEmail($email, $full_name);
        
        $response['success'] = true;
        $response['message'] = 'Реєстрація успішна! Ви автоматично авторизовані.';
        $response['redirect'] = '/client/dashboard.php';
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    
    // Логируем ошибку
    error_log('Registration error: ' . $e->getMessage());
    
    DatabaseConnection::insert(
        "INSERT INTO security_logs (ip_address, action, details, severity) VALUES (?, ?, ?, ?)",
        [
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'registration_error',
            $e->getMessage(),
            'medium'
        ]
    );
}

echo json_encode($response);

// Функция создания клиента в FOSSBilling (заглушка)
function createFOSSBillingClient($email, $password, $full_name, $phone) {
    // Здесь будет реальная интеграция с FOSSBilling API
    return null;
}

// Функция создания аккаунта в ispmanager (заглушка)
function createISPManagerAccount($email, $password, $full_name) {
    // Здесь будет реальная интеграция с ispmanager API
    return false;
}

// Функция отправки welcome email (заглушка)
function sendWelcomeEmail($email, $full_name) {
    // Здесь будет отправка email
    return true;
}
?>