<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Функції-заглушки якщо не визначені
if (!function_exists('formatPrice')) {
    function formatPrice($price, $currency = 'грн') {
        return number_format($price, 0, ',', ' ') . ' ' . $currency;
    }
}

if (!function_exists('t')) {
    function t($key, $def = '') { 
        return $def ?: $key; 
    }
}

if (!function_exists('escapeOutput')) {
    function escapeOutput($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

// Конфігурація сторінки
$page = 'shared';
$page_title = 'Віртуальний хостинг - StormHosting UA';
$meta_description = 'Надійний віртуальний хостинг в Україні. cPanel, безкоштовний SSL, щоденні бекапи. Від 99 грн/міс.';
$meta_keywords = 'віртуальний хостинг, shared hosting, хостинг україна, cpanel хостинг';

// Додаткові CSS та JS файли
$additional_css = [
    '/assets/css/pages/hosting-shared.css'
];

// Fallback дані для планів
$shared_plans = [
    [
        'id' => 1,
        'name_ua' => 'Стартовий',
        'disk_space' => 1024, // MB
        'bandwidth' => 10, // GB
        'databases' => 1,
        'email_accounts' => 5,
        'subdomains' => 3,
        'price_monthly' => 99,
        'price_yearly' => 990,
        'is_popular' => 0,
        'setup_fee' => 0
    ],
    [
        'id' => 2,
        'name_ua' => 'Базовий',
        'disk_space' => 5120,
        'bandwidth' => 50,
        'databases' => 5,
        'email_accounts' => 20,
        'subdomains' => 10,
        'price_monthly' => 199,
        'price_yearly' => 1990,
        'is_popular' => 1,
        'setup_fee' => 0
    ],
    [
        'id' => 3,
        'name_ua' => 'Професійний',
        'disk_space' => 10240,
        'bandwidth' => 100,
        'databases' => 10,
        'email_accounts' => 50,
        'subdomains' => 25,
        'price_monthly' => 349,
        'price_yearly' => 3490,
        'is_popular' => 0,
        'setup_fee' => 0
    ],
    [
        'id' => 4,
        'name_ua' => 'Бізнес',
        'disk_space' => 20480,
        'bandwidth' => 200,
        'databases' => 25,
        'email_accounts' => 100,
        'subdomains' => 50,
        'price_monthly' => 599,
        'price_yearly' => 5990,
        'is_popular' => 0,
        'setup_fee' => 0
    ]
];

// Підключення файлів
try {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
    }
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
    }
} catch (Exception $e) {
    // Ігноруємо помилки включення файлів
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escapeOutput($page_title); ?></title>
    <meta name="description" content="<?php echo escapeOutput($meta_description); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* Інлайн стилі для забезпечення роботи сторінки */
        .shared-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
            overflow: hidden;
            padding: 100px 0 80px;
        }
        
        .shared-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(255,255,255,0.1) 0%, transparent 50%);
        }
        
        .shared-hero .container {
            position: relative;
            z-index: 2;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.1;
        }
        
        .hero-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .hero-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .hero-features .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .hero-features .feature-item i {
            color: #4ade80;
            font-size: 1rem;
        }
        
        .plan-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }
        
        .plan-card.popular {
            border-color: #667eea;
            transform: scale(1.02);
        }
        
        .popular-badge {
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 0 0 15px 15px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .plan-header {
            padding: 2rem 1.5rem 1rem;
            text-align: center;
        }
        
        .plan-card.popular .plan-header {
            padding-top: 3rem;
        }
        
        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .price {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 0.25rem;
        }
        
        .amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
        }
        
        .currency, .period {
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .savings {
            font-size: 0.8rem;
            color: #10b981;
            font-weight: 500;
            margin-top: 0.25rem;
        }
        
        .plan-features {
            padding: 0 1.5rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.9rem;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list i {
            color: #667eea;
            font-size: 1rem;
            width: 16px;
            flex-shrink: 0;
        }
        
        .plan-footer {
            padding: 1.5rem;
            margin-top: auto;
        }
        
        .btn-order {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-order:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .guarantee-text {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }
        
        .guarantee-text i {
            color: #10b981;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        .trust-indicators .col-auto {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .trust-indicators i {
            color: #4ade80;
        }
        
        @media (max-width: 768px) {
            .hero-title { font-size: 2rem; }
            .plan-card.popular { transform: none; }
            .amount { font-size: 2rem; }
            .section-title { font-size: 2rem; }
        }
    </style>
</head>

<body>

<!-- Shared Hosting Hero -->
<section class="shared-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-badge mb-4">
                    <i class="bi bi-hdd-stack"></i>
                    <span>Віртуальний хостинг</span>
                </div>
                
                <h1 class="hero-title mb-4">Надійний віртуальний хостинг в Україні</h1>
                <p class="hero-subtitle mb-4">
                    Швидкі SSD сервери, безкоштовний SSL, cPanel панель керування та підтримка 24/7. 
                    Ідеально підходить для сайтів-візиток, блогів та невеликих інтернет-магазинів.
                </p>
                
                <div class="hero-features">
                    <div class="feature-item">
                        <i class="bi bi-check-circle"></i>
                        <span>Миттєва активація</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle"></i>
                        <span>Безкоштовний SSL сертифікат</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle"></i>
                        <span>Щоденні автобекапи</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle"></i>
                        <span>cPanel панель керування</span>
                    </div>
                </div>
                
                <div class="hero-actions mt-4">
                    <a href="#plans" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-arrow-down"></i>
                        Переглянути тарифи
                    </a>
                    <a href="/pages/contacts.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-chat-dots"></i>
                        Консультація
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="text-center">
                    <h2 class="mb-4">99.9% Uptime</h2>
                    <p class="mb-0">Гарантована стабільність роботи</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Shared Hosting Plans -->
<section id="plans" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Тарифні плани віртуального хостингу</h2>
            <p class="section-subtitle">Оберіть план що підходить для вашого проекту</p>
            
            <!-- Billing Toggle -->
            <div class="billing-toggle mt-4">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="billing" id="monthly" checked>
                    <label class="btn btn-outline-primary" for="monthly">Щомісячно</label>
                    
                    <input type="radio" class="btn-check" name="billing" id="yearly">
                    <label class="btn btn-outline-primary" for="yearly">
                        Щорічно <span class="badge bg-success ms-1">-20%</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($shared_plans as $plan): ?>
            <div class="col-lg-3 col-md-6">
                <div class="plan-card d-flex flex-column <?php echo $plan['is_popular'] ? 'popular' : ''; ?>">
                    <?php if ($plan['is_popular']): ?>
                    <div class="popular-badge">Найпопулярніший</div>
                    <?php endif; ?>
                    
                    <div class="plan-header">
                        <h3 class="plan-name"><?php echo escapeOutput($plan['name_ua']); ?></h3>
                        <div class="plan-price">
                            <div class="price monthly-price">
                                <span class="currency">від</span>
                                <span class="amount"><?php echo $plan['price_monthly']; ?></span>
                                <span class="period">грн/міс</span>
                            </div>
                            <div class="price yearly-price d-none">
                                <span class="currency">від</span>
                                <span class="amount"><?php echo round($plan['price_yearly']/12); ?></span>
                                <span class="period">грн/міс</span>
                                <div class="savings">Економія <?php echo formatPrice($plan['price_monthly'] * 12 - $plan['price_yearly']); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="plan-features">
                        <ul class="feature-list">
                            <li>
                                <i class="bi bi-hdd"></i>
                                <span><?php echo round($plan['disk_space']/1024, 1); ?> ГБ SSD дискового простору</span>
                            </li>
                            <li>
                                <i class="bi bi-arrow-left-right"></i>
                                <span><?php echo $plan['bandwidth']; ?> ГБ місячного трафіку</span>
                            </li>
                            <li>
                                <i class="bi bi-database"></i>
                                <span><?php echo $plan['databases']; ?> MySQL баз даних</span>
                            </li>
                            <li>
                                <i class="bi bi-envelope"></i>
                                <span><?php echo $plan['email_accounts']; ?> email акаунтів</span>
                            </li>
                            <li>
                                <i class="bi bi-globe"></i>
                                <span><?php echo $plan['subdomains']; ?> субдоменів</span>
                            </li>
                            <li>
                                <i class="bi bi-shield-check"></i>
                                <span>Безкоштовний SSL сертифікат</span>
                            </li>
                            <li>
                                <i class="bi bi-gear"></i>
                                <span>cPanel панель керування</span>
                            </li>
                            <li>
                                <i class="bi bi-arrow-repeat"></i>
                                <span>Щоденні автобекапи</span>
                            </li>
                            <li>
                                <i class="bi bi-headset"></i>
                                <span>Підтримка 24/7</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="plan-footer">
                        <!-- 
                        ====== ЗАГЛУШКА ДЛЯ КНОПКИ ОПЛАТИ ======
                        Тут буде кнопка інтеграції з платіжною системою
                        Plan ID: <?php echo $plan['id']; ?>
                        Plan Name: <?php echo $plan['name_ua']; ?>
                        Monthly Price: <?php echo $plan['price_monthly']; ?>
                        Yearly Price: <?php echo $plan['price_yearly']; ?>
                        -->
                        <button class="btn btn-order w-100" 
                                data-plan-id="<?php echo $plan['id']; ?>"
                                data-plan-name="<?php echo escapeOutput($plan['name_ua']); ?>"
                                data-monthly-price="<?php echo $plan['price_monthly']; ?>"
                                data-yearly-price="<?php echo $plan['price_yearly']; ?>">
                            <i class="bi bi-cart-plus"></i>
                            Замовити зараз
                        </button>
                        
                        <div class="guarantee-text">
                            <i class="bi bi-shield-check"></i>
                            <span>30 днів гарантії повернення коштів</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <div class="alert alert-info d-inline-block">
                <i class="bi bi-info-circle"></i>
                Усі тарифи включають безкоштовний перенос сайту з іншого хостингу
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Переваги нашого хостингу</h2>
            <p class="section-subtitle">Чому понад 1000+ клієнтів обирають StormHosting UA</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-hdd-fill"></i>
                    </div>
                    <h5>SSD накопичувачі</h5>
                    <p class="text-muted">
                        Всі сервери оснащені швидкими SSD дисками для максимальної швидкості роботи сайту.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <h5>cPanel панель керування</h5>
                    <p class="text-muted">
                        Інтуїтивна та зручна панель керування для управління всіма аспектами хостингу.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h5>Безкоштовний SSL сертифікат</h5>
                    <p class="text-muted">
                        Let's Encrypt SSL сертифікати автоматично встановлюються та продовжуються.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h5>Автоматичні бекапи</h5>
                    <p class="text-muted">
                        Щоденні резервні копії з можливістю відновлення за останні 30 днів.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-code-slash"></i>
                    </div>
                    <h5>Підтримка всіх PHP версій</h5>
                    <p class="text-muted">
                        Від PHP 5.6 до 8.3 з можливістю переключення для кожного домену.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5>Технічна підтримка 24/7</h5>
                    <p class="text-muted">
                        Кваліфікована підтримка українською мовою. Середній час відповіді - 15 хвилин.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Часті питання</h2>
            <p class="section-subtitle">Відповіді на найпопулярніші запитання про віртуальний хостинг</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="sharedFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Що таке віртуальний хостинг?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#sharedFAQ">
                            <div class="accordion-body">
                                Віртуальний хостинг - це послуга розміщення сайту на сервері, де ресурси одного сервера розділяються між кількома клієнтами. Це найбільш економічний варіант для невеликих та середніх сайтів.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Чи включений SSL сертифікат?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#sharedFAQ">
                            <div class="accordion-body">
                                Так, всі наші тарифи включають безкоштовний SSL сертифікат Let's Encrypt, який автоматично встановлюється та продовжується для всіх доменів.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Як швидко активується хостинг?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#sharedFAQ">
                            <div class="accordion-body">
                                Хостинг активується миттєво після підтвердження оплати. Дані для доступу до cPanel надсилаються на вашу електронну пошту протягом 5-10 хвилин.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Чи можна перенести сайт з іншого хостингу?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#sharedFAQ">
                            <div class="accordion-body">
                                Так, ми надаємо безкоштовну послугу перенесення сайту з іншого хостингу. Наші спеціалісти виконають міграцію протягом 24 годин після замовлення.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="cta-content">
                    <h2 class="mb-4">Готові запустити свій сайт?</h2>
                    <p class="lead mb-4">
                        Приєднуйтесь до понад 1000+ задоволених клієнтів StormHosting UA. 
                        Миттєва активація, професійна підтримка та 30-денна гарантія повернення коштів.
                    </p>
                    
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="#plans" class="btn btn-primary btn-lg">
                            <i class="bi bi-rocket-takeoff"></i>
                            Обрати тариф
                        </a>
                        <a href="/pages/contacts.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-chat-dots"></i>
                            Отримати консультацію
                        </a>
                    </div>
                    
                    <div class="trust-indicators mt-4">
                        <div class="row g-3 align-items-center justify-content-center">
                            <div class="col-auto">
                                <i class="bi bi-shield-check"></i>
                                <span>99.9% Uptime</span>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people"></i>
                                <span>1000+ клієнтів</span>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-award"></i>
                                <span>5 років на ринку</span>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-headset"></i>
                                <span>Підтримка 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// JavaScript для перемикання тарифів (місячні/річні)
document.addEventListener('DOMContentLoaded', function() {
    const monthlyRadio = document.getElementById('monthly');
    const yearlyRadio = document.getElementById('yearly');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const yearlyPrices = document.querySelectorAll('.yearly-price');
    
    function togglePricing() {
        if (yearlyRadio && yearlyRadio.checked) {
            monthlyPrices.forEach(price => price.classList.add('d-none'));
            yearlyPrices.forEach(price => price.classList.remove('d-none'));
        } else {
            monthlyPrices.forEach(price => price.classList.remove('d-none'));
            yearlyPrices.forEach(price => price.classList.add('d-none'));
        }
    }
    
    if (monthlyRadio) monthlyRadio.addEventListener('change', togglePricing);
    if (yearlyRadio) yearlyRadio.addEventListener('change', togglePricing);
    
    // 
    // ====== ЗАГЛУШКА ДЛЯ ОБРОБКИ ЗАМОВЛЕНЬ ======
    // Обробник кліків по кнопках замовлення
    //
    document.querySelectorAll('.btn-order').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const planId = this.dataset.planId;
            const planName = this.dataset.planName;
            const monthlyPrice = this.dataset.monthlyPrice;
            const yearlyPrice = this.dataset.yearlyPrice;
            const isYearly = yearlyRadio && yearlyRadio.checked;
            
            // ТУТ БУДЕ ІНТЕГРАЦІЯ З ПЛАТІЖНОЮ СИСТЕМОЮ
            // Приклад даних для передачі:
            const orderData = {
                plan_id: planId,
                plan_name: planName,
                billing_period: isYearly ? 'yearly' : 'monthly',
                price: isYearly ? yearlyPrice : monthlyPrice,
                service_type: 'shared_hosting'
            };
            
            console.log('Order data for payment gateway:', orderData);
            
            // ТИМЧАСОВА ЗАГЛУШКА - показуємо alert
            alert(`Замовлення плану: ${planName}\nВартість: ${isYearly ? Math.round(yearlyPrice/12) : monthlyPrice} грн/${isYearly ? 'міс (річна оплата)' : 'міс'}\n\nТут буде перенаправлення на оплату!`);
            
            // ТУТ БУДЕ КОД ІНТЕГРАЦІЇ З:
            // - LiqPay
            // - Fondy (WayForPay)  
            // - Portmone
            // - Або інша платіжна система
            
            /*
            Приклад інтеграції з LiqPay:
            
            const liqpayData = {
                action: 'pay',
                amount: orderData.price,
                currency: 'UAH',
                description: `Хостинг план ${orderData.plan_name}`,
                order_id: 'order_' + Date.now(),
                version: '3'
            };
            
            // Відправка на LiqPay
            window.location.href = '/payment/liqpay.php?' + new URLSearchParams(liqpayData);
            */
        });
    });
    
    // Smooth scroll для кнопки "Переглянути тарифи"
    document.querySelectorAll('a[href="#plans"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.getElementById('plans');
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>

</body>
</html>

<?php 
// Підключення footer якщо файл існує
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php')) {
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
}
?>