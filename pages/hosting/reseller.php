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
    <title>Реселерський хостинг - StormHosting UA</title>
    <meta name="description" content="Станьте партнером StormHosting. Реселерський хостинг з ISPManager, білий лейбл, підтримка 24/7. Заробляйте на перепродажі хостингу.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="../../assets/css/main.css">
    <!-- Reseller Hosting CSS -->
    <link rel="stylesheet" href="../../assets/css/pages/hosting-reseller.css">
</head>
<body>

<!-- Hero Section -->
<section class="reseller-hero">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <div class="hero-badge mb-3">
                    <i class="bi bi-award"></i> Партнерська програма
                </div>
                <h1 class="display-4 fw-bold text-white mb-4">
                    Заробляйте на хостингу разом з нами
                </h1>
                <p class="lead text-white-50 mb-4">
                    Станьте реселером StormHosting та отримуйте до 50% від кожного платежу. 
                    Повний контроль, білий лейбл, технічна підтримка 24/7.
                </p>
                <div class="hero-features mb-4">
                    <div class="feature-item">
                        <i class="bi bi-check-circle"></i>
                        <span>WHM/cPanel доступ</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle"></i>
                        <span>Білий лейбл</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle"></i>
                        <span>Підтримка 24/7</span>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <a href="#plans" class="btn btn-primary btn-lg">
                        <i class="bi bi-rocket-takeoff me-2"></i>Обрати план
                    </a>
                    <a href="#calculator" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-calculator me-2"></i>Розрахувати прибуток
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="reseller-illustration">
                    <div class="partner-network">
                        <div class="central-node">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="partner-node" style="--delay: 0">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="partner-node" style="--delay: 1">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="partner-node" style="--delay: 2">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="partner-node" style="--delay: 3">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="connection-lines">
                            <div class="line line-1"></div>
                            <div class="line line-2"></div>
                            <div class="line line-3"></div>
                            <div class="line line-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Чому варто стати нашим партнером</h2>
            <p class="lead text-muted">Ми надаємо все необхідне для успішного бізнесу</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="benefit-card h-100">
                    <div class="benefit-icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <h4>Високі комісійні</h4>
                    <p>Отримуйте до 50% від кожного платежу клієнта. Чим більше клієнтів - тим вищий відсоток.</p>
                    <ul class="benefit-list">
                        <li>30% - перші 10 клієнтів</li>
                        <li>40% - від 11 до 50 клієнтів</li>
                        <li>50% - більше 50 клієнтів</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="benefit-card h-100">
                    <div class="benefit-icon">
                        <i class="bi bi-palette"></i>
                    </div>
                    <h4>Білий лейбл</h4>
                    <p>Використовуйте власний бренд, логотип та доменне ім'я. Ваші клієнти не знатимуть про нас.</p>
                    <ul class="benefit-list">
                        <li>Власний бренд</li>
                        <li>Кастомізація панелі</li>
                        <li>Власні NS-сервери</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="benefit-card h-100">
                    <div class="benefit-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4>Технічна підтримка</h4>
                    <p>Ми беремо на себе всю технічну підтримку ваших клієнтів 24/7 українською мовою.</p>
                    <ul class="benefit-list">
                        <li>Підтримка 24/7</li>
                        <li>Середній час відповіді 15 хв</li>
                        <li>Вирішення 99% проблем</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="benefit-card h-100">
                    <div class="benefit-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Надійна інфраструктура</h4>
                    <p>Сервери в Україні та ЄС, 99.9% uptime, щоденні бекапи, DDoS-захист.</p>
                    <ul class="benefit-list">
                        <li>99.9% uptime гарантія</li>
                        <li>NVMe SSD диски</li>
                        <li>CloudFlare захист</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="benefit-card h-100">
                    <div class="benefit-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h4>Маркетингова підтримка</h4>
                    <p>Отримуйте готові маркетингові матеріали, банери, лендінги та рекламні кампанії.</p>
                    <ul class="benefit-list">
                        <li>Готові лендінги</li>
                        <li>Рекламні матеріали</li>
                        <li>Email шаблони</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="benefit-card h-100">
                    <div class="benefit-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h4>Персональний менеджер</h4>
                    <p>Кожен партнер отримує персонального менеджера для консультацій та допомоги.</p>
                    <ul class="benefit-list">
                        <li>Персональний менеджер</li>
                        <li>Навчання та тренінги</li>
                        <li>Пріоритетна підтримка</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reseller Plans -->
