<!DOCTYPE html>
<?php

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/home_old.css?v=' . filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/home_old.css')
];

$additional_js = [
    '/assets/js/home.js'
];

?>

<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escapeOutput($page_title); ?></title>
    <meta name="description" content="<?php echo escapeOutput($meta_description); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom CSS (если файл существует) -->
  <!--  <?php if (file_exists('assets/css/home_old.css')): ?>
    <link href="/assets/css/home_old.css" rel="stylesheet">
    <?php endif; ?> -->
    
      <!-- Custom JS (если файл существует) -->
    <?php if (file_exists('assets/js/home.js')): ?>
    <link href="/assets/js/home.js" rel="stylesheet">
    <?php endif; ?> 
    
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            min-height: 70vh;
            display: flex;
            align-items: center;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 24px;
        }
        
        .domain-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 2px solid #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .domain-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        .hosting-plan-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            border: 2px solid #f8f9fa;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
        }
        
        .hosting-plan-card.popular {
            border-color: var(--primary-color);
            transform: scale(1.05);
        }
        
        .popular-badge {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-color);
            color: white;
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .footer {
            background: #343a40;
            color: white;
            padding: 50px 0 20px;
        }
        
        .footer a {
            color: #adb5bd;
            text-decoration: none;
        }
        
        .footer a:hover {
            color: white;
        }
    </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4"><?php echo t('hero_title'); ?></h1>
                <p class="lead mb-4"><?php echo t('hero_subtitle'); ?></p>
                
                <div class="d-flex gap-3 flex-wrap">
                    <a href="/hosting" class="btn btn-light btn-lg">
                        <i class="bi bi-rocket-takeoff"></i>
                        Обрати хостинг
                    </a>
                    <a href="/domains" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-globe"></i>
                        Зареєструвати домен
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-server" style="font-size: 200px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 fw-bold">Чому обирають StormHosting UA?</h2>
                <p class="lead text-muted">Ми надаємо найкращі послуги хостингу в Україні</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h4>99.9% Аптайм</h4>
                    <p>Гарантована стабільність роботи ваших сайтів</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4>Підтримка 24/7</h4>
                    <p>Швидка технічна підтримка в будь-який час</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Безкоштовний SSL</h4>
                    <p>Let's Encrypt сертифікати для всіх сайтів</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-cloud-upload"></i>
                    </div>
                    <h4>Автобекапи</h4>
                    <p>Щоденне резервне копіювання даних</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Domains -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-6 fw-bold">Популярні домени</h2>
                <p class="lead text-muted">Зареєструйте домен за найкращою ціною</p>
            </div>
        </div>
        
        <div class="row g-3 justify-content-center">
            <?php foreach (array_slice($popular_domains, 0, 5) as $domain): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="domain-card">
                    <div class="fw-bold fs-4 text-primary"><?php echo escapeOutput($domain['zone']); ?></div>
                    <div class="text-muted small">від</div>
                    <div class="fw-bold"><?php echo formatPrice($domain['price_registration']); ?></div>
                    <div class="text-muted small">грн/рік</div>
                    <a href="/domains" class="btn btn-primary btn-sm mt-2 w-100">Зареєструвати</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Hosting Plans -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-6 fw-bold">Тарифні плани хостингу</h2>
                <p class="lead text-muted">Оберіть ідеальний план для вашого проекту</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php foreach ($popular_hosting as $plan): ?>
            <div class="col-lg-4 col-md-6">
                <div class="hosting-plan-card <?php echo $plan['is_popular'] ? 'popular' : ''; ?>">
                    <?php if ($plan['is_popular']): ?>
                    <div class="popular-badge">Популярний</div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <h3 class="fw-bold"><?php echo escapeOutput($plan['name_' . $current_lang] ?? $plan['name_ua']); ?></h3>
                        <div class="display-6 fw-bold text-primary"><?php echo formatPrice($plan['price_monthly']); ?></div>
                        <div class="text-muted">грн/міс</div>
                    </div>
                    
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check text-success"></i> <?php echo $plan['disk_space'] / 1024; ?> ГБ дискового простору</li>
                        <li class="mb-2"><i class="bi bi-check text-success"></i> <?php echo $plan['bandwidth']; ?> ГБ трафіку</li>
                        <li class="mb-2"><i class="bi bi-check text-success"></i> <?php echo $plan['databases']; ?> бази даних</li>
                        <li class="mb-2"><i class="bi bi-check text-success"></i> <?php echo $plan['email_accounts']; ?> поштових скриньок</li>
                        <li class="mb-2"><i class="bi bi-check text-success"></i> Безкоштовний SSL</li>
                        <li class="mb-2"><i class="bi bi-check text-success"></i> Щоденні бекапи</li>
                    </ul>
                    
                    <div class="text-center mt-4">
                        <a href="/hosting" class="btn btn-primary w-100">Замовити зараз</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5 class="fw-bold mb-3">StormHosting UA</h5>
                <p>Надійний хостинг провайдер для вашого онлайн бізнесу. Ми забезпечуємо стабільну роботу ваших сайтів 24/7.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-light"><i class="bi bi-telegram fs-4"></i></a>
                    <a href="#" class="text-light"><i class="bi bi-facebook fs-4"></i></a>
                    <a href="#" class="text-light"><i class="bi bi-twitter fs-4"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Послуги</h6>
                <ul class="list-unstyled">
                    <li><a href="/hosting">Хостинг</a></li>
                    <li><a href="/vds">VDS/VPS</a></li>
                    <li><a href="/domains">Домени</a></li>
                    <li><a href="#">SSL сертифікати</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Підтримка</h6>
                <ul class="list-unstyled">
                    <li><a href="#">FAQ</a></li>
                    <li><a href="/contacts">Контакти</a></li>
                    <li><a href="#">Документація</a></li>
                    <li><a href="#">Статус серверів</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 mb-4">
                <h6 class="fw-bold mb-3">Контакти</h6>
                <div class="d-flex mb-2">
                    <i class="bi bi-geo-alt me-2"></i>
                    <span>Україна, Дніпро</span>
                </div>
                <div class="d-flex mb-2">
                    <i class="bi bi-envelope me-2"></i>
                    <span>info@sthost.pro</span>
                </div>
                <div class="d-flex mb-2">
                    <i class="bi bi-telephone me-2"></i>
                    <span>+380 XX XXX XX XX</span>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> StormHosting UA. Всі права захищені.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">Розроблено з ❤️ в Україні</small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
// Простые анимации
document.addEventListener('DOMContentLoaded', function() {
    // Плавная прокрутка для якорных ссылок
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Анимация появления карточек при скролле
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Применяем анимацию к карточкам
    document.querySelectorAll('.feature-card, .domain-card, .hosting-plan-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>

</body>
</html>