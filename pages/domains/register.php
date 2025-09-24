<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'register';
$page_title = 'Реєстрація доменів - StormHosting UA | Купити домен .ua, .com.ua, .kiev.ua';
$meta_description = 'Реєстрація доменів .ua, .com.ua, .kiev.ua, .pp.ua та інших. Найкращі ціни на домени в Україні. Миттєва активація, безкоштовне керування DNS.';
$meta_keywords = 'реєстрація доменів .ua, домен .com.ua, домен .kiev.ua, домен .pp.ua, дешеві домени україна, купити домен';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/domains-register-2.css'
];

$additional_js = [
    '/assets/js/domains-register-2.js'
];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Функції-заглушки якщо не визначені
if (!function_exists('t')) {
    function t($key, $def = '') { 
        $translations = [
            'domains_register' => 'Реєстрація доменів',
            'domain_search_button' => 'Перевірити',
            'site_name' => 'StormHosting UA'
        ];
        return $translations[$key] ?? $def ?: $key; 
    }
}

if (!function_exists('escapeOutput')) {
    function escapeOutput($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price) { return number_format($price, 0, ',', ' ') . ' грн'; }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() { 
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token']; 
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return trim(strip_tags($input));
    }
}

// Отримуємо доменні зони з БД
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sthostsitedb;charset=utf8mb4", "sthostdb", "3344Frz@q0607Dm$157");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Популярні домени (перші 8)
    $stmt = $pdo->prepare("
        SELECT dz.zone, dz.price_registration, dz.price_renewal, dz.price_transfer,
               CASE 
                   WHEN dz.zone LIKE '%.ua' THEN 'Український домен'
                   WHEN dz.zone IN ('.com', '.net', '.org') THEN 'Міжнародний домен'
                   ELSE 'Спеціальний домен'
               END as domain_type,
               CASE 
                   WHEN dz.price_registration <= 150 THEN 'Економ'
                   WHEN dz.price_registration <= 250 THEN 'Стандарт'
                   ELSE 'Преміум'
               END as price_category
        FROM domain_zones dz 
        WHERE dz.is_active = 1 AND dz.is_popular = 1
        ORDER BY dz.zone LIKE '%.ua' DESC, dz.price_registration ASC
        LIMIT 8
    ");
    $stmt->execute();
    $popular_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Всі активні зони для поиску
    $stmt = $pdo->prepare("
        SELECT zone, price_registration, price_renewal, price_transfer,
               CASE WHEN zone LIKE '%.ua' THEN 1 ELSE 0 END as is_ua_domain
        FROM domain_zones 
        WHERE is_active = 1 
        ORDER BY is_ua_domain DESC, price_registration ASC
    ");
    $stmt->execute();
    $all_zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Статистика по доменам
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_zones,
            COUNT(CASE WHEN zone LIKE '%.ua' THEN 1 END) as ua_zones,
            MIN(price_registration) as min_price,
            MAX(price_registration) as max_price
        FROM domain_zones WHERE is_active = 1
    ");
    $stmt->execute();
    $domain_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Fallback дані у випадку помилки БД
    $popular_domains = [
        ['zone' => '.ua', 'price_registration' => 200, 'price_renewal' => 200, 'price_transfer' => 180, 'domain_type' => 'Український домен', 'price_category' => 'Стандарт'],
        ['zone' => '.com.ua', 'price_registration' => 150, 'price_renewal' => 160, 'price_transfer' => 130, 'domain_type' => 'Український домен', 'price_category' => 'Економ'],
        ['zone' => '.pp.ua', 'price_registration' => 160, 'price_renewal' => 160, 'price_transfer' => 160, 'domain_type' => 'Український домен', 'price_category' => 'Економ'],
        ['zone' => '.kiev.ua', 'price_registration' => 180, 'price_renewal' => 180, 'price_transfer' => 160, 'domain_type' => 'Український домен', 'price_category' => 'Стандарт'],
        ['zone' => '.net.ua', 'price_registration' => 180, 'price_renewal' => 180, 'price_transfer' => 160, 'domain_type' => 'Український домен', 'price_category' => 'Стандарт'],
        ['zone' => '.org.ua', 'price_registration' => 180, 'price_renewal' => 180, 'price_transfer' => 160, 'domain_type' => 'Український домен', 'price_category' => 'Стандарт'],
        ['zone' => '.com', 'price_registration' => 350, 'price_renewal' => 400, 'price_transfer' => 350, 'domain_type' => 'Міжнародний домен', 'price_category' => 'Преміум'],
        ['zone' => '.net', 'price_registration' => 450, 'price_renewal' => 500, 'price_transfer' => 450, 'domain_type' => 'Міжнародний домен', 'price_category' => 'Преміум']
    ];
    
    $all_zones = $popular_domains;
    $domain_stats = ['total_zones' => count($popular_domains), 'ua_zones' => 6, 'min_price' => 120, 'max_price' => 450];
}

// Обробка AJAX запитів для перевірки доменів
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_POST['action'] === 'check_domain') {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['error' => 'Недійсний токен безпеки']);
            exit;
        }
        
        $domain = sanitizeInput($_POST['domain'] ?? '');
        $zone = sanitizeInput($_POST['zone'] ?? '');
        
        if (empty($domain)) {
            echo json_encode(['error' => 'Введіть ім\'я домену']);
            exit;
        }
        
        // Перевірка формату домену
        if (!preg_match('/^[a-zA-Z0-9-]+$/', $domain) || strlen($domain) < 2 || strlen($domain) > 63) {
            echo json_encode(['error' => 'Недопустимі символи в імені домену або неправильна довжина (2-63 символи)']);
            exit;
        }
        
        // Перевірка що домен не починається та не закінчується дефісом
        if (strpos($domain, '-') === 0 || strrpos($domain, '-') === strlen($domain) - 1) {
            echo json_encode(['error' => 'Домен не може починатися або закінчуватися дефісом']);
            exit;
        }
        
        $full_domain = $domain . $zone;
        
        // Тут буде реальна перевірка через WHOIS API
        // Поки що робимо псевдо-випадкову перевірку
        $hash = crc32($full_domain);
        $is_available = ($hash % 4) !== 0; // ~75% доменів доступні
        
        // Отримуємо ціну для зони
        $zone_info = null;
        foreach ($all_zones as $z) {
            if ($z['zone'] === $zone) {
                $zone_info = $z;
                break;
            }
        }
        
        if (!$zone_info) {
            echo json_encode(['error' => 'Доменна зона не підтримується']);
            exit;
        }
        
        echo json_encode([
            'domain' => $full_domain,
            'available' => $is_available,
            'price' => $zone_info['price_registration'],
            'renewal_price' => $zone_info['price_renewal'],
            'currency' => 'грн',
            'message' => $is_available ? 'Домен доступний для реєстрації!' : 'Домен уже зареєстрований',
            'zone_info' => $zone_info
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'bulk_check') {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['error' => 'Недійсний токен безпеки']);
            exit;
        }
        
        $domain = sanitizeInput($_POST['domain'] ?? '');
        $zones = $_POST['zones'] ?? [];
        
        if (empty($domain)) {
            echo json_encode(['error' => 'Введіть ім\'я домену']);
            exit;
        }
        
        $results = [];
        foreach ($zones as $zone) {
            $zone = sanitizeInput($zone);
            $full_domain = $domain . $zone;
            $hash = crc32($full_domain);
            $is_available = ($hash % 4) !== 0;
            
            $zone_info = null;
            foreach ($all_zones as $z) {
                if ($z['zone'] === $zone) {
                    $zone_info = $z;
                    break;
                }
            }
            
            if ($zone_info) {
                $results[] = [
                    'domain' => $full_domain,
                    'zone' => $zone,
                    'available' => $is_available,
                    'price' => $zone_info['price_registration'],
                    'renewal_price' => $zone_info['price_renewal']
                ];
            }
        }
        
        echo json_encode(['results' => $results]);
        exit;
    }
}

