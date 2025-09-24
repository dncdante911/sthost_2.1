<?php
define('SECURE_ACCESS', true);

// Захист від прямого доступу
if (!defined('SECURE_ACCESS')) {
    die('Прямий доступ заборонено');
}

// Захист від XSS
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// CSRF токен
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Мета-дані сторінки
$page_title = "SSL Сертифікати - Професійний захист для вашого сайту | StormHosting UA";
$page_description = "Професійні SSL сертифікати DV, OV, EV та Wildcard від провідних центрів сертифікації. Швидка установка, 256-bit шифрування, підтримка 24/7. Захистіть свій сайт вже сьогодні!";
$page_keywords = "ssl сертифікати україна, https сертифікат, ssl купити, dv ov ev wildcard ssl, безпека сайту, шифрування даних";
$canonical_url = "https://sthost.pro/pages/info/ssl";

// Додаткові CSS та JS файли для цієї сторінки
//$additional_css = [
//    '/assets/css/pages/info-ssl.css?v=' . filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/pages/info-ssl.css')
//];
//
//$additional_js = [
//   '/assets/js/info-ssl.js'
//];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Захист від SQL ін'єкцій - підготовлені запити
function get_ssl_prices($conn) {
    $stmt = $conn->prepare("SELECT * FROM ssl_certificates WHERE active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Захист від CSRF при обробці форм
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF токен невірний');
    }
    
    // Обробка форми замовлення SSL
    if (isset($_POST['order_ssl'])) {
        $ssl_type = clean_input($_POST['ssl_type']);
        $domain = clean_input($_POST['domain']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        
        if ($email && !empty($ssl_type) && !empty($domain)) {
            // Безпечне збереження замовлення
            $stmt = $conn->prepare("INSERT INTO ssl_orders (ssl_type, domain, email, created_at, ip_address) VALUES (?, ?, ?, NOW(), ?)");
            $stmt->execute([$ssl_type, $domain, $email, $_SERVER['REMOTE_ADDR']]);
            
            $success_message = "Ваше замовлення прийнято! Ми зв'яжемося з вами найближчим часом.";
        } else {
            $error_message = "Будь ласка, заповніть всі поля правильно.";
        }
    }
}

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/pages/info-ssl.css">

<main class="main-content">
    <!-- Hero Section -->
    <section class="ssl-hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="icon-shield"></i>
                        <span>Безпека та довіра</span>
                    </div>
                    
                    <h1 class="hero-title">SSL Сертифікати</h1>
                    <p class="hero-subtitle">
                        Захистіть свій сайт професійними SSL сертифікатами від провідних центрів сертифікації. 
                        Забезпечте безпеку даних користувачів та підвищте рейтинг у пошукових системах.
                    </p>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number">99.9%</div>
                            <div class="stat-label">Сумісність браузерів</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">5 хв</div>
                            <div class="stat-label">Швидкість установки</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Технічна підтримка</div>
                        </div>
                    </div>
                    
                    <div class="hero-actions">
                        <a href="#ssl-packages" class="btn btn-primary btn-large">
                            <i class="icon-package"></i>
                            Вибрати сертифікат
                        </a>
                        <a href="#ssl-comparison" class="btn btn-outline btn-large">
                            <i class="icon-compare"></i>
                            Порівняти типи
                        </a>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="ssl-demo">
                        <div class="browser-frame">
                            <div class="browser-header">
                                <div class="browser-controls">
                                    <span class="control-dot red"></span>
                                    <span class="control-dot yellow"></span>
                                    <span class="control-dot green"></span>
                                </div>
                                <div class="address-bar">
                                    <div class="secure-indicator">
                                        <i class="icon-lock-secure"></i>
                                        <span class="secure-text">Захищено</span>
                                    </div>
                                    <span class="url-text">https://yourdomain.com</span>
                                </div>
                            </div>
                            <div class="browser-content">
                                <div class="ssl-certificate-preview">
                                    <div class="cert-icon">
                                        <i class="icon-certificate"></i>
                                    </div>
                                    <div class="cert-info">
                                        <h3>Сертифікат безпеки</h3>
                                        <p>Ваш сайт захищено SSL сертифікатом</p>
                                        <div class="cert-details">
                                            <span class="cert-encryption">256-bit шифрування</span>
                                            <span class="cert-validity">Дійсний до 2025</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SSL Benefits -->
    <section class="ssl-benefits">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Чому SSL сертифікат необхідний вашому сайту?</h2>
                <p class="section-subtitle">
                    SSL сертифікат не просто захищає дані - він підвищує довіру користувачів та покращує SEO
                </p>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="icon-shield-check"></i>
                    </div>
                    <h3 class="benefit-title">Захист даних</h3>
                    <p class="benefit-description">
                        256-bit шифрування забезпечує повну безпеку передачі персональних даних та платіжної інформації
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="icon-search-plus"></i>
                    </div>
                    <h3 class="benefit-title">SEO переваги</h3>
                    <p class="benefit-description">
                        Google надає перевагу HTTPS сайтам у пошуковій видачі, що покращує позиції вашого сайту
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="icon-users-trust"></i>
                    </div>
                    <h3 class="benefit-title">Довіра користувачів</h3>
                    <p class="benefit-description">
                        Зелений замочок у браузері та HTTPS підвищують довіру відвідувачів та конверсію сайту
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="icon-speed"></i>
                    </div>
                    <h3 class="benefit-title">Швидкість завантаження</h3>
                    <p class="benefit-description">
                        HTTP/2 протокол працює лише з HTTPS, забезпечуючи вищу швидкість завантаження сторінок
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="icon-mobile"></i>
                    </div>
                    <h3 class="benefit-title">Мобільна сумісність</h3>
                    <p class="benefit-description">
                        Повна підтримка всіх мобільних браузерів та пристроїв для максимального охоплення аудиторії
                    </p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="icon-compliance"></i>
                    </div>
                    <h3 class="benefit-title">Відповідність стандартам</h3>
                    <p class="benefit-description">
                        Відповідність міжнародним стандартам безпеки та вимогам законодавства про захист персональних даних
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SSL Packages -->
    <section id="ssl-packages" class="ssl-packages">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Типи SSL сертифікатів</h2>
                <p class="section-subtitle">
                    Оберіть підходящий тип сертифікату залежно від ваших потреб та бюджету
                </p>
            </div>
            
            <div class="packages-grid">
                <!-- DV SSL -->
                <div class="package-card">
                    <div class="package-header">
                        <div class="package-icon">
                            <i class="icon-certificate-basic"></i>
                        </div>
                        <h3 class="package-name">Domain Validation (DV)</h3>
                        <p class="package-description">
                            Базовий рівень захисту для персональних сайтів та блогів
                        </p>
                    </div>
                    
                    <div class="package-price">
                        <span class="price-amount">990</span>
                        <span class="price-currency">грн</span>
                        <span class="price-period">/рік</span>
                    </div>
                    
                    <div class="package-features">
                        <ul class="features-list">
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Перевірка лише домену</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>256-bit шифрування</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Видача за 5-10 хвилин</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>99% сумісність браузерів</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Мобільна підтримка</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Базова підтримка</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="package-footer">
                        <button class="btn btn-outline btn-full order-ssl-btn" 
                                data-ssl-type="dv" 
                                data-ssl-name="Domain Validation (DV)"
                                data-ssl-price="990">
                            <i class="icon-cart"></i>
                            Замовити DV SSL
                        </button>
                    </div>
                </div>
                
                <!-- OV SSL -->
                <div class="package-card popular">
                    <div class="popular-badge">
                        <i class="icon-star"></i>
                        <span>Популярний</span>
                    </div>
                    
                    <div class="package-header">
                        <div class="package-icon">
                            <i class="icon-certificate-business"></i>
                        </div>
                        <h3 class="package-name">Organization Validation (OV)</h3>
                        <p class="package-description">
                            Оптимальний вибір для бізнесу з підтвердженням організації
                        </p>
                    </div>
                    
                    <div class="package-price">
                        <span class="price-amount">2,490</span>
                        <span class="price-currency">грн</span>
                        <span class="price-period">/рік</span>
                    </div>
                    
                    <div class="package-features">
                        <ul class="features-list">
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Перевірка організації</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>256-bit шифрування</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Відображення назви компанії</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Гарантія до $1,250,000</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Пріоритетна підтримка</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Захист від фішингу</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="package-footer">
                        <button class="btn btn-primary btn-full order-ssl-btn" 
                                data-ssl-type="ov" 
                                data-ssl-name="Organization Validation (OV)"
                                data-ssl-price="2490">
                            <i class="icon-cart"></i>
                            Замовити OV SSL
                        </button>
                    </div>
                </div>
                
                <!-- EV SSL -->
                <div class="package-card">
                    <div class="package-header">
                        <div class="package-icon">
                            <i class="icon-certificate-premium"></i>
                        </div>
                        <h3 class="package-name">Extended Validation (EV)</h3>
                        <p class="package-description">
                            Максимальний захист для великого бізнесу та інтернет-магазинів
                        </p>
                    </div>
                    
                    <div class="package-price">
                        <span class="price-amount">7,990</span>
                        <span class="price-currency">грн</span>
                        <span class="price-period">/рік</span>
                    </div>
                    
                    <div class="package-features">
                        <ul class="features-list">
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Повна перевірка компанії</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Зелена адресна стрічка</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Максимальна довіра</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Гарантія до $1,750,000</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>VIP підтримка 24/7</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Антивірусне сканування</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="package-footer">
                        <button class="btn btn-outline btn-full order-ssl-btn" 
                                data-ssl-type="ev" 
                                data-ssl-name="Extended Validation (EV)"
                                data-ssl-price="7990">
                            <i class="icon-cart"></i>
                            Замовити EV SSL
                        </button>
                    </div>
                </div>
                
                <!-- Wildcard SSL -->
                <div class="package-card">
                    <div class="package-header">
                        <div class="package-icon">
                            <i class="icon-certificate-wildcard"></i>
                        </div>
                        <h3 class="package-name">Wildcard SSL</h3>
                        <p class="package-description">
                            Захистіть необмежену кількість піддоменів одним сертифікатом
                        </p>
                    </div>
                    
                    <div class="package-price">
                        <span class="price-amount">4,990</span>
                        <span class="price-currency">грн</span>
                        <span class="price-period">/рік</span>
                    </div>
                    
                    <div class="package-features">
                        <ul class="features-list">
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Необмежені піддомени</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>*.yourdomain.com</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>256-bit шифрування</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Легке управління</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Економія бюджету</span>
                            </li>
                            <li class="feature-item">
                                <i class="icon-check"></i>
                                <span>Стандартна підтримка</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="package-footer">
                        <button class="btn btn-outline btn-full order-ssl-btn" 
                                data-ssl-type="wildcard" 
                                data-ssl-name="Wildcard SSL"
                                data-ssl-price="4990">
                            <i class="icon-cart"></i>
                            Замовити Wildcard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SSL Comparison -->
    <section id="ssl-comparison" class="ssl-comparison">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Порівняння SSL сертифікатів</h2>
                <p class="section-subtitle">
                    Детальне порівняння всіх типів сертифікатів для вибору оптимального варіанту
                </p>
            </div>
            
            <div class="comparison-wrapper">
                <div class="comparison-table-container">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th class="feature-column">Характеристика</th>
                                <th class="ssl-column">DV SSL</th>
                                <th class="ssl-column popular-column">OV SSL</th>
                                <th class="ssl-column">EV SSL</th>
                                <th class="ssl-column">Wildcard</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="feature-name">Час видачі</td>
                                <td class="feature-value">5-10 хвилин</td>
                                <td class="feature-value">1-3 дні</td>
                                <td class="feature-value">3-7 днів</td>
                                <td class="feature-value">5-10 хвилин</td>
                            </tr>
                            <tr>
                                <td class="feature-name">Рівень шифрування</td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> 256-bit
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> 256-bit
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> 256-bit
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> 256-bit
                                </td>
                            </tr>
                            <tr>
                                <td class="feature-name">Перевірка домену</td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="feature-name">Перевірка організації</td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="feature-name">Розширена перевірка</td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="feature-name">Зелена адресна стрічка</td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="feature-name">Страхова гарантія</td>
                                <td class="feature-value">$10,000</td>
                                <td class="feature-value">$1,250,000</td>
                                <td class="feature-value">$1,750,000</td>
                                <td class="feature-value">$10,000</td>
                            </tr>
                            <tr>
                                <td class="feature-name">Мобільна сумісність</td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> 99%
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> 99.9%
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> 99.9%
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> 99%
                                </td>
                            </tr>
                            <tr>
                                <td class="feature-name">Підтримка піддоменів</td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-cross-red"></i>
                                </td>
                                <td class="feature-value">
                                    <i class="icon-check-green"></i> Необмежено
                                </td>
                            </tr>
                            <tr class="price-row">
                                <td class="feature-name">Ціна за рік</td>
                                <td class="feature-value price-cell">990 грн</td>
                                <td class="feature-value price-cell popular-price">2,490 грн</td>
                                <td class="feature-value price-cell">7,990 грн</td>
                                <td class="feature-value price-cell">4,990 грн</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Installation Process -->
    <section class="installation-process">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Як ми встановлюємо SSL сертифікат</h2>
                <p class="section-subtitle">
                    Простий та швидкий процес установки без технічних складнощів з вашого боку
                </p>
            </div>
            
            <div class="process-timeline">
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <div class="marker-number">1</div>
                        <div class="marker-icon">
                            <i class="icon-select"></i>
                        </div>
                    </div>
                    <div class="timeline-content">
                        <h3 class="timeline-title">Оберіть тип сертифікату</h3>
                        <p class="timeline-description">
                            Визначтеся з потрібним типом SSL сертифікату залежно від ваших потреб та специфіки сайту. 
                            Наші консультанти допоможуть з вибором, якщо у вас виникнуть питання.
                        </p>
                        <div class="timeline-details">
                            <span class="detail-item">
                                <i class="icon-time"></i>
                                Миттєво
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <div class="marker-number">2</div>
                        <div class="marker-icon">
                            <i class="icon-verify"></i>
                        </div>
                    </div>
                    <div class="timeline-content">
                        <h3 class="timeline-title">Перевірка та валідація</h3>
                        <p class="timeline-description">
                            Проводимо необхідну перевірку домену або організації відповідно до типу обраного сертифікату. 
                            Для DV перевіряємо лише домен, для OV/EV - додатково організацію.
                        </p>
                        <div class="timeline-details">
                            <span class="detail-item">
                                <i class="icon-time"></i>
                                5 хв - 7 днів
                            </span>
                            <span class="detail-item">
                                <i class="icon-secure"></i>
                                Безпечна перевірка
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <div class="marker-number">3</div>
                        <div class="marker-icon">
                            <i class="icon-install"></i>
                        </div>
                    </div>
                    <div class="timeline-content">
                        <h3 class="timeline-title">Автоматична установка</h3>
                        <p class="timeline-description">
                            Після отримання сертифікату наша система автоматично встановлює його на ваш сервер, 
                            налаштовує HTTPS та перенаправлення з HTTP.
                        </p>
                        <div class="timeline-details">
                            <span class="detail-item">
                                <i class="icon-time"></i>
                                2-5 хвилин
                            </span>
                            <span class="detail-item">
                                <i class="icon-auto"></i>
                                Автоматично
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <div class="marker-number">4</div>
                        <div class="marker-icon">
                            <i class="icon-check-circle"></i>
                        </div>
                    </div>
                    <div class="timeline-content">
                        <h3 class="timeline-title">Тестування та запуск</h3>
                        <p class="timeline-description">
                            Перевіряємо коректність роботи SSL сертифікату, тестуємо всі функції та сповіщаємо вас 
                            про успішне завершення установки. Ваш сайт захищено!
                        </p>
                        <div class="timeline-details">
                            <span class="detail-item">
                                <i class="icon-time"></i>
                                1-2 хвилини
                            </span>
                            <span class="detail-item">
                                <i class="icon-notification"></i>
                                SMS/Email сповіщення
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="ssl-faq">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Часті питання про SSL сертифікати</h2>
                <p class="section-subtitle">
                    Відповіді на найпопулярніші питання про SSL сертифікати та їх використання
                </p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <button class="faq-question" data-faq="1">
                        <span>Що таке SSL сертифікат і навіщо він потрібен?</span>
                        <i class="icon-chevron-down"></i>
                    </button>
                    <div class="faq-answer" id="faq-1">
                        <p>
                            SSL сертифікат - це цифровий документ, який забезпечує шифрування даних між браузером 
                            користувача та веб-сервером. Він необхідний для захисту конфіденційної інформації, 
                            підвищення довіри користувачів та покращення SEO позицій сайту.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question" data-faq="2">
                        <span>Як довго встановлюється SSL сертифікат?</span>
                        <i class="icon-chevron-down"></i>
                    </button>
                    <div class="faq-answer" id="faq-2">
                        <p>
                            Час установки залежить від типу сертифікату: DV SSL встановлюється за 5-10 хвилин, 
                            OV SSL - 1-3 дні, EV SSL - 3-7 днів. Wildcard SSL встановлюється так само швидко, 
                            як DV сертифікат. Сама технічна установка займає лише кілька хвилин.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question" data-faq="3">
                        <span>Чим відрізняються DV, OV та EV сертифікати?</span>
                        <i class="icon-chevron-down"></i>
                    </button>
                    <div class="faq-answer" id="faq-3">
                        <p>
                            DV (Domain Validation) - базова перевірка лише володіння доменом. OV (Organization Validation) - 
                            додатково перевіряється існування та легальність організації. EV (Extended Validation) - 
                            найвищий рівень перевірки з відображенням зеленої адресної стрічки та назви компанії у браузері.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question" data-faq="4">
                        <span>Що таке Wildcard SSL і коли його використовувати?</span>
                        <i class="icon-chevron-down"></i>
                    </button>
                    <div class="faq-answer" id="faq-4">
                        <p>
                            Wildcard SSL дозволяє захистити необмежену кількість піддоменів одним сертифікатом 
                            (*.yourdomain.com). Це економічно вигідно, якщо у вас багато піддоменів: shop.domain.com, 
                            blog.domain.com, api.domain.com тощо.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question" data-faq="5">
                        <span>Чи впливає SSL на швидкість сайту?</span>
                        <i class="icon-chevron-down"></i>
                    </button>
                    <div class="faq-answer" id="faq-5">
                        <p>
                            Сучасні SSL сертифікати практично не впливають на швидкість сайту. Більше того, 
                            HTTPS дозволяє використовувати HTTP/2 протокол, який значно прискорює завантаження 
                            сторінок порівняно зі звичайним HTTP.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question" data-faq="6">
                        <span>Чи потрібно щось робити після установки SSL?</span>
                        <i class="icon-chevron-down"></i>
                    </button>
                    <div class="faq-answer" id="faq-6">
                        <p>
                            Ми повністю налаштовуємо SSL сертифікат, включаючи автоматичне перенаправлення з HTTP на HTTPS. 
                            Рекомендуємо лише оновити внутрішні посилання на сайті та налаштувати автопродовження 
                            сертифікату, що ми також можемо зробити за вас.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question" data-faq="7">
                        <span>Що відбувається, якщо сертифікат закінчується?</span>
                        <i class="icon-chevron-down"></i>
                    </button>
                    <div class="faq-answer" id="faq-7">
                        <p>
                            Ми надсилаємо сповіщення за 30, 14 та 7 днів до закінчення терміну дії сертифікату. 
                            Можна налаштувати автоматичне продовження. Якщо сертифікат закінчиться, користувачі 
                            побачать попередження про небезпеку у браузері.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question" data-faq="8">
                        <span>Чи можна перенести SSL сертифікат на інший хостинг?</span>
                        <i class="icon-chevron-down"></i>
                    </button>
                    <div class="faq-answer" id="faq-8">
                        <p>
                            Так, SSL сертифікат можна перенести на інший хостинг. Ми надаємо всі необхідні файли 
                            сертифікату та приватний ключ. Наша служба підтримки допоможе з експортом сертифікату 
                            та інструкціями для установки на новому хостингу.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Section -->
    <section class="ssl-support">
        <div class="container">
            <div class="support-grid">
                <div class="support-info">
                    <h2 class="support-title">Потрібна допомога з вибором SSL?</h2>
                    <p class="support-description">
                        Наші експерти з безпеки допоможуть вибрати оптимальний SSL сертифікат для вашого проекту 
                        та проведуть безкоштовну консультацію щодо налаштування.
                    </p>
                    
                    <div class="support-features">
                        <div class="support-feature">
                            <i class="icon-phone"></i>
                            <div class="feature-content">
                                <h4>Телефонна консультація</h4>
                                <p>+38 (044) 123-45-67</p>
                            </div>
                        </div>
                        
                        <div class="support-feature">
                            <i class="icon-chat"></i>
                            <div class="feature-content">
                                <h4>Онлайн чат</h4>
                                <p>Відповідаємо протягом 2 хвилин</p>
                            </div>
                        </div>
                        
                        <div class="support-feature">
                            <i class="icon-email"></i>
                            <div class="feature-content">
                                <h4>Email підтримка</h4>
                                <p>ssl@sthost.pro</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="support-form">
                    <div class="form-header">
                        <h3>Замовити SSL сертифікат</h3>
                        <p>Заповніть форму і ми зв'яжемося з вами для оформлення замовлення</p>
                    </div>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="icon-check-circle"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="icon-alert-circle"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="ssl-order-form" id="sslOrderForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="order_ssl" value="1">
                        
                        <div class="form-group">
                            <label for="ssl_type" class="form-label">Тип SSL сертифікату</label>
                            <select name="ssl_type" id="ssl_type" class="form-select" required>
                                <option value="">Оберіть тип сертифікату</option>
                                <option value="dv">Domain Validation (DV) - 990 грн/рік</option>
                                <option value="ov">Organization Validation (OV) - 2,490 грн/рік</option>
                                <option value="ev">Extended Validation (EV) - 7,990 грн/рік</option>
                                <option value="wildcard">Wildcard SSL - 4,990 грн/рік</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="domain" class="form-label">Доменне ім'я</label>
                            <input type="text" name="domain" id="domain" class="form-input" 
                                   placeholder="example.com" required>
                            <div class="form-hint">
                                Вкажіть домен без www та протоколу
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email для зв'язку</label>
                            <input type="email" name="email" id="email" class="form-input" 
                                   placeholder="your@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Телефон (опціонально)</label>
                            <input type="tel" name="phone" id="phone" class="form-input" 
                                   placeholder="+38 (XXX) XXX-XX-XX">
                        </div>
                        
                        <div class="form-group">
                            <label for="comments" class="form-label">Додаткові побажання</label>
                            <textarea name="comments" id="comments" class="form-textarea" rows="3" 
                                      placeholder="Опишіть ваші побажання або особливі вимоги..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-large btn-full">
                            <i class="icon-send"></i>
                            Відправити заявку
                        </button>
                        
                        <div class="form-note">
                            Натиснувши кнопку, ви погоджуєтеся з 
                            <a href="/pages/info/legal" target="_blank">політикою конфіденційності</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Indicators -->
    <section class="trust-indicators">
        <div class="container">
            <div class="indicators-grid">
                <div class="indicator-item">
                    <div class="indicator-icon">
                        <i class="icon-users"></i>
                    </div>
                    <div class="indicator-content">
                        <div class="indicator-number">15,000+</div>
                        <div class="indicator-label">Активних SSL сертифікатів</div>
                    </div>
                </div>
                
                <div class="indicator-item">
                    <div class="indicator-icon">
                        <i class="icon-clock"></i>
                    </div>
                    <div class="indicator-content">
                        <div class="indicator-number">24/7</div>
                        <div class="indicator-label">Технічна підтримка</div>
                    </div>
                </div>
                
                <div class="indicator-item">
                    <div class="indicator-icon">
                        <i class="icon-shield-check"></i>
                    </div>
                    <div class="indicator-content">
                        <div class="indicator-number">99.9%</div>
                        <div class="indicator-label">Час роботи серверів</div>
                    </div>
                </div>
                
                <div class="indicator-item">
                    <div class="indicator-icon">
                        <i class="icon-award"></i>
                    </div>
                    <div class="indicator-content">
                        <div class="indicator-number">7 років</div>
                        <div class="indicator-label">Досвіду роботи</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Order Modal -->
<div class="modal" id="orderModal">
    <div class="modal-overlay" id="modalOverlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Замовлення SSL сертифікату</h3>
            <button class="modal-close" id="modalClose">
                <i class="icon-x"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="selected-ssl-info">
                <div class="ssl-info-card">
                    <div class="ssl-info-icon">
                        <i class="icon-certificate"></i>
                    </div>
                    <div class="ssl-info-details">
                        <h4 class="ssl-info-name" id="selectedSslName">-</h4>
                        <div class="ssl-info-price">
                            <span id="selectedSslPrice">-</span> грн/рік
                        </div>
                    </div>
                </div>
            </div>
            
            <form class="modal-form" id="modalOrderForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="ssl_type" id="modalSslType">
                
                <div class="form-group">
                    <label for="modalDomain" class="form-label">Доменне ім'я</label>
                    <input type="text" name="domain" id="modalDomain" class="form-input" 
                           placeholder="example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="modalEmail" class="form-label">Email</label>
                    <input type="email" name="email" id="modalEmail" class="form-input" 
                           placeholder="your@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="modalPhone" class="form-label">Телефон</label>
                    <input type="tel" name="phone" id="modalPhone" class="form-input" 
                           placeholder="+38 (XXX) XXX-XX-XX">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" id="modalCancel">
                        Скасувати
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-cart"></i>
                        Замовити сертифікат
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/assets/js/info-ssl.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>