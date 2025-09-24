<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'about';
$page_title = 'Про компанію StormHosting UA - Надійний хостинг провайдер в Україні';
$meta_description = 'StormHosting UA - український хостинг провайдер з 2020 року. Надійні послуги хостингу, домени, VPS серверів. 24/7 підтримка, SSD накопичувачі, безкоштовний SSL.';
$meta_keywords = 'про компанію, stormhosting ua, український хостинг, надійний провайдер, історія компанії, команда';

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- Підключення CSS для сторінки -->
<link rel="stylesheet" href="/assets/css/pages/info-about.css">

<!-- About Hero Section -->
<section class="about-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <div class="company-badge mb-3">
                        <i class="bi bi-award-fill"></i>
                        <span>Український хостинг з 2020 року</span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Про StormHosting UA
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Ми — український хостинг провайдер, який надає надійні послуги 
                        веб-хостингу, доменів та VPS серверів для бізнесу будь-якого розміру.
                    </p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Клієнтів</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">5</div>
                            <div class="stat-label">Років досвіду</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">99%</div>
                            <div class="stat-label">Uptime</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Підтримка</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image">
                    <div class="company-illustration">
                        <div class="server-rack">
                            <div class="server-unit active"></div>
                            <div class="server-unit active"></div>
                            <div class="server-unit"></div>
                            <div class="server-unit active"></div>
                            <div class="server-unit"></div>
                            <div class="server-unit active"></div>
                        </div>
                        <div class="network-connections">
                            <div class="connection-line"></div>
                            <div class="connection-line"></div>
                            <div class="connection-line"></div>
                        </div>
                        <div class="cloud-icon">
                            <i class="bi bi-cloud-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Story Section -->
<section class="company-story py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Наша історія</h2>
            <p class="lead text-muted">Від ідеї до провідного українського хостинг провайдера</p>
        </div>
        
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-year">2020</div>
                <div class="timeline-content">
                    <h4>Заснування компанії</h4>
                    <p>StormHosting UA було засновано групою досвідчених IT-спеціалістів з метою надання якісних хостинг послуг українським бізнесам.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2021</div>
                <div class="timeline-content">
                    <h4>Перші 100 клієнтів</h4>
                    <p>Досягли першої важливої позначки — 100 задоволених клієнтів. Запустили власний біллінг та систему підтримки.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2021</div>
                <div class="timeline-content">
                    <h4>Розширення послуг</h4>
                    <p>Додали VPS серверів та розширили географію присутності. Відкрили власний дата-центр у Києві.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2022</div>
                <div class="timeline-content">
                    <h4>500+ клієнтів</h4>
                    <p>Перетнули позначку в 500 клієнтів. Запустили cloud хостинг та SSL сертифікати.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2024</div>
                <div class="timeline-content">
                    <h4>Міжнародна експансія</h4>
                    <p>Почали працювати з клієнтами з усієї Європи. Додали підтримку англійської мови.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2024</div>
                <div class="timeline-content">
                    <h4>Модернізація інфраструктури</h4>
                    <p>Повністю оновили серверне обладнання, перейшли на NVMe SSD накопичувачі.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2025</div>
                <div class="timeline-content">
                    <h4>2000+ клієнтів</h4>
                    <p>Досягли позначки 2000+ клієнтів. Запустили нову панель управління та розширили команду підтримки.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Values Section -->
