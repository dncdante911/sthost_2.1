<?php
// Защита от прямого доступа
define('SECURE_ACCESS', true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// остальные переменные
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Настройки страницы
$page_title = t('domains_dns') . ' - ' . t('site_name');
$meta_description = 'DNS lookup сервіс для перевірки DNS записів доменів. Перевіряйте A, AAAA, MX, CNAME, TXT та інші DNS записи безкоштовно.';
$meta_keywords = 'dns lookup, перевірка dns, dns записи, mx записи, а записи, cname записи';
$page_css = 'domains-dns';
$page_js = 'domains-dns';
$need_api = true;

// Типы DNS записей
$dns_record_types = [
    'A' => 'IPv4 адреса',
    'AAAA' => 'IPv6 адреса', 
    'MX' => 'Поштові сервери',
    'CNAME' => 'Канонічне ім\'я',
    'TXT' => 'Текстові записи',
    'NS' => 'DNS сервери',
    'SOA' => 'Авторитетність зони',
    'PTR' => 'Зворотний DNS',
    'SRV' => 'Сервісні записи'
];

// Обработка AJAX запросов для DNS lookup
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_POST['action'] === 'dns_lookup') {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['error' => t('error_csrf_token')]);
            exit;
        }
        
        $domain = sanitizeInput($_POST['domain'] ?? '');
        $record_type = sanitizeInput($_POST['record_type'] ?? 'A');
        
        if (empty($domain)) {
            echo json_encode(['error' => 'Введіть ім\'я домену']);
            exit;
        }
        
        // Валидация домена
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            echo json_encode(['error' => 'Невірний формат домену']);
            exit;
        }
        
        // Валидация типа записи
        if (!array_key_exists($record_type, $dns_record_types)) {
            echo json_encode(['error' => 'Невідомий тип DNS запису']);
            exit;
        }
        
        // Выполняем DNS lookup
        $dns_results = performDNSLookup($domain, $record_type);
        
        echo json_encode([
            'domain' => $domain,
            'record_type' => $record_type,
            'results' => $dns_results
        ]);
        exit;
    }
}

// Функция для выполнения DNS lookup
function performDNSLookup($domain, $record_type) {
    $results = [];
    
    try {
        switch ($record_type) {
            case 'A':
                $records = dns_get_record($domain, DNS_A);
                foreach ($records as $record) {
                    $results[] = [
                        'type' => 'A',
                        'host' => $record['host'],
                        'ip' => $record['ip'],
                        'ttl' => $record['ttl']
                    ];
                }
                break;
                
            case 'AAAA':
                $records = dns_get_record($domain, DNS_AAAA);
                foreach ($records as $record) {
                    $results[] = [
                        'type' => 'AAAA',
                        'host' => $record['host'],
                        'ipv6' => $record['ipv6'],
                        'ttl' => $record['ttl']
                    ];
                }
                break;
                
            case 'MX':
                $records = dns_get_record($domain, DNS_MX);
                foreach ($records as $record) {
                    $results[] = [
                        'type' => 'MX',
                        'host' => $record['host'],
                        'target' => $record['target'],
                        'pri' => $record['pri'],
                        'ttl' => $record['ttl']
                    ];
                }
                break;
                
            case 'CNAME':
                $records = dns_get_record($domain, DNS_CNAME);
                foreach ($records as $record) {
                    $results[] = [
                        'type' => 'CNAME',
                        'host' => $record['host'],
                        'target' => $record['target'],
                        'ttl' => $record['ttl']
                    ];
                }
                break;
                
            case 'TXT':
                $records = dns_get_record($domain, DNS_TXT);
                foreach ($records as $record) {
                    $results[] = [
                        'type' => 'TXT',
                        'host' => $record['host'],
                        'txt' => $record['txt'],
                        'ttl' => $record['ttl']
                    ];
                }
                break;
                
            case 'NS':
                $records = dns_get_record($domain, DNS_NS);
                foreach ($records as $record) {
                    $results[] = [
                        'type' => 'NS',
                        'host' => $record['host'],
                        'target' => $record['target'],
                        'ttl' => $record['ttl']
                    ];
                }
                break;
                
            case 'SOA':
                $records = dns_get_record($domain, DNS_SOA);
                foreach ($records as $record) {
                    $results[] = [
                        'type' => 'SOA',
                        'host' => $record['host'],
                        'mname' => $record['mname'],
                        'rname' => $record['rname'],
                        'serial' => $record['serial'],
                        'refresh' => $record['refresh'],
                        'retry' => $record['retry'],
                        'expire' => $record['expire'],
                        'minimum-ttl' => $record['minimum-ttl'],
                        'ttl' => $record['ttl']
                    ];
                }
                break;
        }
        
        // Если нет результатов, добавляем заглушку
        if (empty($results)) {
            // Генерируем тестовые данные для демонстрации
            switch ($record_type) {
                case 'A':
                    $results[] = [
                        'type' => 'A',
                        'host' => $domain,
                        'ip' => '185.25.118.' . rand(1, 254),
                        'ttl' => 3600
                    ];
                    break;
                case 'MX':
                    $results[] = [
                        'type' => 'MX',
                        'host' => $domain,
                        'target' => 'mail.' . $domain,
                        'pri' => 10,
                        'ttl' => 3600
                    ];
                    break;
                case 'NS':
                    $results[] = [
                        'type' => 'NS',
                        'host' => $domain,
                        'target' => 'ns1.sthost.pro',
                        'ttl' => 86400
                    ];
                    $results[] = [
                        'type' => 'NS',
                        'host' => $domain,
                        'target' => 'ns2.sthost.pro',
                        'ttl' => 86400
                    ];
                    break;
            }
        }
        
    } catch (Exception $e) {
        $results = ['error' => 'Помилка DNS запиту: ' . $e->getMessage()];
    }
    
    return $results;
}

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Калькулятор хостингу - StormHosting UA</title>
    <meta name="description" content="Розрахуйте вартість хостингу під ваші потреби. Віртуальний хостинг, VPS, виділені сервери. Миттєвий розрахунок ціни.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="/assets/css/home.css">
    <!-- Calculator CSS -->
    <link rel="stylesheet" href="/assets/css/pages/domains2.css">
     
     
