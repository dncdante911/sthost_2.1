<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'ip-check';
$page_title = 'Перевірка IP адреси - StormHosting UA';
$meta_description = 'Безкоштовна перевірка IP адреси: геолокація, провайдер, чорні списки, ASN інформація, загрози безпеки.';
$meta_keywords = 'ip check, перевірка ip, геолокація ip, чорний список ip, ASN lookup, ip blacklist';

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- Підключення CSS для сторінки -->
<link rel="stylesheet" href="/assets/css/pages/tools-ip-check2.css">

<!-- IP Check Hero -->
<section class="ip-check-hero">
    <div class="container">
        <div class="text-center">
            <div class="tool-icon mb-3">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <h1 class="display-4 fw-bold text-white mb-4">
                Перевірка IP адреси
            </h1>
            <p class="lead text-white-50 mb-5">
                Отримайте детальну інформацію про будь-який IP адрес: геолокація, провайдер, 
                безпека, чорні списки та багато іншого.
            </p>
            
            <!-- Current IP Display -->
            <div class="current-ip-card mb-4">
                <h5 class="text-white mb-2">Ваша поточна IP адреса:</h5>
                <div class="current-ip" id="currentIp">
                    <i class="bi bi-geo-alt me-2"></i>
                    <span class="ip-address"><?= $_SERVER['REMOTE_ADDR'] ?? 'Невідомо' ?></span>
                    <button type="button" class="btn btn-sm btn-outline-light ms-2" onclick="checkCurrentIp()">
                        <i class="bi bi-search"></i> Перевірити
                    </button>
                </div>
            </div>
            
            <!-- IP Check Form -->
            <div class="ip-check-form">
                <form id="ipCheckForm" method="post">
                    <div class="form-group">
                        <label for="ipAddress" class="form-label">IP адреса для перевірки:</label>
                        <div class="input-group">
                            <input type="text" 
                                   id="ipAddress" 
                                   name="ip" 
                                   class="form-control" 
                                   placeholder="192.168.1.1 або 2001:db8::1" 
                                   pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$|^(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$"
                                   required>
                            <button type="submit" class="btn-check">
                                <i class="bi bi-search me-1"></i>
                                Перевірити IP
                            </button>
                        </div>
                        <small class="form-text">Підтримуються IPv4 та IPv6 адреси</small>
                    </div>
                    
                    <!-- Дополнительные опции -->
                    <div class="check-options">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="option-item">
                                    <input type="checkbox" id="checkBlacklists" name="check_blacklists" checked>
                                    <label for="checkBlacklists">
                                        <i class="bi bi-shield-exclamation text-warning"></i>
                                        Перевірка чорних списків
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="option-item">
                                    <input type="checkbox" id="checkThreatIntel" name="check_threat_intel" checked>
                                    <label for="checkThreatIntel">
                                        <i class="bi bi-bug text-danger"></i>
                                        Аналіз загроз
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="option-item">
                                    <input type="checkbox" id="checkDistance" name="check_distance" checked>
                                    <label for="checkDistance">
                                        <i class="bi bi-compass text-info"></i>
                                        Розрахунок відстані
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CSRF Token -->
                    <?php if (function_exists('generateCSRFToken')): ?>
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Quick Actions -->
<section class="quick-actions py-4">
    <div class="container">
        <div class="text-center mb-4">
            <h5 class="text-muted">Швидкі дії:</h5>
        </div>
        <div class="row justify-content-center">
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm" onclick="checkSampleIP('8.8.8.8')">
                    <i class="bi bi-lightning"></i> Google DNS
                </button>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm" onclick="checkSampleIP('1.1.1.1')">
                    <i class="bi bi-cloud"></i> Cloudflare DNS
                </button>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm" onclick="checkSampleIP('208.67.222.222')">
                    <i class="bi bi-shield"></i> OpenDNS
                </button>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm" onclick="pasteFromClipboard()">
                    <i class="bi bi-clipboard"></i> З буфера
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Можливості перевірки IP</h2>
            <p class="lead text-muted">Комплексний аналіз IP адрес з перевіркою безпеки</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon geolocation">
                        <i class="bi bi-globe"></i>
                    </div>
                    <h5>Геолокація</h5>
                    <p class="text-muted">
                        Точне визначення країни, регіону, міста та координат IP адреси
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon security">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Перевірка безпеки</h5>
                    <p class="text-muted">
                        Сканування по 20+ чорних списках та базах загроз
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon network">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h5>Мережева інформація</h5>
                    <p class="text-muted">
                        ASN, провайдер, тип з'єднання та технічні деталі
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon distance">
                        <i class="bi bi-compass"></i>
                    </div>
                    <h5>Розрахунок відстані</h5>
                    <p class="text-muted">
                        Відстань від вашої локації до IP адреси в кілометрах
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon weather">
                        <i class="bi bi-cloud-sun"></i>
                    </div>
                    <h5>Погода в регіоні</h5>
                    <p class="text-muted">
                        Поточна погода в місці розташування IP адреси
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon history">
                        <i class="bi bi-clock-history"></i>
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

<!-- Security Badges -->
<section class="security-badges py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Джерела перевірки безпеки</h2>
            <p class="text-muted">Ми перевіряємо IP адреси по найкращих базах даних загроз</p>
        </div>
        
        <div class="row g-3">
            <div class="col-lg-2 col-md-3 col-6">
                <div class="security-badge">
                    <i class="bi bi-shield"></i>
                    <span>Spamhaus</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="security-badge">
                    <i class="bi bi-bug"></i>
                    <span>SURBL</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="security-badge">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Barracuda</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="security-badge">
                    <i class="bi bi-eye"></i>
                    <span>UCEPROTECT</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="security-badge">
                    <i class="bi bi-lock"></i>
                    <span>Composite</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="security-badge">
                    <i class="bi bi-plus-circle"></i>
                    <span>20+ інших</span>
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
                    <h5>Введіть IP</h5>
                    <p class="text-muted">Вкажіть IPv4 або IPv6 адресу для аналізу</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>Геолокація</h5>
                    <p class="text-muted">Визначаємо місце розташування IP</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Перевірка безпеки</h5>
                    <p class="text-muted">Сканування по базах загроз</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h5>Детальний звіт</h5>
                    <p class="text-muted">Отримуйте повну інформацію про IP</p>
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
                        <i class="bi bi-globe"></i>
                    </div>
                    <h5>Site Check</h5>
                    <p class="text-muted">Перевірка доступності сайту</p>
                    <a href="/tools/site-check" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
            
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

<!-- Підключення JS для сторінки -->
<script src="/assets/js/tools-ip-check2.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>