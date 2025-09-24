<?php
// Защита от прямого доступа
define('SECURE_ACCESS', true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// остальные переменные
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Настройки страницы
$page_title = 'Перенесення домену - StormHosting UA';
$meta_description = 'Перенесіть ваш домен до StormHosting UA безкоштовно. Простий процес трансферу доменів з будь-якого реєстратора. Продовження на 1 рік включено.';
$meta_keywords = 'трансфер доменів, перенесення доменів, домен transfer, зміна реєстратора';
$page_css = 'domains';
$page_js = 'domains';
$need_api = true;

// Получаем поддерживаемые зоны для трансфера
try {
    if (defined('DB_AVAILABLE') && DB_AVAILABLE) {
        $transferable_zones = db_fetch_all(
            "SELECT zone, price_transfer, price_renewal 
             FROM domain_zones 
             WHERE is_active = 1 AND price_transfer > 0
             ORDER BY zone LIKE '%.ua' DESC, price_transfer ASC"
        );
    } else {
        throw new Exception('Database not available');
    }
} catch (Exception $e) {
    $transferable_zones = [
        ['zone' => '.ua', 'price_transfer' => 180, 'price_renewal' => 200],
        ['zone' => '.com.ua', 'price_transfer' => 130, 'price_renewal' => 150],
        ['zone' => '.kiev.ua', 'price_transfer' => 160, 'price_renewal' => 180],
        ['zone' => '.net.ua', 'price_transfer' => 160, 'price_renewal' => 180],
        ['zone' => '.org.ua', 'price_transfer' => 160, 'price_renewal' => 180],
        ['zone' => '.com', 'price_transfer' => 300, 'price_renewal' => 350],
        ['zone' => '.net', 'price_transfer' => 400, 'price_renewal' => 450],
        ['zone' => '.org', 'price_transfer' => 350, 'price_renewal' => 400]
    ];
}

// Обработка формы трансфера
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'start_transfer') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Помилка безпеки. Оновіть сторінку та спробуйте знову.', 'error');
    } else {
        $domain = sanitizeInput($_POST['domain'] ?? '');
        $auth_code = sanitizeInput($_POST['auth_code'] ?? '');
        $contact_email = sanitizeInput($_POST['contact_email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $notes = sanitizeInput($_POST['notes'] ?? '');
        
        if (empty($domain) || empty($auth_code) || empty($contact_email)) {
            setFlashMessage('Заповніть всі обов\'язкові поля', 'error');
        } elseif (!validateEmail($contact_email)) {
            setFlashMessage('Невірний формат email', 'error');
        } else {
            // Здесь должна быть логика инициации трансфера
            // Добавляем в БД заявку на трансфер
            try {
                if (function_exists('db_execute')) {
                    db_execute(
                        "INSERT INTO contact_requests (name, email, phone, subject, message, form_type, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [
                            'Domain Transfer Request',
                            $contact_email,
                            $phone,
                            'Трансфер домену: ' . $domain,
                            "Домен: {$domain}\nКод авторизації: {$auth_code}\nПримітки: {$notes}",
                            'transfer',
                            getUserIP()
                        ]
                    );
                }
                
                // Логируем активность
                if (function_exists('logActivity')) {
                    logActivity('domain_transfer_request', "Domain: {$domain}, Email: {$contact_email}");
                }
                
                setFlashMessage("Заявка на трансфер домену {$domain} подана успішно! Ми зв'яжемося з вами протягом 24 годин.", 'success');
                
                // Очищаем форму после успешной отправки
                unset($_POST);
                
            } catch (Exception $e) {
                setFlashMessage('Помилка при обробці заявки. Спробуйте пізніше.', 'error');
            }
        }
    }
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Калькулятор хостингу - StormHosting UA</title>
    <meta name="description" content="Розрахуйте вартість хостингу під ваші потреби. Віртуальний хостинг, VPS, виділені сервери. Миттєвий розрахунок ціни.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Calculator CSS -->
    <link rel="stylesheet" href="/assets/css/pages/domains-transfer.css">
</head>

<!-- Transfer Hero -->
<section class="transfer-hero py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Перенесення домену</h1>
                <p class="lead mb-4">Перенесіть ваш домен до StormHosting UA та отримайте кращий сервіс, захист та підтримку 24/7.</p>
                
                <div class="transfer-benefits">
                    <div class="benefit-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Безкоштовне перенесення</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Продовження на 1 рік включено</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Без втрати налаштувань DNS</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Захист від несанкціонованого трансферу</span>
                    </div>
                </div>
                
                <a href="#transfer-form" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-right-circle"></i>
                    Почати трансфер
                </a>
            </div>
            
            <div class="col-lg-6">
                <div class="transfer-visual">
                    <div class="transfer-diagram">
                        <div class="old-registrar">
                            <div class="registrar-box">
                                <i class="bi bi-building"></i>
                                <span>Старий реєстратор</span>
                            </div>
                        </div>
                        
                        <div class="transfer-arrow">
                            <i class="bi bi-arrow-right"></i>
                            <span>Безкоштовний трансфер</span>
                        </div>
                        
                        <div class="new-registrar">
                            <div class="registrar-box stormhosting">
                                <i class="bi bi-shield-check"></i>
                                <span>StormHosting UA</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="domain-icon">
                        <i class="bi bi-globe"></i>
                        <span>your-domain.com</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Process -->
<section class="transfer-process py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Як проходить трансфер</h2>
                <p class="section-subtitle">Простий процес з 4 кроків</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="bi bi-key"></i>
                    </div>
                    <h4>Отримайте код авторизації</h4>
                    <p>Зверніться до поточного реєстратора для отримання EPP/Auth коду домену</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <h4>Подайте заявку</h4>
                    <p>Заповніть форму трансферу з кодом авторизації та контактними даними</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="bi bi-envelope-check"></i>
                    </div>
                    <h4>Підтвердьте трансфер</h4>
                    <p>Підтвердіть трансфер через email, який прийде на адресу власника домену</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h4>Готово!</h4>
                    <p>Домен буде перенесено протягом 5-7 днів з автоматичним продовженням на рік</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Form -->
<section id="transfer-form" class="transfer-form-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="transfer-form-card">
                    <div class="form-header text-center">
                        <h2 class="fw-bold">Форма трансферу домену</h2>
                        <p>Заповніть форму для початку процесу трансферу</p>
                    </div>
                    
                    <form method="POST" class="transfer-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="start_transfer">
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="domain" class="form-label">Домен для трансферу *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-globe"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="domain" 
                                           name="domain"
                                           placeholder="example.com"
                                           pattern="[a-zA-Z0-9.-]+"
                                           value="<?php echo escapeOutput($_POST['domain'] ?? ''); ?>"
                                           required>
                                </div>
                                <div class="form-text">Введіть повне ім'я домену включно з зоною</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="auth_code" class="form-label">Код авторизації (EPP/Auth код) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="auth_code" 
                                           name="auth_code"
                                           placeholder="Auth-Code123"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Код авторизації можна отримати у поточного реєстратора домену">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="contact_email" class="form-label">Email для зв'язку *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control" 
                                           id="contact_email" 
                                           name="contact_email"
                                           placeholder="your@email.com"
                                           value="<?php echo escapeOutput($_POST['contact_email'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Телефон</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="phone" 
                                           name="phone"
                                           placeholder="+380 XX XXX XX XX"
                                           value="<?php echo escapeOutput($_POST['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label for="notes" class="form-label">Додаткові примітки</label>
                                <textarea class="form-control" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Вкажіть будь-яку додаткову інформацію або особливі вимоги..."><?php echo escapeOutput($_POST['notes'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" required>
                                    <label class="form-check-label" for="agree_terms">
                                        Я погоджуюсь з <a href="/info/rules" target="_blank">умовами трансферу</a> та підтверджую, що є власником домену або маю повноваження на його трансфер *
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="auto_renew">
                                    <label class="form-check-label" for="auto_renew">
                                        Увімкнути автоматичне продовження домену
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-arrow-right-circle"></i>
                                    Подати заявку на трансфер
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Pricing -->
<section class="transfer-pricing py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Ціни на трансфер доменів</h2>
                <p class="section-subtitle">Прозорі ціни без прихованих платежів</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach (array_chunk($transferable_zones, ceil(count($transferable_zones) / 2)) as $chunk): ?>
            <div class="col-lg-6">
                <div class="pricing-table">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Доменна зона</th>
                                    <th>Трансфер</th>
                                    <th>Продовження</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($chunk as $zone): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo escapeOutput($zone['zone']); ?></strong>
                                        <?php if (strpos($zone['zone'], '.ua') !== false): ?>
                                        <span class="badge bg-primary ms-2">UA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="price-highlight"><?php echo formatPrice($zone['price_transfer']); ?></span>
                                        <small class="text-muted d-block">+ 1 рік продовження</small>
                                    </td>
                                    <td><?php echo formatPrice($zone['price_renewal']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <div class="pricing-note">
                    <i class="bi bi-info-circle"></i>
                    <strong>Важливо:</strong> Ціна трансферу включає продовження домену на 1 рік. 
                    DNS налаштування зберігаються без змін.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="transfer-faq py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Часто задавані питання</h2>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="transferFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Скільки часу займає трансфер домену?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Трансфер домену зазвичай займає від 5 до 7 днів. Це залежить від доменної зони та швидкості підтвердження з боку поточного реєстратора. Українські домени (.ua, .com.ua) можуть трансферитися швидше - протягом 2-3 днів.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Що таке код авторизації (EPP код)?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                EPP код (також Auth код) - це унікальний код, який підтверджує ваші права на домен. Його можна отримати в панелі управління поточного реєстратора або звернувшись до їхньої підтримки. Код зазвичай складається з 8-16 символів.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Чи втрачу я налаштування DNS при трансфері?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Ні, всі DNS налаштування зберігаються під час трансферу. Ваш сайт та email продовжать працювати без перебоїв. Після трансферу ви зможете керувати DNS через нашу панель управління.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Чи можу я скасувати трансфер?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Так, ви можете скасувати трансфер до його завершення. Також поточний реєстратор може відхилити трансфер протягом 5 днів. У такому випадку кошти будуть повернені на ваш рахунок.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Які домени можна трансферити?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Можна трансферити більшість доменів, включаючи .com, .net, .org, .ua, .com.ua та інші. Домен повинен бути зареєстрований більше 60 днів тому та не мати блокування на трансфер. Деякі домени (.gov, .edu) не можна трансферити.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Що робити, якщо я не можу отримати код авторизації?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#transferFAQ">
                            <div class="accordion-body">
                                Зверніться до поточного реєстратора домену. Вони зобов'язані надати код авторизації власнику домену. Якщо у вас виникли проблеми, наша підтримка допоможе вам з процедурою отримання коду.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Benefits -->
<section class="transfer-benefits-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-4">Чому варто перенести домен до нас?</h2>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <h5>Надійність та безпека</h5>
                                <p>Захист від несанкціонованого трансферу, блокування та зламу</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <i class="bi bi-headset"></i>
                            <div>
                                <h5>Підтримка 24/7</h5>
                                <p>Технічна підтримка українською мовою цілодобово</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <i class="bi bi-gear"></i>
                            <div>
                                <h5>Зручне керування</h5>
                                <p>Інтуїтивна панель управління з усіма необхідними функціями</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <i class="bi bi-cash-coin"></i>
                            <div>
                                <h5>Конкурентні ціни</h5>
                                <p>Найкращі ціни на ринку України для продовження доменів</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 text-center">
                <div class="cta-box">
                    <h3>Готові перенести домен?</h3>
                    <p>Почніть зараз та отримайте безкоштовний трансфер</p>
                    <a href="#transfer-form" class="btn btn-light btn-lg">
                        <i class="bi bi-arrow-up-circle"></i>
                        Заповнити форму
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Support -->
<section class="transfer-support py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-4">Потрібна допомога з трансфером?</h2>
                <p class="lead mb-4">Наша команда експертів готова допомогти вам з будь-якими питаннями щодо трансферу доменів.</p>
                
                <div class="contact-options">
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <div class="contact-method">
                                <i class="bi bi-chat-dots text-primary"></i>
                                <h5>Онлайн чат</h5>
                                <p>Миттєва відповідь від наших спеціалістів</p>
                                <button class="btn btn-outline-primary" onclick="openChat()">Почати чат</button>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="contact-method">
                                <i class="bi bi-telephone text-primary"></i>
                                <h5>Телефон</h5>
                                <p>Зателефонуйте нам для консультації</p>
                                <a href="tel:+380XXXXXXXXX" class="btn btn-outline-primary">+380 XX XXX XX XX</a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="contact-method">
                                <i class="bi bi-envelope text-primary"></i>
                                <h5>Email</h5>
                                <p>Надішліть нам детальний запит</p>
                                <a href="mailto:domains@sthost.pro" class="btn btn-outline-primary">domains@sthost.pro</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Константы для скрипта
window.transferConfig = {
    csrfToken: '<?php echo generateCSRFToken(); ?>',
    supportedZones: <?php echo json_encode(array_column($transferable_zones, 'zone')); ?>,
    translations: {
        invalidDomain: 'Невірний формат домену',
        unsupportedZone: 'Ця доменна зона не підтримується для трансферу',
        authCodeRequired: 'Код авторизації обов\'язковий',
        emailRequired: 'Email обов\'язковий для зв\'язку'
    }
};

function openChat() {
    // Здесь будет код для открытия чата
    alert('Онлайн чат буде доступний незабаром');
}

// Валидация формы трансфера
document.addEventListener('DOMContentLoaded', function() {
    const transferForm = document.querySelector('.transfer-form');
    const domainInput = document.getElementById('domain');
    
    if (transferForm && domainInput) {
        domainInput.addEventListener('input', function(e) {
            const domain = e.target.value.toLowerCase();
            const isValid = /^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(domain);
            
            e.target.classList.toggle('is-invalid', !isValid && domain.length > 0);
            e.target.classList.toggle('is-valid', isValid);
        });
        
        transferForm.addEventListener('submit', function(e) {
            const domain = domainInput.value;
            const authCode = document.getElementById('auth_code').value;
            const email = document.getElementById('contact_email').value;
            const terms = document.getElementById('agree_terms').checked;
            
            if (!domain || !authCode || !email || !terms) {
                e.preventDefault();
                alert('Заповніть всі обов\'язкові поля та погодьтеся з умовами');
                return false;
            }
            
            // Показываем индикатор загрузки
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Обробляємо заявку...';
            submitBtn.disabled = true;
            
            // Если форма не прошла валидацию сервера, восстанавливаем кнопку
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }, 5000);
        });
    }
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
});
</script>
<script src="/assets/js/domains-transfer.js"></script>

 <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>