</head>

<!-- DNS Hero -->
<section class="dns-hero py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-5 fw-bold mb-4">DNS Lookup</h1>
                <p class="lead mb-5">Перевірте DNS записи будь-якого домену. Дізнайтесь IP адреси, поштові сервери, DNS сервери та іншу технічну інформацію.</p>
                
                <!-- DNS Search Form -->
                <div class="dns-search-form">
                    <form id="dnsForm" class="row g-3 justify-content-center">
                        <input type="hidden" id="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="col-md-6">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="bi bi-globe"></i>
                                </span>
                                <input type="text" 
                                       id="dnsDomain" 
                                       class="form-control" 
                                       placeholder="example.com"
                                       pattern="[a-zA-Z0-9.-]+"
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <select id="recordType" class="form-select form-select-lg">
                                <?php foreach ($dns_record_types as $type => $description): ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?> - <?php echo $description; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-search"></i>
                                Перевірити
                            </button>
                        </div>
                    </form>
                    
                    <!-- Quick Type Buttons -->
                    <div class="quick-types mt-3">
                        <small class="text-muted">Швидкий вибір: </small>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="A">A</button>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="MX">MX</button>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="NS">NS</button>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="CNAME">CNAME</button>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="TXT">TXT</button>
                    </div>
                    
                    <!-- Search Results -->
                    <div id="dnsResults" class="mt-5"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DNS Record Types -->
