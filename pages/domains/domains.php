<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'domains';
$page_title = 'Доменні послуги - StormHosting UA | Реєстрація .ua доменів';
$meta_description = 'Доменні послуги від StormHosting UA: реєстрація .ua доменів, WHOIS перевірка, DNS налаштування, трансфер доменів. Найкращі ціни в Україні.';
$meta_keywords = 'домени .ua, реєстрація доменів україна, whois перевірка, dns налаштування, трансфер доменів';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/domains2.css'
];

$additional_js = [
    '/assets/js/domains.js'
];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
// Функції-заглушки якщо не визначені
if (!function_exists('t')) {
    function t($key, $def = '') { 
        $translations = [
            'domains_title' => 'Доменні послуги',
            'domains_subtitle' => 'Знайдіть та зареєструйте ідеальний домен',
            'site_name' => 'StormHosting UA'
        ];
        return $translations[$key] ?? $def ?: $key; 
    }
}

if (!function_exists('escapeOutput')) {
    function escapeOutput($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

// ВАЖЛИВО: Додаємо функцію formatPrice
if (!function_exists('formatPrice')) {
    function formatPrice($price, $currency = 'грн') {
        return number_format($price, 0, ',', ' ') . ' ' . $currency;
    }
}

// Тестові дані для демонстрації (замініть на реальні запити до БД)
$domain_stats = [
    'total_zones' => 25,
    'ua_zones' => 18,
    'min_price' => 99,
    'registered_today' => 47
];

$popular_domains = [
    [
        'zone' => '.ua',
        'price_registration' => 150,
        'price_renewal' => 150,
        'domain_type' => 'Український домен',
        'is_ua_domain' => true
    ],
    [
        'zone' => '.com.ua',
        'price_registration' => 99,
        'price_renewal' => 99,
        'domain_type' => 'Український домен',
        'is_ua_domain' => true
    ],
    [
        'zone' => '.kiev.ua',
        'price_registration' => 120,
        'price_renewal' => 120,
        'domain_type' => 'Київський домен',
        'is_ua_domain' => true
    ],
    [
        'zone' => '.pp.ua',
        'price_registration' => 110,
        'price_renewal' => 110,
        'domain_type' => 'Персональний домен',
        'is_ua_domain' => true
    ],
    [
        'zone' => '.com',
        'price_registration' => 399,
        'price_renewal' => 399,
        'domain_type' => 'Міжнародний домен',
        'is_ua_domain' => false
    ],
    [
        'zone' => '.net',
        'price_registration' => 450,
        'price_renewal' => 450,
        'domain_type' => 'Міжнародний домен',
        'is_ua_domain' => false
    ]
];

$recent_domains = [
    'example-site.com.ua',
    'mycompany.ua',
    'webstore.kiev.ua',
    'blog.pp.ua',
    'portfolio.com.ua',
    'startup.ua'
];

// Підключення header

?>
    <!-- Main CSS -->
    <link rel="stylesheet" href="/assets/css/home.css">
    <!-- Calculator CSS -->
     <link rel="stylesheet" href="/assets/css/pages/domains2.css">
     
<!-- Domains Hero -->
<section class="domain-hero py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Домени для вашого бізнесу</h1>
                <p class="lead mb-4">
                    Знайдіть та зареєструйте ідеальний домен для вашого сайту.
                    Підтримуємо всі популярні українські та міжнародні доменні зони.
                </p>
                
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="/pages/domains/register.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle"></i>
                        Зареєструвати домен
                    </a>
                    <a href="/pages/domains/whois.php" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-search"></i>
                        WHOIS перевірка
                    </a>
                </div>
                
                <div class="domain-features">
                    <div class="feature-item">
                        <i class="bi bi-check-circle text-success"></i>
                        <span>Миттєва активація</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle text-success"></i>
                        <span>Безкоштовне DNS керування</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle text-success"></i>
                        <span>Підтримка 24/7</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="domain-illustration">
                    <div class="domain-bubble" data-aos="fade-up" data-aos-delay="100">
                        <i class="bi bi-globe"></i>
                        <span>.ua</span>
                    </div>
                    <div class="domain-bubble" data-aos="fade-up" data-aos-delay="200">
                        <i class="bi bi-building"></i>
                        <span>.com.ua</span>
                    </div>
                    <div class="domain-bubble" data-aos="fade-up" data-aos-delay="300">
                        <i class="bi bi-geo-alt"></i>
                        <span>.kiev.ua</span>
                    </div>
                    <div class="domain-bubble" data-aos="fade-up" data-aos-delay="400">
                        <i class="bi bi-person"></i>
                        <span>.pp.ua</span>
                    </div>
                    <div class="domain-bubble central" data-aos="zoom-in" data-aos-delay="500">
                        <i class="bi bi-lightning"></i>
                        <span>StormHosting</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Domain Stats -->
<section class="domain-stats py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $domain_stats['total_zones']; ?></div>
                    <div class="stat-label">Доменних зон</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $domain_stats['ua_zones']; ?></div>
                    <div class="stat-label">Українських зон</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number">від <?php echo formatPrice($domain_stats['min_price']); ?></div>
                    <div class="stat-label">Мінімальна ціна</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $domain_stats['registered_today']; ?></div>
                    <div class="stat-label">Зареєстровано сьогодні</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Domain Services -->
<section class="domain-services py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="section-title">Наші доменні послуги</h2>
                <p class="section-subtitle">Повний спектр послуг для роботи з доменами</p>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Domain Registration -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <h4>Реєстрація доменів</h4>
                    <p>Зареєструйте домен у популярних українських та міжнародних зонах за найкращими цінами.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check"></i> Миттєва активація</li>
                        <li><i class="bi bi-check"></i> Всі популярні зони</li>
                        <li><i class="bi bi-check"></i> Безкоштовний DNS</li>
                    </ul>
                    <a href="/pages/domains/register.php" class="btn btn-primary w-100 mt-auto">
                        Зареєструвати
                    </a>
                </div>
            </div>
            
            <!-- Domain Transfer -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <h4>Трансфер доменів</h4>
                    <p>Перенесіть свої домени до нас та отримайте кращі умови обслуговування.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check"></i> Безкоштовний трансфер</li>
                        <li><i class="bi bi-check"></i> Збереження налаштувань</li>
                        <li><i class="bi bi-check"></i> Збереження DNS</li>
                    </ul>
                    <a href="/pages/domains/transfer.php" class="btn btn-outline-primary w-100 mt-auto">
                        Перенести домен
                    </a>
                </div>
            </div>
            
            <!-- WHOIS Lookup -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <h4>WHOIS lookup</h4>
                    <p>Перевірте інформацію про власника домену, дати реєстрації та DNS сервери.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check"></i> Детальна інформація</li>
                        <li><i class="bi bi-check"></i> Всі доменні зони</li>
                        <li><i class="bi bi-check"></i> Безкоштовно</li>
                    </ul>
                    <a href="/pages/domains/whois.php" class="btn btn-outline-primary w-100 mt-auto">
                        Перевірити WHOIS
                    </a>
                </div>
            </div>
            
            <!-- DNS Tools -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="bi bi-dns"></i>
                    </div>
                    <h4>DNS інструменти</h4>
                    <p>Перевірка DNS записів, діагностика проблем та управління налаштуваннями.</p>
                    <ul class="service-features">
                        <li><i class="bi bi-check"></i> Всі типи записів</li>
                        <li><i class="bi bi-check"></i> Діагностика</li>
                        <li><i class="bi bi-check"></i> Експорт даних</li>
                    </ul>
                    <a href="/pages/domains/dns.php" class="btn btn-outline-primary w-100 mt-auto">
                        Перевірити DNS
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Domains -->
<section class="popular-domains py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Популярні доменні зони</h2>
                <p class="section-subtitle">Найпопулярніші варіанти для українського бізнесу</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($popular_domains as $domain): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="domain-zone-card compact">
                    <div class="zone-header">
                        <div class="zone-name"><?php echo escapeOutput($domain['zone']); ?></div>
                        <?php if ($domain['is_ua_domain']): ?>
                        <span class="badge bg-primary">UA</span>
                        <?php endif; ?>
                    </div>
                    <div class="zone-price">
                        <span class="price"><?php echo formatPrice($domain['price_registration']); ?></span>
                        <span class="period">/ рік</span>
                    </div>
                    <button class="btn btn-sm btn-primary w-100 zone-check-btn" 
                            data-zone="<?php echo escapeOutput($domain['zone']); ?>">
                        Перевірити
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="/pages/domains/register.php" class="btn btn-primary btn-lg">
                Переглянути всі зони
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Recent Activity -->
<section class="recent-activity py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Останні реєстрації</h2>
                <p class="lead mb-4">Приєднуйтесь до тисяч клієнтів, які довіряють нам свої домени</p>
                
                <div class="recent-domains">
                    <?php foreach (array_slice($recent_domains, 0, 6) as $index => $domain): ?>
                    <div class="recent-domain-item" style="animation-delay: <?php echo $index * 0.1; ?>s">
                        <i class="bi bi-check-circle text-success"></i>
                        <span><?php echo escapeOutput($domain); ?></span>
                        <small class="text-muted">щойно зареєстровано</small>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4">
                    <a href="/pages/domains/register.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i>
                        Приєднатися до них
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="info-content">
                            <h5>Захищені домени</h5>
                            <p>Всі домени захищені від несанкціонованого трансферу</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-lightning"></i>
                        </div>
                        <div class="info-content">
                            <h5>Миттєва активація</h5>
                            <p>Домени активуються протягом 5 хвилин після оплати</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div class="info-content">
                            <h5>Підтримка 24/7</h5>
                            <p>Наша команда завжди готова допомогти вам</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="cta-content">
                    <h2 class="mb-4">Готові зареєструвати свій домен?</h2>
                    <p class="lead mb-4">
                        Почніть свій онлайн-бізнес з ідеального домену. 
                        Миттєва активація, найкращі ціни та професійна підтримка.
                    </p>
                    
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="/pages/domains/register.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-search"></i>
                            Знайти домен
                        </a>
                        <a href="/pages/domains/transfer.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-arrow-left-right"></i>
                            Перенести домен
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>