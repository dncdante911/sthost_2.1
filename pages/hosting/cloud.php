<!DOCTYPE html>
<?php
define('SECURE_ACCESS', true);

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// остальные переменные

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Хмарний хостинг - StormHosting UA</title>
    <meta name="description" content="Масштабований хмарний хостинг з гарантованими ресурсами. Автоматичне масштабування, SSD накопичувачі, CDN. Калькулятор вартості.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="../../assets/css/main.css">
    <!-- Cloud Hosting CSS -->
    <link rel="stylesheet" href="../../assets/css/pages/hosting-cloud.css">
</head>
<body>

<!-- Hero Section -->
<section class="cloud-hero">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-white mb-4">
                    Хмарний хостинг нового покоління
                </h1>
                <p class="lead text-white-50 mb-4">
                    Масштабований хостинг з гарантованими ресурсами для проектів будь-якої складності. 
                    Автоматичне резервування, миттєве масштабування та максимальна продуктивність.
                </p>
                <div class="d-flex gap-3">
                    <a href="#calculator" class="btn btn-primary btn-lg">
                        <i class="bi bi-calculator me-2"></i>Розрахувати вартість
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-info-circle me-2"></i>Докладніше
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="cloud-animation">
                    <div class="cloud-server">
                        <div class="server-node" data-delay="0">
                            <i class="bi bi-server"></i>
                        </div>
                        <div class="server-node" data-delay="200">
                            <i class="bi bi-hdd-network"></i>
                        </div>
                        <div class="server-node" data-delay="400">
                            <i class="bi bi-database"></i>
                        </div>
                        <div class="server-node" data-delay="600">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                    <div class="connection-lines">
                        <svg viewBox="0 0 400 400">
                            <path class="line line-1" d="M100,100 L300,100 L300,300 L100,300 Z" />
                            <path class="line line-2" d="M150,50 L350,250" />
                            <path class="line line-3" d="M50,150 L250,350" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Переваги хмарного хостингу</h2>
            <p class="lead text-muted">Технології майбутнього для вашого бізнесу</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon gradient-1">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h4>Миттєве масштабування</h4>
                    <p>Збільшуйте або зменшуйте ресурси в реальному часі відповідно до навантаження</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon gradient-2">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h4>Максимальна безпека</h4>
                    <p>DDoS-захист, SSL-сертифікати, резервне копіювання та моніторинг 24/7</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon gradient-3">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h4>99.99% Uptime</h4>
                    <p>Гарантована доступність завдяки розподіленій архітектурі та резервуванню</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon gradient-4">
                        <i class="bi bi-globe"></i>
                    </div>
                    <h4>Глобальна CDN</h4>
                    <p>Швидка доставка контенту користувачам по всьому світу через CloudFlare</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon gradient-5">
                        <i class="bi bi-cpu"></i>
                    </div>
                    <h4>Виділені ресурси</h4>
                    <p>Гарантовані CPU, RAM та дисковий простір без впливу сусідніх сайтів</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="feature-icon gradient-6">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h4>Оплата за використання</h4>
                    <p>Платіть лише за спожиті ресурси без прихованих платежів</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Calculator Section -->