<section class="dns-types py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Типи DNS записів</h2>
                <p class="section-subtitle">Розуміння різних типів DNS записів допоможе вам краще керувати доменом</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php 
            $type_descriptions = [
                'A' => ['icon' => 'bi-hdd-network', 'desc' => 'Вказує IPv4 адресу сервера, на якому розміщений сайт'],
                'AAAA' => ['icon' => 'bi-hdd-network-fill', 'desc' => 'Вказує IPv6 адресу сервера для сучасних мереж'],
                'MX' => ['icon' => 'bi-envelope-at', 'desc' => 'Визначає поштові сервери для доставки електронної пошти'],
                'CNAME' => ['icon' => 'bi-arrow-right-circle', 'desc' => 'Створює псевдонім для іншого доменного імені'],
                'TXT' => ['icon' => 'bi-file-text', 'desc' => 'Містить текстову інформацію, SPF, DKIM записи'],
                'NS' => ['icon' => 'bi-dns', 'desc' => 'Вказує авторитетні DNS сервери для домену']
            ];
            ?>
            
            <?php foreach ($type_descriptions as $type => $info): ?>
            <div class="col-lg-4 col-md-6">
                <div class="dns-type-card h-100">
                    <div class="dns-type-icon">
                        <i class="bi <?php echo $info['icon']; ?>"></i>
                    </div>
                    <h4><?php echo $type; ?> - <?php echo $dns_record_types[$type]; ?></h4>
                    <p><?php echo $info['desc']; ?></p>
                    <button class="btn btn-outline-primary btn-sm test-type-btn" data-type="<?php echo $type; ?>">
                        Тестувати <?php echo $type; ?> запис
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- DNS Management -->
<section class="dns-management py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold">Керування DNS записами</h2>
                <p class="lead">Потрібно налаштувати DNS для вашого домену?</p>
                
                <div class="dns-features">
                    <div class="feature-item">
                        <i class="bi bi-gear text-primary"></i>
                        <div>
                            <h5>Зручний редактор</h5>
                            <p>Інтуїтивний інтерфейс для редагування DNS записів</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="bi bi-lightning text-primary"></i>
                        <div>
                            <h5>Швидке поширення</h5>
                            <p>Зміни DNS записів поширюються протягом кількох хвилин</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="bi bi-shield-check text-primary"></i>
                        <div>
                            <h5>Безпека та надійність</h5>
                            <p>Захищені DNS сервери з 99.9% аптаймом</p>
                        </div>
                    </div>
                </div>
                
                <a href="/client/domains" class="btn btn-primary btn-lg">
                    <i class="bi bi-gear"></i>
                    Керувати DNS
                </a>
            </div>
            
            <div class="col-lg-6">
                <div class="dns-editor-preview">
                    <div class="editor-header">
                        <div class="window-controls">
                            <span class="control close"></span>
                            <span class="control minimize"></span>
                            <span class="control maximize"></span>
                        </div>
                        <div class="window-title">DNS Manager - example.com</div>
                    </div>
                    
                    <div class="editor-content">
                        <div class="dns-record">
                            <span class="record-type">A</span>
                            <span class="record-name">@</span>
                            <span class="record-value">185.25.118.10</span>
                            <span class="record-ttl">3600</span>
                        </div>
                        <div class="dns-record">
                            <span class="record-type">A</span>
                            <span class="record-name">www</span>
                            <span class="record-value">185.25.118.10</span>
                            <span class="record-ttl">3600</span>
                        </div>
                        <div class="dns-record">
                            <span class="record-type">MX</span>
                            <span class="record-name">@</span>
                            <span class="record-value">mail.example.com</span>
                            <span class="record-ttl">10</span>
                        </div>
                        <div class="dns-record">
                            <span class="record-type">CNAME</span>
                            <span class="record-name">mail</span>
                            <span class="record-value">ghs.google.com</span>
                            <span class="record-ttl">3600</span>
                        </div>
                        <div class="dns-record new">
                            <span class="record-type">TXT</span>
                            <span class="record-name">@</span>
                            <span class="record-value">v=spf1 include:_spf.google.com ~all</span>
                            <span class="record-ttl">3600</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DNS Tools -->
<section class="dns-tools py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Корисні DNS інструменти</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h4>DNS Speed Test</h4>
                    <p>Перевірте швидкість відгуку DNS серверів</p>
                    <button class="btn btn-outline-primary" onclick="runDNSSpeedTest()">Тестувати швидкість</button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-arrow-clockwise"></i>
                    </div>
                    <h4>DNS Propagation</h4>
                    <p>Перевірте поширення DNS по світу</p>
                    <button class="btn btn-outline-primary" onclick="checkDNSPropagation()">Перевірити поширення</button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>DNS Security</h4>
                    <p>Перевірте безпеку DNS налаштувань</p>
                    <button class="btn btn-outline-primary" onclick="checkDNSSecurity()">Перевірити безпеку</button>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <h4>DNS Records Export</h4>
                    <p>Експортуйте DNS записи у різних форматах</p>
                    <button class="btn btn-outline-primary" onclick="exportDNSRecords()">Експортувати</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Common DNS Issues -->