<section class="mission-values py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <h3>Наша місія</h3>
                    <p>Надавати надійні, швидкі та доступні хостинг послуги, які допомагають українським бізнесам розвиватися в цифровому світі.</p>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                    <h3>Наше бачення</h3>
                    <p>Стати провідним хостинг провайдером в Україні, відомим своєю надійністю, інноваціями та турботою про клієнтів.</p>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h3>Наші цінності</h3>
                    <p>Надійність, інновації, прозорість, відповідальність та постійне прагнення до досконалості в усьому, що ми робимо.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Наша команда</h2>
            <p class="lead text-muted">Професіонали, які забезпечують надійність наших послуг</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5>Кошевой Максим</h5>
                    <p class="team-role">Генеральний директор</p>
                    <p class="team-description">Засновник компанії та голова компанії.</p>
                    <div class="team-social">
                        <a href="#" class="social-link">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="bi bi-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5>Дихятр Олександр</h5>
                    <p class="team-role">Технічний директор</p>
                    <p class="team-description">Експерт з системного адміністрування та архітектури мереж.</p>
                    <div class="team-social">
                        <a href="#" class="social-link">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="bi bi-github"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5>Яков</h5>
                    <p class="team-role">Керівник підтримки</p>
                    <p class="team-description">Забезпечує цілодобову підтримку клієнтів та вирішення технічних питань.</p>
                    <div class="team-social">
                        <a href="#" class="social-link">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="bi bi-telegram"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5>Діхтярь Ірина</h5>
                    <p class="team-role">Головний бухгалтер та єкономіст</p>
                    <p class="team-description">Допомагає клієнтам обрати оптимальні рішення для їхнього бізнесу.</p>
                    <div class="team-social">
                        <a href="#" class="social-link">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="bi bi-facebook"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Infrastructure Section -->
<section class="infrastructure py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Наша інфраструктура</h2>
                <p class="lead mb-4">
                    Сучасні дата-центри та передове обладнання забезпечують максимальну надійність та продуктивність.
                </p>
                
                <div class="infrastructure-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-hdd-rack"></i>
                        </div>
                        <div class="feature-content">
                            <h5>NVMe SSD накопичувачі</h5>
                            <p>Найшвидші накопичувачі для максимальної продуктивності</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Резервне копіювання</h5>
                            <p>Автоматичні щоденні бекапи та можливість відновлення</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-lightning"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Висока доступність</h5>
                            <p>99.9% uptime завдяки резервному живленню та мережі</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Українські дата-центри</h5>
                            <p>Серверів розташовані в Києві та Дніпрі</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="infrastructure-visual">
                    <div class="datacenter-building">
                        <div class="building-floor floor-1">
                            <div class="server-light active"></div>
                            <div class="server-light"></div>
                            <div class="server-light active"></div>
                        </div>
                        <div class="building-floor floor-2">
                            <div class="server-light active"></div>
                            <div class="server-light active"></div>
                            <div class="server-light"></div>
                        </div>
                        <div class="building-floor floor-3">
                            <div class="server-light"></div>
                            <div class="server-light active"></div>
                            <div class="server-light active"></div>
                        </div>
                        <div class="building-base"></div>
                    </div>
                    <div class="network-globe">
                        <div class="globe-connections">
                            <div class="connection-dot"></div>
                            <div class="connection-dot"></div>
                            <div class="connection-dot"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Achievements Section -->
<section class="achievements py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Наші досягнення</h2>
            <p class="lead text-muted">Результати, якими ми пишаємося</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="achievement-card">
                    <div class="achievement-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <div class="achievement-number">
                        <span class="counter" data-target="5000">0</span>+
                    </div>
                    <h5>Задоволених клієнтів</h5>
                    <p>Довіра тисяч користувачів по всій Україні</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="achievement-card">
                    <div class="achievement-icon">
                        <i class="bi bi-server"></i>
                    </div>
                    <div class="achievement-number">
                        <span class="counter" data-target="150">0</span>+
                    </div>
                    <h5>Серверів в роботі</h5>
                    <p>Потужна інфраструктура для будь-яких завдань</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="achievement-card">
                    <div class="achievement-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="achievement-number">
                        99.<span class="counter" data-target="9">0</span>%
                    </div>
                    <h5>Uptime гарантія</h5>
                    <p>Максимальна доступність ваших проектів</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="achievement-card">
                    <div class="achievement-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div class="achievement-number">
                        24/<span class="counter" data-target="7">0</span>
                    </div>
                    <h5>Підтримка</h5>
                    <p>Цілодобова технічна підтримка українською</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="contact-cta py-5 bg-primary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="text-white mb-3">Готові розпочати співпрацю?</h2>
                <p class="text-white-50 mb-0">
                    Зв'яжіться з нашою командою та отримайте персональну консультацію щодо оптимального рішення для вашого проекту.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="/info/contacts" class="btn btn-light btn-lg">
                    <i class="bi bi-telephone me-2"></i>
                    Зв'язатися з нами
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Підключення JS для сторінки -->
<script src="/assets/js/info-about.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>