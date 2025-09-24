<?php
// Защита от прямого доступа
define('SECURE_ACCESS', true);

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// остальные переменные
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Получаем текущий язык
$current_lang = $_SESSION['lang'] ?? DEFAULT_LANG;

// Загрузка языкового файла
$lang_file = "../../lang/{$current_lang}.php";
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Настройки страницы
$page = 'hosting';
$page_title = 'Хостинг послуги - StormHosting UA';
$meta_description = 'Віртуальний хостинг, хмарні послуги та реселерський хостинг від StormHosting UA. SSD накопичувачі, безкоштовний SSL, підтримка 24/7.';
$meta_keywords = 'хостинг україни, віртуальний хостинг, хмарний хостинг, реселер хостинг, ssd хостинг';
$page_css = 'hosting';
$canonical_url = SITE_URL . '/pages/hosting/hosting.php';

// Получаем данные хостинг планов
try {
    if (defined('DB_AVAILABLE') && DB_AVAILABLE) {
        $hosting_plans = db_fetch_all(
            "SELECT * FROM hosting_plans WHERE is_active = 1 ORDER BY plan_type, price_monthly ASC"
        );
        
        $hosting_features = db_fetch_all(
            "SELECT * FROM hosting_features WHERE is_active = 1 ORDER BY sort_order ASC"
        );
        
        $hosting_stats = db_fetch_one(
            "SELECT 
                COUNT(*) as active_sites,
                AVG(uptime_percent) as avg_uptime,
                SUM(total_bandwidth) as total_bandwidth,
                COUNT(DISTINCT server_id) as total_servers
             FROM hosting_accounts WHERE status = 'active'"
        );
    } else {
        throw new Exception('Database not available');
    }
} catch (Exception $e) {
    // Fallback данные
    $hosting_plans = [
        [
            'id' => 1,
            'name_ua' => 'Базовий',
            'plan_type' => 'shared',
            'disk_space' => 1024,
            'bandwidth' => 10,
            'databases' => 1,
            'email_accounts' => 5,
            'price_monthly' => 99,
            'price_yearly' => 990,
            'is_popular' => 0
        ],
        [
            'id' => 2,
            'name_ua' => 'Стандарт',
            'plan_type' => 'shared',
            'disk_space' => 5120,
            'bandwidth' => 50,
            'databases' => 5,
            'email_accounts' => 20,
            'price_monthly' => 199,
            'price_yearly' => 1990,
            'is_popular' => 1
        ],
        [
            'id' => 3,
            'name_ua' => 'Хмарний',
            'plan_type' => 'cloud',
            'disk_space' => 10240,
            'bandwidth' => 100,
            'databases' => 10,
            'email_accounts' => 50,
            'price_monthly' => 399,
            'price_yearly' => 3990,
            'is_popular' => 0
        ]
    ];
    
    $hosting_features = [
        ['name_ua' => 'SSD накопичувачі', 'icon' => 'hdd'],
        ['name_ua' => 'Безкоштовний SSL', 'icon' => 'shield-lock'],
        ['name_ua' => 'Щоденні бекапи', 'icon' => 'arrow-repeat'],
        ['name_ua' => 'Підтримка 24/7', 'icon' => 'headphones']
    ];
    
    $hosting_stats = [
        'active_sites' => 1850,
        'avg_uptime' => 99.95,
        'total_bandwidth' => 25600,
        'total_servers' => 12
    ];
}

?>

<!-- Hosting Hero -->
<section class="hosting-hero py-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4">Хостинг послуги StormHosting UA</h1>
                <p class="lead mb-4">
                    Надійні рішення для розміщення вашого сайту. Від простого віртуального хостингу 
                    до потужних хмарних платформ та реселерських програм.
                </p>
                <div class="hosting-benefits mb-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="benefit-item d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>99.9% гарантований аптайм</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="benefit-item d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>SSD накопичувачі</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="benefit-item d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Безкоштовний SSL</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="benefit-item d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Підтримка 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-actions d-flex gap-3 flex-wrap">
                    <a href="shared.php" class="btn btn-light btn-lg">
                        <i class="bi bi-server"></i>
                        Віртуальний хостинг
                    </a>
                    <a href="cloud.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-cloud"></i>
                        Хмарний хостинг
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-visual text-center">
                    <div class="server-stack">
                        <div class="server-item bg-white bg-opacity-10 rounded p-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Web Server</h5>
                                    <small class="opacity-75">Apache/Nginx</small>
                                </div>
                                <div class="server-status">
                                    <span class="badge bg-success">Online</span>
                                </div>
                            </div>
                        </div>
                        <div class="server-item bg-white bg-opacity-10 rounded p-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Database</h5>
                                    <small class="opacity-75">MySQL/PostgreSQL</small>
                                </div>
                                <div class="server-status">
                                    <span class="badge bg-success">Online</span>
                                </div>
                            </div>
                        </div>
                        <div class="server-item bg-white bg-opacity-10 rounded p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Storage</h5>
                                    <small class="opacity-75">SSD NVMe</small>
                                </div>
                                <div class="server-status">
                                    <span class="badge bg-warning">High Load</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hosting Statistics -->