<section id="calculator" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Калькулятор вартості</h2>
            <p class="lead text-muted">Налаштуйте конфігурацію під ваші потреби</p>
        </div>
        
        <div class="calculator-container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="calculator-panel">
                        <h4 class="mb-4">Налаштування ресурсів</h4>
                        
                        <!-- CPU -->
                        <div class="resource-slider mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="fw-semibold">
                                    <i class="bi bi-cpu text-primary"></i> Процесор (vCPU)
                                </label>
                                <div class="value-display">
                                    <span id="cpu-value">2</span> ядра
                                </div>
                            </div>
                            <input type="range" class="form-range" id="cpu-slider" min="1" max="16" value="2" step="1">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">1 ядро</small>
                                <small class="text-muted">16 ядер</small>
                            </div>
                        </div>
                        
                        <!-- RAM -->
                        <div class="resource-slider mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="fw-semibold">
                                    <i class="bi bi-memory text-primary"></i> Оперативна пам'ять
                                </label>
                                <div class="value-display">
                                    <span id="ram-value">4</span> ГБ
                                </div>
                            </div>
                            <input type="range" class="form-range" id="ram-slider" min="1" max="64" value="4" step="1">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">1 ГБ</small>
                                <small class="text-muted">64 ГБ</small>
                            </div>
                        </div>
                        
                        <!-- Storage -->
                        <div class="resource-slider mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="fw-semibold">
                                    <i class="bi bi-hdd text-primary"></i> SSD диск
                                </label>
                                <div class="value-display">
                                    <span id="storage-value">50</span> ГБ
                                </div>
                            </div>
                            <input type="range" class="form-range" id="storage-slider" min="10" max="1000" value="50" step="10">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">10 ГБ</small>
                                <small class="text-muted">1000 ГБ</small>
                            </div>
                        </div>
                        
                        <!-- Bandwidth -->
                        <div class="resource-slider mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="fw-semibold">
                                    <i class="bi bi-arrow-left-right text-primary"></i> Трафік
                                </label>
                                <div class="value-display">
                                    <span id="bandwidth-value">1000</span> ГБ/міс
                                </div>
                            </div>
                            <input type="range" class="form-range" id="bandwidth-slider" min="100" max="10000" value="1000" step="100">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">100 ГБ</small>
                                <small class="text-muted">10 ТБ</small>
                            </div>
                        </div>
                        
                        <!-- Additional Options -->
                        <h5 class="mb-3">Додаткові опції</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check option-check">
                                    <input class="form-check-input" type="checkbox" id="backup" data-price="150">
                                    <label class="form-check-label" for="backup">
                                        <span class="option-title">Щоденне резервне копіювання</span>
                                        <span class="option-price">+150 ₴/міс</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check option-check">
                                    <input class="form-check-input" type="checkbox" id="monitoring" data-price="100">
                                    <label class="form-check-label" for="monitoring">
                                        <span class="option-title">Розширений моніторинг</span>
                                        <span class="option-price">+100 ₴/міс</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check option-check">
                                    <input class="form-check-input" type="checkbox" id="ssl" data-price="200">
                                    <label class="form-check-label" for="ssl">
                                        <span class="option-title">SSL Wildcard сертифікат</span>
                                        <span class="option-price">+200 ₴/міс</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check option-check">
                                    <input class="form-check-input" type="checkbox" id="cdn" data-price="250">
                                    <label class="form-check-label" for="cdn">
                                        <span class="option-title">Premium CDN</span>
                                        <span class="option-price">+250 ₴/міс</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="price-summary sticky-top">
                        <h4 class="mb-4">Ваша конфігурація</h4>
                        
                        <div class="config-details">
                            <div class="config-item">
                                <span>Процесор:</span>
                                <strong><span id="summary-cpu">2</span> vCPU</strong>
                            </div>
                            <div class="config-item">
                                <span>Пам'ять:</span>
                                <strong><span id="summary-ram">4</span> ГБ RAM</strong>
                            </div>
                            <div class="config-item">
                                <span>Диск:</span>
                                <strong><span id="summary-storage">50</span> ГБ SSD</strong>
                            </div>
                            <div class="config-item">
                                <span>Трафік:</span>
                                <strong><span id="summary-bandwidth">1000</span> ГБ/міс</strong>
                            </div>
                        </div>
                        
                        <div class="selected-options mt-3" id="selected-options">
                            <!-- Selected options will appear here -->
                        </div>
                        
                        <div class="price-total">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Вартість за місяць:</span>
                                <div class="price-amount">
                                    <span class="currency">₴</span>
                                    <span class="amount" id="monthly-price">850</span>
                                </div>
                            </div>
                            
                            <div class="billing-period mb-4">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="period" id="monthly" checked>
                                    <label class="btn btn-outline-primary" for="monthly">Щомісяця</label>
                                    
                                    <input type="radio" class="btn-check" name="period" id="yearly">
                                    <label class="btn btn-outline-primary" for="yearly">
                                        Щорічно
                                        <span class="badge bg-success ms-1">-15%</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="yearly-info d-none" id="yearly-info">
                                <div class="alert alert-success">
                                    <small>
                                        <i class="bi bi-check-circle me-1"></i>
                                        Економія: <strong>₴<span id="yearly-savings">0</span></strong> на рік
                                    </small>
                                </div>
                            </div>
                            
                            <button class="btn btn-primary btn-lg w-100 mb-2" onclick="orderCloud()">
                                <i class="bi bi-cart-plus me-2"></i>Замовити хостинг
                            </button>
                            
                            <button class="btn btn-outline-secondary w-100" onclick="saveConfiguration()">
                                <i class="bi bi-bookmark me-2"></i>Зберегти конфігурацію
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ready Configurations -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Готові конфігурації</h2>
            <p class="lead text-muted">Оберіть оптимальний план для вашого проекту</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="config-card">
                    <div class="config-header">
                        <h4>Cloud Start</h4>
                        <p class="text-muted">Для невеликих проектів</p>
                    </div>
                    <div class="config-price">
                        <span class="currency">₴</span>
                        <span class="amount">399</span>
                        <span class="period">/міс</span>
                    </div>
                    <ul class="config-features">
                        <li><i class="bi bi-check-circle text-success"></i> 1 vCPU</li>
                        <li><i class="bi bi-check-circle text-success"></i> 2 ГБ RAM</li>
                        <li><i class="bi bi-check-circle text-success"></i> 25 ГБ SSD</li>
                        <li><i class="bi bi-check-circle text-success"></i> 500 ГБ трафіку</li>
                        <li><i class="bi bi-check-circle text-success"></i> Безкоштовний SSL</li>
                    </ul>
                    <button class="btn btn-outline-primary w-100" onclick="selectConfig('start')">
                        Обрати план
                    </button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="config-card popular">
                    <div class="popular-badge">Популярний</div>
                    <div class="config-header">
                        <h4>Cloud Business</h4>
                        <p class="text-muted">Для бізнес-сайтів</p>
                    </div>
                    <div class="config-price">
                        <span class="currency">₴</span>
                        <span class="amount">799</span>
                        <span class="period">/міс</span>
                    </div>
                    <ul class="config-features">
                        <li><i class="bi bi-check-circle text-success"></i> 2 vCPU</li>
                        <li><i class="bi bi-check-circle text-success"></i> 4 ГБ RAM</li>
                        <li><i class="bi bi-check-circle text-success"></i> 50 ГБ SSD</li>
                        <li><i class="bi bi-check-circle text-success"></i> 1 ТБ трафіку</li>
                        <li><i class="bi bi-check-circle text-success"></i> Безкоштовний SSL</li>
                        <li><i class="bi bi-check-circle text-success"></i> Щоденні бекапи</li>
                    </ul>
                    <button class="btn btn-primary w-100" onclick="selectConfig('business')">
                        Обрати план
                    </button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="config-card">
                    <div class="config-header">
                        <h4>Cloud Pro</h4>
                        <p class="text-muted">Для інтернет-магазинів</p>
                    </div>
                    <div class="config-price">
                        <span class="currency">₴</span>
                        <span class="amount">1499</span>
                        <span class="period">/міс</span>
                    </div>
                    <ul class="config-features">
                        <li><i class="bi bi-check-circle text-success"></i> 4 vCPU</li>
                        <li><i class="bi bi-check-circle text-success"></i> 8 ГБ RAM</li>
                        <li><i class="bi bi-check-circle text-success"></i> 100 ГБ SSD</li>
                        <li><i class="bi bi-check-circle text-success"></i> 2 ТБ трафіку</li>
                        <li><i class="bi bi-check-circle text-success"></i> Безкоштовний SSL</li>
                        <li><i class="bi bi-check-circle text-success"></i> Щоденні бекапи</li>
                        <li><i class="bi bi-check-circle text-success"></i> Premium CDN</li>
                    </ul>
                    <button class="btn btn-outline-primary w-100" onclick="selectConfig('pro')">
                        Обрати план
                    </button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="config-card">
                    <div class="config-header">
                        <h4>Cloud Enterprise</h4>
                        <p class="text-muted">Для корпорацій</p>
                    </div>
                    <div class="config-price">
                        <span class="currency">₴</span>
                        <span class="amount">2999</span>
                        <span class="period">/міс</span>
                    </div>
                    <ul class="config-features">
                        <li><i class="bi bi-check-circle text-success"></i> 8 vCPU</li>
                        <li><i class="bi bi-check-circle text-success"></i> 16 ГБ RAM</li>
                        <li><i class="bi bi-check-circle text-success"></i> 200 ГБ SSD</li>
                        <li><i class="bi bi-check-circle text-success"></i> 5 ТБ трафіку</li>
                        <li><i class="bi bi-check-circle text-success"></i> Безкоштовний SSL</li>
                        <li><i class="bi bi-check-circle text-success"></i> Щоденні бекапи</li>
                        <li><i class="bi bi-check-circle text-success"></i> Premium CDN</li>
                        <li><i class="bi bi-check-circle text-success"></i> Пріоритетна підтримка</li>
                    </ul>
                    <button class="btn btn-outline-primary w-100" onclick="selectConfig('enterprise')">
                        Обрати план
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technologies -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Технології та інфраструктура</h2>
            <p class="lead text-muted">Використовуємо найкращі рішення для вашого успіху</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="tech-card text-center">
                    <img src="/assets/images/tech/kubernetes.svg" alt="Kubernetes" class="tech-logo">
                    <h5>Kubernetes</h5>
                    <p class="text-muted small">Оркестрація контейнерів для автоматичного масштабування</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="tech-card text-center">
                    <img src="/assets/images/tech/docker.svg" alt="Docker" class="tech-logo">
                    <h5>Docker</h5>
                    <p class="text-muted small">Контейнеризація додатків для швидкого розгортання</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="tech-card text-center">
                    <img src="/assets/images/tech/cloudflare.svg" alt="CloudFlare" class="tech-logo">
                    <h5>CloudFlare CDN</h5>
                    <p class="text-muted small">Глобальна мережа доставки контенту</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="tech-card text-center">
                    <img src="/assets/images/tech/nvme.svg" alt="NVMe" class="tech-logo">
                    <h5>NVMe SSD</h5>
                    <p class="text-muted small">Надшвидкі диски для максимальної продуктивності</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Migration Service -->
