<?php
/**
 * Страница профиля пользователя - ИСПРАВЛЕННАЯ ВЕРСИЯ
 * Файл: /client/profile.php
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Начинаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем авторизацию
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: /?login_required=1');
    exit;
}

// Основные переменные страницы
$page_title = 'Налаштування профілю - StormHosting UA';
$meta_description = 'Налаштування профілю користувача в панелі управління StormHosting UA';
$additional_css = [
    '/assets/css/pages/client-profile.css'
];

// Подключение к БД
try {
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
    die('Помилка підключення до бази даних');
}

// Создаем недостающие поля в таблице users если их нет
try {
    // Проверяем структуру таблицы
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Добавляем недостающие поля
    if (!in_array('phone', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email");
    }
    
    if (!in_array('email_verified', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER phone");
    }
    
    if (!in_array('avatar', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER email_verified");
    }
    
    if (!in_array('created_at', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER avatar");
    }
    
    if (!in_array('updated_at', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    }
    
} catch (Exception $e) {
    error_log('Failed to update table structure: ' . $e->getMessage());
}

// Получаем данные пользователя
$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';
$errors = [];

try {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, phone, email_verified, avatar, created_at, updated_at 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: /?session_expired=1');
        exit;
    }
    
    // Устанавливаем значения по умолчанию если поля NULL
    $user['phone'] = $user['phone'] ?? '';
    $user['email_verified'] = $user['email_verified'] ?? 0;
    $user['avatar'] = $user['avatar'] ?? '';
    $user['created_at'] = $user['created_at'] ?? date('Y-m-d H:i:s');
    $user['updated_at'] = $user['updated_at'] ?? date('Y-m-d H:i:s');
    
} catch (Exception $e) {
    error_log('Failed to fetch user data: ' . $e->getMessage());
    $error_message = 'Помилка завантаження даних профілю: ' . $e->getMessage();
}

// Обработка загрузки аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/';
    
    // Создаем папку если не существует
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['avatar'];
    
    // Проверяем размер файла (максимум 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $error_message = 'Розмір файлу не повинен перевищувати 2MB';
    }
    // Проверяем тип файла
    elseif (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
        $error_message = 'Дозволені тільки файли типу: JPG, PNG, GIF, WebP';
    }
    else {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Удаляем старый аватар если есть
            if ($user['avatar'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $user['avatar'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $user['avatar']);
            }
            
            $avatar_path = '/uploads/avatars/' . $filename;
            
            try {
                $stmt = $pdo->prepare("UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$avatar_path, $user_id]);
                
                $user['avatar'] = $avatar_path;
                $success_message = 'Аватар успішно оновлено!';
                
            } catch (Exception $e) {
                error_log('Failed to update avatar: ' . $e->getMessage());
                $error_message = 'Помилка збереження аватара в базі даних';
            }
        } else {
            $error_message = 'Помилка завантаження файлу';
        }
    }
}

// Обработка форм
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Валидация
        if (empty($full_name)) {
            $errors['full_name'] = 'Вкажіть повне ім\'я';
        } elseif (strlen($full_name) < 2) {
            $errors['full_name'] = 'Ім\'я повинно містити мінімум 2 символи';
        } elseif (strlen($full_name) > 255) {
            $errors['full_name'] = 'Ім\'я занадто довге';
        }
        
        if (!empty($phone)) {
            // Очищаем номер от всех символов кроме цифр и +
            $cleaned_phone = preg_replace('/[^0-9+]/', '', $phone);
            
            // Проверяем формат украинского номера
            if (!preg_match('/^\+380\d{9}$/', $cleaned_phone)) {
                $errors['phone'] = 'Невірний формат телефону. Використовуйте формат +380XXXXXXXXX';
            } else {
                $phone = $cleaned_phone;
            }
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = ?, phone = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $result = $stmt->execute([$full_name, $phone, $user_id]);
                
                if ($result) {
                    // Обновляем данные в сессии и локальной переменной
                    $_SESSION['user_name'] = $full_name;
                    $user['full_name'] = $full_name;
                    $user['phone'] = $phone;
                    $user['updated_at'] = date('Y-m-d H:i:s');
                    
                    $success_message = 'Дані профілю успішно оновлені!';
                } else {
                    $error_message = 'Не вдалося оновити дані профілю';
                }
                
            } catch (Exception $e) {
                error_log('Failed to update profile: ' . $e->getMessage());
                $error_message = 'Помилка оновлення профілю: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Валидация
        if (empty($current_password)) {
            $errors['current_password'] = 'Вкажіть поточний пароль';
        }
        
        if (empty($new_password)) {
            $errors['new_password'] = 'Вкажіть новий пароль';
        } elseif (strlen($new_password) < 8) {
            $errors['new_password'] = 'Новий пароль повинен містити мінімум 8 символів';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
            $errors['new_password'] = 'Пароль повинен містити великі та малі літери, цифри';
        }
        
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Паролі не співпадають';
        }
        
        if (empty($errors)) {
            try {
                // Проверяем текущий пароль
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch();
                
                if (!password_verify($current_password, $user_data['password_hash'])) {
                    $errors['current_password'] = 'Невірний поточний пароль';
                } else {
                    // Обновляем пароль
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET password_hash = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$new_password_hash, $user_id]);
                    
                    // Удаляем все remember tokens
                    try {
                        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
                        $stmt->execute([$user_id]);
                    } catch (Exception $e) {
                        // Игнорируем ошибку если таблица не существует
                    }
                    
                    $success_message = 'Пароль успішно змінений!';
                }
                
            } catch (Exception $e) {
                error_log('Failed to change password: ' . $e->getMessage());
                $error_message = 'Помилка зміни пароля: ' . $e->getMessage();
            }
        }
    }
}

// Подключаем header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="container">
        <a href="/" class="breadcrumb-link">Головна</a>
        <span class="breadcrumb-separator">/</span>
        <a href="/client/dashboard.php" class="breadcrumb-link">Кабінет</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Налаштування профілю</span>
    </div>
</div>

<!-- Main Content -->
<main class="main-content client-profile">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <div class="page-icon">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div>
                    <h1 class="page-title">Налаштування профілю</h1>
                    <p class="page-subtitle">Управління особистими даними та налаштуваннями аккаунту</p>
                </div>
            </div>
            <div class="page-actions">
                <a href="/client/dashboard.php" class="btn btn-outline">
                    <i class="bi bi-arrow-left"></i>
                    Повернутися до кабінету
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Info Card -->
            <div class="col-lg-4">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php if ($user['avatar'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $user['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="avatar-image">
                        <?php else: ?>
                            <div class="avatar-circle">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <button class="avatar-edit-btn" type="button" title="Змінити аватар" onclick="document.getElementById('avatarUpload').click()">
                            <i class="bi bi-camera"></i>
                        </button>
                        
                        <!-- Скрытое поле для загрузки файла -->
                        <form method="POST" enctype="multipart/form-data" id="avatarForm" style="display: none;">
                            <input type="file" id="avatarUpload" name="avatar" accept="image/*" onchange="uploadAvatar()">
                        </form>
                    </div>
                    
                    <div class="profile-info">
                        <h3 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="profile-email">
                            <i class="bi bi-envelope me-1"></i>
                            <?php echo htmlspecialchars($user['email']); ?>
                            <?php if ($user['email_verified']): ?>
                                <span class="verified-badge">
                                    <i class="bi bi-patch-check-fill"></i>
                                    Підтверджено
                                </span>
                            <?php else: ?>
                                <span class="unverified-badge">
                                    <i class="bi bi-exclamation-circle"></i>
                                    Не підтверджено
                                </span>
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($user['phone']): ?>
                            <p class="profile-phone">
                                <i class="bi bi-telephone me-1"></i>
                                <?php echo htmlspecialchars($user['phone']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="profile-meta">
                            <div class="meta-item">
                                <span class="meta-label">Дата реєстрації:</span>
                                <span class="meta-value"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Останнє оновлення:</span>
                                <span class="meta-value"><?php echo date('d.m.Y H:i', strtotime($user['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value">0</div>
                            <div class="stat-label">Активних послуг</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">0</div>
                            <div class="stat-label">Доменів</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">0</div>
                            <div class="stat-label">Серверів</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Forms -->
            <div class="col-lg-8">
                <!-- Profile Settings -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="settings-title">
                            <i class="bi bi-person me-2"></i>
                            Особисті дані
                        </h2>
                        <p class="settings-description">Оновіть ваші особисті дані та контактну інформацію</p>
                    </div>
                    
                    <form method="POST" class="settings-form" id="profileForm">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person"></i>
                                Повне ім'я
                            </label>
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   class="form-control<?php echo isset($errors['full_name']) ? ' is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                   required>
                            <?php if (isset($errors['full_name'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['full_name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i>
                                Email адреса
                            </label>
                            <input type="email" 
                                   id="email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   disabled>
                            <div class="form-text">Email адресу неможливо змінити. Зверніться до підтримки для зміни.</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone"></i>
                                Номер телефону
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-control<?php echo isset($errors['phone']) ? ' is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>"
                                   placeholder="+380 93 123 45 67">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                            <?php endif; ?>
                            <div class="form-text">Використовується для важливих повідомлень та двофакторної аутентифікації</div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i>
                                Зберегти зміни
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Password Change -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="settings-title">
                            <i class="bi bi-shield-lock me-2"></i>
                            Зміна пароля
                        </h2>
                        <p class="settings-description">Регулярно оновлюйте пароль для забезпечення безпеки аккаунту</p>
                    </div>
                    
                    <form method="POST" class="settings-form" id="passwordForm">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password" class="form-label">
                                <i class="bi bi-lock"></i>
                                Поточний пароль
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="form-control<?php echo isset($errors['current_password']) ? ' is-invalid' : ''; ?>"
                                       required>
                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="current_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['current_password']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">
                                <i class="bi bi-lock-fill"></i>
                                Новий пароль
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="form-control<?php echo isset($errors['new_password']) ? ' is-invalid' : ''; ?>"
                                       required>
                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="new_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['new_password']; ?></div>
                            <?php endif; ?>
                            <div class="form-text">Мінімум 8 символів, включаючи великі та малі літери, цифри</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-check-square"></i>
                                Підтвердження нового пароля
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control<?php echo isset($errors['confirm_password']) ? ' is-invalid' : ''; ?>"
                                       required>
                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="confirm_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-shield-check"></i>
                                Змінити пароль
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Security Settings -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="settings-title">
                            <i class="bi bi-shield-check me-2"></i>
                            Налаштування безпеки
                        </h2>
                        <p class="settings-description">Додаткові опції для захисту вашого аккаунту</p>
                    </div>
                    
                    <div class="settings-content">
                        <div class="security-option">
                            <div class="option-info">
                                <h4 class="option-title">Двофакторна аутентифікація</h4>
                                <p class="option-description">Додатковий рівень захисту за допомогою SMS або додатку</p>
                            </div>
                            <div class="option-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" disabled>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="option-status">Незабаром</span>
                            </div>
                        </div>
                        
                        <div class="security-option">
                            <div class="option-info">
                                <h4 class="option-title">Email повідомлення</h4>
                                <p class="option-description">Отримувати сповіщення про вхід з нових пристроїв</p>
                            </div>
                            <div class="option-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="option-status">Увімкнено</span>
                            </div>
                        </div>
                        
                        <div class="security-option">
                            <div class="option-info">
                                <h4 class="option-title">Журнал активності</h4>
                                <p class="option-description">Переглянути історію входів та дій в аккаунті</p>
                            </div>
                            <div class="option-control">
                                <button class="btn btn-outline" disabled>
                                    <i class="bi bi-clock-history"></i>
                                    Переглянути
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Danger Zone -->
                <div class="settings-card danger-zone">
                    <div class="settings-header">
                        <h2 class="settings-title">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Небезпечна зона
                        </h2>
                        <p class="settings-description">Дії, які можуть призвести до втрати даних</p>
                    </div>
                    
                    <div class="settings-content">
                        <div class="danger-option">
                            <div class="option-info">
                                <h4 class="option-title">Видалення аккаунту</h4>
                                <p class="option-description">Повне видалення аккаунту та всіх пов'язаних даних. Цю дію неможливо скасувати.</p>
                            </div>
                            <div class="option-control">
                                <button class="btn btn-danger" disabled>
                                    <i class="bi bi-trash"></i>
                                    Видалити аккаунт
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript для страницы -->
<script>
// Upload avatar function
function uploadAvatar() {
    const form = document.getElementById('avatarForm');
    const fileInput = document.getElementById('avatarUpload');
    
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        
        // Проверяем размер файла (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Розмір файлу не повинен перевищувати 2MB');
            return;
        }
        
        // Проверяем тип файла
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Дозволені тільки файли типу: JPG, PNG, GIF, WebP');
            return;
        }
        
        // Показываем индикатор загрузки
        const avatarBtn = document.querySelector('.avatar-edit-btn');
        const originalIcon = avatarBtn.innerHTML;
        avatarBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        avatarBtn.disabled = true;
        
        // Отправляем форму
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Phone number formatting - ИСПРАВЛЕННАЯ ВЕРСИЯ
    const phoneField = document.getElementById('phone');
    if (phoneField) {
        phoneField.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Удаляем все кроме цифр
            
            // Если номер начинается с 380, добавляем +
            if (value.startsWith('380')) {
                value = '+' + value;
            }
            // Если номер начинается с 0, заменяем на +380
            else if (value.startsWith('0')) {
                value = '+38' + value;
            }
            // Если номер начинается с 80, заменяем на +380
            else if (value.startsWith('80')) {
                value = '+3' + value;
            }
            // Если просто цифры без кода страны
            else if (value.length > 0 && !value.startsWith('+')) {
                value = '+380' + value;
            }
            
            // Убираем + для форматирования
            let cleanValue = value.replace('+', '');
            
            // Форматируем как +380 XX XXX XX XX
            if (cleanValue.length >= 3) {
                if (cleanValue.startsWith('380')) {
                    let formatted = '+380';
                    if (cleanValue.length > 3) {
                        formatted += ' ' + cleanValue.substring(3, 5); // XX
                    }
                    if (cleanValue.length > 5) {
                        formatted += ' ' + cleanValue.substring(5, 8); // XXX
                    }
                    if (cleanValue.length > 8) {
                        formatted += ' ' + cleanValue.substring(8, 10); // XX
                    }
                    if (cleanValue.length > 10) {
                        formatted += ' ' + cleanValue.substring(10, 12); // XX
                    }
                    
                    // Ограничиваем длину до +380 XX XXX XX XX
                    if (cleanValue.length > 12) {
                        formatted = formatted.substring(0, 17); // +380 XX XXX XX XX = 17 символов
                    }
                    
                    this.value = formatted;
                } else {
                    this.value = value;
                }
            } else {
                this.value = value;
            }
        });
    }
    
    // Password toggle buttons
    document.querySelectorAll('[data-toggle-password]').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'bi bi-eye-slash';
                    this.setAttribute('title', 'Приховати пароль');
                } else {
                    input.type = 'password';
                    icon.className = 'bi bi-eye';
                    this.setAttribute('title', 'Показати пароль');
                }
                
                // Возвращаем фокус на поле ввода
                input.focus();
            }
        });
    });
    
    // Form validation for password change
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Паролі не співпадають!');
                return false;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Новий пароль повинен містити мінімум 8 символів!');
                return false;
            }
            
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/;
            if (!passwordRegex.test(newPassword)) {
                e.preventDefault();
                alert('Пароль повинен містити великі та малі літери, цифри!');
                return false;
            }
        });
        
        // Real-time password validation
        const newPasswordField = document.getElementById('new_password');
        const confirmPasswordField = document.getElementById('confirm_password');
        
        if (newPasswordField && confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function() {
                const newPassword = newPasswordField.value;
                const confirmPassword = this.value;
                
                if (confirmPassword && newPassword !== confirmPassword) {
                    this.classList.add('is-invalid');
                    let feedback = this.parentElement.parentElement.querySelector('.invalid-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        this.parentElement.parentElement.appendChild(feedback);
                    }
                    feedback.textContent = 'Паролі не співпадають';
                    feedback.style.display = 'block';
                } else {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentElement.parentElement.querySelector('.invalid-feedback');
                    if (feedback && !feedback.textContent.includes('повинен містити')) {
                        feedback.style.display = 'none';
                    }
                }
            });
        }
    }
    
    // Profile form validation
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value.trim();
            
            if (fullName.length < 2) {
                e.preventDefault();
                alert('Ім\'я повинно містити мінімум 2 символи!');
                return false;
            }
        });
    }
    
    // Toggle switches
    document.querySelectorAll('.toggle-switch input').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const status = this.closest('.security-option').querySelector('.option-status');
            if (status && !this.disabled) {
                status.textContent = this.checked ? 'Увімкнено' : 'Вимкнено';
                
                // Здесь можно добавить AJAX запрос для сохранения настройки
                console.log('Setting changed:', this.name, this.checked);
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            if (alert.querySelector('.btn-close')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 300);
            }
        });
    }, 5000);
    
    // Future functionality placeholders
    document.querySelectorAll('button[disabled]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.textContent.includes('Видалити')) {
                alert('Функція видалення аккаунту буде додана в майбутніх версіях.');
            } else if (this.textContent.includes('Переглянути')) {
                alert('Журнал активності буде доступний найближчим часом.');
            } else {
                alert('Ця функція знаходиться в розробці.');
            }
        });
    });
});

// Utility function for AJAX requests (for future use)
function makeAjaxRequest(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (callback) callback(data);
    })
    .catch(error => {
        console.error('AJAX Error:', error);
    });
}

// Function to show notification
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
    notification.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}
</script>

<!-- Дополнительные стили для аватара -->
<style>
.avatar-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.profile-avatar {
    position: relative;
    display: inline-block;
    margin-bottom: 1.5rem;
}

.avatar-edit-btn:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: scale(1.1);
}

.form-control:disabled {
    background-color: #f8f9fa;
    opacity: 0.7;
}

/* Улучшенные стили для телефона */
#phone {
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
}

/* Анимация загрузки */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.avatar-edit-btn .bi-hourglass-split {
    animation: spin 1s linear infinite;
}
</style>

<?php 
// Подключаем footer
include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; 
?>