<section id="plans" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Тарифні плани для реселерів</h2>
            <p class="lead text-muted">Оберіть план відповідно до ваших потреб</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="plan-card">
                    <div class="plan-header">
                        <h4>Reseller Start</h4>
                        <div class="plan-price">
                            <span class="currency">₴</span>
                            <span class="amount">1499</span>
                            <span class="period">/міс</span>
                        </div>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="bi bi-check-circle text-success"></i> 50 ГБ SSD диску</li>
                            <li><i class="bi bi-check-circle text-success"></i> 500 ГБ трафіку</li>
                            <li><i class="bi bi-check-circle text-success"></i> До 25 акаунтів cPanel</li>
                            <li><i class="bi bi-check-circle text-success"></i> WHM доступ</li>
                            <li><i class="bi bi-check-circle text-success"></i> Безкоштовний SSL</li>
                            <li><i class="bi bi-check-circle text-success"></i> Softaculous</li>
                            <li><i class="bi bi-check-circle text-success"></i> Email підтримка</li>
                        </ul>
                    </div>
                    <button class="btn btn-outline-primary w-100" onclick="orderReseller('start')">
                        Замовити план
                    </button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="plan-card popular">
                    <div class="popular-badge">Популярний</div>
                    <div class="plan-header">
                        <h4>Reseller Pro</h4>
                        <div class="plan-price">
                            <span class="currency">₴</span>
                            <span class="amount">2999</span>
                            <span class="period">/міс</span>
                        </div>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="bi bi-check-circle text-success"></i> 150 ГБ SSD диску</li>
                            <li><i class="bi bi-check-circle text-success"></i> 1500 ГБ трафіку</li>
                            <li><i class="bi bi-check-circle text-success"></i> До 75 акаунтів cPanel</li>
                            <li><i class="bi bi-check-circle text-success"></i> WHM доступ</li>
                            <li><i class="bi bi-check-circle text-success"></i> Безкоштовний SSL</li>
                            <li><i class="bi bi-check-circle text-success"></i> Softaculous Pro</li>
                            <li><i class="bi bi-check-circle text-success"></i> Білий лейбл</li>
                            <li><i class="bi bi-check-circle text-success"></i> Пріоритетна підтримка</li>
                        </ul>
                    </div>
                    <button class="btn btn-primary w-100" onclick="orderReseller('pro')">
                        Замовити план
                    </button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="plan-card">
                    <div class="plan-header">
                        <h4>Reseller Business</h4>
                        <div class="plan-price">
                            <span class="currency">₴</span>
                            <span class="amount">4999</span>
                            <span class="period">/міс</span>
                        </div>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="bi bi-check-circle text-success"></i> 300 ГБ SSD диску</li>
                            <li><i class="bi bi-check-circle text-success"></i> 3000 ГБ трафіку</li>
                            <li><i class="bi bi-check-circle text-success"></i> До 150 акаунтів cPanel</li>
                            <li><i class="bi bi-check-circle text-success"></i> WHM доступ</li>
                            <li><i class="bi bi-check-circle text-success"></i> Безкоштовний SSL</li>
                            <li><i class="bi bi-check-circle text-success"></i> Softaculous Pro</li>
                            <li><i class="bi bi-check-circle text-success"></i> Білий лейбл</li>
                            <li><i class="bi bi-check-circle text-success"></i> WHMCS ліцензія</li>
                            <li><i class="bi bi-check-circle text-success"></i> Персональний менеджер</li>
                        </ul>
                    </div>
                    <button class="btn btn-outline-primary w-100" onclick="orderReseller('business')">
                        Замовити план
                    </button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="plan-card">
                    <div class="plan-header">
                        <h4>Reseller Enterprise</h4>
                        <div class="plan-price">
                            <span class="currency">₴</span>
                            <span class="amount">9999</span>
                            <span class="period">/міс</span>
                        </div>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="bi bi-check-circle text-success"></i> Необмежений SSD</li>
                            <li><i class="bi bi-check-circle text-success"></i> Необмежений трафік</li>
                            <li><i class="bi bi-check-circle text-success"></i> Необмежені акаунти</li>
                            <li><i class="bi bi-check-circle text-success"></i> WHM доступ</li>
                            <li><i class="bi bi-check-circle text-success"></i> Безкоштовний SSL</li>
                            <li><i class="bi bi-check-circle text-success"></i> Softaculous Pro</li>
                            <li><i class="bi bi-check-circle text-success"></i> Білий лейбл</li>
                            <li><i class="bi bi-check-circle text-success"></i> WHMCS ліцензія</li>
                            <li><i class="bi bi-check-circle text-success"></i> Виділений сервер</li>
                            <li><i class="bi bi-check-circle text-success"></i> VIP підтримка</li>
                        </ul>
                    </div>
                    <button class="btn btn-outline-primary w-100" onclick="orderReseller('enterprise')">
                        Замовити план
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Profit Calculator -->
<section id="calculator" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Калькулятор прибутку</h2>
            <p class="lead text-muted">Розрахуйте ваш потенційний дохід</p>
        </div>
        
        <div class="calculator-container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="calculator-panel">
                        <h4 class="mb-4">Параметри розрахунку</h4>
                        
                        <!-- Кількість клієнтів -->
                        <div class="calculator-field mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-people text-primary"></i> Кількість клієнтів
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="range" class="form-range flex-grow-1" id="clients-slider" 
                                       min="5" max="200" value="25" step="5">
                                <div class="value-display">
                                    <span id="clients-value">25</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">5 клієнтів</small>
                                <small class="text-muted">200 клієнтів</small>
                            </div>
                        </div>
                        
                        <!-- Середня вартість -->
                        <div class="calculator-field mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-cash text-primary"></i> Середня вартість послуг
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="range" class="form-range flex-grow-1" id="price-slider" 
                                       min="100" max="1000" value="300" step="50">
                                <div class="value-display">
                                    ₴<span id="price-value">300</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">₴100/міс</small>
                                <small class="text-muted">₴1000/міс</small>
                            </div>
                        </div>
                        
                        <!-- Тип плану -->
                        <div class="calculator-field mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-box text-primary"></i> Ваш реселерський план
                            </label>
                            <select class="form-select" id="reseller-plan">
                                <option value="start">Reseller Start (30% комісії)</option>
                                <option value="pro" selected>Reseller Pro (40% комісії)</option>
                                <option value="business">Reseller Business (45% комісії)</option>
                                <option value="enterprise">Reseller Enterprise (50% комісії)</option>
                            </select>
                        </div>
                        
                        <!-- Додаткові послуги -->
                        <h5 class="mb-3">Додаткові послуги</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ssl-sales" checked>
                                    <label class="form-check-label" for="ssl-sales">
                                        Продаж SSL сертифікатів
                                        <span class="text-muted">(+₴50/клієнт)</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="domain-sales" checked>
                                    <label class="form-check-label" for="domain-sales">
                                        Реєстрація доменів
                                        <span class="text-muted">(+₴100/клієнт)</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="backup-sales">
                                    <label class="form-check-label" for="backup-sales">
                                        Додаткові бекапи
                                        <span class="text-muted">(+₴30/клієнт)</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="support-sales">
                                    <label class="form-check-label" for="support-sales">
                                        Преміум підтримка
                                        <span class="text-muted">(+₴75/клієнт)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="profit-summary">
                        <h4 class="mb-4">Ваш прибуток</h4>
                        
                        <div class="profit-details">
                            <div class="profit-item">
                                <span>Клієнтів:</span>
                                <strong id="summary-clients">25</strong>
                            </div>
                            <div class="profit-item">
                                <span>Середній чек:</span>
                                <strong>₴<span id="summary-price">300</span></strong>
                            </div>
                            <div class="profit-item">
                                <span>Комісія:</span>
                                <strong id="summary-commission">40%</strong>
                            </div>
                            <div class="profit-item">
                                <span>Додаткові послуги:</span>
                                <strong>₴<span id="summary-additional">3750</span></strong>
                            </div>
                        </div>
                        
                        <div class="profit-total">
                            <div class="monthly-profit">
                                <span class="label">Прибуток за місяць:</span>
                                <div class="amount">
                                    <span class="currency">₴</span>
                                    <span class="value" id="monthly-profit">6750</span>
                                </div>
                            </div>
                            
                            <div class="yearly-profit mt-3">
                                <span class="label">Прибуток за рік:</span>
                                <div class="amount">
                                    <span class="currency">₴</span>
                                    <span class="value" id="yearly-profit">81000</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profit-chart mt-4">
                            <canvas id="profitChart" width="300" height="200"></canvas>
                        </div>
                        
                        <button class="btn btn-primary btn-lg w-100 mt-4" onclick="startPartnership()">
                            <i class="bi bi-handshake me-2"></i>Стати партнером
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Як це працює</h2>
            <p class="lead text-muted">Простий процес початку співпраці</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5>Реєстрація</h5>
                    <p>Заповніть форму та оберіть тарифний план</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>Налаштування</h5>
                    <p>Отримайте доступ до WHM та налаштуйте бренд</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Продажі</h5>
                    <p>Починайте продавати хостинг під своїм брендом</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h5>Прибуток</h5>
                    <p>Отримуйте щомісячний прибуток від клієнтів</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Часті питання</h2>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="resellerFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Які технічні знання потрібні для початку?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#resellerFAQ">
                            <div class="accordion-body">
                                Спеціальні технічні знання не потрібні. Ми надаємо повне навчання по роботі з WHM/cPanel, 
                                а всю технічну підтримку клієнтів беремо на себе. Вам потрібно лише займатися продажами.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Чи можу я встановлювати власні ціни?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#resellerFAQ">
                            <div class="accordion-body">
                                Так, ви повністю контролюєте ціноутворення. Встановлюйте будь-які ціни для ваших клієнтів. 
                                Ваш прибуток = різниця між вашою ціною та вартістю реселерського плану.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Як працює технічна підтримка?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#resellerFAQ">
                            <div class="accordion-body">
                                Ваші клієнти можуть звертатися безпосередньо до нашої підтримки 24/7. 
                                Ми вирішуємо всі технічні питання від вашого імені. Клієнти не знатимуть, що ви реселер.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Чи можу я перенести існуючих клієнтів?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#resellerFAQ">
                            <div class="accordion-body">
                                Так, ми безкоштовно перенесемо всіх ваших існуючих клієнтів з будь-якого хостингу. 
                                Процес займає 24-48 годин без простоїв сайтів.
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
            <h2 class="display-5 fw-bold mb-4">Готові розпочати?</h2>
            <p class="lead mb-4">
                Приєднуйтесь до сотень успішних партнерів та почніть заробляти вже сьогодні
            </p>
            <div class="d-flex gap-3 justify-content-center">
                <button class="btn btn-light btn-lg" onclick="showPartnerForm()">
                    <i class="bi bi-person-plus me-2"></i>Стати партнером
                </button>
                <a href="/pages/info/contacts.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-telephone me-2"></i>Зв'язатись з нами
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Partner Form Modal -->
<div class="modal fade" id="partnerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Заявка на партнерство</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="partnerForm">
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Назва компанії</label>
                            <input type="text" class="form-control" name="company">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Веб-сайт</label>
                            <input type="url" class="form-control" name="website">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telegram</label>
                            <input type="text" class="form-control" name="telegram" placeholder="@username">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Обраний план</label>
                            <select class="form-select" name="plan">
                                <option value="start">Reseller Start</option>
                                <option value="pro" selected>Reseller Pro</option>
                                <option value="business">Reseller Business</option>
                                <option value="enterprise">Reseller Enterprise</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Досвід у хостингу</label>
                            <textarea class="form-control" name="experience" rows="3" 
                                      placeholder="Розкажіть про ваш досвід роботи з хостингом"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    Я погоджуюсь з умовами партнерської програми
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" onclick="submitPartnerForm()">
                    <i class="bi bi-send me-2"></i>Відправити заявку
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js для графіків -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Reseller Hosting JS -->
<script src="../../assets/js/pages/hosting-reseller.js"></script>
 <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

</body>
</html>