// Підключення header
//include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/pages/domains-register-2.css">
<!-- Domain Search Hero -->
<section class="domain-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="hero-badge mb-4">
                    <i class="bi bi-globe"></i>
                    <span>Реєстрація доменів в Україні</span>
                </div>
                
                <h1 class="hero-title mb-4">Знайдіть ідеальний домен для вашого проекту</h1>
                <p class="hero-subtitle mb-5">
                    Підтримуємо всі популярні українські та міжнародні доменні зони. 
                    Миттєва активація, безкоштовне керування DNS та професійна підтримка 24/7.
                </p>
                
                <!-- Domain Search Form -->
                <div class="domain-search-wrapper">
                    <form id="domainSearchForm" class="domain-search-form">
                        <input type="hidden" id="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="search-input-group">
                            <div class="input-wrapper">
                                <i class="bi bi-globe input-icon"></i>
                                <input type="text" 
                                       id="domainName" 
                                       class="domain-input" 
                                       placeholder="назва-вашого-сайту"
                                       autocomplete="off"
                                       maxlength="63"
                                       required>
                            </div>
                            
                            <div class="zone-selector">
                                <select id="domainZone" class="zone-select">
                                    <?php foreach ($popular_domains as $domain): ?>
                                    <option value="<?php echo escapeOutput($domain['zone']); ?>" 
                                            data-price="<?php echo $domain['price_registration']; ?>"
                                            data-renewal="<?php echo $domain['price_renewal']; ?>">
                                        <?php echo escapeOutput($domain['zone']); ?> 
                                        (<?php echo formatPrice($domain['price_registration']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="search-btn">
                                <i class="bi bi-search"></i>
                                <span>Перевірити</span>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Search Results -->
                    <div id="searchResults" class="search-results"></div>
                    
                    <!-- Bulk Search -->
                    <div class="bulk-search-toggle">
                        <button type="button" id="toggleBulkSearch" class="btn-link">
                            <i class="bi bi-list-check"></i>
                            Перевірити у всіх популярних зонах
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Background Elements -->
    <div class="hero-bg-elements">
        <div class="floating-element element-1">
            <i class="bi bi-globe"></i>
        </div>
        <div class="floating-element element-2">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="floating-element element-3">
            <i class="bi bi-lightning"></i>
        </div>
    </div>
</section>

<!-- Domain Statistics -->
<section class="domain-stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-collection"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $domain_stats['total_zones']; ?>+</div>
                    <div class="stat-label">Доменних зон</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-flag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $domain_stats['ua_zones']; ?></div>
                    <div class="stat-label">Українських зон</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">від <?php echo formatPrice($domain_stats['min_price']); ?></div>
                    <div class="stat-label">Мінімальна ціна</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-headphones"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Підтримка</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Domains -->
<section class="popular-domains">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Популярні доменні зони</h2>
            <p class="section-subtitle">Оберіть найкращий домен для вашого проекту з найвигіднішими цінами</p>
        </div>
        
        <div class="domains-grid">
            <?php foreach ($popular_domains as $index => $domain): ?>
            <div class="domain-card" data-zone="<?php echo escapeOutput($domain['zone']); ?>" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <div class="domain-card-header">
                    <div class="domain-zone"><?php echo escapeOutput($domain['zone']); ?></div>
                    <div class="domain-type"><?php echo escapeOutput($domain['domain_type']); ?></div>
                </div>
                
                <div class="domain-card-body">
                    <div class="price-section">
                        <div class="main-price">
                            <span class="price-amount"><?php echo formatPrice($domain['price_registration']); ?></span>
                            <span class="price-period">/ рік</span>
                        </div>
                        <div class="renewal-price">
                            Продовження: <?php echo formatPrice($domain['price_renewal']); ?>
                        </div>
                    </div>
                    
                    <div class="price-badge badge-<?php echo strtolower($domain['price_category']); ?>">
                        <?php echo escapeOutput($domain['price_category']); ?>
                    </div>
                    
                    <ul class="domain-features">
                        <li><i class="bi bi-check"></i> Безкоштовне керування DNS</li>
                        <li><i class="bi bi-check"></i> Захист конфіденційності WHOIS</li>
                        <li><i class="bi bi-check"></i> Автопродовження</li>
                        <li><i class="bi bi-check"></i> Підтримка 24/7</li>
                    </ul>
                </div>
                
                <div class="domain-card-footer">
                    <button class="btn-check-domain" data-zone="<?php echo escapeOutput($domain['zone']); ?>">
                        <i class="bi bi-search"></i>
                        Перевірити доступність
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Domain Features -->
<section class="domain-features">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Переваги реєстрації доменів у нас</h2>
            <p class="section-subtitle">Ми пропонуємо найкращі умови для реєстрації та управління доменами</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="0">
                <div class="feature-icon">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Миттєва активація</h4>
                    <p class="feature-description">Домен активується автоматично протягом декількох хвилин після оплати</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Захист приватності</h4>
                    <p class="feature-description">Безкоштовний захист персональних даних в WHOIS базі</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon">
                    <i class="bi bi-gear"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Повне керування</h4>
                    <p class="feature-description">Зручна панель управління доменом з усіма необхідними функціями</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Безкоштовне перенесення</h4>
                    <p class="feature-description">Перенесіть свій домен від іншого реєстратора абсолютно безкоштовно</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon">
                    <i class="bi bi-dns"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Керування DNS</h4>
                    <p class="feature-description">Повний контроль над DNS записами через зручний інтерфейс</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-icon">
                    <i class="bi bi-headphones"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Підтримка 24/7</h4>
                    <p class="feature-description">Кваліфікована технічна підтримка доступна цілодобово</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Domain Transfer CTA -->
<section class="domain-transfer-cta">
    <div class="container">
        <div class="transfer-card">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="transfer-content">
                        <h3 class="transfer-title">Маєте домен у іншого реєстратора?</h3>
                        <p class="transfer-subtitle">Перенесіть його до нас безкоштовно та отримайте кращі умови обслуговування</p>
                        
                        <ul class="transfer-benefits">
                            <li><i class="bi bi-check-circle"></i> Безкоштовне перенесення</li>
                            <li><i class="bi bi-check-circle"></i> Продовження на 1 рік</li>
                            <li><i class="bi bi-check-circle"></i> Кращі ціни на продовження</li>
                            <li><i class="bi bi-check-circle"></i> Професійна підтримка</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-4 text-lg-end">
                    <a href="/pages/domains/transfer.php" class="btn btn-transfer">
                        <i class="bi bi-arrow-right-circle"></i>
                        Перенести домен
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="domain-faq">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Часті питання</h2>
            <p class="section-subtitle">Відповіді на найпоширеніші питання про реєстрацію доменів</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="domainFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Як довго займає реєстрація домену?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#domainFAQ">
                            <div class="accordion-body">
                                Реєстрація домену відбувається миттєво після підтвердження оплати. Зазвичай це займає від 5 до 15 хвилин. Для українських доменів (.ua, .com.ua) процес може зайняти до 24 годин через додаткові перевірки.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Чи можу я зареєструвати домен без хостингу?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#domainFAQ">
                            <div class="accordion-body">
                                Так, ви можете зареєструвати домен окремо від хостингу. Домен можна використовувати для електронної пошти, перенаправлення або підключити до хостингу пізніше.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Що включено в ціну домену?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#domainFAQ">
                            <div class="accordion-body">
                                У ціну включено: реєстрацію на 1 рік, безкоштовне керування DNS, захист приватності WHOIS, автопродовження (опціонально) та технічну підтримку 24/7.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Як змінити DNS сервери?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#domainFAQ">
                            <div class="accordion-body">
                                Змінити DNS сервери можна в панелі управління доменом. Зміни вступають в силу протягом 24-48 годин через особливості поширення DNS записів.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Конфігурація для JavaScript
window.domainConfig = {
    searchUrl: '?ajax=1',
    csrfToken: '<?php echo generateCSRFToken(); ?>',
    zones: <?php echo json_encode($all_zones); ?>,
    translations: {
        searching: 'Перевіряємо доступність...',
        available: 'Домен доступний!',
        unavailable: 'Домен зайнятий',
        error: 'Помилка перевірки'
    }
};
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>