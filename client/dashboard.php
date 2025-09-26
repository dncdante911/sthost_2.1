<?php
// ============================================
// DASHBOARD - PHP ЛОГИКА
// ============================================

define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    header('Location: /auth/login.php');
    exit;
}

// Данные пользователя
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$fossbilling_client_id = $_SESSION['fossbilling_client_id'];

// Получаем информацию о пользователе
$user_info = DatabaseConnection::fetchOne(
    "SELECT * FROM users WHERE id = ?",
    [$user_id]
);


// Инициализация статистики услуг
$services_stats = [
    'domains' => 0,
    'hosting' => 0, 
    'vps' => 0,
    'active_services' => 0
];

// Получаем VPS статистику (если таблицы существуют)
try {
    // Общее количество VPS
    $stmt = $pdo->prepare("SELECT COUNT(*) as vps_count FROM vps_instances WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $vps_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $services_stats['vps'] = $vps_result['vps_count'] ?? 0;
    
    // Активные VPS
    $stmt = $pdo->prepare("SELECT COUNT(*) as active_vps FROM vps_instances WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $active_vps = $stmt->fetch(PDO::FETCH_ASSOC);
    $services_stats['active_services'] += $active_vps['active_vps'] ?? 0;
    
} catch (Exception $e) {
    // VPS таблицы не созданы - это нормально для начала
}

// Получаем заказы (заглушка для будущей интеграции с FOSSBilling)
$recent_orders = [];

// Получаем счета (заглушка для будущей интеграции с FOSSBilling)  
$invoices = [];

// Получаем VPS операции (если есть)
$recent_vps_operations = [];
try {
    $stmt = $pdo->prepare("
        SELECT vol.*, vi.hostname 
        FROM vps_operations_log vol
        LEFT JOIN vps_instances vi ON vol.vps_id = vi.id
        WHERE vol.user_id = ? 
        ORDER BY vol.started_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_vps_operations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Таблица логов не существует
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Особистий кабінет - StormHosting UA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">


     <link rel="stylesheet" href="/assets/css/dashboard.css">
     
</head>
<body>

<!-- ============================================
     BREADCRUMBS
============================================ -->
<div class="breadcrumbs">
    <div class="container">
        <a href="/" class="breadcrumb-link">Головна</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Особистий кабінет</span>
    </div>
</div>

<!-- ============================================
     MAIN CONTENT WRAPPER
============================================ -->
<main class="main-content client-dashboard">
    <div class="container">
        
        <!-- ============================================
             WELCOME SECTION
        ============================================ -->
        <section class="welcome-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="welcome-content">
                        <!-- Welcome Badge -->
                        <div class="welcome-badge">
                            <i class="bi bi-person-circle"></i>
                            <span>Особистий кабінет</span>
                        </div>
                        
                        <!-- Main Welcome Title -->
                        <h1 class="welcome-title">
                            Вітаємо, <span class="text-highlight"><?php echo htmlspecialchars($user_name); ?></span>!
                        </h1>
                        
                        <!-- Subtitle -->
                        <p class="welcome-subtitle">
                            Керуйте своїми послугами, відстежуйте статистику та налаштовуйте свій аккаунт
                        </p>
                        
                        <!-- User Info -->
                        <div class="welcome-info">
                            <div class="info-item">
                                <i class="bi bi-envelope"></i>
                                <span><?php echo htmlspecialchars($user_email); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-calendar"></i>
                                <span>Учасник з <?php echo date('d.m.Y', strtotime($user_info['created_at'])); ?></span>
                            </div>
                            <?php if ($fossbilling_client_id): ?>
                            <div class="info-item">
                                <i class="bi bi-credit-card"></i>
                                <span>ID клієнта: #<?php echo $fossbilling_client_id; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Welcome Illustration -->
                <div class="col-lg-4">
                    <div class="welcome-illustration">
                        <div class="floating-card">
                            <div class="card-glow"></div>
                            <div class="card-content">
                                <i class="bi bi-speedometer2"></i>
                                <div class="card-text">Dashboard</div>
                            </div>
                        </div>
                        <div class="floating-elements">
                            <div class="element element-1"><i class="bi bi-globe"></i></div>
                            <div class="element element-2"><i class="bi bi-server"></i></div>
                            <div class="element element-3"><i class="bi bi-hdd-rack"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================
             STATISTICS SECTION
        ============================================ -->
        <section class="statistics-section">
            <div class="row g-4">
                
                <!-- Domains Statistics -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card">
                        <div class="stats-card-inner">
                            <div class="stats-icon domains-icon">
                                <i class="bi bi-globe"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $services_stats['domains']; ?></div>
                                <div class="stats-label">Доменів</div>
                                <div class="stats-trend">
                                    <i class="bi bi-arrow-up"></i>
                                    <span>Всі активні</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-progress">
                            <div class="progress-bar domains-progress" style="width: <?php echo min(($services_stats['domains'] / 10) * 100, 100); ?>%"></div>
                        </div>
                    </div>
                </div>

                <!-- Hosting Statistics -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card">
                        <div class="stats-card-inner">
                            <div class="stats-icon hosting-icon">
                                <i class="bi bi-server"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $services_stats['hosting']; ?></div>
                                <div class="stats-label">Хостинг пакетів</div>
                                <div class="stats-trend">
                                    <i class="bi bi-arrow-up"></i>
                                    <span>Готові до використання</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-progress">
                            <div class="progress-bar hosting-progress" style="width: <?php echo min(($services_stats['hosting'] / 5) * 100, 100); ?>%"></div>
                        </div>
                    </div>
                </div>

                <!-- VPS Statistics - ОСОБЛИВА КАРТОЧКА -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card vps-stats-card">
                        <div class="stats-card-inner">
                            <div class="stats-icon vps-icon">
                                <i class="bi bi-hdd-rack"></i>
                                <?php if ($services_stats['vps'] == 0): ?>
                                <div class="new-feature-badge">
                                    <span>НОВІ!</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $services_stats['vps']; ?></div>
                                <div class="stats-label">VPS серверів</div>
                                <div class="stats-trend vps-trend">
                                    <i class="bi bi-lightning-charge"></i>
                                    <span>KVM віртуалізація</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-progress">
                            <div class="progress-bar vps-progress" style="width: <?php echo min(($services_stats['vps'] / 10) * 100, 100); ?>%"></div>
                        </div>
                        <div class="vps-sparkle"></div>
                    </div>
                </div>

                <!-- Active Services Statistics -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="stats-card">
                        <div class="stats-card-inner">
                            <div class="stats-icon active-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $services_stats['active_services']; ?></div>
                                <div class="stats-label">Активних послуг</div>
                                <div class="stats-trend">
                                    <i class="bi bi-heart-fill"></i>
                                    <span>Працюють стабільно</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-progress">
                            <div class="progress-bar active-progress" style="width: <?php echo $services_stats['active_services'] > 0 ? 100 : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
                
            </div>
        </section>

        <!-- ============================================
             QUICK ACTIONS SECTION
        ============================================ -->
        <section class="quick-actions-section">
            <!-- Section Header -->
            <div class="section-header">
                <h2 class="section-title">
                    <i class="bi bi-lightning-charge"></i>
                    Швидкі дії
                </h2>
                <p class="section-subtitle">Найпопулярніші операції одним кліком</p>
            </div>
            
            <!-- Actions Grid -->
            <div class="quick-actions-grid">
                
                <!-- Domain Registration -->
                <a href="/pages/domains/" class="action-card domains-card">
                    <div class="action-card-bg"></div>
                    <div class="action-card-content">
                        <div class="action-icon">
                            <i class="bi bi-globe"></i>
                        </div>
                        <div class="action-info">
                            <h4>Зареєструвати домен</h4>
                            <p>Нові домени від 120₴/рік</p>
                            <div class="action-features">
                                <span>🔒 SSL включено</span>
                                <span>⚡ Миттєва активація</span>
                            </div>
                        </div>
                        <div class="action-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </a>

                <!-- Hosting Order -->
                <a href="/pages/hosting/" class="action-card hosting-card">
                    <div class="action-card-bg"></div>
                    <div class="action-card-content">
                        <div class="action-icon">
                            <i class="bi bi-server"></i>
                        </div>
                        <div class="action-info">
                            <h4>Замовити хостинг</h4>
                            <p>SSD хостинг від 99₴/місяць</p>
                            <div class="action-features">
                                <span>💾 SSD накопичувачі</span>
                                <span>🚀 99.9% Uptime</span>
                            </div>
                        </div>
                        <div class="action-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </a>

                <!-- VPS Management - ГОЛОВНА ФІШКА -->
                <a href="/client/vps/" class="action-card vps-card featured-card">
                    <div class="action-card-bg"></div>
                    <div class="featured-glow"></div>
                    <div class="action-card-content">
                        <div class="action-icon vps-action-icon">
                            <i class="bi bi-hdd-rack"></i>
                            <?php if ($services_stats['vps'] == 0): ?>
                            <div class="pulse-ring"></div>
                            <?php endif; ?>
                        </div>
                        <div class="action-info">
                            <h4>
                                Керування VPS
                                <?php if ($services_stats['vps'] == 0): ?>
                                <span class="new-badge">NEW</span>
                                <?php endif; ?>
                            </h4>
                            <p>Віртуальні сервери від 299₴/міс</p>
                            <div class="action-features vps-features">
                                <span>⚡ KVM віртуалізація</span>
                                <span>🖥️ VNC консоль</span>
                                <span>🔧 Root доступ</span>
                            </div>
                        </div>
                        <div class="action-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                    <?php if ($services_stats['vps'] == 0): ?>
                    <div class="sparkles">
                        <div class="sparkle sparkle-1">✨</div>
                        <div class="sparkle sparkle-2">⭐</div>
                        <div class="sparkle sparkle-3">💫</div>
                    </div>
                    <?php endif; ?>
                </a>

                <!-- Profile Settings -->
                <a href="/client/profile.php" class="action-card profile-card">
                    <div class="action-card-bg"></div>
                    <div class="action-card-content">
                        <div class="action-icon">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <div class="action-info">
                            <h4>Налаштування профілю</h4>
                            <p>Безпека та персоналізація</p>
                            <div class="action-features">
                                <span>🔐 Двофакторна аутентифікація</span>
                                <span>🎨 Персональні налаштування</span>
                            </div>
                        </div>
                        <div class="action-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </a>
                
            </div>
        </section>

<!-- ============================================
             CONTENT SECTIONS ROW
        ============================================ -->
        <div class="row">
            
            <!-- ============================================
                 RECENT ORDERS SECTION
            ============================================ -->
            <div class="col-lg-6">
                <div class="content-card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div>
                                <h3 class="card-title">Останні замовлення</h3>
                                <p class="card-subtitle">Історія ваших покупок</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <button class="btn-icon" title="Оновити">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <div class="empty-illustration">
                                <i class="bi bi-bag"></i>
                            </div>
                            <h4>Немає замовлень</h4>
                            <p>Замовлення з'являться після оформлення послуг</p>
                            <div class="empty-actions">
                                <a href="/pages/hosting/" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle me-2"></i>Зробити перше замовлення
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Orders List -->
                        <div class="orders-list">
                            <?php foreach ($recent_orders as $order): ?>
                            <div class="order-item">
                                <div class="order-icon">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="order-info">
                                    <h6 class="order-title">Замовлення #<?php echo $order['id']; ?></h6>
                                    <p class="order-service"><?php echo htmlspecialchars($order['service_name']); ?></p>
                                    <small class="order-date">
                                        <i class="bi bi-calendar3"></i>
                                        <?php echo date('d.m.Y', strtotime($order['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php 
                                        switch($order['status']) {
                                            case 'active': echo 'Активне'; break;
                                            case 'pending': echo 'Очікування'; break;
                                            case 'suspended': echo 'Призупинене'; break;
                                            default: echo 'Невідомо';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Card Footer -->
                    <?php if (!empty($recent_orders)): ?>
                    <div class="card-footer">
                        <a href="/client/orders.php" class="btn btn-outline btn-sm">
                            Всі замовлення
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- ============================================
                 RECENT INVOICES SECTION
            ============================================ -->
            <div class="col-lg-6">
                <div class="content-card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div>
                                <h3 class="card-title">Рахунки до сплати</h3>
                                <p class="card-subtitle">Фінансовий стан аккаунту</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <button class="btn-icon" title="Оновити">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body">
                        <?php if (empty($invoices)): ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <div class="empty-illustration">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <h4>Немає рахунків</h4>
                            <p>Рахунки з'являться після замовлення послуг</p>
                            <div class="balance-info">
                                <div class="balance-item positive">
                                    <span class="balance-label">Поточний баланс:</span>
                                    <span class="balance-value">0.00 ₴</span>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Invoices List -->
                        <div class="invoices-list">
                            <?php foreach ($invoices as $invoice): ?>
                            <div class="invoice-item">
                                <div class="invoice-icon">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="invoice-info">
                                    <h6 class="invoice-title">Рахунок #<?php echo $invoice['id']; ?></h6>
                                    <p class="invoice-amount"><?php echo number_format($invoice['amount'], 2); ?> ₴</p>
                                    <small class="invoice-date">
                                        <i class="bi bi-calendar3"></i>
                                        <?php echo date('d.m.Y', strtotime($invoice['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="invoice-status">
                                    <span class="status-badge status-<?php echo $invoice['status']; ?>">
                                        <?php 
                                        switch($invoice['status']) {
                                            case 'paid': echo 'Оплачено'; break;
                                            case 'pending': echo 'Очікування'; break;
                                            case 'overdue': echo 'Прострочено'; break;
                                            default: echo 'Невідомо';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Card Footer -->
                    <?php if (!empty($invoices)): ?>
                    <div class="card-footer">
                        <a href="/client/invoices.php" class="btn btn-outline btn-sm">
                            Всі рахунки
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>

        <!-- ============================================
             VPS ACTIVITY SECTION - Тільки якщо є VPS
        ============================================ -->
        <?php if ($services_stats['vps'] > 0 || !empty($recent_vps_operations)): ?>
        <section class="vps-activity-section">
            <div class="content-card vps-activity-card">
                <!-- Card Header -->
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon vps-header-icon">
                            <i class="bi bi-hdd-rack"></i>
                        </div>
                        <div>
                            <h3 class="card-title">Активність VPS серверів</h3>
                            <p class="card-subtitle">Останні операції з віртуальними серверами</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="/client/vps/" class="btn btn-primary btn-sm">
                            <i class="bi bi-gear me-2"></i>Керувати VPS
                        </a>
                    </div>
                </div>
                
                <!-- Card Body -->
                <div class="card-body">
                    <?php if (!empty($recent_vps_operations)): ?>
                    <!-- VPS Operations List -->
                    <div class="vps-operations-list">
                        <?php foreach ($recent_vps_operations as $operation): ?>
                        <div class="operation-item">
                            <div class="operation-icon status-<?php echo $operation['status']; ?>">
                                <?php
                                switch($operation['operation']) {
                                    case 'create': echo '<i class="bi bi-plus-circle"></i>'; break;
                                    case 'start': echo '<i class="bi bi-play-circle"></i>'; break;
                                    case 'stop': echo '<i class="bi bi-stop-circle"></i>'; break;
                                    case 'restart': echo '<i class="bi bi-arrow-clockwise"></i>'; break;
                                    default: echo '<i class="bi bi-gear"></i>';
                                }
                                ?>
                            </div>
                            <div class="operation-info">
                                <h6 class="operation-title">
                                    <?php
                                    switch($operation['operation']) {
                                        case 'create': echo 'Створення VPS'; break;
                                        case 'start': echo 'Запуск VPS'; break;
                                        case 'stop': echo 'Зупинка VPS'; break;
                                        case 'restart': echo 'Перезапуск VPS'; break;
                                        default: echo ucfirst($operation['operation']);
                                    }
                                    ?>
                                </h6>
                                <p class="operation-target"><?php echo htmlspecialchars($operation['hostname']); ?></p>
                                <small class="operation-time">
                                    <i class="bi bi-clock"></i>
                                    <?php echo date('d.m.Y H:i', strtotime($operation['started_at'])); ?>
                                </small>
                            </div>
                            <div class="operation-status">
                                <span class="status-badge status-<?php echo $operation['status']; ?>">
                                    <?php
                                    switch($operation['status']) {
                                        case 'completed': echo 'Виконано'; break;
                                        case 'running': echo 'Виконується'; break;
                                        case 'failed': echo 'Помилка'; break;
                                        case 'pending': echo 'Очікування'; break;
                                        default: echo 'Невідомо';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <!-- Empty VPS State -->
                    <div class="empty-state vps-empty">
                        <div class="empty-illustration vps-empty-icon">
                            <i class="bi bi-hdd-rack"></i>
                        </div>
                        <h4>Немає операцій</h4>
                        <p>Операції з VPS серверами з'являться тут</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============================================
             ACCOUNT INFORMATION SECTION
        ============================================ -->
        <section class="account-section">
            <div class="content-card">
                <!-- Card Header -->
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div>
                            <h3 class="card-title">Інформація про аккаунт</h3>
                            <p class="card-subtitle">Ваші особисті дані та налаштування</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="/client/profile.php" class="btn btn-outline btn-sm">
                            <i class="bi bi-pencil-square me-2"></i>Редагувати
                        </a>
                    </div>
                </div>
                
                <!-- Card Body -->
                <div class="card-body">
                    <div class="account-info-grid">
                        
                        <!-- Full Name -->
                        <div class="info-group">
                            <div class="info-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="info-content">
                                <label>Повне ім'я</label>
                                <value><?php echo htmlspecialchars($user_info['full_name']); ?></value>
                            </div>
                        </div>
                        
                        <!-- Email Address -->
                        <div class="info-group">
                            <div class="info-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="info-content">
                                <label>Email адреса</label>
                                <value><?php echo htmlspecialchars($user_info['email']); ?></value>
                                <div class="verification-status verified">
                                    <i class="bi bi-check-circle"></i>
                                    Підтверджено
                                </div>
                            </div>
                        </div>
                        
                        <!-- Phone Number -->
                        <div class="info-group">
                            <div class="info-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="info-content">
                                <label>Номер телефону</label>
                                <value><?php echo htmlspecialchars($user_info['phone'] ?? 'Не вказано'); ?></value>
                            </div>
                        </div>
                        
                        <!-- Language -->
                        <div class="info-group">
                            <div class="info-icon">
                                <i class="bi bi-globe"></i>
                            </div>
                            <div class="info-content">
                                <label>Мова інтерфейсу</label>
                                <value>
                                    <?php 
                                    $languages = ['ua' => 'Українська', 'en' => 'English', 'ru' => 'Русский'];
                                    echo $languages[$user_info['language']] ?? 'Українська';
                                    ?>
                                </value>
                            </div>
                        </div>
                        
                        <!-- Registration Date -->
                        <div class="info-group">
                            <div class="info-icon">
                                <i class="bi bi-calendar-plus"></i>
                            </div>
                            <div class="info-content">
                                <label>Дата реєстрації</label>
                                <value><?php echo date('d.m.Y', strtotime($user_info['created_at'])); ?></value>
                            </div>
                        </div>
                        
                        <!-- Account Status -->
                        <div class="info-group">
                            <div class="info-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="info-content">
                                <label>Статус аккаунту</label>
                                <value>
                                    <span class="status-badge status-active">
                                        <i class="bi bi-check-circle"></i>
                                        Активний
                                    </span>
                                </value>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- ============================================
     ПОДКЛЮЧЕНИЕ СКРИПТОВ
============================================ -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- ПОДКЛЮЧЕНИЕ CUSTOM JAVASCRIPT -->
<script src="/assets/js/dashboard.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

</body>
</html>