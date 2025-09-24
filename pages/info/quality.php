<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'quality';
$page_title = 'Гарантія якості - StormHosting UA | Надійність та професійні стандарти';
$meta_description = 'Гарантія якості послуг StormHosting UA: 99.9% uptime, SLA угода, 24/7 моніторинг, професійна підтримка. Дізнайтеся про наші стандарти обслуговування.';
$meta_keywords = 'гарантія якості, sla угода, uptime 99.9%, моніторинг серверів, професійна підтримка, стандарти якості';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/info-quality.css'
];

$additional_js = [
    '/assets/js/info-quality.js'
];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- Додаткові стилі для цієї сторінки -->
<?php if (isset($additional_css)): ?>
    <?php foreach ($additional_css as $css_file): ?>
        <link rel="stylesheet" href="<?php echo $css_file; ?>">
    <?php endforeach; ?>
<?php endif; ?>

<!-- Quality Hero Section -->
<section class="quality-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <div class="quality-badge mb-3">
                        <i class="bi bi-shield-check"></i>
                        <span>Гарантована якість обслуговування</span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Гарантія якості
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Ми гарантуємо найвищі стандарти якості послуг хостингу, 
                        надійність інфраструктури та професійну підтримку 24/7.
                    </p>
                    
                    <!-- Основні гарантії -->
                    <div class="guarantee-highlights">
                        <div class="guarantee-item">
                            <div class="guarantee-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="guarantee-info">
                                <h5>99.9% Uptime</h5>
                                <p>Гарантована доступність ваших сайтів</p>
                            </div>
                        </div>
                        
                        <div class="guarantee-item">
                            <div class="guarantee-icon">
                                <i class="bi bi-headset"></i>
                            </div>
                            <div class="guarantee-info">
                                <h5>24/7 Підтримка</h5>
                                <p>Цілодобова технічна підтримка</p>
                            </div>
                        </div>
                        
                        <div class="guarantee-item">
                            <div class="guarantee-icon">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <div class="guarantee-info">
                                <h5>Повернення коштів</h5>
                                <p>30 днів гарантії або повернення грошей</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="quality-dashboard">
                        <div class="dashboard-header">
                            <h6>Моніторинг якості в реальному часі</h6>
                            <div class="status-indicator online">
                                <span class="status-dot"></span>
                                Всі системи працюють
                            </div>
                        </div>
                        
                        <div class="metrics-grid">
                            <div class="metric-card">
                                <div class="metric-value">99.97</div>
                                <div class="metric-label">% Uptime цього місяця</div>
                                <div class="metric-progress">
                                    <div class="progress-bar" style="width: 99.97%"></div>
                                </div>
                            </div>
                            
                            <div class="metric-card">
                                <div class="metric-value">200</div>
                                <div class="metric-label">мс Середній час відповіді</div>
                                <div class="metric-chart">
                                    <div class="chart-bars">
                                        <div class="bar" style="height: 60%"></div>
                                        <div class="bar" style="height: 80%"></div>
                                        <div class="bar" style="height: 40%"></div>
                                        <div class="bar" style="height: 90%"></div>
                                        <div class="bar" style="height: 70%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="metric-card">
                                <div class="metric-value">0</div>
                                <div class="metric-label">Критичні інциденти</div>
                                <div class="metric-status success">
                                    <i class="bi bi-check-circle"></i>
                                    Все стабільно
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SLA Agreement Section -->
<section class="sla-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">SLA Угода</h2>
            <p class="lead text-muted">Наші зобов'язання перед вами у цифрах та фактах</p>
        </div>
        
        <div class="sla-grid">
            <div class="sla-card featured">
                <div class="sla-header">
                    <div class="sla-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h4>99.9% Uptime</h4>
                </div>
                <div class="sla-content">
                    <div class="sla-percentage">99.9%</div>
                    <p class="sla-description">
                        Гарантована доступність серверів протягом року. 
                        Максимальний час простою: 8.76 годин на рік.
                    </p>
                    <div class="sla-details">
                        <div class="detail-item">
                            <span class="detail-label">На місяць:</span>
                            <span class="detail-value">43.8 хвилин</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">На тиждень:</span>
                            <span class="detail-value">10.1 хвилин</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">На день:</span>
                            <span class="detail-value">1.44 хвилини</span>
                        </div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm mt-3" onclick="copySLAInfo('uptime')">
                        <i class="bi bi-clipboard"></i> Копіювати деталі
                    </button>
                </div>
            </div>
            
            <div class="sla-card">
                <div class="sla-header">
                    <div class="sla-icon">
                        <i class="bi bi-reply"></i>
                    </div>
                    <h4>Час відповіді підтримки</h4>
                </div>
                <div class="sla-content">
                    <div class="response-times">
                        <div class="response-item urgent">
                            <div class="response-priority">Критичні</div>
                            <div class="response-time">15 хвилин</div>
                        </div>
                        <div class="response-item high">
                            <div class="response-priority">Високі</div>
                            <div class="response-time">1 година</div>
                        </div>
                        <div class="response-item normal">
                            <div class="response-priority">Звичайні</div>
                            <div class="response-time">4 години</div>
                        </div>
                        <div class="response-item low">
                            <div class="response-priority">Низькі</div>
                            <div class="response-time">24 години</div>
                        </div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm mt-3" onclick="copySLAInfo('support')">
                        <i class="bi bi-clipboard"></i> Копіювати деталі
                    </button>
                </div>
            </div>
            
            <div class="sla-card">
                <div class="sla-header">
                    <div class="sla-icon">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <h4>Швидкість завантаження</h4>
                </div>
                <div class="sla-content">
                    <div class="speed-metrics">
                        <div class="speed-item">
                            <div class="speed-value">300</div>
                            <div class="speed-label">мс TTFB</div>
                        </div>
                        <div class="speed-item">
                            <div class="speed-value">2</div>
                            <div class="speed-label">с Повна загрузка</div>
                        </div>
                    </div>
                    <p class="speed-description">
                        Гарантована швидкість відповіді серверів 
                        для оптимальної роботи ваших сайтів.
                    </p>
                    <button class="btn btn-outline-primary btn-sm mt-3" onclick="copySLAInfo('backup')">
                        <i class="bi bi-clipboard"></i> Копіювати деталі
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Monitoring & Security Section -->
<section class="monitoring-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5">
                <h2 class="display-6 fw-bold mb-4">Моніторинг 24/7</h2>
                <p class="lead mb-4">
                    Наша система моніторингу відстежує стан серверів цілодобово, 
                    забезпечуючи миттєве реагування на будь-які проблеми.
                </p>
                
                <div class="monitoring-features">
                    <div class="monitoring-feature">
                        <div class="feature-icon">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Моніторинг ресурсів</h5>
                            <p>Відстеження CPU, RAM, дискового простору та мережевого трафіку</p>
                        </div>
                    </div>
                    
                    <div class="monitoring-feature">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Аналіз продуктивності</h5>
                            <p>Постійний моніторинг швидкості роботи та часу відповіді</p>
                        </div>
                    </div>
                    
                    <div class="monitoring-feature">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Автоматичні сповіщення</h5>
                            <p>Миттєві повідомлення про критичні події та потенційні проблеми</p>
                        </div>
                    </div>
                    
                    <div class="monitoring-feature">
                        <div class="feature-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Автоматичне відновлення</h5>
                            <p>Система самодіагностики та автоматичного вирішення простих проблем</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="monitoring-visual">
                    <div class="monitoring-dashboard">
                        <div class="dashboard-title">
                            <h6>Система моніторингу</h6>
                            <span class="live-indicator">LIVE</span>
                        </div>
                        
                        <div class="monitoring-grid">
                            <div class="monitor-item">
                                <div class="monitor-label">Сервер #1</div>
                                <div class="monitor-status online">
                                    <span class="status-dot"></span>
                                    Online
                                </div>
                                <div class="monitor-metrics">
                                    <span>CPU: 23%</span>
                                    <span>RAM: 67%</span>
                                </div>
                            </div>
                            
                            <div class="monitor-item">
                                <div class="monitor-label">Сервер #2</div>
                                <div class="monitor-status online">
                                    <span class="status-dot"></span>
                                    Online
                                </div>
                                <div class="monitor-metrics">
                                    <span>CPU: 45%</span>
                                    <span>RAM: 52%</span>
                                </div>
                            </div>
                            
                            <div class="monitor-item">
                                <div class="monitor-label">База даних</div>
                                <div class="monitor-status online">
                                    <span class="status-dot"></span>
                                    Online
                                </div>
                                <div class="monitor-metrics">
                                    <span>Зв'язків: 847</span>
                                    <span>Запитів/с: 245</span>
                                </div>
                            </div>
                            
                            <div class="monitor-item">
                                <div class="monitor-label">Мережа</div>
                                <div class="monitor-status online">
                                    <span class="status-dot"></span>
                                    Online
                                </div>
                                <div class="monitor-metrics">
                                    <span>Пінг: 12ms</span>
                                    <span>Швидкість: 1Gb/s</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="recent-events">
                            <div class="events-title">Останні події</div>
                            <div class="event-item">
                                <span class="event-time">14:32</span>
                                <span class="event-text">Автоматичне оновлення безпеки виконано</span>
                                <span class="event-status success">✓</span>
                            </div>
                            <div class="event-item">
                                <span class="event-time">12:15</span>
                                <span class="event-text">Резервне копіювання завершено</span>
                                <span class="event-status success">✓</span>
                            </div>
                            <div class="event-item">
                                <span class="event-time">09:45</span>
                                <span class="event-text">Планове обслуговування розпочато</span>
                                <span class="event-status info">i</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Security Standards Section -->