<section class="hosting-stats py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <div class="stat-item">
                    <h3 class="text-primary mb-1"><?php echo number_format($hosting_stats['active_sites']); ?></h3>
                    <p class="mb-0 text-muted">Активних сайтів</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <div class="stat-item">
                    <h3 class="text-success mb-1"><?php echo $hosting_stats['avg_uptime']; ?>%</h3>
                    <p class="mb-0 text-muted">Середній аптайм</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <div class="stat-item">
                    <h3 class="text-info mb-1"><?php echo number_format($hosting_stats['total_bandwidth']/1024, 1); ?> ТБ</h3>
                    <p class="mb-0 text-muted">Трафіку щомісяця</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-item">
                    <h3 class="text-warning mb-1"><?php echo $hosting_stats['total_servers']; ?></h3>
                    <p class="mb-0 text-muted">Серверів</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hosting Services -->
<section class="hosting-services py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Наші хостинг послуги</h2>
            <p class="text-muted">Оберіть рішення що підходить для вашого проекту</p>
        </div>
        
        <div class="row">
            <!-- Shared Hosting -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card h-100 text-center">
                    <div class="service-icon mb-4">
                        <i class="bi bi-server"></i>
                    </div>
                    <h4 class="service-title">Віртуальний хостинг</h4>
                    <p class="service-description">
                        Ідеальне рішення для персональних сайтів, блогів та невеликих бізнес-проектів. 
                        Простий у використанні та доступний за ціною.
                    </p>
                    <ul class="service-features list-unstyled mb-4">
                        <li><i class="bi bi-check text-success"></i> cPanel панель управління</li>
                        <li><i class="bi bi-check text-success"></i> Автоматичне встановлення CMS</li>
                        <li><i class="bi bi-check text-success"></i> Безкоштовний SSL сертифікат</li>
                        <li><i class="bi bi-check text-success"></i> Щоденні резервні копії</li>
                    </ul>
                    <div class="service-price mb-4">
                        <span class="price-from">від</span>
                        <span class="price-amount">99 грн</span>
                        <span class="price-period">/місяць</span>
                    </div>
                    <a href="shared.php" class="btn btn-primary btn-lg w-100">
                        Переглянути тарифи
                    </a>
                </div>
            </div>
            
            <!-- Cloud Hosting -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card h-100 text-center popular">
                    <div class="popular-badge">
                        <i class="bi bi-star-fill"></i>
                        Популярний
                    </div>
                    <div class="service-icon mb-4">
                        <i class="bi bi-cloud"></i>
                    </div>
                    <h4 class="service-title">Хмарний хостинг</h4>
                    <p class="service-description">
                        Масштабоване рішення з високою продуктивністю для середніх та великих проектів. 
                        Автоматичне резервування та балансування навантаження.
                    </p>
                    <ul class="service-features list-unstyled mb-4">
                        <li><i class="bi bi-check text-success"></i> Автоматичне масштабування</li>
                        <li><i class="bi bi-check text-success"></i> Гарантовані ресурси</li>
                        <li><i class="bi bi-check text-success"></i> Миттєві бекапи</li>
                        <li><i class="bi bi-check text-success"></i> CDN включено</li>
                    </ul>
                    <div class="service-price mb-4">
                        <span class="price-from">від</span>
                        <span class="price-amount">399 грн</span>
                        <span class="price-period">/місяць</span>
                    </div>
                    <a href="cloud.php" class="btn btn-primary btn-lg w-100">
                        Переглянути тарифи
                    </a>
                </div>
            </div>
            
            <!-- Reseller Hosting -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card h-100 text-center">
                    <div class="service-icon mb-4">
                        <i class="bi bi-diagram-2"></i>
                    </div>
                    <h4 class="service-title">Реселер хостинг</h4>
                    <p class="service-description">
                        Можливість створити власний хостинг бізнес. Готові білі лейбли, 
                        інструменти для управління клієнтами та flexible ціноутворення.
                    </p>
                    <ul class="service-features list-unstyled mb-4">
                        <li><i class="bi bi-check text-success"></i> WHM панель управління</li>
                        <li><i class="bi bi-check text-success"></i> Білий лейбл</li>
                        <li><i class="bi bi-check text-success"></i> Приватні NS сервери</li>
                        <li><i class="bi bi-check text-success"></i> Комісійна програма</li>
                    </ul>
                    <div class="service-price mb-4">
                        <span class="price-from">від</span>
                        <span class="price-amount">699 грн</span>
                        <span class="price-period">/місяць</span>
                    </div>
                    <a href="reseller.php" class="btn btn-primary btn-lg w-100">
                        Переглянути тарифи
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Our Hosting -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Чому обирають наш хостинг?</h2>
            <p class="text-muted">Переваги що роблять нас лідерами ринку</p>
        </div>
        
        <div class="row">
            <?php foreach ($hosting_features as $feature): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-item text-center h-100">
                    <div class="feature-icon-wrapper mb-3">
                        <i class="bi bi-<?php echo $feature['icon']; ?>"></i>
                    </div>
                    <h5><?php echo $feature['name_' . $current_lang]; ?></h5>
                    <p class="text-muted">
                        <?php echo $feature['description_' . $current_lang] ?? 'Опис функції буде додано незабаром'; ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Hosting Calculator -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Калькулятор вартості хостингу</h2>
            <p class="text-muted">Розрахуйте вартість послуг відповідно до ваших потреб</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="calculator-card">
                    <form id="hostingCalculator">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Тип хостингу</label>
                                <select class="form-select" name="hosting_type" required>
                                    <option value="">Оберіть тип</option>
                                    <option value="shared">Віртуальний хостинг</option>
                                    <option value="cloud">Хмарний хостинг</option>
                                    <option value="reseller">Реселер хостинг</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Дисковий простір (ГБ)</label>
                                <select class="form-select" name="disk_space">
                                    <option value="1">1 ГБ</option>
                                    <option value="5">5 ГБ</option>
                                    <option value="10" selected>10 ГБ</option>
                                    <option value="25">25 ГБ</option>
                                    <option value="50">50 ГБ</option>
                                    <option value="100">100 ГБ</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Трафік (ГБ/місяць)</label>
                                <select class="form-select" name="bandwidth">
                                    <option value="10">10 ГБ</option>
                                    <option value="50" selected>50 ГБ</option>
                                    <option value="100">100 ГБ</option>
                                    <option value="unlimited">Безлімітний</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Термін оплати</label>
                                <select class="form-select" name="payment_period">
                                    <option value="1">1 місяць</option>
                                    <option value="3">3 місяці</option>
                                    <option value="6">6 місяців</option>
                                    <option value="12" selected>12 місяців</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="calculator-result mt-4 p-4 bg-light rounded">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-2">Розрахована вартість:</h5>
                                    <div id="calculatedPrice" class="h3 text-primary mb-0">- грн/місяць</div>
                                    <small class="text-muted">*Ціна може змінюватися в залежності від додаткових опцій</small>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button type="button" class="btn btn-primary btn-lg">
                                        Замовити хостинг
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <h3 class="mb-2">Готові розпочати з нашим хостингом?</h3>
                <p class="mb-0">Миттєва активація, безкоштовне перенесення сайту та підтримка 24/7</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="shared.php" class="btn btn-light btn-lg me-3">
                    Обрати план
                </a>
                <a href="../../pages/info/contacts.php" class="btn btn-outline-light btn-lg">
                    Зв'язатися
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.service-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.service-card.popular {
    border-color: #007bff;
}

