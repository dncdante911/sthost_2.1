<?php
define('SECURE_ACCESS', true);

// Захист від прямого доступу
if (!defined('SECURE_ACCESS')) {
    die('Прямий доступ заборонено');
}

// Захист від XSS
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// CSRF токен
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Мета-дані сторінки
$page_title = "Скарги і пропозиції - Служба якості StormHosting UA";
$page_description = "Ми цінуємо ваші відгуки! Залишайте скарги, пропозиції та побажання для покращення наших послуг. Швидка обробка звернень та професійна підтримка.";
$page_keywords = "скарги пропозиції, зворотний зв'язок, якість послуг, служба підтримки, відгуки клієнтів";
$canonical_url = "https://sthost.pro/pages/info/complaints";

// Додаткові CSS та JS файли для цієї сторінки
//$additional_css = [
//    '/assets/css/pages/info-complaints.css?v=' . filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/pages/info-complaints.css')
//];
//
//$additional_js = [
//    '/assets/js/info-complaints.js'
//];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Захист від SQL ін'єкцій - підготовлені запити
function save_complaint($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO complaints (
            type, priority, name, email, phone, subject, message, 
            created_at, ip_address, user_agent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
    ");
    
    $stmt->execute([
        $data['type'],
        $data['priority'],
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['subject'],
        $data['message'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
    
    return $conn->lastInsertId();
}

// Обробка форми
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Захист від CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF токен невірний');
    }
    
    if (isset($_POST['submit_complaint'])) {
        // Валідація даних
        $complaint_data = [
            'type' => clean_input($_POST['complaint_type'] ?? ''),
            'priority' => clean_input($_POST['priority'] ?? 'normal'),
            'name' => clean_input($_POST['name'] ?? ''),
            'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'phone' => clean_input($_POST['phone'] ?? ''),
            'subject' => clean_input($_POST['subject'] ?? ''),
            'message' => clean_input($_POST['message'] ?? '')
        ];
        
        // Перевірка обов'язкових полів
        if (empty($complaint_data['type']) || empty($complaint_data['name']) || 
            !$complaint_data['email'] || empty($complaint_data['subject']) || 
            empty($complaint_data['message'])) {
            
            $error_message = "Будь ласка, заповніть всі обов'язкові поля.";
        } else {
            try {
                $complaint_id = save_complaint($conn, $complaint_data);
                
                // Відправка email сповіщення (опціонально)
                send_complaint_notification($complaint_data, $complaint_id);
                
                $success_message = "Дякуємо за ваше звернення! Ми розглянемо його протягом 24 годин. Номер звернення: #" . str_pad($complaint_id, 6, '0', STR_PAD_LEFT);
                
                // Очищаємо POST дані після успішної відправки
                $_POST = [];
                
            } catch (Exception $e) {
                error_log("Complaint submission error: " . $e->getMessage());
                $error_message = "Виникла помилка при обробці вашого звернення. Спробуйте ще раз.";
            }
        }
    }
}

function send_complaint_notification($data, $id) {
    // Тут має бути логіка відправки email
    // mail() або використання phpmailer
}

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/pages/info-complaints.css">

<main class="main-content">
    <!-- Hero Section -->
    <section class="complaints-hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="icon-message-circle"></i>
                        <span>Служба якості</span>
                    </div>
                    
                    <h1 class="hero-title">Скарги і пропозиції</h1>
                    <p class="hero-subtitle">
                        Ми цінуємо ваші відгуки та постійно працюємо над покращенням якості наших послуг. 
                        Ваша думка допомагає нам стати кращими!
                    </p>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number">24</div>
                            <div class="stat-label">години на розгляд</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">98%</div>
                            <div class="stat-label">задоволених клієнтів</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">365</div>
                            <div class="stat-label">днів на рік працюємо</div>
                        </div>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="feedback-illustration">
                        <div class="feedback-icons">
                            <div class="feedback-icon">
                                <i class="icon-heart"></i>
                            </div>
                            <div class="feedback-icon">
                                <i class="icon-thumbs-up"></i>
                            </div>
                            <div class="feedback-icon">
                                <i class="icon-star"></i>
                            </div>
                            <div class="feedback-icon">
                                <i class="icon-smile"></i>
                            </div>
                        </div>
                        <div class="feedback-message">
                            <div class="message-bubble">
                                <p>"Дякуємо за чудовий сервіс!"</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Complaint Types -->
    <section class="complaint-types">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Типи звернень</h2>
                <p class="section-subtitle">
                    Оберіть підходящий тип звернення для швидшої обробки вашого запиту
                </p>
            </div>
            
            <div class="types-grid">
                <div class="type-card" data-type="complaint">
                    <div class="type-icon complaint-icon">
                        <i class="icon-alert-triangle"></i>
                    </div>
                    <h3 class="type-title">Скарга</h3>
                    <p class="type-description">
                        Проблеми з обслуговуванням, технічні неполадки або незадоволення якістю послуг
                    </p>
                    <div class="type-time">
                        <i class="icon-clock"></i>
                        <span>Розгляд: до 24 годин</span>
                    </div>
                </div>
                
                <div class="type-card" data-type="suggestion">
                    <div class="type-icon suggestion-icon">
                        <i class="icon-lightbulb"></i>
                    </div>
                    <h3 class="type-title">Пропозиція</h3>
                    <p class="type-description">
                        Ідеї для покращення сервісу, нові функції або рекомендації
                    </p>
                    <div class="type-time">
                        <i class="icon-clock"></i>
                        <span>Розгляд: до 7 днів</span>
                    </div>
                </div>
                
                <div class="type-card" data-type="feedback">
                    <div class="type-icon feedback-icon">
                        <i class="icon-message-square"></i>
                    </div>
                    <h3 class="type-title">Відгук</h3>
                    <p class="type-description">
                        Позитивні відгуки, подяки або загальні коментарі про роботу компанії
                    </p>
                    <div class="type-time">
                        <i class="icon-clock"></i>
                        <span>Відповідь: до 48 годин</span>
                    </div>
                </div>
                
                <div class="type-card" data-type="question">
                    <div class="type-icon question-icon">
                        <i class="icon-help-circle"></i>
                    </div>
                    <h3 class="type-title">Питання</h3>
                    <p class="type-description">
                        Загальні питання про послуги, тарифи або умови обслуговування
                    </p>
                    <div class="type-time">
                        <i class="icon-clock"></i>
                        <span>Відповідь: до 4 годин</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Form -->
    <section class="complaint-form-section">
        <div class="container">
            <div class="form-wrapper">
                <div class="form-header">
                    <h2 class="form-title">Залишити звернення</h2>
                    <p class="form-subtitle">
                        Заповніть форму нижче, і ми обов'язково розглянемо ваше звернення
                    </p>
                </div>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="icon-check-circle"></i>
                        <span><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="icon-alert-circle"></i>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="complaint-form" id="complaintForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="submit_complaint" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="complaint_type" class="form-label">
                                Тип звернення <span class="required">*</span>
                            </label>
                            <select name="complaint_type" id="complaint_type" class="form-select" required>
                                <option value="">Оберіть тип звернення</option>
                                <option value="complaint" <?php echo (($_POST['complaint_type'] ?? '') === 'complaint') ? 'selected' : ''; ?>>
                                    Скарга
                                </option>
                                <option value="suggestion" <?php echo (($_POST['complaint_type'] ?? '') === 'suggestion') ? 'selected' : ''; ?>>
                                    Пропозиція
                                </option>
                                <option value="feedback" <?php echo (($_POST['complaint_type'] ?? '') === 'feedback') ? 'selected' : ''; ?>>
                                    Відгук
                                </option>
                                <option value="question" <?php echo (($_POST['complaint_type'] ?? '') === 'question') ? 'selected' : ''; ?>>
                                    Питання
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority" class="form-label">Пріоритет</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="low" <?php echo (($_POST['priority'] ?? '') === 'low') ? 'selected' : ''; ?>>
                                    Низький
                                </option>
                                <option value="normal" <?php echo (($_POST['priority'] ?? 'normal') === 'normal') ? 'selected' : ''; ?>>
                                    Звичайний
                                </option>
                                <option value="high" <?php echo (($_POST['priority'] ?? '') === 'high') ? 'selected' : ''; ?>>
                                    Високий
                                </option>
                                <option value="urgent" <?php echo (($_POST['priority'] ?? '') === 'urgent') ? 'selected' : ''; ?>>
                                    Терміновий
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                Ваше ім'я <span class="required">*</span>
                            </label>
                            <input type="text" name="name" id="name" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                   placeholder="Введіть ваше повне ім'я" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                Email <span class="required">*</span>
                            </label>
                            <input type="email" name="email" id="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   placeholder="your@email.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Телефон (опціонально)</label>
                        <input type="tel" name="phone" id="phone" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                               placeholder="+38 (XXX) XXX-XX-XX">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">
                            Тема звернення <span class="required">*</span>
                        </label>
                        <input type="text" name="subject" id="subject" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                               placeholder="Коротко опишіть суть звернення" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">
                            Детальний опис <span class="required">*</span>
                        </label>
                        <textarea name="message" id="message" class="form-textarea" rows="6" 
                                  placeholder="Детально опишіть ваше звернення, проблему або пропозицію..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        <div class="form-hint">
                            Мінімум 20 символів. Чим детальніше ви опишете ситуацію, тим швидше ми зможемо вам допомогти.
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="icon-send"></i>
                            Відправити звернення
                        </button>
                        <button type="reset" class="btn btn-outline btn-large">
                            <i class="icon-refresh-cw"></i>
                            Очистити форму
                        </button>
                    </div>
                    
                    <div class="form-note">
                        Натиснувши кнопку "Відправити звернення", ви погоджуєтеся з 
                        <a href="/pages/info/legal" target="_blank">політикою конфіденційності</a> 
                        та даєте згоду на обробку персональних даних.
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Process Steps -->
    <section class="process-steps">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Як ми обробляємо звернення</h2>
                <p class="section-subtitle">
                    Прозорий процес розгляду для максимальної ефективності
                </p>
            </div>
            
            <div class="steps-timeline">
                <div class="step-item">
                    <div class="step-marker">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="icon-mail"></i>
                        </div>
                    </div>
                    <div class="step-content">
                        <h3 class="step-title">Отримання звернення</h3>
                        <p class="step-description">
                            Ваше звернення автоматично реєструється в нашій системі та отримує унікальний номер для відстеження.
                        </p>
                        <div class="step-time">
                            <i class="icon-clock"></i>
                            <span>Миттєво</span>
                        </div>
                    </div>
                </div>
                
                <div class="step-item">
                    <div class="step-marker">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="icon-user-check"></i>
                        </div>
                    </div>
                    <div class="step-content">
                        <h3 class="step-title">Призначення відповідального</h3>
                        <p class="step-description">
                            Звернення автоматично направляється відповідальному спеціалісту згідно з типом та пріоритетом.
                        </p>
                        <div class="step-time">
                            <i class="icon-clock"></i>
                            <span>До 1 години</span>
                        </div>
                    </div>
                </div>
                
                <div class="step-item">
                    <div class="step-marker">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="icon-search"></i>
                        </div>
                    </div>
                    <div class="step-content">
                        <h3 class="step-title">Дослідження та аналіз</h3>
                        <p class="step-description">
                            Наш спеціаліст детально вивчає ваше звернення, проводить необхідні перевірки та готує рішення.
                        </p>
                        <div class="step-time">
                            <i class="icon-clock"></i>
                            <span>До 24 годин</span>
                        </div>
                    </div>
                </div>
                
                <div class="step-item">
                    <div class="step-marker">
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <i class="icon-message-circle"></i>
                        </div>
                    </div>
                    <div class="step-content">
                        <h3 class="step-title">Відповідь та рішення</h3>
                        <p class="step-description">
                            Ми надсилаємо детальну відповідь на ваш email та, за необхідності, вживаємо заходи для вирішення проблеми.
                        </p>
                        <div class="step-time">
                            <i class="icon-clock"></i>
                            <span>Згідно з SLA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="contact-info">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-methods">
                    <h2 class="contact-title">Інші способи зв'язку</h2>
                    <p class="contact-description">
                        Якщо у вас термінова проблема або ви хочете поговорити особисто, 
                        скористайтеся одним з альтернативних способів зв'язку.
                    </p>
                    
                    <div class="contact-options">
                        <div class="contact-option">
                            <div class="contact-icon">
                                <i class="icon-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Телефонна підтримка</h4>
                                <p>+38 (044) 123-45-67</p>
                                <span class="contact-hours">Пн-Пт: 9:00-18:00</span>
                            </div>
                        </div>
                        
                        <div class="contact-option">
                            <div class="contact-icon">
                                <i class="icon-message-square"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Онлайн чат</h4>
                                <p>Миттєва підтримка</p>
                                <span class="contact-hours">24/7 автоматично</span>
                            </div>
                        </div>
                        
                        <div class="contact-option">
                            <div class="contact-icon">
                                <i class="icon-mail"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Email підтримка</h4>
                                <p>support@sthost.pro</p>
                                <span class="contact-hours">Відповідь до 4 годин</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="quality-info">
                    <div class="quality-card">
                        <div class="quality-header">
                            <div class="quality-icon">
                                <i class="icon-award"></i>
                            </div>
                            <h3>Гарантія якості</h3>
                        </div>
                        
                        <div class="quality-stats">
                            <div class="quality-stat">
                                <div class="stat-value">98.5%</div>
                                <div class="stat-label">Задоволених клієнтів</div>
                            </div>
                            <div class="quality-stat">
                                <div class="stat-value">4.8/5</div>
                                <div class="stat-label">Рейтинг підтримки</div>
                            </div>
                            <div class="quality-stat">
                                <div class="stat-value">2.5 год</div>
                                <div class="stat-label">Середній час відповіді</div>
                            </div>
                        </div>
                        
                        <div class="quality-commitment">
                            <h4>Наші зобов'язання:</h4>
                            <ul>
                                <li><i class="icon-check"></i> Розгляд кожного звернення</li>
                                <li><i class="icon-check"></i> Прозорий процес обробки</li>
                                <li><i class="icon-check"></i> Регулярні оновлення статусу</li>
                                <li><i class="icon-check"></i> Конфіденційність даних</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<script src="/assets/js/info-complaints.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>