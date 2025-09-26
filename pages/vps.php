<?php
/**
 * VPS главная страница - выбор планов и заказ
 * Файл: /pages/vps.php
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Конфигурация страницы
$page = 'vps';
$page_title = 'VPS сервери - StormHosting UA | Віртуальні приватні сервери';
$meta_description = 'Надійні VPS сервери від StormHosting UA. Миттєва активація, SSD диски, повний root доступ. Від 299 грн/місяць.';
$meta_keywords = 'vps, впс, віртуальний сервер, приватний сервер, ssd vps, linux vps, windows vps';

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/vps.css'
];

$additional_js = [
    '/assets/js/pages/vps.js'
];

// Підключення VPS Manager
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/classes/VPSManager.php';

try {
    $vpsManager = new VPSManager();
    
    // Получаем планы VPS
    $plans_result = $vpsManager->getVPSPlans();
    $vps_plans = $plans_result['success'] ? $plans_result['plans'] : [];
    
    // Получаем операционные системы
    $os_result = $vpsManager->getOSTemplates();
    $os_templates = $os_result['success'] ? $os_result['templates'] : [];
    
} catch (Exception $e) {
    error_log("VPS page error: " . $e->getMessage());
    $vps_plans = [];
    $os_templates = [];
}

// Группируем ОС по типам
$os_by_type = [];
foreach ($os_templates as $os) {
    $os_by_type[$os['type']][] = $os;
}

// Подключение header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- Hero секция -->
<section class="hero-section vps-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Потужні VPS сервери<br>
                        <span class="text-primary">з повним контролем</span>
                    </h1>
                    <p class="hero-subtitle">
                        Віртуальні приватні сервери з гарантованими ресурсами, SSD дисками та root доступом. 
                        Миттєва активація та підтримка 24/7.
                    </p>
                    <div class="hero-features">
                        <div class="feature-item">
                            <i class="fas fa-rocket"></i>
                            <span>Миттєва активація</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>99.9% Uptime</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-headset"></i>
                            <span>Підтримка 24/7</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image">
                    <img src="/assets/images/vps-server-illustration.svg" alt="VPS Server" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Плани VPS -->
<section class="vps-plans-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header text-center mb-5">
                    <h2>Оберіть ваш VPS план</h2>
                    <p class="section-subtitle">Всі плани включають SSD диски, безлімітну підтримку та повний root доступ</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($vps_plans as $index => $plan): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="vps-plan-card <?= $plan['is_popular'] ? 'popular' : '' ?>">
                        <?php if ($plan['is_popular']): ?>
                            <div class="popular-badge">Популярний</div>
                        <?php endif; ?>
                        
                        <div class="plan-header">
                            <h3 class="plan-name"><?= htmlspecialchars($plan['name_ua']) ?></h3>
                            <div class="plan-price">
                                <span class="price"><?= number_format($plan['price_monthly'], 0) ?></span>
                                <span class="currency">грн/міс</span>
                            </div>
                            <div class="plan-price-yearly">
                                <?= number_format($plan['price_yearly'], 0) ?> грн/рік 
                                <small class="text-success">(знижка <?= round((1 - $plan['price_yearly'] / ($plan['price_monthly'] * 12)) * 100) ?>%)</small>
                            </div>
                        </div>
                        
                        <div class="plan-specs">
                            <div class="spec-item">
                                <i class="fas fa-microchip"></i>
                                <span><?= $plan['cpu_cores'] ?> CPU ядр<?= $plan['cpu_cores'] > 1 ? 'а' : 'о' ?></span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-memory"></i>
                                <span><?= round($plan['ram_mb'] / 1024, 1) ?> GB RAM</span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-hdd"></i>
                                <span><?= $plan['disk_gb'] ?> GB SSD</span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-network-wired"></i>
                                <span><?= $plan['bandwidth_gb'] ?> GB трафік</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($plan['features_ua'])): ?>
                            <div class="plan-features">
                                <?php 
                                $features = json_decode($plan['features_ua'], true);
                                if (is_array($features)):
                                ?>
                                    <?php foreach ($features as $feature): ?>
                                        <div class="feature-item">
                                            <i class="fas fa-check text-success"></i>
                                            <span><?= htmlspecialchars($feature) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="plan-footer">
                            <button class="btn btn-primary btn-block order-vps-btn" 
                                    data-plan-id="<?= $plan['id'] ?>"
                                    data-plan-name="<?= htmlspecialchars($plan['name_ua']) ?>"
                                    data-plan-price="<?= $plan['price_monthly'] ?>">
                                <i class="fas fa-server me-2"></i>
                                Замовити VPS
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Особливості VPS -->
<section class="vps-features-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header text-center mb-5">
                    <h2>Чому обирають наші VPS?</h2>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4>Швидкі SSD диски</h4>
                    <p>Всі VPS використовують швидкі SSD диски для максимальної продуктивності ваших додатків.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h4>Root доступ</h4>
                    <p>Повний контроль над сервером з root правами для встановлення будь-якого ПЗ.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <h4>VNC консоль</h4>
                    <p>Прямий доступ до консолі сервера через веб-браузер, навіть якщо SSH недоступний.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-backup"></i>
                    </div>
                    <h4>Автоматичні бекапи</h4>
                    <p>Регулярне резервне копіювання ваших даних для максимальної безпеки.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Моніторинг ресурсів</h4>
                    <p>Детальна статистика використання CPU, RAM, диска та мережі в реальному часі.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </div>
                    <h4>Легке масштабування</h4>
                    <p>Збільшуйте або зменшуйте ресурси вашого VPS залежно від потреб.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Операционные системы -->
<section class="os-templates-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header text-center mb-5">
                    <h2>Доступні операційні системи</h2>
                    <p class="section-subtitle">Оберіть зручну для вас ОС з попередньо налаштованими шаблонами</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($os_by_type as $type => $os_list): ?>
                <div class="col-lg-6 mb-4">
                    <div class="os-type-card">
                        <h4 class="os-type-title">
                            <?php
                            $type_names = [
                                'linux' => 'Linux дистрибутиви',
                                'windows' => 'Windows системи',
                                'bsd' => 'BSD системи',
                                'other' => 'Інші системи'
                            ];
                            echo $type_names[$type] ?? ucfirst($type);
                            ?>
                        </h4>
                        <div class="os-list">
                            <?php foreach ($os_list as $os): ?>
                                <div class="os-item">
                                    <?php if ($os['icon']): ?>
                                        <img src="<?= htmlspecialchars($os['icon']) ?>" alt="<?= htmlspecialchars($os['display_name']) ?>" class="os-icon">
                                    <?php else: ?>
                                        <div class="os-icon-placeholder">
                                            <i class="fab fa-<?= $type === 'linux' ? 'linux' : ($type === 'windows' ? 'windows' : 'server') ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="os-info">
                                        <div class="os-name"><?= htmlspecialchars($os['display_name']) ?></div>
                                        <?php if ($os['version']): ?>
                                            <div class="os-version"><?= htmlspecialchars($os['version']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ секция -->
<section class="faq-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header text-center mb-5">
                    <h2>Часті питання про VPS</h2>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="vpsAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                Що таке VPS і чим він відрізняється від звичайного хостингу?
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#vpsAccordion">
                            <div class="accordion-body">
                                VPS (Virtual Private Server) - це віртуальний приватний сервер з гарантованими ресурсами, 
                                повним root доступом та можливістю встановлення будь-якого ПЗ. На відміну від звичайного хостингу, 
                                ви отримуєте повний контроль над сервером.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                Як швидко активується VPS після оплати?
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#vpsAccordion">
                            <div class="accordion-body">
                                VPS активується автоматично протягом 2-5 хвилин після підтвердження оплати. 
                                Ви отримаете email з усіма даними для підключення.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                Чи можна змінити операційну систему після створення VPS?
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#vpsAccordion">
                            <div class="accordion-body">
                                Так, ви можете переустановити VPS з будь-якою доступною операційною системою через панель управління. 
                                Перед переустановкою рекомендуємо створити резервну копію.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                Чи можна збільшити ресурси VPS?
                            </button>
                        </h2>
                        <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#vpsAccordion">
                            <div class="accordion-body">
                                Так, ви можете в будь-який час збільшити або зменшити ресурси VPS (CPU, RAM, диск) 
                                через панель управління. Зміни застосовуються миттєво.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq5">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                                Чи є технічна підтримка 24/7?
                            </button>
                        </h2>
                        <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#vpsAccordion">
                            <div class="accordion-body">
                                Так, наша технічна підтримка працює 24/7 через тикет систему, чат та телефон. 
                                Ми допомагаємо з налаштуванням сервера, встановленням ПЗ та вирішенням технічних питань.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Модальное окно заказа VPS -->
<div class="modal fade" id="orderVPSModal" tabindex="-1" aria-labelledby="orderVPSModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderVPSModalLabel">Замовлення VPS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
            </div>
            <div class="modal-body">
                <form id="orderVPSForm">
                    <input type="hidden" id="selectedPlanId" name="plan_id">
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="selected-plan-info">
                                <h6>Обраний план: <span id="selectedPlanName" class="text-primary"></span></h6>
                                <p class="mb-0">Вартість: <span id="selectedPlanPrice" class="fw-bold text-success"></span> грн/міс</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vpsHostname" class="form-label">Ім'я хоста</label>
                            <input type="text" class="form-control" id="vpsHostname" name="hostname" 
                                   placeholder="myvps" pattern="[a-z0-9-]+" maxlength="32" required>
                            <div class="form-text">Тільки малі літери, цифри та дефіс. Максимум 32 символи.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="vpsPeriod" class="form-label">Період оплати</label>
                            <select class="form-select" id="vpsPeriod" name="period" required>
                                <option value="monthly">Щомісячно</option>
                                <option value="quarterly">Щоквартально (-5%)</option>
                                <option value="annually">Щорічно (-10%)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="vpsOS" class="form-label">Операційна система</label>
                            <select class="form-select" id="vpsOS" name="os_template" required>
                                <option value="">Оберіть ОС</option>
                                <?php foreach ($os_by_type as $type => $os_list): ?>
                                    <optgroup label="<?= $type_names[$type] ?? ucfirst($type) ?>">
                                        <?php foreach ($os_list as $os): ?>
                                            <option value="<?= htmlspecialchars($os['name']) ?>">
                                                <?= htmlspecialchars($os['display_name']) ?>
                                                <?= $os['version'] ? ' ' . $os['version'] : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="vpsRootPassword" class="form-label">Root пароль (необов'язково)</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="vpsRootPassword" name="root_password" 
                                       placeholder="Залиште пустим для автогенерації">
                                <button class="btn btn-outline-secondary" type="button" id="generatePassword">
                                    <i class="fas fa-key"></i> Генерувати
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Мінімум 8 символів. Якщо не вказано, буде згенеровано автоматично.</div>
                        </div>
                    </div>
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Для замовлення VPS необхідно <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">увійти в акаунт</a> 
                                або <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">зареєструватися</a>.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="order-summary p-3 bg-light rounded">
                                <h6>Підсумок замовлення:</h6>
                                <div class="d-flex justify-content-between">
                                    <span>План VPS:</span>
                                    <span id="summaryPlanName"></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Період:</span>
                                    <span id="summaryPeriod">Щомісячно</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Вартість установки:</span>
                                    <span class="text-success">Безкоштовно</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>До сплати:</span>
                                    <span id="totalPrice" class="text-success">0 грн</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button type="button" class="btn btn-primary" id="submitOrderBtn">
                        <i class="fas fa-shopping-cart me-2"></i>Замовити VPS
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary" onclick="showLoginRequired()">
                        <i class="fas fa-user me-2"></i>Увійти для замовлення
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// VPS ordering functionality
document.addEventListener('DOMContentLoaded', function() {
    // План selection
    const orderButtons = document.querySelectorAll('.order-vps-btn');
    const modal = new bootstrap.Modal(document.getElementById('orderVPSModal'));
    
    orderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const planId = this.dataset.planId;
            const planName = this.dataset.planName;
            const planPrice = this.dataset.planPrice;
            
            document.getElementById('selectedPlanId').value = planId;
            document.getElementById('selectedPlanName').textContent = planName;
            document.getElementById('selectedPlanPrice').textContent = planPrice;
            document.getElementById('summaryPlanName').textContent = planName;
            
            updateTotalPrice();
            modal.show();
        });
    });
    
    // Period change handler
    document.getElementById('vpsPeriod').addEventListener('change', updateTotalPrice);
    
    // Password generator
    document.getElementById('generatePassword').addEventListener('click', function() {
        const password = generateRandomPassword(12);
        document.getElementById('vpsRootPassword').value = password;
    });
    
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordField = document.getElementById('vpsRootPassword');
        const icon = this.querySelector('i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    
    // Order submission
    document.getElementById('submitOrderBtn')?.addEventListener('click', function() {
        const form = document.getElementById('orderVPSForm');
        const formData = new FormData(form);
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Обробка...';
        
        fetch('/api/vps/order', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('VPS успішно замовлено! Перенаправлення до панелі управління...');
                setTimeout(() => {
                    window.location.href = '/client/vps';
                }, 2000);
            } else {
                showError(data.error || 'Помилка при замовленні VPS');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Замовити VPS';
            }
        })
        .catch(error => {
            showError('Помилка мережі');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Замовити VPS';
        });
    });
});

function updateTotalPrice() {
    const planPrice = parseFloat(document.getElementById('selectedPlanPrice').textContent || 0);
    const period = document.getElementById('vpsPeriod').value;
    
    let multiplier = 1;
    let periodText = 'Щомісячно';
    
    switch (period) {
        case 'quarterly':
            multiplier = 3 * 0.95; // 5% скидка
            periodText = 'Щоквартально (-5%)';
            break;
        case 'annually':
            multiplier = 12 * 0.9; // 10% скидка
            periodText = 'Щорічно (-10%)';
            break;
    }
    
    const totalPrice = Math.round(planPrice * multiplier);
    
    document.getElementById('summaryPeriod').textContent = periodText;
    document.getElementById('totalPrice').textContent = totalPrice + ' грн';
}

function generateRandomPassword(length) {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < length; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return password;
}

function showLoginRequired() {
    showError('Для замовлення VPS необхідно увійти в акаунт');
}

function showSuccess(message) {
    // Implement your success notification
    alert(message);
}

function showError(message) {
    // Implement your error notification
    alert(message);
}
</script>

<?php
// Подключение footer
include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
?>