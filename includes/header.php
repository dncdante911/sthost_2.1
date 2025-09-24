<?php
// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Проверяем авторизацию пользователя
$user_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

// Определяем текущую страницу для активных состояний
$current_page = $_SERVER['REQUEST_URI'];
$page_parts = explode('/', trim($current_page, '/'));
$page = $page_parts[1] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang ?? 'uk'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'StormHosting UA'; ?></title>
    <meta name="description" content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'Надійний хостинг для вашого бізнесу'; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Modal Auth CSS -->
    <link rel="stylesheet" href="/assets/css/pages/modal-auth.css">
   

<style>
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --secondary-color: #6366f1;
            --text-color: #1f2937;
            --bg-color: #ffffff;
            --border-color: #e5e7eb;
            --menu-bg: rgba(255, 255, 255, 0.95);
        }

        /* Header Styles */
        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1000;
        }

        .navbar {
            padding: 1rem 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
            color: #f0f8ff !important;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            margin-right: 0.75rem;
            filter: brightness(0) invert(1);
        }

        /* Navigation Links */
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.75rem 1rem;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link.active {
            color: white !important;
            background: rgba(255, 255, 255, 0.2);
        }

        /* Auth Buttons */
        .auth-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-auth-header {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-login {
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
            background: transparent;
        }

        .btn-login:hover {
            color: #667eea;
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-register {
            color: #667eea;
            background: white;
            border-color: white;
        }

        .btn-register:hover {
            color: white;
            background: transparent;
            border-color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-logout {
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
            background: transparent;
        }

        .btn-logout:hover {
            color: #dc3545;
            background: white;
            border-color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* User Info */
        .user-info {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            margin-right: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }

        /* Menu Toggle Button */
        .menu-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: white;
        }

        .menu-icon {
            width: 20px;
            height: 20px;
            position: relative;
        }

        .menu-icon span {
            display: block;
            width: 100%;
            height: 2px;
            background: currentColor;
            transition: all 0.3s ease;
            border-radius: 1px;
        }

        .menu-icon span:nth-child(1) { transform: translateY(-6px); }
        .menu-icon span:nth-child(3) { transform: translateY(6px); }

        /* Slide Menu */
        .slide-menu {
            position: fixed;
            top: 0;
            right: -420px;
            width: 420px;
            height: 100vh;
            background: var(--menu-bg);
            backdrop-filter: blur(20px);
            box-shadow: -5px 0 25px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            transition: right 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            overflow-y: auto;
        }

        .slide-menu.open {
            right: 0;
        }

        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .menu-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .menu-header {
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .menu-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .menu-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .menu-content {
            padding: 1.5rem;
        }

        .menu-section {
            margin-bottom: 2rem;
        }

        .menu-section-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .menu-section-title:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
        }

        .menu-items {
            display: none;
            padding-left: 1rem;
        }

        .menu-items.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 0.25rem;
        }

        .menu-item:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            opacity: 0.7;
        }

        .menu-item-content {
            flex: 1;
        }

        .menu-item-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .menu-item-desc {
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Quick Actions */
        .quick-actions {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .quick-action-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .slide-menu {
                width: 100%;
                right: -100%;
            }

            .navbar-nav {
                display: none;
            }

            .auth-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .btn-auth-header {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }

            .user-info {
                flex-direction: column;
                align-items: flex-end;
                margin-right: 0.5rem;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                margin-left: 1rem;
            }
        }
</style>
 
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <!-- Brand Logo -->
                <a class="navbar-brand" href="/">
                <!--    <img src="/assets/images/Black.png" class="brand-logo">-->
                    <span>StormHosting UA</span>
                </a>

                <!-- Main Navigation (Desktop) -->
                <ul class="navbar-nav mx-auto d-none d-lg-flex">
                    <li class="nav-item">
                        <a class="nav-link<?php echo ($page === '' || $page === 'index') ? ' active' : ''; ?>" href="/">
                            Головна
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/contacts.php">
                            Контакти
                        </a>
                    </li>
                </ul>

                <!-- Auth Buttons & Menu Toggle -->
                <div class="d-flex align-items-center">
                    <?php if ($user_logged_in): ?>
                        <!-- Информация о пользователе -->
                        <div class="user-info d-none d-sm-flex">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($user_name); ?>
                                </div>
                                <div style="font-size: 0.75rem; opacity: 0.8;">
                                    <?php echo htmlspecialchars($user_email); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопка выхода -->
                        <div class="auth-buttons me-3">
                            <a href="/auth/logout.php" class="btn-auth-header btn-logout" onclick="return confirm('Ви впевнені, що хочете вийти?')">
                                <i class="bi bi-box-arrow-right"></i>
                                Вийти
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Кнопки авторизации для неавторизованных -->
                        <div class="auth-buttons me-3">
                            <a href="#" class="btn-auth-header btn-login" data-open-login>
                                <i class="bi bi-box-arrow-in-right"></i>
                                Вхід
                            </a>
                            <a href="#" class="btn-auth-header btn-register" data-open-register>
                                <i class="bi bi-person-plus"></i>
                                Реєстрація
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Menu Toggle Button -->
                    <button type="button" class="menu-toggle" id="menuToggle">
                        <div class="menu-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span class="d-none d-sm-inline">Меню</span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Slide Menu -->
    <div class="slide-menu" id="slideMenu">
        <div class="menu-header">
            <h3>Навігація</h3>
            <?php if ($user_logged_in): ?>
                <div class="d-sm-none mt-2" style="font-size: 0.9rem; opacity: 0.9;">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo htmlspecialchars($user_name); ?>
                </div>
            <?php endif; ?>
            <button type="button" class="menu-close" id="menuClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="menu-content">
            <?php if ($user_logged_in): ?>
                <!-- Панель пользователя в меню -->
                <div class="menu-section">
                    <div class="menu-section-title">
                        <i class="bi bi-person-circle"></i>
                        <span>Мій кабінет</span>
                    </div>
                    <div class="menu-items show">
                        <a href="/client/dashboard.php" class="menu-item">
                            <i class="bi bi-speedometer2"></i>
                            <div class="menu-item-content">
                                <div class="menu-item-title">Панель управління</div>
                                <div class="menu-item-desc">Головна сторінка кабінету</div>
                            </div>
                        </a>
                        <a href="/client/profile.php" class="menu-item">
                            <i class="bi bi-person-gear"></i>
                            <div class="menu-item-content">
                                <div class="menu-item-title">Налаштування профілю</div>
                                <div class="menu-item-desc">Редагування даних</div>
                            </div>
                        </a>
                        <a href="/auth/logout.php" class="menu-item" onclick="return confirm('Ви впевнені, що хочете вийти?')">
                            <i class="bi bi-box-arrow-right"></i>
                            <div class="menu-item-content">
                                <div class="menu-item-title">Вийти з системи</div>
                                <div class="menu-item-desc">Завершити сеанс</div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Domains Section -->
            <div class="menu-section">
                <div class="menu-section-title" onclick="toggleMenuSection('domains')">
                    <i class="bi bi-globe"></i>
                    <span>Домени</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </div>
                <div class="menu-items" id="domains-items">
                    <a href="/pages/domains/register.php" class="menu-item">
                        <i class="bi bi-plus-circle"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Реєстрація доменів</div>
                            <div class="menu-item-desc">Зареєструйте домен для вашого сайту</div>
                        </div>
                    </a>
                    <a href="/pages/domains/transfer.php" class="menu-item">
                        <i class="bi bi-arrow-left-right"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Трансфер доменів</div>
                            <div class="menu-item-desc">Перенесіть домен до нас</div>
                        </div>
                    </a>
                    <a href="/pages/domains/whois.php" class="menu-item">
                        <i class="bi bi-search"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">WHOIS перевірка</div>
                            <div class="menu-item-desc">Інформація про домен</div>
                        </div>
                    </a>
                    <a href="/pages/domains/dns.php" class="menu-item">
                        <i class="bi bi-diagram-3"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">DNS перевірка</div>
                            <div class="menu-item-desc">Перевірка DNS записів</div>
                        </div>
                    </a>
                    <a href="/pages/domains/domains.php" class="menu-item">
                        <i class="bi bi-list-ul"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Всі домени</div>
                            <div class="menu-item-desc">Перегляд доменних зон</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Hosting Section -->
            <div class="menu-section">
                <div class="menu-section-title" onclick="toggleMenuSection('hosting')">
                    <i class="bi bi-server"></i>
                    <span>Хостинг</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </div>
                <div class="menu-items" id="hosting-items">
                    <a href="/pages/hosting/shared.php" class="menu-item">
                        <i class="bi bi-share"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Спільний хостинг</div>
                            <div class="menu-item-desc">Оптимальний для сайтів</div>
                        </div>
                    </a>
                    <a href="/pages/hosting/reseller.php" class="menu-item">
                        <i class="bi bi-people"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Реселер хостинг</div>
                            <div class="menu-item-desc">Продавайте хостинг клієнтам</div>
                        </div>
                    </a>
                    <a href="/pages/hosting/cloud.php" class="menu-item">
                        <i class="bi bi-cloud"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Хмарне сховище</div>
                            <div class="menu-item-desc">Зберігайте файли в хмарі</div>
                        </div>
                    </a>
                    <a href="/pages/hosting/hosting.php" class="menu-item">
                        <i class="bi bi-grid"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Всі тарифи</div>
                            <div class="menu-item-desc">Порівняння планів</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- VDS/VPS Section -->
            <div class="menu-section">
                <div class="menu-section-title" onclick="toggleMenuSection('vds')">
                    <i class="bi bi-pc-display"></i>
                    <span>VDS/VPS</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </div>
                <div class="menu-items" id="vds-items">
                    <a href="/pages/vds/virtual.php" class="menu-item">
                        <i class="bi bi-cpu"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Віртуальні сервери</div>
                            <div class="menu-item-desc">VPS на KVM</div>
                        </div>
                    </a>
                    <a href="/pages/vds/dedicated.php" class="menu-item">
                        <i class="bi bi-pc-display-horizontal"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Виділені сервери</div>
                            <div class="menu-item-desc">Фізичні сервери</div>
                        </div>
                    </a>
                    <a href="/pages/vds/vds-calc.php" class="menu-item">
                        <i class="bi bi-calculator"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Калькулятор VDS</div>
                            <div class="menu-item-desc">Розрахунок вартості</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Tools Section -->
            <div class="menu-section">
                <div class="menu-section-title" onclick="toggleMenuSection('tools')">
                    <i class="bi bi-tools"></i>
                    <span>Інструменти</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </div>
                <div class="menu-items" id="tools-items">
                    <a href="/pages/tools/site-check.php" class="menu-item">
                        <i class="bi bi-globe2"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Перевірка сайту</div>
                            <div class="menu-item-desc">Доступність сайту</div>
                        </div>
                    </a>
                    <a href="/pages/tools/ip-check.php" class="menu-item">
                        <i class="bi bi-router"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Перевірка IP</div>
                            <div class="menu-item-desc">Геолокація та безпека</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Info Section -->
            <div class="menu-section">
                <div class="menu-section-title" onclick="toggleMenuSection('info')">
                    <i class="bi bi-info-circle"></i>
                    <span>Інформація</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </div>
                <div class="menu-items" id="info-items">
                    <a href="/pages/info/about.php" class="menu-item">
                        <i class="bi bi-building"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Про компанію</div>
                            <div class="menu-item-desc">Наша історія та місія</div>
                        </div>
                    </a>
                    <a href="/pages/info/quality.php" class="menu-item">
                        <i class="bi bi-shield-check"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Гарантія якості</div>
                            <div class="menu-item-desc">SLA та стандарти</div>
                        </div>
                    </a>
                    <a href="/pages/info/rules.php" class="menu-item">
                        <i class="bi bi-file-text"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Правила надання послуг</div>
                            <div class="menu-item-desc">Умови використання</div>
                        </div>
                    </a>
                    <a href="/pages/info/legal.php" class="menu-item">
                        <i class="bi bi-briefcase"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Юридична інформація</div>
                            <div class="menu-item-desc">Реквізити компанії</div>
                        </div>
                    </a>
                    <a href="/pages/info/faq.php" class="menu-item">
                        <i class="bi bi-question-circle"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Часті питання</div>
                            <div class="menu-item-desc">Відповіді на питання</div>
                        </div>
                    </a>
                    <a href="/pages/info/ssl.php" class="menu-item">
                        <i class="bi bi-shield-lock"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">SSL сертифікати</div>
                            <div class="menu-item-desc">Захист та довіра</div>
                        </div>
                    </a>
                    <a href="/pages/info/complaints.php" class="menu-item">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">Скарги та пропозиції</div>
                            <div class="menu-item-desc">Зворотний зв'язок</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="/pages/hosting/shared.php" class="quick-action-btn">
                    <i class="bi bi-server me-2"></i>
                    Замовити хостинг
                </a>
                <a href="/pages/domains/register.php" class="quick-action-btn">
                    <i class="bi bi-globe me-2"></i>
                    Купити домен
                </a>
            </div>

            <!-- Contact Info -->
            <div class="menu-section">
                <div class="menu-section-title">
                    <i class="bi bi-telephone"></i>
                    <span>Контакти</span>
                </div>
                <div class="menu-items show">
                    <div class="menu-item">
                        <i class="bi bi-telephone"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">+380 99 623 96 37</div>
                            <div class="menu-item-desc">Цілодобово</div>
                        </div>
                    </div>
                    <div class="menu-item">
                        <i class="bi bi-envelope"></i>
                        <div class="menu-item-content">
                            <div class="menu-item-title">support@sthost.pro</div>
                            <div class="menu-item-desc">Підтримка</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Overlay -->
    <div class="menu-overlay" id="menuOverlay"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <!-- Modal Auth JS (только для неавторизованных пользователей) -->
    <?php if (!$user_logged_in): ?>
        <script src="/assets/js/modal-auth.js"></script>
    <?php endif; ?>

    <!-- Header JavaScript -->
    <script>
        // Menu Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const slideMenu = document.getElementById('slideMenu');
            const menuOverlay = document.getElementById('menuOverlay');
            const menuClose = document.getElementById('menuClose');

            // Open menu
            function openMenu() {
                slideMenu.classList.add('open');
                menuOverlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            // Close menu
            function closeMenu() {
                slideMenu.classList.remove('open');
                menuOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }

            // Event listeners
            menuToggle.addEventListener('click', openMenu);
            menuClose.addEventListener('click', closeMenu);
            menuOverlay.addEventListener('click', closeMenu);

            // Close menu on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && slideMenu.classList.contains('open')) {
                    closeMenu();
                }
            });

            // Initialize Auth Modal only for non-logged users
            <?php if (!$user_logged_in): ?>
                if (typeof AuthModal !== 'undefined') {
                    window.authModal = new AuthModal();
                }
            <?php endif; ?>
        });

        // Toggle menu sections
        function toggleMenuSection(sectionId) {
            const items = document.getElementById(sectionId + '-items');
            const title = event.currentTarget;
            const chevron = title.querySelector('.bi-chevron-down');
            
            if (items.classList.contains('show')) {
                items.classList.remove('show');
                chevron.style.transform = 'rotate(0deg)';
            } else {
                // Close all other sections
                document.querySelectorAll('.menu-items.show').forEach(item => {
                    if (item.id !== sectionId + '-items' && !item.closest('.menu-section').querySelector('.menu-section-title').textContent.includes('Контакти') && !item.closest('.menu-section').querySelector('.menu-section-title').textContent.includes('Мій кабінет')) {
                        item.classList.remove('show');
                    }
                });
                document.querySelectorAll('.bi-chevron-down').forEach(icon => {
                    if (icon !== chevron) {
                        icon.style.transform = 'rotate(0deg)';
                    }
                });
                
                // Open current section
                items.classList.add('show');
                chevron.style.transform = 'rotate(180deg)';
            }
        }
    </script>

    <?php if (isset($additional_js) && is_array($additional_js)): ?>
        <?php foreach ($additional_js as $js_file): ?>
            <script src="<?php echo htmlspecialchars($js_file); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>