.popular-badge {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background: #007bff;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 2rem;
}

.service-features li {
    padding: 0.5rem 0;
    text-align: left;
}

.price-amount {
    font-size: 2.5rem;
    font-weight: 700;
    color: #007bff;
}

.price-from, .price-period {
    color: #6c757d;
}

.feature-icon-wrapper {
    width: 60px;
    height: 60px;
    background: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 1.5rem;
}

.calculator-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculator = document.getElementById('hostingCalculator');
    const priceDisplay = document.getElementById('calculatedPrice');
    
    // Базовые цены
    const basePrices = {
        shared: 99,
        cloud: 399,
        reseller: 699
    };
    
    // Коэффициенты для дополнительных ресурсов
    const diskMultiplier = {
        1: 1, 5: 1.2, 10: 1.5, 25: 2, 50: 2.5, 100: 3
    };
    
    const bandwidthMultiplier = {
        10: 1, 50: 1.3, 100: 1.6, unlimited: 2
    };
    
    const periodDiscount = {
        1: 1, 3: 0.95, 6: 0.9, 12: 0.8
    };
    
    function calculatePrice() {
        const formData = new FormData(calculator);
        const hostingType = formData.get('hosting_type');
        const diskSpace = formData.get('disk_space');
        const bandwidth = formData.get('bandwidth');
        const period = formData.get('payment_period');
        
        if (!hostingType) {
            priceDisplay.textContent = '- грн/місяць';
            return;
        }
        
        let basePrice = basePrices[hostingType];
        let diskCoeff = diskMultiplier[diskSpace] || 1;
        let bandwidthCoeff = bandwidthMultiplier[bandwidth] || 1;
        let periodCoeff = periodDiscount[period] || 1;
        
        let finalPrice = Math.round(basePrice * diskCoeff * bandwidthCoeff * periodCoeff);
        
        priceDisplay.textContent = finalPrice + ' грн/місяць';
    }
    
    // Обновляем цену при изменении полей
    calculator.addEventListener('change', calculatePrice);
    
    // Начальный расчет
    calculatePrice();
});
</script>

 <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>