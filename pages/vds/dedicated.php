<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'dedicated';
$page_title = 'Виділені сервери - StormHosting UA';
$meta_description = 'Потужні виділені сервери в Україні. Intel Xeon, AMD EPYC процесори, до 1TB RAM, NVMe диски. Повний контроль над залізом.';
$meta_keywords = 'виділений сервер, dedicated server, фізичний сервер, bare metal, колокація';

// Додаткові CSS та JS файли для цієї сторінки
//$additional_css = [
//    '/assets/css/pages/vds-dedicated.css'
//];
//
//$additional_js = [
//    '/assets/js/pages/vds-dedicated.js'
//];

// Безпечне підключення конфігурації та БД
$config_loaded = false;
$db_connected = faLSE;

try {
    $config_path = $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
    if (file_exists($config_path)) {
        require_once $config_path;
        $config_loaded = true;
    }
} catch (Exception $e) {
    // Логируем ошибку, но продолжаем работу
    error_log("Config load error: " . $e->getMessage());
}

try {
    $db_path = $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
    if (file_exists($db_path) && $config_loaded) {
        require_once $db_path;
        $db_connected = true;
    }
} catch (Exception $e) {
    // Логируем ошибку, но продолжаем работу
    error_log("DB connect error: " . $e->getMessage());
}

// Отримання серверів з БД або fallback дані
$servers = [];
if ($db_connected && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM dedicated_servers WHERE is_available = 1 ORDER BY price_monthly ASC");
        $stmt->execute();
        $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
    }
}

