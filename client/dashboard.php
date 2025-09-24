<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Проверяем авторизацию
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    header('Location: /auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$fossbilling_client_id = $_SESSION['fossbilling_client_id'];

// Получаем информацию о пользователе
$user_info = DatabaseConnection::fetchOne(
    "SELECT * FROM users WHERE id = ?",
    [$user_id]
);

// Получаем статистику услуг (заглушки)
$services_stats = [
    'domains' => 0,
    'hosting' => 0,
    'vps' => 0,
    'active_services' => 0
];

// Получаем последние заказы (заглушки)
$recent_orders = [];

// Получаем счета (заглушки)
$invoices = [];

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
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-container {
            padding: 30px 0;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-card h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-card .user-info {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 15px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #666;
            font-size: 14px;
        }
        
        .section-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .section-header {
            background: #f8f9fa;
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .section-header h3 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        
        .section-body {
            padding: 25px;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .action-btn i {
            margin-right: 8px;
        }
        
        .service-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .service-item:last-child {
            border-bottom: none;
        }
        
        .service-info h5 {
            margin: 0 0 5px 0;
            font-weight: 600;
            color: #333;
        }
        
        .service-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .service-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-suspended {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-action-card {
            background: white;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .quick-action-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            color: #333;
        }
        
        .quick-action-card i {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .quick-action-card h6 {
            font-weight: 600;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px 0;
            }
            
            .welcome-card {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .stats-card {
                padding: 20px;
                margin-bottom: 15px;
            }
            
            .section-header,
            .section-body {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Вітаємо, <?php echo escapeOutput($user_name); ?>!</h1>
                    <div class="user-info">
                        <i class="bi bi-envelope me-2"></i><?php echo escapeOutput($user_email); ?>
                        <br>
                        <i class="bi bi-calendar me-2"></i>Член з <?php echo date('d.m.Y', strtotime($user_info['registration_date'])); ?>
                        <?php if ($user_info['last_login']): ?>
                        <br>
                        <i class="bi bi-clock me-2"></i>Останній вхід: <?php echo date('d.m.Y H:i', strtotime($user_info['last_login'])); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="/client/profile.php" class="btn btn-light btn-lg">
                        <i class="bi bi-person-gear me-2"></i>Налаштування профілю
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="bi bi-globe"></i>
                    </div>
                    <div class="stats-number"><?php echo $services_stats['domains']; ?></div>
                    <div class="stats-label">Домени</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="bi bi-server"></i>
                    </div>
                    <div class="stats-number"><?php echo $services_stats['hosting']; ?></div>
                    <div class="stats-label">Хостинг</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="bi bi-hdd-stack"></i>
                    </div>
                    <div class="stats-number"><?php echo $services_stats['vps']; ?></div>
                    <div class="stats-label">VPS/VDS</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stats-number"><?php echo $services_stats['active_services']; ?></div>
                    <div class="stats-label">Активні послуги</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="section-card">
            <div class="section-header">
                <h3><i class="bi bi-lightning me-2"></i>Швидкі дії</h3>
            </div>
            <div class="section-body">
                <div class="quick-actions">
                    <a href="/pages/domains/register.php" class="quick-action-card">
                        <i class="bi bi-globe"></i>
                        <h6>Зареєструвати домен</h6>
                        <p class="mb-0 text-muted">Знайдіть ідеальний домен</p>
                    </a>
                    <a href="/pages/hosting/shared.php" class="quick-action-card">
                        <i class="bi bi-server"></i>
                        <h6>Замовити хостинг</h6>
                        <p class="mb-0 text-muted">Надійний веб-хостинг</p>
                    </a>
                    <a href="/pages/vds/virtual.php" class="quick-action-card">
                        <i class="bi bi-hdd-stack"></i>
                        <h6>VPS сервери</h6>
                        <p class="mb-0 text-muted">Віртуальні сервери</p>
                    </a>
                    <a href="/pages/contacts.php" class="quick-action-card">
                        <i class="bi bi-headset"></i>
                        <h6>Підтримка</h6>
                        <p class="mb-0 text-muted">Отримати допомогу</p>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Services Section -->
            <div class="col-lg-8">
                <div class="section-card">
                    <div class="section-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3><i class="bi bi-box me-2"></i>Мої послуги</h3>
                            <a href="/client/services.php" class="btn btn-outline-primary btn-sm">
                                Переглянути всі
                            </a>
                        </div>
                    </div>
                    <div class="section-body">
                        <?php if (empty($recent_orders)): ?>
                        <div class="empty-state">
                            <i class="bi bi-box-seam"></i>
                            <h5>У вас поки немає активних послуг</h5>
                            <p>Замовте першу послугу, щоб почати роботу з нами</p>
                            <a href="/pages/domains/register.php" class="action-btn">
                                <i class="bi bi-plus-lg"></i>Замовити послугу
                            </a>
                        </div>
                        <?php else: ?>
                        <?php foreach ($recent_orders as $service): ?>
                        <div class="service-item">
                            <div class="service-info">
                                <h5><?php echo escapeOutput($service['name']); ?></h5>
                                <p>
                                    <i class="bi bi-calendar me-1"></i>
                                    Створено: <?php echo date('d.m.Y', strtotime($service['created_at'])); ?>
                                    <?php if (isset($service['expires_at'])): ?>
                                    | Діє до: <?php echo date('d.m.Y', strtotime($service['expires_at'])); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <span class="service-status status-<?php echo $service['status']; ?>">
                                    <?php 
                                    switch($service['status']) {
                                        case 'active': echo 'Активна'; break;
                                        case 'pending': echo 'Очікування'; break;
                                        case 'suspended': echo 'Призупинена'; break;
                                        default: echo 'Невідомо';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Billing Section -->
            <div class="col-lg-4">
                <div class="section-card">
                    <div class="section-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3><i class="bi bi-receipt me-2"></i>Рахунки</h3>
                            <a href="/client/billing.php" class="btn btn-outline-primary btn-sm">
                                Всі рахунки
                            </a>
                        </div>
                    </div>
                    <div class="section-body">
                        <?php if (empty($invoices)): ?>
                        <div class="empty-state">
                            <i class="bi bi-receipt-cutoff"></i>
                            <h6>Немає рахунків</h6>
                            <p class="mb-0">Рахунки з'являться після замовлення послуг</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                        <div class="service-item">
                            <div class="service-info">
                                <h6>Рахунок #<?php echo $invoice['id']; ?></h6>
                                <p class="mb-0">
                                    <?php echo number_format($invoice['amount'], 2); ?> ₴
                                    <br>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y', strtotime($invoice['created_at'])); ?>
                                    </small>
                                </p>
                            </div>
                            <div>
                                <span class="service-status status-<?php echo $invoice['status']; ?>">
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
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Account Info -->
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="bi bi-person-circle me-2"></i>Обліковий запис</h3>
                    </div>
                    <div class="section-body">
                        <div class="mb-3">
                            <small class="text-muted">Мова інтерфейсу</small>
                            <div>
                                <?php 
                                $languages = ['ua' => 'Українська', 'en' => 'English', 'ru' => 'Русский'];
                                echo $languages[$user_info['language']] ?? 'Українська';
                                ?>
                            </div>
                        </div>
                        
                        <?php if ($fossbilling_client_id): ?>
                        <div class="mb-3">
                            <small class="text-muted">ID клієнта біллінгу</small>
                            <div>#<?php echo $fossbilling_client_id; ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <small class="text-muted">Статус облікового запису</small>
                            <div>
                                <span class="service-status status-<?php echo $user_info['is_active'] ? 'active' : 'suspended'; ?>">
                                    <?php echo $user_info['is_active'] ? 'Активний' : 'Деактивований'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="/client/profile.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-gear me-1"></i>Налаштування
                            </a>
                            <a href="/auth/logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-box-arrow-right me-1"></i>Вийти
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="section-card">
            <div class="section-header">
                <h3><i class="bi bi-clock-history me-2"></i>Остання активність</h3>
            </div>
            <div class="section-body">
                <div class="empty-state">
                    <i class="bi bi-clock-history"></i>
                    <h6>Активність відсутня</h6>
                    <p class="mb-0">Тут з'являться ваші останні дії в системі</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Анимация для статистических карточек
            const statsCards = document.querySelectorAll('.stats-card');
            
            statsCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
            
            // Анимация для карточек быстрых действий
            const quickActions = document.querySelectorAll('.quick-action-card');
            
            quickActions.forEach((card, index) => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Проверяем наличие уведомлений (заглушка)
            checkNotifications();
        });
        
        function checkNotifications() {
            // Здесь можно добавить AJAX запрос для проверки уведомлений
            // Пока оставляем заглушку
        }
        
        // Функция для обновления статистики
        function updateStats() {
            // AJAX запрос для обновления статистики
            fetch('/api/client/stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Обновляем счетчики
                        document.querySelector('.stats-card:nth-child(1) .stats-number').textContent = data.domains || 0;
                        document.querySelector('.stats-card:nth-child(2) .stats-number').textContent = data.hosting || 0;
                        document.querySelector('.stats-card:nth-child(3) .stats-number').textContent = data.vps || 0;
                        document.querySelector('.stats-card:nth-child(4) .stats-number').textContent = data.active_services || 0;
                    }
                })
                .catch(error => {
                    console.error('Error updating stats:', error);
                });
        }
        
        // Обновляем статистику каждые 30 секунд
        setInterval(updateStats, 30000);
    </script>
</body>
</html>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>