<section class="security-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Стандарти безпеки</h2>
            <p class="lead text-muted">Комплексний захист ваших даних та інфраструктури</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="security-card">
                    <div class="security-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h5>SSL/TLS шифрування</h5>
                    <p>Безкоштовні SSL сертифікати та підтримка TLS 1.3 для максимального захисту даних</p>
                    <div class="security-features">
                        <span class="feature-badge">Let's Encrypt</span>
                        <span class="feature-badge">Wildcard SSL</span>
                        <span class="feature-badge">EV SSL</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="security-card">
                    <div class="security-icon">
                        <i class="bi bi-firewall"></i>
                    </div>
                    <h5>Мережевий захист</h5>
                    <p>Багаторівневий firewall, DDoS захист та системи виявлення вторгнень</p>
                    <div class="security-features">
                        <span class="feature-badge">DDoS Protection</span>
                        <span class="feature-badge">WAF</span>
                        <span class="feature-badge">IDS/IPS</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="security-card">
                    <div class="security-icon">
                        <i class="bi bi-database-lock"></i>
                    </div>
                    <h5>Захист даних</h5>
                    <p>Шифрування на рівні диска, регулярні резервні копії та контроль доступу</p>
                    <div class="security-features">
                        <span class="feature-badge">AES-256</span>
                        <span class="feature-badge">Daily Backup</span>
                        <span class="feature-badge">RBAC</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="security-card">
                    <div class="security-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                    <h5>Моніторинг безпеки</h5>
                    <p>Цілодобове відстеження загроз, аналіз логів та реагування на інциденти</p>
                    <div class="security-features">
                        <span class="feature-badge">SIEM</span>
                        <span class="feature-badge">Log Analysis</span>
                        <span class="feature-badge">Threat Intel</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="security-card">
                    <div class="security-icon">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <h5>Оновлення безпеки</h5>
                    <p>Автоматичні оновлення системи, патчі безпеки та актуальні антивірусні бази</p>
                    <div class="security-features">
                        <span class="feature-badge">Auto Updates</span>
                        <span class="feature-badge">Security Patches</span>
                        <span class="feature-badge">Malware Scan</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="security-card">
                    <div class="security-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5>Контроль доступу</h5>
                    <p>Багатофакторна автентифікація, рольовий доступ та аудит дій користувачів</p>
                    <div class="security-features">
                        <span class="feature-badge">2FA/MFA</span>
                        <span class="feature-badge">SSO</span>
                        <span class="feature-badge">Audit Logs</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Compensation Section -->