// Fallback дані якщо БД недоступна
if (empty($servers)) {
    $servers = [
        [
            'id' => 1,
            'name' => 'Entry Server',
            'cpu' => 'Intel Xeon E-2288G',
            'cpu_cores' => 8,
            'cpu_threads' => 16,
            'cpu_freq' => '3.7-5.0 GHz',
            'ram' => 16,
            'ram_type' => 'DDR4 ECC',
            'storage' => '2x 500GB NVMe',
            'raid' => 'RAID 1',
            'bandwidth' => 10,
            'port_speed' => '1 Gbps',
            'price_monthly' => 4999,
            'setup_fee' => 0,
            'is_popular' => 0
        ],
        [
            'id' => 2,
            'name' => 'Business Server',
            'cpu' => 'Intel Xeon Gold 6226R',
            'cpu_cores' => 16,
            'cpu_threads' => 32,
            'cpu_freq' => '2.9-3.9 GHz',
            'ram' => 64,
            'ram_type' => 'DDR4 ECC',
            'storage' => '2x 1TB NVMe',
            'raid' => 'RAID 1',
            'bandwidth' => 20,
            'port_speed' => '1 Gbps',
            'price_monthly' => 7999,
            'setup_fee' => 0,
            'is_popular' => 1
        ],
        [
            'id' => 3,
            'name' => 'Power Server',
            'cpu' => 'AMD EPYC 7443',
            'cpu_cores' => 24,
            'cpu_threads' => 48,
            'cpu_freq' => '2.85-4.0 GHz',
            'ram' => 128,
            'ram_type' => 'DDR4 ECC',
            'storage' => '4x 2TB NVMe',
            'raid' => 'RAID 10',
            'bandwidth' => 50,
            'port_speed' => '10 Gbps',
            'price_monthly' => 12999,
            'setup_fee' => 0,
            'is_popular' => 0
        ],
        [
            'id' => 4,
            'name' => 'Enterprise Server',
            'cpu' => '2x AMD EPYC 7763',
            'cpu_cores' => 128,
            'cpu_threads' => 256,
            'cpu_freq' => '2.45-3.5 GHz',
            'ram' => 512,
            'ram_type' => 'DDR4 ECC',
            'storage' => '8x 4TB NVMe',
            'raid' => 'RAID 10',
            'bandwidth' => 100,
            'port_speed' => '10 Gbps',
            'price_monthly' => 24999,
            'setup_fee' => 2000,
            'is_popular' => 0
        ],
        [
            'id' => 5,
            'name' => 'Performance Pro',
            'cpu' => 'Intel Xeon Platinum 8358',
            'cpu_cores' => 32,
            'cpu_threads' => 64,
            'cpu_freq' => '2.6-3.4 GHz',
            'ram' => 256,
            'ram_type' => 'DDR4 ECC',
            'storage' => '6x 3TB NVMe',
            'raid' => 'RAID 10',
            'bandwidth' => 75,
            'port_speed' => '10 Gbps',
            'price_monthly' => 18999,
            'setup_fee' => 1000,
            'is_popular' => 0
        ],
        [
            'id' => 6,
            'name' => 'Storage Beast',
            'cpu' => 'AMD EPYC 7532',
            'cpu_cores' => 32,
            'cpu_threads' => 64,
            'cpu_freq' => '2.4-3.3 GHz',
            'ram' => 192,
            'ram_type' => 'DDR4 ECC',
            'storage' => '12x 8TB SAS',
            'raid' => 'RAID 6',
            'bandwidth' => 40,
            'port_speed' => '1 Gbps',
            'price_monthly' => 15999,
            'setup_fee' => 1500,
            'is_popular' => 0
        ],
        [
            'id' => 7,
            'name' => 'GPU Compute',
            'cpu' => 'Intel Xeon Gold 6248R',
            'cpu_cores' => 24,
            'cpu_threads' => 48,
            'cpu_freq' => '3.0-4.0 GHz',
            'ram' => 384,
            'ram_type' => 'DDR4 ECC',
            'storage' => '4x 2TB NVMe',
            'raid' => 'RAID 10',
            'bandwidth' => 60,
            'port_speed' => '10 Gbps',
            'price_monthly' => 29999,
            'setup_fee' => 3000,
            'is_popular' => 0,
            'gpu' => '2x NVIDIA RTX 4090'
        ],
        [
            'id' => 8,
            'name' => 'Cloud Edge',
            'cpu' => 'AMD EPYC 7313',
            'cpu_cores' => 16,
            'cpu_threads' => 32,
            'cpu_freq' => '3.0-3.7 GHz',
            'ram' => 128,
            'ram_type' => 'DDR4 ECC',
            'storage' => '4x 1TB NVMe',
            'raid' => 'RAID 10',
            'bandwidth' => 30,
            'port_speed' => '1 Gbps',
            'price_monthly' => 9999,
            'setup_fee' => 500,
            'is_popular' => 0
        ],
        [
            'id' => 9,
            'name' => 'DB Master',
            'cpu' => 'Intel Xeon Gold 6354',
            'cpu_cores' => 18,
            'cpu_threads' => 36,
            'cpu_freq' => '3.0-3.6 GHz',
            'ram' => 768,
            'ram_type' => 'DDR4 ECC',
            'storage' => '8x 1TB NVMe',
            'raid' => 'RAID 10',
            'bandwidth' => 50,
            'port_speed' => '10 Gbps',
            'price_monthly' => 22999,
            'setup_fee' => 2500,
            'is_popular' => 0
        ],
        [
            'id' => 10,
            'name' => 'Media Stream',
            'cpu' => 'AMD EPYC 7402',
            'cpu_cores' => 24,
            'cpu_threads' => 48,
            'cpu_freq' => '2.8-3.35 GHz',
            'ram' => 256,
            'ram_type' => 'DDR4 ECC',
            'storage' => '6x 4TB NVMe',
            'raid' => 'RAID 5',
            'bandwidth' => 200,
            'port_speed' => '10 Gbps',
            'price_monthly' => 19999,
            'setup_fee' => 1500,
            'is_popular' => 0
        ]
    ];
}

// Безпечне підключення header
$header_loaded = false;
try {
    $header_path = $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
    if (file_exists($header_path)) {
        include $header_path;
        $header_loaded = true;
    }
} catch (Exception $e) {
    error_log("Header include error: " . $e->getMessage());
}

// Fallback header якщо основний не завантажився
if (!$header_loaded) {
    ?>
        <!DOCTYPE html>
    <html lang="uk">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($page_title); ?></title>
        <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
        
        <!-- Bootstrap CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
        
        <!-- Custom CSS -->
        <link href="/assets/css/pages/vds-dedicated.css" rel="stylesheet">
        
    </head>
    <body>
        <!-- Простой header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <strong>StormHosting UA</strong>
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="/">Головна</a>
                    <a class="nav-link" href="/pages/domains/">Домени</a>
                    <a class="nav-link" href="/pages/hosting/">Хостинг</a>
                    <a class="nav-link active" href="/pages/vds/">VDS/VPS</a>
                    <a class="nav-link" href="/pages/info/">Інформація</a>
                    <a class="nav-link" href="/pages/contacts.php">Контакти</a>
                </div>
            </div>
        </nav>
    <?php
}
?>