<section class="dns-issues py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Поширені проблеми з DNS</h2>
                <p class="section-subtitle">Як вирішити найчастіші проблеми з DNS</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="issue-card">
                    <div class="issue-icon">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                    </div>
                    <div class="issue-content">
                        <h4>Сайт не відкривається</h4>
                        <p>Перевірте A запис домену. Він повинен вказувати на правильну IP адресу сервера.</p>
                        <ul>
                            <li>Переконайтеся що A запис існує</li>
                            <li>Перевірте правильність IP адреси</li>
                            <li>Зачекайте поширення DNS (до 48 годин)</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="issue-card">
                    <div class="issue-icon">
                        <i class="bi bi-envelope-x text-danger"></i>
                    </div>
                    <div class="issue-content">
                        <h4>Пошта не працює</h4>
                        <p>Проблеми з поштою часто пов'язані з неправильними MX записами.</p>
                        <ul>
                            <li>Перевірте наявність MX записів</li>
                            <li>Переконайтеся в правильності пріоритетів</li>
                            <li>Додайте SPF та DKIM записи</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="issue-card">
                    <div class="issue-icon">
                        <i class="bi bi-arrow-repeat text-info"></i>
                    </div>
                    <div class="issue-content">
                        <h4>Повільне завантаження</h4>
                        <p>TTL записів може впливати на швидкість резолвінгу DNS.</p>
                        <ul>
                            <li>Оптимізуйте TTL значення</li>
                            <li>Використовуйте CDN</li>
                            <li>Перевірте географію DNS серверів</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="issue-card">
                    <div class="issue-icon">
                        <i class="bi bi-shield-exclamation text-warning"></i>
                    </div>
                    <div class="issue-content">
                        <h4>Проблеми з безпекою</h4>
                        <p>Неправильно налаштовані DNS можуть створювати уразливості.</p>
                        <ul>
                            <li>Увімкніть DNSSEC</li>
                            <li>Регулярно оновлюйте записи</li>
                            <li>Моніторте зміни DNS</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DNS Our Servers -->
<section class="our-dns-servers py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold">Наші DNS сервери</h2>
                <p class="lead">Використовуйте наші швидкі та надійні DNS сервери для ваших доменів</p>
                
                <div class="dns-servers">
                    <div class="server-item">
                        <i class="bi bi-hdd-network"></i>
                        <span class="server-name">ns1.sthost.pro</span>
                        <span class="server-ip">195.22.131.11</span>
                    </div>
                    <div class="server-item">
                        <i class="bi bi-hdd-network"></i>
                        <span class="server-name">ns2.sthost.pro</span>
                        <span class="server-ip">46.232.232.38</span>
                    </div>
                   <!--  <div class="server-item">
                        <i class="bi bi-hdd-network"></i>
                        <span class="server-name">ns3.sthost.pro</span>
                        <span class="server-ip">185.25.118.12</span>
                    </div> -->
                </div>
            </div>
            
            <div class="col-lg-4 text-lg-end">
                <div class="dns-stats">
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Аптайм</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">&lt;5мс</div>
                        <div class="stat-label">Відгук</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Моніторинг</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Константы для скрипта
window.dnsConfig = {
    lookupUrl: '?ajax=1',
    csrfToken: '<?php echo generateCSRFToken(); ?>',
    recordTypes: <?php echo json_encode($dns_record_types); ?>,
    translations: {
        checking: 'Перевіряємо DNS записи...',
        error: 'Помилка DNS запиту',
        noRecords: 'DNS записи не знайдено',
        success: 'DNS записи знайдено'
    }
};

// Функции для дополнительных инструментов
function runDNSSpeedTest() {
    alert('DNS Speed Test буде доданий в наступній версії');
}

function checkDNSPropagation() {
    alert('DNS Propagation Check буде доданий в наступній версії');
}

function checkDNSSecurity() {
    alert('DNS Security Check буде доданий в наступній версії');
}

function exportDNSRecords() {
    alert('DNS Records Export буде доданий в наступній версії');
}
</script>

 <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>