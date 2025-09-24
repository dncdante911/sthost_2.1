<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'calculator';
$page_title = 'Калькулятор хостингу - StormHosting UA';
$meta_description = 'Розрахуйте вартість хостингу під ваші потреби. Віртуальний хостинг, VPS, виділені сервери. Миттєвий розрахунок ціни.';
$meta_keywords = 'калькулятор хостингу, розрахунок вартості, ціни на хостинг';

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- Підключення CSS для сторінки -->
<link rel="stylesheet" href="/assets/css/pages/hosting-calculator.css">

<!-- Hero Section -->
<section class="calculator-hero">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4 fw-bold text-white mb-4">
                Калькулятор хостингу
            </h1>
            <p class="lead text-white-50 mb-5">
                Підберіть оптимальну конфігурацію хостингу під ваші потреби та бюджет
            </p>
        </div>
    </div>
</section>

<!-- Main Calculator Section -->
<section class="py-5">
    <div class="container">
        <!-- Service Type Selection -->
        <div class="service-selector mb-5">
            <h3 class="text-center mb-4">Оберіть тип послуги</h3>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="service-card active" data-service="shared">
                        <div class="service-icon">
                            <i class="bi bi-hdd-network"></i>
                        </div>
                        <h5>Віртуальний хостинг</h5>
                        <p class="text-muted small">Для сайтів та блогів</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="service-card" data-service="vps">
                        <div class="service-icon">
                            <i class="bi bi-server"></i>
                        </div>
                        <h5>VPS/VDS</h5>
                        <p class="text-muted small">Віртуальні сервери</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="service-card" data-service="dedicated">
                        <div class="service-icon">
                            <i class="bi bi-pc-display"></i>
                        </div>
                        <h5>Виділений сервер</h5>
                        <p class="text-muted small">Фізичні сервери</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="service-card" data-service="cloud">
                        <div class="service-icon">
                            <i class="bi bi-cloud"></i>
                        </div>
                        <h5>Хмарний хостинг</h5>
                        <p class="text-muted small">Масштабовані рішення</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Configuration Panel -->
            <div class="col-lg-8">
                <div class="config-panel">
                    <h4 class="mb-4">
                        <i class="bi bi-sliders text-primary"></i> 
                        Налаштування конфігурації
                    </h4>
                    
                    <!-- Shared Hosting Configuration -->
                    <div id="shared-config" class="service-config active">
                        <div class="config-group">
                            <label class="form-label fw-semibold">Тарифний план</label>
                            <div class="plan-selector">
                                <div class="plan-option active" data-plan="start" data-price="99">
                                    <h6>Start</h6>
                                    <p class="mb-0 small">1 сайт, 5 ГБ</p>
                                </div>
                                <div class="plan-option" data-plan="basic" data-price="199">
                                    <h6>Basic</h6>
                                    <p class="mb-0 small">5 сайтів, 15 ГБ</p>
                                </div>
                                <div class="plan-option" data-plan="pro" data-price="399">
                                    <h6>Pro</h6>
                                    <p class="mb-0 small">Необмежено, 50 ГБ</p>
                                </div>
                                <div class="plan-option" data-plan="business" data-price="699">
                                    <h6>Business</h6>
                                    <p class="mb-0 small">Необмежено, 100 ГБ</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-group">
                            <label class="form-label fw-semibold">Додаткові опції</label>
                            <div class="options-grid">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="shared-ssl" data-price="99">
                                    <label class="form-check-label" for="shared-ssl">
                                        SSL сертифікат <span class="text-muted">(+99 ₴/міс)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="shared-backup" data-price="50">
                                    <label class="form-check-label" for="shared-backup">
                                        Щоденні бекапи <span class="text-muted">(+50 ₴/міс)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="shared-ip" data-price="150">
                                    <label class="form-check-label" for="shared-ip">
                                        Виділений IP <span class="text-muted">(+150 ₴/міс)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="shared-cdn" data-price="200">
                                    <label class="form-check-label" for="shared-cdn">
                                        CloudFlare Pro <span class="text-muted">(+200 ₴/міс)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- VPS Configuration -->
                    <div id="vps-config" class="service-config">
                        <div class="config-group">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-cpu"></i> Процесор (vCPU)
                            </label>
                            <div class="slider-container">
                                <input type="range" class="form-range" id="vps-cpu" min="1" max="16" value="2">
                                <div class="slider-values">
                                    <span>1</span>
                                    <span class="value-display" id="vps-cpu-value">2 ядра</span>
                                    <span>16</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-group">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-memory"></i> Оперативна пам'ять
                            </label>
                            <div class="slider-container">
                                <input type="range" class="form-range" id="vps-ram" min="1" max="32" value="4">
                                <div class="slider-values">
                                    <span>1 ГБ</span>
                                    <span class="value-display" id="vps-ram-value">4 ГБ</span>
                                    <span>32 ГБ</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-group">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-hdd"></i> SSD диск
                            </label>
                            <div class="slider-container">
                                <input type="range" class="form-range" id="vps-storage" min="20" max="500" value="50" step="10">
                                <div class="slider-values">
                                    <span>20 ГБ</span>
                                    <span class="value-display" id="vps-storage-value">50 ГБ</span>
                                    <span>500 ГБ</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-group">
                            <label class="form-label fw-semibold">Операційна система</label>
                            <select class="form-select" id="vps-os">
                                <option value="ubuntu">Ubuntu 22.04 LTS</option>
                                <option value="debian">Debian 11</option>
                                <option value="centos">CentOS 8</option>
                                <option value="windows" data-price="500">Windows Server (+500 ₴/міс)</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Dedicated Server Configuration -->
                    <div id="dedicated-config" class="service-config">
                        <div class="config-group">
                            <label class="form-label fw-semibold">Конфігурація сервера</label>
                            <div class="server-selector">
                                <div class="server-option active" data-price="4999">
                                    <h6>Entry Server</h6>
                                    <ul class="small mb-0">
                                        <li>Intel Xeon E3-1230</li>
                                        <li>16 GB RAM</li>
                                        <li>2x 500GB SSD</li>
                                        <li>100 Mbps</li>
                                    </ul>
                                </div>
                                <div class="server-option" data-price="7999">
                                    <h6>Power Server</h6>
                                    <ul class="small mb-0">
                                        <li>Intel Xeon E5-2680</li>
                                        <li>32 GB RAM</li>
                                        <li>2x 1TB SSD</li>
                                        <li>1 Gbps</li>
                                    </ul>
                                </div>
                                <div class="server-option" data-price="12999">
                                    <h6>Elite Server</h6>
                                    <ul class="small mb-0">
                                        <li>2x Intel Xeon Gold</li>
                                        <li>128 GB RAM</li>
                                        <li>4x 2TB NVMe</li>
                                        <li>10 Gbps</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-group">
                            <label class="form-label fw-semibold">Додаткові послуги</label>
                            <div class="options-grid">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dedicated-admin" data-price="1500">
                                    <label class="form-check-label" for="dedicated-admin">
                                        Адміністрування <span class="text-muted">(+1500 ₴/міс)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dedicated-backup" data-price="500">
                                    <label class="form-check-label" for="dedicated-backup">
                                        Резервне копіювання <span class="text-muted">(+500 ₴/міс)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dedicated-ddos" data-price="1000">
                                    <label class="form-check-label" for="dedicated-ddos">
                                        DDoS захист Pro <span class="text-muted">(+1000 ₴/міс)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cloud Hosting Configuration -->
                    <div id="cloud-config" class="service-config">
                        <div class="config-group">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-cpu"></i> vCPU ядра
                            </label>
                            <div class="slider-container">
                                <input type="range" class="form-range" id="cloud-cpu" min="1" max="32" value="4">
                                <div class="slider-values">
                                    <span>1</span>
                                    <span class="value-display" id="cloud-cpu-value">4 ядра</span>
                                    <span>32</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-group">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-memory"></i> RAM
                            </label>
                            <div class="slider-container">
                                <input type="range" class="form-range" id="cloud-ram" min="1" max="128" value="8">
                                <div class="slider-values">
                                    <span>1 ГБ</span>
                                    <span class="value-display" id="cloud-ram-value">8 ГБ</span>
                                    <span>128 ГБ</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-group">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-hdd"></i> Сховище
                            </label>
                            <div class="slider-container">
                                <input type="range" class="form-range" id="cloud-storage" min="10" max="2000" value="100" step="10">
                                <div class="slider-values">
                                    <span>10 ГБ</span>
                                    <span class="value-display" id="cloud-storage-value">100 ГБ</span>
                                    <span>2 ТБ</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-group">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-arrow-left-right"></i> Трафік
                            </label>
                            <div class="slider-container">
                                <input type="range" class="form-range" id="cloud-bandwidth" min="100" max="10000" value="1000" step="100">
                                <div class="slider-values">
                                    <span>100 ГБ</span>
                                    <span class="value-display" id="cloud-bandwidth-value">1 ТБ</span>
                                    <span>10 ТБ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Billing Period -->
                    <div class="config-group mt-4">
                        <label class="form-label fw-semibold">Період оплати</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="period" id="monthly" checked>
                            <label class="btn btn-outline-primary" for="monthly">Щомісяця</label>
                            
                            <input type="radio" class="btn-check" name="period" id="quarterly">
                            <label class="btn btn-outline-primary" for="quarterly">
                                Квартал <span class="badge bg-success">-5%</span>
                            </label>
                            
                            <input type="radio" class="btn-check" name="period" id="semiannual">
                            <label class="btn btn-outline-primary" for="semiannual">
                                Півроку <span class="badge bg-success">-10%</span>
                            </label>
                            
                            <input type="radio" class="btn-check" name="period" id="annual">
                            <label class="btn btn-outline-primary" for="annual">
                                Рік <span class="badge bg-success">-15%</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Price Summary -->
            <div class="col-lg-4">
                <div class="price-panel sticky-top">
                    <h4 class="mb-4">
                        <i class="bi bi-receipt text-primary"></i> 
                        Підсумок
                    </h4>
                    
                    <div class="summary-details">
                        <div id="summary-content">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>
                    
                    <div class="price-breakdown">
                        <div class="price-line">
                            <span>Базова вартість:</span>
                            <span id="base-price">99 ₴</span>
                        </div>
                        <div class="price-line">
                            <span>Додаткові опції:</span>
                            <span id="options-price">0 ₴</span>
                        </div>
                        <div class="price-line discount-line d-none">
                            <span>Знижка:</span>
                            <span id="discount-amount" class="text-success">-0 ₴</span>
                        </div>
                    </div>
                    
                    <div class="total-price">
                        <div class="price-label">Всього за місяць:</div>
                        <div class="price-value">
                            <span class="currency">₴</span>
                            <span id="total-price">99</span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary btn-lg w-100 mb-2" onclick="proceedToOrder()">
                            <i class="bi bi-cart-check me-2"></i>Замовити
                        </button>
                        <button class="btn btn-outline-secondary w-100" onclick="saveConfiguration()">
                            <i class="bi bi-save me-2"></i>Зберегти конфігурацію
                        </button>
                    </div>
                    
                    <div class="help-section mt-4">
                        <p class="text-muted small mb-2">Потрібна допомога з вибором?</p>
                        <a href="/pages/info/contacts.php" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-headset me-1"></i>Консультація спеціаліста
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Comparison Table -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Порівняння послуг</h2>
        <div class="table-responsive">
            <table class="table table-bordered comparison-table">
                <thead>
                    <tr>
                        <th>Параметр</th>
                        <th>Віртуальний хостинг</th>
                        <th>VPS/VDS</th>
                        <th>Виділений сервер</th>
                        <th>Хмарний хостинг</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Для кого</strong></td>
                        <td>Сайти, блоги</td>
                        <td>Веб-додатки</td>
                        <td>Великі проекти</td>
                        <td>Масштабовані проекти</td>
                    </tr>
                    <tr>
                        <td><strong>Ресурси</strong></td>
                        <td>Спільні</td>
                        <td>Виділені віртуальні</td>
                        <td>Повністю виділені</td>
                        <td>Гнучкі</td>
                    </tr>
                    <tr>
                        <td><strong>Масштабування</strong></td>
                        <td>Обмежене</td>
                        <td>Середнє</td>
                        <td>Ручне</td>
                        <td>Автоматичне</td>
                    </tr>
                    <tr>
                        <td><strong>Управління</strong></td>
                        <td>cPanel</td>
                        <td>Root доступ</td>
                        <td>Повний контроль</td>
                        <td>API + панель</td>
                    </tr>
                    <tr>
                        <td><strong>Ціна від</strong></td>
                        <td>99 ₴/міс</td>
                        <td>299 ₴/міс</td>
                        <td>4999 ₴/міс</td>
                        <td>399 ₴/міс</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Замовлення хостингу</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="order-summary" class="mb-4">
                    <!-- Order details will be inserted here -->
                </div>
                
                <form id="orderForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ім'я *</label>
                            <input type="text" class="form-control" name="name" required>
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
                            <label class="form-label">Домен</label>
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

<!-- Підключення JS для сторінки -->
<script src="/assets/js/pages/hosting-calculator.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>