<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'site-check';
$page_title = 'Перевірка доступності сайту - StormHosting UA';
$meta_description = 'Безкоштовний інструмент перевірки доступності сайту. Перевірте статус, час відповіді, HTTP коди з різних локацій.';
$meta_keywords = 'перевірка сайту, site checker, uptime monitor, доступність сайту, ping сайту';

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- Підключення CSS для сторінки -->
<link rel="stylesheet" href="/assets/css/pages/tools-site-check.css">

<!-- Site Check Hero -->
<section class="site-check-hero">
    <div class="container">
        <div class="text-center">
            <div class="tool-icon mb-3">
                <i class="bi bi-globe-americas"></i>
            </div>
            <h1 class="display-4 fw-bold text-white mb-4">
                Перевірка доступності сайту
            </h1>
            <p class="lead text-white-50 mb-5">
                Миттєва перевірка статусу вашого сайту з різних точок світу. 
                Дізнайтеся час відповіді, HTTP статус та доступність ресурсу.
            </p>
            
            <!-- Site Check Form -->
            <div class="site-check-form">
                <form id="siteCheckForm" method="post">
                    <div class="form-group">
                        <label for="siteUrl" class="form-label">URL для перевірки:</label>
                        <div class="input-group">
                            <input type="url" 
                                   id="siteUrl" 
                                   name="url" 
                                   class="form-control" 
                                   placeholder="https://example.com" 
                                   required>
                            <button type="submit" class="btn-check">
                                <i class="bi bi-search me-1"></i>
                                Перевірити
                            </button>
                        </div>
                    </div>
                    
                    <!-- Локації будуть додані динамічно через JavaScript -->
                    
                    <!-- CSRF Token -->
                    <?php if (function_exists('generateCSRFToken')): ?>
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Можливості інструменту</h2>
            <p class="lead text-muted">Комплексна перевірка доступності та продуктивності</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <h5>Глобальна перевірка</h5>
                    <p class="text-muted">
                        Перевірка доступності з 6 різних географічних локацій по всьому світу
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h5>Час відповіді</h5>
                    <p class="text-muted">
                        Вимірювання швидкості завантаження та часу відповіді сервера
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>SSL перевірка</h5>
                    <p class="text-muted">
                        Аналіз SSL сертифіката, термін дії та коректність налаштування
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h5>DNS аналіз</h5>
                    <p class="text-muted">
                        Перевірка DNS записів, швидкість резолвінгу та налаштування
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-file-code"></i>
                    </div>
                    <h5>HTTP заголовки</h5>
                    <p class="text-muted">
                        Детальний аналіз HTTP заголовків відповіді сервера
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h5>Історія перевірок</h5>
                    <p class="text-muted">
                        Збереження та порівняння результатів попередніх перевірок
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Як це працює</h2>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5>Введіть URL</h5>
                    <p class="text-muted">Вкажіть адресу сайту для перевірки</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>Оберіть локації</h5>
                    <p class="text-muted">Виберіть точки для тестування</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Запустіть перевірку</h5>
                    <p class="text-muted">Натисніть кнопку для початку аналізу</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h5>Отримайте звіт</h5>
                    <p class="text-muted">Детальні результати за кілька секунд</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- API Section -->
<section class="api-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">API для розробників</h2>
                <p class="lead mb-4">
                    Інтегруйте перевірку доступності у ваші додатки через наш REST API
                </p>
                
                <div class="api-features">
                    <div class="api-feature">
                        <i class="bi bi-check-circle text-success"></i>
                        <span>RESTful API з JSON відповідями</span>
                    </div>
                    <div class="api-feature">
                        <i class="bi bi-check-circle text-success"></i>
                        <span>Автентифікація через API ключі</span>
                    </div>
                    <div class="api-feature">
                        <i class="bi bi-check-circle text-success"></i>
                        <span>Rate limit: 1000 запитів/годину</span>
                    </div>
                    <div class="api-feature">
                        <i class="bi bi-check-circle text-success"></i>
                        <span>Webhook інтеграція</span>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="/api/docs" class="btn btn-primary">
                        <i class="bi bi-book me-2"></i>Документація API
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="code-example">
                    <div class="code-header">
                        <span class="code-lang">curl</span>
                        <button class="btn btn-sm btn-outline-light" onclick="copyCode()">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <pre><code>curl -X POST https://api.stormhosting.ua/v1/site-check \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com",
    "locations": ["kyiv", "frankfurt"],
    "check_ssl": true
  }'</code></pre>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Tools -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Інші корисні інструменти</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5>WHOIS lookup</h5>
                    <p class="text-muted">Інформація про власника домену</p>
                    <a href="/domains/whois" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <h5>DNS lookup</h5>
                    <p class="text-muted">Перевірка DNS записів</p>
                    <a href="/domains/dns" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5>IP lookup</h5>
                    <p class="text-muted">Геолокація IP адреси</p>
                    <a href="/tools/ip-check" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-code-square"></i>
                    </div>
                    <h5>HTTP Headers</h5>
                    <p class="text-muted">Аналіз HTTP заголовків</p>
                    <a href="/tools/http-headers" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js для графіків -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Підключення JS для сторінки -->
<script src="/assets/js/tools-site-check.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>