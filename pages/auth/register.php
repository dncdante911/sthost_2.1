<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Обработка AJAX регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $response = ['success' => false, 'message' => '', 'errors' => []];
    
    try {
        // CSRF защита
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!DatabaseConnection::validateCSRFToken($csrf_token)) {
            throw new Exception('Невірний токен безпеки');
        }
        
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
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Хешируем пароль
        $password_hash = password_hash($password, PASSWORD_ARGON2ID);
        
        // Начинаем транзакцию
        $pdo = DatabaseConnection::getSiteConnection();
        $pdo->beginTransaction();
        
        try {
            // Создаем пользователя в основной БД
            $user_id = DatabaseConnection::insert(
                "INSERT INTO users (email, password, full_name, phone, language, registration_date, is_active) VALUES (?, ?, ?, ?, ?, NOW(), 1)",
                [$email, $password_hash, $full_name, $phone, $language]
            );
            
            // Создаем клиента в FOSSBilling
            $fossbilling_client_id = createFOSSBillingClient($email, $password, $full_name, $phone);
            
            // Обновляем запись пользователя с ID клиента FOSSBilling
            if ($fossbilling_client_id) {
                DatabaseConnection::execute(
                    "UPDATE users SET fossbilling_client_id = ? WHERE id = ?",
                    [$fossbilling_client_id, $user_id]
                );
            }
            
            // Создаем аккаунт в ispmanager (опционально, для будущих услуг)
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
            
            // Отправляем welcome email (если настроен SMTP)
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
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Функция создания клиента в FOSSBilling
function createFOSSBillingClient($email, $password, $full_name, $phone) {
    try {
        $url = FOSSBILLING_URL . '/api/admin/client';
        
        // Разбиваем имя на части
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0] ?? '';
        $last_name = $name_parts[1] ?? '';
        
        $data = [
            'email' => $email,
            'password' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone ?: '',
            'company' => '',
            'group_id' => 1, // Группа по умолчанию
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . FOSSBILLING_API_TOKEN
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            return $result['result']['id'] ?? null;
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log('FOSSBilling client creation error: ' . $e->getMessage());
        return null;
    }
}

// Функция создания аккаунта в ispmanager
function createISPManagerAccount($email, $password, $full_name) {
    try {
        $url = ISPMANAGER_URL . '/api/v3/user';
        
        $data = [
            'username' => $email,
            'password' => $password,
            'name' => $full_name,
            'email' => $email,
            'preset' => 'user', // Базовый пресет пользователя
            'sok' => 'ok'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_USERPWD => ISPMANAGER_USER . ':' . ISPMANAGER_PASS,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $http_code === 200;
        
    } catch (Exception $e) {
        error_log('ISPManager account creation error: ' . $e->getMessage());
        return false;
    }
}

// Функция отправки welcome email
function sendWelcomeEmail($email, $full_name) {
    // Здесь можно добавить отправку email через PHPMailer или другой SMTP сервис
    // Пока оставляем заглушку
    return true;
}

// Генерируем CSRF токен для формы
$csrf_token = DatabaseConnection::generateCSRFToken();

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація - StormHosting UA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #666;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e1e5e9;
            border-right: none;
            border-radius: 12px 0 0 12px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-register:disabled {
            opacity: 0.7;
            transform: none;
        }
        
        .form-check {
            margin: 20px 0;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
        }
        
        .invalid-feedback {
            font-size: 14px;
            margin-top: 5px;
        }
        
        .password-strength {
            margin-top: 8px;
            display: none;
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e1e5e9;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #28a745; width: 75%; }
        .strength-strong { background: #007bff; width: 100%; }
        
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .register-container {
                padding: 30px 20px;
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1><i class="bi bi-person-plus me-2"></i>Реєстрація</h1>
            <p>Створіть обліковий запис у StormHosting UA</p>
        </div>
        
        <div id="alertContainer"></div>
        
        <form id="registerForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="action" value="register">
            
            <div class="form-group">
                <label for="full_name" class="form-label">
                    <i class="bi bi-person me-1"></i>Повне ім'я
                </label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       class="form-control" 
                       placeholder="Введіть ваше повне ім'я"
                       required>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-1"></i>Email адреса
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control" 
                       placeholder="Введіть ваш email"
                       required>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label">
                    <i class="bi bi-telephone me-1"></i>Номер телефону (опціонально)
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-telephone"></i>
                    </span>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           class="form-control" 
                           placeholder="+380xxxxxxxxx">
                </div>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i>Пароль
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Створіть надійний пароль"
                           required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill"></div>
                    </div>
                    <small class="text-muted">Пароль повинен містити мінімум 8 символів, великі і малі літери, цифри</small>
                </div>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="password_confirm" class="form-label">
                    <i class="bi bi-lock-fill me-1"></i>Підтвердження паролю
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input type="password" 
                           id="password_confirm" 
                           name="password_confirm" 
                           class="form-control" 
                           placeholder="Повторіть пароль"
                           required>
                </div>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="language" class="form-label">
                    <i class="bi bi-globe me-1"></i>Мова інтерфейсу
                </label>
                <select id="language" name="language" class="form-control">
                    <option value="ua" selected>Українська</option>
                    <option value="en">English</option>
                    <option value="ru">Русский</option>
                </select>
            </div>
            
            <div class="form-check">
                <input type="checkbox" id="accept_terms" name="accept_terms" class="form-check-input" required>
                <label for="accept_terms" class="form-check-label">
                    Я приймаю <a href="/pages/info/rules.php" target="_blank">умови використання</a> та 
                    <a href="/pages/info/legal.php" target="_blank">політику конфіденційності</a>
                </label>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-check">
                <input type="checkbox" id="marketing_emails" name="marketing_emails" class="form-check-input">
                <label for="marketing_emails" class="form-check-label">
                    Я хочу отримувати новини та спеціальні пропозиції на email
                </label>
            </div>
            
            <button type="submit" class="btn btn-register" id="submitBtn">
                <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                    <span class="visually-hidden">Завантаження...</span>
                </div>
                <span class="btn-text">Зареєструватися</span>
            </button>
        </form>
        
        <div class="login-link">
            <p>Вже маєте обліковий запис? <a href="/pages/auth/login.php">Увійдіть тут</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const submitBtn = document.getElementById('submitBtn');
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirm');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const strengthBar = document.querySelector('.password-strength');
            const strengthFill = document.querySelector('.strength-fill');
            
            // Показать/скрыть пароль
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
            
            // Проверка силы пароля
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                strengthBar.style.display = password.length > 0 ? 'block' : 'none';
                
                if (password.length === 0) return;
                
                let strength = 0;
                
                // Проверки
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[^a-zA-Z\d]/.test(password)) strength++;
                
                // Установка класса силы
                strengthFill.className = 'strength-fill';
                if (strength === 1) strengthFill.classList.add('strength-weak');
                else if (strength === 2) strengthFill.classList.add('strength-fair');
                else if (strength === 3) strengthFill.classList.add('strength-good');
                else if (strength >= 4) strengthFill.classList.add('strength-strong');
            });
            
            // Проверка совпадения паролей
            passwordConfirmInput.addEventListener('input', function() {
                if (this.value && this.value !== passwordInput.value) {
                    this.setCustomValidity('Паролі не співпадають');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            // Обработка отправки формы
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                
                submitForm();
            });
            
            function submitForm() {
                const formData = new FormData(form);
                
                // Показываем загрузку
                submitBtn.disabled = true;
                document.querySelector('.loading-spinner').style.display = 'inline-block';
                document.querySelector('.btn-text').textContent = 'Реєстрація...';
                
                // Очищаем предыдущие ошибки
                clearErrors();
                
                fetch('/pages/auth/register.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        
                        // Перенаправляем в личный кабинет
                        setTimeout(() => {
                            window.location.href = data.redirect || '/client/dashboard.php';
                        }, 1500);
                    } else {
                        if (data.errors) {
                            showFieldErrors(data.errors);
                        }
                        if (data.message) {
                            showAlert('danger', data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'Виникла помилка під час реєстрації. Спробуйте ще раз.');
                })
                .finally(() => {
                    // Скрываем загрузку
                    submitBtn.disabled = false;
                    document.querySelector('.loading-spinner').style.display = 'none';
                    document.querySelector('.btn-text').textContent = 'Зареєструватися';
                });
            }
            
            function showAlert(type, message) {
                const alertContainer = document.getElementById('alertContainer');
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show`;
                alert.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                alertContainer.appendChild(alert);
                
                // Автоматически скрыть через 5 секунд
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            }
            
            function showFieldErrors(errors) {
                form.classList.add('was-validated');
                
                Object.keys(errors).forEach(field => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.parentNode.querySelector('.invalid-feedback') || 
                                       input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = errors[field];
                        }
                    }
                });
            }
            
            function clearErrors() {
                form.classList.remove('was-validated');
                form.querySelectorAll('.is-invalid').forEach(input => {
                    input.classList.remove('is-invalid');
                });
                form.querySelectorAll('.invalid-feedback').forEach(feedback => {
                    feedback.textContent = '';
                });
                document.getElementById('alertContainer').innerHTML = '';
            }
        });
    </script>
</body>
</html>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>