<!-- Dedicated Hero Section -->
<section class="dedicated-hero">
    <div class="container">
        <div class="row align-items-center min-vh-60">
            <div class="col-lg-6">
                <div class="hero-badge mb-3">
                    <i class="bi bi-pc-display-horizontal"></i> Bare Metal Servers
                </div>
                <h1 class="display-4 fw-bold text-white mb-4">
                    Виділені сервери для вимогливих проектів
                </h1>
                <p class="lead text-white-50 mb-4">
                    Повний контроль над апаратним забезпеченням. Intel Xeon та AMD EPYC процесори, 
                    до 1TB RAM, NVMe накопичувачі. Ідеально для високонавантажених проектів.
                </p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-value">15 хв</div>
                        <div class="stat-label">Налаштування</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">99.99%</div>
                        <div class="stat-label">Uptime SLA</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Підтримка</div>
                    </div>
                </div>
                <div class="hero-actions mt-4">
                    <a href="#servers" class="btn btn-primary btn-lg">
                        <i class="bi bi-server me-2"></i>Обрати сервер
                    </a>
                    <a href="/pages/vds/vds-calc.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-gear me-2"></i>Конфігуратор
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="server-3d-view">
                    <div class="server-chassis">
                        <div class="server-front">
                            <div class="drive-bay">
                                <div class="drive active"></div>
                                <div class="drive active"></div>
                                <div class="drive active"></div>
                                <div class="drive active"></div>
                            </div>
                            <div class="drive-bay">
                                <div class="drive active"></div>
                                <div class="drive active"></div>
                                <div class="drive"></div>
                                <div class="drive"></div>
                            </div>
                            <div class="control-panel">
                                <div class="power-button"></div>
                                <div class="status-led power"></div>
                                <div class="status-led network"></div>
                                <div class="status-led storage"></div>
                            </div>
                        </div>
                        <div class="server-specs-overlay">
                            <div class="spec-line">
                                <i class="bi bi-cpu"></i> До 256 ядер
                            </div>
                            <div class="spec-line">
                                <i class="bi bi-memory"></i> До 1TB RAM
                            </div>
                            <div class="spec-line">
                                <i class="bi bi-hdd-stack"></i> До 100TB NVMe
                            </div>
                            <div class="spec-line">
                                <i class="bi bi-ethernet"></i> До 100 Gbps
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dedicated Servers -->
<section id="servers" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Готові конфігурації серверів</h2>
            <p class="lead text-muted">Оберіть сервер або створіть власну конфігурацію</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($servers as $server): ?>
            <div class="col-lg-6">
                <div class="server-card <?php echo $server['is_popular'] ? 'popular' : ''; ?>">
                    <?php if ($server['is_popular']): ?>
                    <div class="popular-badge">
                        <i class="bi bi-star-fill"></i> Популярний
                    </div>
                    <?php endif; ?>
                    
                    <div class="server-header">
                        <h4><?php echo htmlspecialchars($server['name']); ?></h4>
                        <div class="price-info">
                            <span class="price"><?php echo number_format($server['price_monthly'], 0, ',', ' '); ?> ₴</span>
                            <span class="period">/місяць</span>
                        </div>
                        <?php if ($server['setup_fee'] > 0): ?>
                        <div class="setup-fee">
                            Активація: <?php echo number_format($server['setup_fee'], 0, ',', ' '); ?> ₴
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="server-specs">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="spec-group">
                                    <h6><i class="bi bi-cpu"></i> Процесор</h6>
                                    <p><?php echo htmlspecialchars($server['cpu']); ?></p>
                                    <small class="text-muted">
                                        <?php echo $server['cpu_cores']; ?> ядер / <?php echo $server['cpu_threads']; ?> потоків<br>
                                        <?php echo htmlspecialchars($server['cpu_freq']); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="spec-group">
                                    <h6><i class="bi bi-memory"></i> Пам'ять</h6>
                                    <p><?php echo $server['ram']; ?> GB</p>
                                    <small class="text-muted"><?php echo htmlspecialchars($server['ram_type']); ?></small>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="spec-group">
                                    <h6><i class="bi bi-hdd-stack"></i> Диски</h6>
                                    <p><?php echo htmlspecialchars($server['storage']); ?></p>
                                    <small class="text-muted"><?php echo htmlspecialchars($server['raid']); ?></small>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="spec-group">
                                    <h6><i class="bi bi-ethernet"></i> Мережа</h6>
                                    <p><?php echo $server['bandwidth']; ?> TB</p>
                                    <small class="text-muted"><?php echo htmlspecialchars($server['port_speed']); ?></small>
                                </div>
                            </div>
                            
                            <?php if (isset($server['gpu'])): ?>
                            <div class="col-12">
                                <div class="spec-group">
                                    <h6><i class="bi bi-gpu-card"></i> GPU</h6>
                                    <p><?php echo htmlspecialchars($server['gpu']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="server-features">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="feature-item">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Root доступ
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="feature-item">
                                    <i class="bi bi-check-circle text-success"></i>
                                    DDoS захист
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="feature-item">
                                    <i class="bi bi-check-circle text-success"></i>
                                    IPv4 + IPv6
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="feature-item">
                                    <i class="bi bi-check-circle text-success"></i>
                                    IPMI доступ
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="server-footer">
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" 
                                data-bs-target="#orderModal" 
                                data-server-id="<?php echo $server['id']; ?>"
                                data-server-name="<?php echo htmlspecialchars($server['name']); ?>"
                                data-server-price="<?php echo $server['price_monthly']; ?>"
                                data-setup-fee="<?php echo $server['setup_fee']; ?>">
                            <i class="bi bi-cart-plus me-2"></i>Замовити сервер
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Custom Configuration CTA -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <div class="custom-config-cta text-center">
                    <h3>Потрібна індивідуальна конфігурація?</h3>
                    <p>Ми можемо зібрати сервер під ваші конкретні потреби. Від початкових конфігурацій до топових рішень з GPU, великими обсягами RAM та storage.</p>
                    <div class="mt-3">
                        <a href="/pages/vds/vds-calc.php" class="btn btn-primary me-3">
                            <i class="bi bi-gear me-2"></i>Конфігуратор
                        </a>
                        <a href="/pages/contacts.php" class="btn btn-outline-primary">
                            <i class="bi bi-chat-dots me-2"></i>Консультація
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Переваги наших серверів</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon gradient-1">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h5>Швидке розгортання</h5>
                    <p class="text-muted">
                        Автоматизовані системи дозволяють налаштувати та ввести 
                        сервер в експлуатацію протягом 15 хвилин.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon gradient-2">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Максимальна безпека</h5>
                    <p class="text-muted">
                        Tier III дата-центр, DDoS захист, системи 
                        моніторингу та фізична охорона 24/7.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon gradient-3">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h5>Гнучке масштабування</h5>
                    <p class="text-muted">
                        Можливість апгрейду RAM, дисків та CPU без 
                        переривання роботи сервісів.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon gradient-4">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <h5>Автоматичне бекапування</h5>
                    <p class="text-muted">
                        Щоденні резервні копії з можливістю швидкого 
                        відновлення даних.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon gradient-5">
                        <i class="bi bi-arrows-move"></i>
                    </div>
                    <h5>Безкоштовна міграція</h5>
                    <p class="text-muted">
                        Наші фахівці безкоштовно перенесуть ваші дані та 
                        сервіси з іншого провайдера.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon gradient-6">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5>Керований хостинг</h5>
                    <p class="text-muted">
                        Опціональне адміністрування: налаштування, моніторинг, 
                        оновлення, резервне копіювання.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Use Cases -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Для яких завдань підходять</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="use-case-card">
                    <div class="use-case-icon">
                        <i class="bi bi-camera-video"></i>
                    </div>
                    <h5>Стрімінг та CDN</h5>
                    <p>Відео-хостинг, live-трансляції, розподілення контенту</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="use-case-card">
                    <div class="use-case-icon">
                        <i class="bi bi-cpu"></i>
                    </div>
                    <h5>Machine Learning</h5>
                    <p>Навчання моделей, обробка великих даних, GPU обчислення</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="use-case-card">
                    <div class="use-case-icon">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <h5>Віртуалізація</h5>
                    <p>Proxmox, VMware, Hyper-V для хостингу VPS/VDS</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Data Center Info -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-6 fw-bold mb-4">Наш дата-центр</h2>
                <div class="dc-features">
                    <div class="dc-feature">
                        <i class="bi bi-building text-primary"></i>
                        <div>
                            <strong>Tier III сертифікація</strong>
                            <p class="mb-0 text-muted">Відповідність міжнародним стандартам надійності та безпеки</p>
                        </div>
                    </div>
                    
                    <div class="dc-feature">
                        <i class="bi bi-shield-check text-success"></i>
                        <div>
                            <strong>99.99% SLA</strong>
                            <p class="mb-0 text-muted">Гарантія доступності з компенсацією простоїв</p>
                        </div>
                    </div>
                    
                    <div class="dc-feature">
                        <i class="bi bi-lightning text-warning"></i>
                        <div>
                            <strong>Резервне живлення</strong>
                            <p class="mb-0 text-muted">UPS системи та дизель-генератори</p>
                        </div>
                    </div>
                    
                    <div class="dc-feature">
                        <i class="bi bi-thermometer text-info"></i>
                        <div>
                            <strong>Клімат-контроль</strong>
                            <p class="mb-0 text-muted">Підтримка оптимальної температури 24/7</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="dc-image">
                    <!-- Заглушка для изображения дата-центра -->
                    <div style="width: 100%; height: 300px; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); 
                         border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; border: 2px solid #e2e8f0;">
                        <div class="text-center">
                            <i class="bi bi-building" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <h5>Наш дата-центр</h5>
                            <p class="mb-0">Tier III сертифікація</p>
                        </div>
                    </div>
                    <div class="dc-stats">
                        <div class="stat">
                            <span class="value">24/7</span>
                            <span class="label">Моніторинг</span>
                        </div>
                        <div class="stat">
                            <span class="value">99.99%</span>
                            <span class="label">Uptime</span>
                        </div>
                        <div class="stat">
                            <span class="value">15°C</span>
                            <span class="label">Температура</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Поширені запитання</h2>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Що включено в вартість сервера?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                У вартість включено: сервер з обраною конфігурацією, безлімітний трафік, 1 IP адресу, 
                                базову підтримку 24/7, DDoS захист, IPMI доступ. Операційна система встановлюється безкоштовно.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Як швидко буде налаштовано сервер?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Готові конфігурації налаштовуються автоматично протягом 15 хвилин після оплати. 
                                Індивідуальні конфігурації можуть потребувати до 24 годин.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Чи можна змінити конфігурацію після замовлення?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Так, ми підтримуємо апгрейд RAM та дисків без переривання роботи. 
                                Заміна процесора потребує короткочасного перезавантаження.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Яка система резервного копіювання?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ми пропонуємо автоматичні щоденні бекапи (опціонально) з зберіганням до 30 днів. 
                                Також доступне миттєве створення снапшотів через панель управління.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Які операційні системи доступні?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ubuntu Server, CentOS, Debian, Windows Server, VMware ESXi, Proxmox VE та інші. 
                                Повний список доступний при замовленні.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 text-white">
    <div class="container">
        <div class="cta-content text-center">
            <h2 class="display-5 fw-bold mb-4">Готові почати?</h2>
            <p class="lead mb-4">Оберіть конфігурацію сервера або зв'яжіться з нами для консультації</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="#servers" class="btn btn-light btn-lg">
                    <i class="bi bi-server me-2"></i>Обрати сервер
                </a>
                <a href="/pages/contacts.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-telephone me-2"></i>Зв'язатися з нами
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-cart-plus me-2"></i>Замовлення сервера
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="serverOrderForm">
                    <div class="order-summary mb-4">
                        <h6>Обрана конфігурація:</h6>
                        <div id="selectedServerInfo">
                            <!-- Заповнюється JavaScript -->
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ім'я *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Телефон</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Операційна система</label>
                            <select class="form-select" name="os">
                                <option value="ubuntu-22.04">Ubuntu Server 22.04 LTS</option>
                                <option value="centos-8">CentOS Stream 8</option>
                                <option value="debian-11">Debian 11</option>
                                <option value="windows-2022">Windows Server 2022</option>
                                <option value="esxi-7">VMware ESXi 7.0</option>
                                <option value="proxmox">Proxmox VE</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Коментарі до замовлення</label>
                            <textarea class="form-control" name="comments" rows="3" 
                                      placeholder="Додаткові вимоги або побажання..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="terms" required>
                                <label class="form-check-label">
                                    Я погоджуюся з <a href="/pages/info/rules.php" target="_blank">умовами користування</a> 
                                    та <a href="/pages/info/legal.php" target="_blank">політикою конфіденційності</a> *
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="total-summary">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Вартість за місяць:</span>
                            <span class="fw-bold" id="monthlyPrice">0 ₴</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center" id="setupFeeRow" style="display: none;">
                            <span>Активація:</span>
                            <span id="setupFeePrice">0 ₴</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h6 mb-0">До оплати:</span>
                            <span class="h5 mb-0 text-primary" id="totalPrice">0 ₴</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="submit" form="serverOrderForm" class="btn btn-primary">
                    <i class="bi bi-credit-card me-2"></i>Оформити замовлення
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Безпечне підключення footer
$footer_loaded = false;
try {
    $footer_path = $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
    if (file_exists($footer_path)) {
        include $footer_path;
        $footer_loaded = true;
    }
} catch (Exception $e) {
    error_log("Footer include error: " . $e->getMessage());
}

// Fallback footer якщо основний не завантажився
if (!$footer_loaded) {
    ?>
    <footer style="background: #1e293b; color: white; padding: 40px 0;">
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
                        <li><a href="/pages/hosting/" style="color: #adb5bd; text-decoration: none;">Хостинг</a></li>
                        <li><a href="/pages/vds/" style="color: #adb5bd; text-decoration: none;">VDS/VPS</a></li>
                        <li><a href="/pages/domains/" style="color: #adb5bd; text-decoration: none;">Домени</a></li>
                        <li><a href="/pages/info/ssl.php" style="color: #adb5bd; text-decoration: none;">SSL сертифікати</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Підтримка</h6>
                    <ul class="list-unstyled">
                        <li><a href="/pages/info/faq.php" style="color: #adb5bd; text-decoration: none;">FAQ</a></li>
                        <li><a href="/pages/contacts.php" style="color: #adb5bd; text-decoration: none;">Контакти</a></li>
                        <li><a href="/pages/info/about.php" style="color: #adb5bd; text-decoration: none;">Документація</a></li>
                        <li><a href="#" style="color: #adb5bd; text-decoration: none;">Статус серверів</a></li>
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
            
            <hr style="border-color: #475569; margin: 2rem 0;">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> StormHosting UA. Всі права захищені.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small style="color: #9ca3af;">Розроблено з ❤️ в Україні</small>
                </div>
            </div>
        </div>
    </footer>
    <?php
}
?>

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

<!-- Додаткові JS файли для сторінки -->
<?php if (isset($additional_js) && is_array($additional_js)): ?>
    <?php foreach ($additional_js as $js_file): ?>
        <script src="<?php echo htmlspecialchars($js_file); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Fallback JavaScript если внешний файл не загрузился -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dedicated servers page loaded');
    
    // Проверяем, загрузился ли внешний JS файл
    if (typeof initDedicatedPage === 'undefined') {
        console.log('External JS not loaded, using fallback functionality');
        
        // Базовая функциональность модального окна
        const orderButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#orderModal"]');
        orderButtons.forEach(button => {
            button.addEventListener('click', function() {
                const serverId = this.dataset.serverId;
                const serverName = this.dataset.serverName;
                const serverPrice = parseInt(this.dataset.serverPrice);
                const setupFee = parseInt(this.dataset.setupFee || 0);
                
                // Обновляем информацию в модальном окне
                const serverInfo = document.getElementById('selectedServerInfo');
                if (serverInfo) {
                    serverInfo.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${serverName}</h6>
                                <small class="text-muted">ID: ${serverId}</small>
                            </div>
                            <div class="text-end">
                                <div class="h6 mb-0">${serverPrice.toLocaleString()} ₴/мес</div>
                                ${setupFee > 0 ? `<small class="text-danger">Активация: ${setupFee.toLocaleString()} ₴</small>` : ''}
                            </div>
                        </div>
                    `;
                }
                
                // Обновляем цены
                const monthlyPriceEl = document.getElementById('monthlyPrice');
                const setupFeeEl = document.getElementById('setupFeePrice');
                const setupFeeRow = document.getElementById('setupFeeRow');
                const totalPriceEl = document.getElementById('totalPrice');
                
                if (monthlyPriceEl) monthlyPriceEl.textContent = serverPrice.toLocaleString() + ' ₴';
                if (setupFeeEl && setupFeeRow) {
                    if (setupFee > 0) {
                        setupFeeEl.textContent = setupFee.toLocaleString() + ' ₴';
                        setupFeeRow.style.display = 'flex';
                    } else {
                        setupFeeRow.style.display = 'none';
                    }
                }
                if (totalPriceEl) {
                    const total = serverPrice + setupFee;
                    totalPriceEl.textContent = total.toLocaleString() + ' ₴';
                }
            });
        });
        
        // Обработка формы заказа
        const orderForm = document.getElementById('serverOrderForm');
        if (orderForm) {
            orderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Функция заказа в разработке. Мы свяжемся с вами в ближайшее время!');
            });
        }
        
        // Smooth scroll для якорных ссылок
        const links = document.querySelectorAll('a[href^="#"]');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }
});
</script>
<script src="/assets/js/vds-dedicated.js"></script>
</body>
</html>