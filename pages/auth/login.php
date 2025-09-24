<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Обработка AJAX авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $response = ['success' => false, 'message' => '', 'errors' => []];
    
    try {
        // CSRF защита
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!DatabaseConnection::validateCSRFToken($csrf_token)) {
            throw new Exception('Невірний токен безпеки');
        }
        
        // Получение данных
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember_me = !empty($_POST['remember_me']);
        
        // Валидация
        if (!$email) {
            $response['errors']['email'] = 'Введіть коректну email адресу';
        }
        
        if (empty($password)) {
            $response['errors']['password'] = 'Введіть пароль';
        }
        
        // Проверка rate limiting
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $login_attempts = DatabaseConnection::fetchOne(
            "SELECT attempts, locked_until FROM login_attempts WHERE ip_address = ? OR email = ?",
            [$client_ip, $email]
        );
        
        if ($login_attempts && $login_attempts['locked_until'] && strtotime($login_attempts['locked_until']) > time()) {
            $lockout_minutes = ceil((strtotime($login_attempts['locked_until']) - time()) / 60);
            throw new Exception("Забагато невдалих спроб входу. Спробуйте через {$lockout_minutes} хвилин.");
        }
        
        // Если есть ошибки валидации, возвращаем их
        if (!empty($response['errors'])) {
            $response['message'] = 'Будь ласка, виправте помилки у формі';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Проверяем пользователя в БД
        $user = DatabaseConnection::fetchOne(
            "SELECT id, email, password, full_name, language, is_active, fossbilling_client_id FROM users WHERE email = ?",
            [$email]
        );
        
        if (!$user || !password_verify($password, $user['password'])) {
            // Записываем неудачную попытку
            recordLoginAttempt($client_ip, $email, false);
            throw new Exception('Невірний email або пароль');
        }
        
        if (!$user['is_active']) {
            throw new Exception('Ваш обліковий запис деактивовано. Зверніться до підтримки.');
        }
        
        // Успешная авторизация
        recordLoginAttempt($client_ip, $email, true);
        
        // Обновляем время последнего входа
        DatabaseConnection::execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );
        
        // Устанавливаем сессию
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_language'] = $user['language'];
        $_SESSION['is_logged_in'] = true;
        $_SESSION['fossbilling_client_id'] = $user['fossbilling_client_id'];
        
        // Если выбрано "Запомнить меня"
        if ($remember_me) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 дней
            
            // Сохраняем токен в БД (нужно создать таблицу remember_tokens)
            DatabaseConnection::insert(
                "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))",
                [$user['id'], hash('sha256', $token)]
            );
        }
        
        // Логируем успешный вход
        DatabaseConnection::insert(
            "INSERT INTO security_logs (ip_address, user_id, action, details, severity) VALUES (?, ?, ?, ?, ?)",
            [
                $client_ip,
                $user['id'],
                'user_login',
                'Успішний вхід в систему',
                'low'
            ]
        );
        
        $response['success'] = true;
        $response['message'] = 'Авторизація успішна!';
        $response['redirect'] = '/client/dashboard.php';
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        
        // Логируем ошибку
        DatabaseConnection::insert(
            "INSERT INTO security_logs (ip_address, action, details, severity) VALUES (?, ?, ?, ?)",
            [
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'login_error',
                $e->getMessage(),
                'medium'
            ]
        );
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Функция записи попыток входа
function recordLoginAttempt($ip, $email, $success) {
    if ($success) {
        // Удаляем записи о неудачных попытках
        DatabaseConnection::execute(
            "DELETE FROM login_attempts WHERE ip_address = ? OR email = ?",
            [$ip, $email]
        );
    } else {
        // Увеличиваем счетчик попыток
        $existing = DatabaseConnection::fetchOne(
            "SELECT id, attempts FROM login_attempts WHERE ip_address = ? OR email = ?",
            [$ip, $email]
        );
        
        if ($existing) {
            $new_attempts = $existing['attempts'] + 1;
            $locked_until = null;
            
            // Блокировка после 5 попыток на 15 минут
            if ($new_attempts >= 5) {
                $locked_until = date('Y-m-d H:i:s', time() + 900); // 15 минут
            }
            
            DatabaseConnection::execute(
                "UPDATE login_attempts SET attempts = ?, last_attempt = NOW(), locked_until = ? WHERE id = ?",
                [$new_attempts, $locked_until, $existing['id']]
            );
        } else {
            DatabaseConnection::insert(
                "INSERT INTO login_attempts (ip_address, email, attempts, last_attempt) VALUES (?, ?, 1, NOW())",
                [$ip, $email]
            );
        }
    }
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
    <title>Вхід - StormHosting UA</title>
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
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
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
        
        .btn-login {
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:disabled {
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
        
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        
        .forgot-password {
            text-align: center;
            margin: 20px 0;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="bi bi-box-arrow-in-right me-2"></i>Вхід</h1>
            <p>Увійдіть в ваш обліковий запис</p>
        </div>
        
        <div id="alertContainer"></div>
        
        <form id="loginForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-1"></i>Email адреса
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="Введіть ваш email"
                           required
                           autocomplete="email">
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
                           placeholder="Введіть ваш пароль"
                           required
                           autocomplete="current-password">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-check">
                <input type="checkbox" id="remember_me" name="remember_me" class="form-check-input">
                <label for="remember_me" class="form-check-label">
                    Запам'ятати мене
                </label>
            </div>
            
            <button type="submit" class="btn btn-login" id="submitBtn">
                <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                    <span class="visually-hidden">Завантаження...</span>
                </div>
                <span class="btn-text">Увійти</span>
            </button>
        </form>
        
        <div class="forgot-password">
            <a href="/pages/auth/forgot-password.php">Забули пароль?</a>
        </div>
        
        <div class="register-link">
            <p>Немає облікового запису? <a href="/pages/auth/register.php">Зареєструйтеся тут</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            // Показать/скрыть пароль
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
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
                document.querySelector('.btn-text').textContent = 'Вхід...';
                
                // Очищаем предыдущие ошибки
                clearErrors();
                
                fetch('/pages/auth/login.php', {
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
                        }, 1000);
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
                    showAlert('danger', 'Виникла помилка під час авторизації. Спробуйте ще раз.');
                })
                .finally(() => {
                    // Скрываем загрузку
                    submitBtn.disabled = false;
                    document.querySelector('.loading-spinner').style.display = 'none';
                    document.querySelector('.btn-text').textContent = 'Увійти';
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