<section class="py-5">
    <div class="container">
        <div class="migration-banner">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-3">Безкоштовна міграція</h3>
                    <p class="mb-0">
                        Наші спеціалісти безкоштовно перенесуть ваш сайт з будь-якого хостингу. 
                        Процес займає до 24 годин без простоїв та втрати даних.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <button class="btn btn-light btn-lg" onclick="requestMigration()">
                        <i class="bi bi-arrow-right-circle me-2"></i>Замовити перенесення
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Часті питання</h2>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="cloudFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Що таке хмарний хостинг?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#cloudFAQ">
                            <div class="accordion-body">
                                Хмарний хостинг - це розміщення вашого сайту на кластері серверів, що працюють як єдине ціле. 
                                Це забезпечує високу доступність, автоматичне масштабування та розподіл навантаження.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Які переваги перед звичайним хостингом?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#cloudFAQ">
                            <div class="accordion-body">
                                Хмарний хостинг надає виділені ресурси, автоматичне масштабування, вищу надійність через 
                                розподілену архітектуру та можливість оплати лише за використані ресурси.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Чи включено резервне копіювання?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#cloudFAQ">
                            <div class="accordion-body">
                                Базове резервне копіювання раз на тиждень включено в усі тарифи. 
                                Щоденні бекапи доступні як додаткова опція за 150 ₴/міс.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Яка панель управління використовується?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#cloudFAQ">
                            <div class="accordion-body">
                                Ми надаємо доступ до cPanel або ISPmanager на ваш вибір. 
                                Також доступна власна панель управління з API для автоматизації.
                            </div>
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
        <div class="cta-content text-center text-white">
            <h2 class="display-5 fw-bold mb-4">Готові почати?</h2>
            <p class="lead mb-4">
                Приєднуйтесь до тисяч задоволених клієнтів та отримайте 30 днів безкоштовного тестування
            </p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="#calculator" class="btn btn-light btn-lg">
                    <i class="bi bi-rocket-takeoff me-2"></i>Почати зараз
                </a>
                <a href="/pages/info/contacts.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-headset me-2"></i>Зв'язатись з нами
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Замовлення хмарного хостингу</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="order-summary mb-4">
                    <h6>Обрана конфігурація:</h6>
                    <div id="order-config-details"></div>
                </div>
                
                <form id="orderForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ім'я *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Прізвище *</label>
                            <input type="text" class="form-control" name="surname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Телефон *</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Домен (якщо є)</label>
                            <input type="text" class="form-control" name="domain" placeholder="example.com.ua">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Коментар</label>
                            <textarea class="form-control" name="comment" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" onclick="submitOrder()">
                    <i class="bi bi-check-circle me-2"></i>Підтвердити замовлення
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Cloud Hosting JS -->
<script src="../../assets/js/pages/hosting-cloud.js"></script>
 <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>