<section class="compensation-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Компенсація за простої</h2>
                <p class="lead mb-4">
                    Якщо ми не дотримуємо SLA угоди, ви отримуєте компенсацію. 
                    Ваша довіра - наш пріоритет.
                </p>
                
                <div class="compensation-table">
                    <div class="compensation-header">
                        <h6>Розрахунок компенсації</h6>
                    </div>
                    
                    <div class="compensation-rows">
                        <div class="compensation-row">
                            <div class="downtime-range">99.0% - 99.8%</div>
                            <div class="compensation-amount">10% місячної плати</div>
                        </div>
                        <div class="compensation-row">
                            <div class="downtime-range">95.0% - 98.9%</div>
                            <div class="compensation-amount">25% місячної плати</div>
                        </div>
                        <div class="compensation-row">
                            <div class="downtime-range">90.0% - 94.9%</div>
                            <div class="compensation-amount">50% місячної плати</div>
                        </div>
                        <div class="compensation-row">
                            <div class="downtime-range">< 90.0%</div>
                            <div class="compensation-amount">100% місячної плати</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="compensation-visual">
                    <div class="calculator-widget">
                        <div class="calculator-header">
                            <h6>Калькулятор компенсації</h6>
                        </div>
                        
                        <div class="calculator-form">
                            <div class="form-group">
                                <label>Uptime за місяць</label>
                                <div class="input-group">
                                    <input type="number" id="uptimeInput" value="99.5" min="0" max="100" step="0.1">
                                    <span class="input-unit">%</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Місячна плата</label>
                                <div class="input-group">
                                    <input type="number" id="monthlyFeeInput" value="299" min="1">
                                    <span class="input-unit">грн</span>
                                </div>
                            </div>
                            
                            <div class="calculation-result">
                                <div class="result-label">Ваша компенсація:</div>
                                <div class="result-amount" id="compensationResult">29.90 грн</div>
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="calculateCompensation()">
                                Розрахувати
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Certificates Section -->
<section class="certificates-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Сертифікати та відзнаки</h2>
            <p class="lead text-muted">Наша якість підтверджена міжнародними стандартами</p>
        </div>
        
        <div class="certificates-grid">
            <div class="certificate-card">
                <div class="certificate-icon">
                    <i class="bi bi-award"></i>
                </div>
                <h5>ISO 27001</h5>
                <p>Міжнародний стандарт управління інформаційною безпекою</p>
                <div class="certificate-status">Сертифіковано</div>
            </div>
            
            <div class="certificate-card">
                <div class="certificate-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h5>SOC 2 Type II</h5>
                <p>Аудит безпеки та контролю процесів обробки даних</p>
                <div class="certificate-status">Сертифіковано</div>
            </div>
            
            <div class="certificate-card">
                <div class="certificate-icon">
                    <i class="bi bi-globe"></i>
                </div>
                <h5>GDPR Compliance</h5>
                <p>Відповідність Європейському регламенту захисту даних</p>
                <div class="certificate-status">Сертифіковано</div>
            </div>
            
            <div class="certificate-card">
                <div class="certificate-icon">
                    <i class="bi bi-star"></i>
                </div>
                <h5>Uptime Institute</h5>
                <p>Сертифікація надійності дата-центру Tier III</p>
                <div class="certificate-status">Сертифіковано</div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="contact-cta py-5 bg-primary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="text-white mb-3">Маєте питання щодо якості послуг?</h2>
                <p class="text-white-50 mb-0">
                    Наша команда готова надати детальну інформацію про наші стандарти якості 
                    та SLA угоди. Зв'яжіться з нами для персональної консультації.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="/pages/contacts.php" class="btn btn-light btn-lg">
                    <i class="bi bi-chat-dots me-2"></i>
                    Задати питання
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Додаткові скрипти для цієї сторінки -->
<?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js_file): ?>
        <script src="<?php echo $js_file; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>