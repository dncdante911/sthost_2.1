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
$page_title = t('domains_whois') . ' - ' . t('site_name');
$meta_description = 'WHOIS сервіс для перевірки інформації про домени .ua, .com.ua та інші. Дізнайтесь хто власник домену, коли закінчується реєстрація.';
$meta_keywords = 'whois домен, інформація про домен, власник домену, дата реєстрації домену';
$page_css = 'domains-whois';
$page_js = 'domains-whois';
$need_api = true;

// Получаем WHOIS серверы из БД
try {
    if (defined('DB_AVAILABLE') && DB_AVAILABLE) {
        $whois_servers = db_fetch_all(
            "SELECT zone, whois_server FROM domain_whois_servers WHERE is_active = 1 ORDER BY zone"
        );
    } else {
        throw new Exception('Database not available');
    }
} catch (Exception $e) {
    // Fallback данные
    $whois_servers = [
        ['zone' => '.ua', 'whois_server' => 'whois.ua'],
        ['zone' => '.com.ua', 'whois_server' => 'whois.ua'],
        ['zone' => '.com', 'whois_server' => 'whois.verisign-grs.com'],
        ['zone' => '.net', 'whois_server' => 'whois.verisign-grs.com'],
        ['zone' => '.org', 'whois_server' => 'whois.pir.org']
    ];
}

// Обработка AJAX запросов для WHOIS
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_POST['action'] === 'whois_lookup') {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['error' => t('error_csrf_token')]);
            exit;
        }
        
        $domain = sanitizeInput($_POST['domain'] ?? '');
        
        if (empty($domain)) {
            echo json_encode(['error' => 'Введіть ім\'я домену']);
            exit;
        }
        
        // Валидация домена
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            echo json_encode(['error' => 'Невірний формат домену']);
            exit;
        }
        
        // Определяем зону домена
        $domain_parts = explode('.', $domain);
        if (count($domain_parts) < 2) {
            echo json_encode(['error' => 'Невірний формат домену']);
            exit;
        }
        
        $zone = '.' . end($domain_parts);
        if (count($domain_parts) > 2 && in_array(end($domain_parts), ['ua'])) {
            $zone = '.' . $domain_parts[count($domain_parts)-2] . '.' . end($domain_parts);
        }
        
        // Находим WHOIS сервер
        $whois_server = null;
        foreach ($whois_servers as $server) {
            if ($server['zone'] === $zone) {
                $whois_server = $server['whois_server'];
                break;
            }
        }
        
        if (!$whois_server) {
            echo json_encode(['error' => 'WHOIS сервер для данной зоны не найден']);
            exit;
        }
        
        // Здесь должен быть реальный WHOIS запрос
        // Пока что возвращаем тестовые данные
        $whois_data = performWhoisLookup($domain, $whois_server);
        
        echo json_encode([
            'domain' => $domain,
            'zone' => $zone,
            'whois_server' => $whois_server,
            'data' => $whois_data
        ]);
        exit;
    }
}

// Функция для выполнения WHOIS запроса (упрощенная версия)
function performWhoisLookup($domain, $whois_server) {
    // Пока что возвращаем тестовые данные
    // В реальной реализации здесь будет подключение к WHOIS серверу
    
    $is_registered = (crc32($domain) % 4) !== 0; // ~75% доменов зарегистрированы
    
    if (!$is_registered) {
        return [
            'status' => 'available',
            'message' => 'Домен доступен для регистрации'
        ];
    }
    
    return [
        'status' => 'registered',
        'domain' => $domain,
        'registrar' => 'Test Registrar',
        'creation_date' => date('Y-m-d', strtotime('-' . rand(30, 3650) . ' days')),
        'expiration_date' => date('Y-m-d', strtotime('+' . rand(30, 365) . ' days')),
        'updated_date' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')),
        'name_servers' => [
            'ns1.example.com',
            'ns2.example.com'
        ],
        'status' => 'clientTransferProhibited',
        'raw_data' => "Domain Name: " . strtoupper($domain) . "\n" .
                      "Registry Domain ID: 123456789_DOMAIN_COM-VRSN\n" .
                      "Registrar WHOIS Server: whois.example.com\n" .
                      "Creation Date: " . date('Y-m-d\TH:i:s\Z', strtotime('-' . rand(30, 3650) . ' days')) . "\n" .
                      "Registry Expiry Date: " . date('Y-m-d\TH:i:s\Z', strtotime('+' . rand(30, 365) . ' days')) . "\n" .
                      "Registrar: Test Registrar\n" .
                      "Domain Status: clientTransferProhibited\n" .
                      "Name Server: NS1.EXAMPLE.COM\n" .
                      "Name Server: NS2.EXAMPLE.COM\n"
    ];
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
    <link rel="stylesheet" href="../../assets/css/main.css">
    <!-- Calculator CSS -->
     <link rel="stylesheet" href="/assets/css/pages/domains2.css">
</head>

<!-- WHOIS Hero -->
<section class="whois-hero py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-5 fw-bold mb-4">WHOIS lookup</h1>
                <p class="lead mb-5">Перевірте інформацію про власника домену, дати реєстрації та закінчення, DNS сервери та інші дані.</p>
                
                <!-- WHOIS Search Form -->
                <div class="whois-search-form">
                    <form id="whoisForm" class="row g-3 justify-content-center">
                        <input type="hidden" id="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="col-md-8">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       id="whoisDomain" 
                                       class="form-control" 
                                       placeholder="example.com або example.com.ua"
                                       pattern="[a-zA-Z0-9.-]+"
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-info-circle"></i>
                                Перевірити WHOIS
                            </button>
                        </div>
                    </form>
                    
                    <!-- Search Results -->
                    <div id="whoisResults" class="mt-5"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- WHOIS Info -->
<section class="whois-info py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Що таке WHOIS?</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="info-card h-100">
                    <div class="info-icon">
                        <i class="bi bi-database"></i>
                    </div>
                    <h4>База даних доменів</h4>
                    <p>WHOIS - це протокол і база даних, що містить інформацію про зареєстровані домени, включаючи дані про власників, реєстраторів та технічні деталі.</p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="info-card h-100">
                    <div class="info-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h4>Інформація про власника</h4>
                    <p>Через WHOIS можна дізнатись хто є власником домену, контактну інформацію (якщо не приховано), дати реєстрації та закінчення терміну дії.</p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="info-card h-100">
                    <div class="info-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Перевірка доменів</h4>
                    <p>WHOIS допомагає перевірити статус домену, визначити чи доступний він для реєстрації, а також отримати технічну інформацію про DNS сервери.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Supported Zones -->
<section class="supported-zones py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Підтримувані доменні зони</h2>
                <p class="section-subtitle">Наш WHOIS сервіс працює з наступними доменними зонами</p>
            </div>
        </div>
        
        <div class="row g-3">
            <?php foreach ($whois_servers as $server): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="zone-card text-center">
                    <div class="zone-name"><?php echo escapeOutput($server['zone']); ?></div>
                    <div class="zone-server"><?php echo escapeOutput($server['whois_server']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Privacy Protection -->
<section class="privacy-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold">Захист приватності WHOIS</h2>
                <p class="lead">Стурбовані приватністю ваших даних в WHOIS базі?</p>
                
                <div class="privacy-benefits">
                    <div class="benefit-item">
                        <i class="bi bi-eye-slash text-primary"></i>
                        <span>Приховання особистих даних</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-shield-lock text-primary"></i>
                        <span>Захист від спаму та небажаних дзвінків</span>
                    </div>
                    <div class="benefit-item">
                        <i class="bi bi-incognito text-primary"></i>
                        <span>Анонімна реєстрація доменів</span>
                    </div>
                </div>
                
                <p>При реєстрації домену у нас ви автоматично отримуєте безкоштовний захист приватності WHOIS.</p>
                
                <a href="/domains/register" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i>
                    Зареєструвати домен
                </a>
            </div>
            
            <div class="col-lg-6">
                <div class="privacy-visual">
                    <div class="before-after">
                        <div class="before">
                            <h5>Без захисту:</h5>
                            <div class="whois-data">
                                <div class="data-line">Name: Іван Петренко</div>
                                <div class="data-line">Email: ivan@example.com</div>
                                <div class="data-line">Phone: +380501234567</div>
                                <div class="data-line">Address: вул. Хрещатик 1, Київ</div>
                            </div>
                        </div>
                        
                        <div class="after">
                            <h5>З захистом:</h5>
                            <div class="whois-data protected">
                                <div class="data-line">Name: REDACTED FOR PRIVACY</div>
                                <div class="data-line">Email: REDACTED FOR PRIVACY</div>
                                <div class="data-line">Phone: REDACTED FOR PRIVACY</div>
                                <div class="data-line">Address: REDACTED FOR PRIVACY</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- WHOIS Tools -->
<section class="whois-tools py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Додаткові інструменти</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-dns"></i>
                    </div>
                    <h4>DNS Lookup</h4>
                    <p>Перевірте DNS записи домену</p>
                    <a href="/domains/dns" class="btn btn-outline-primary">Перевірити DNS</a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h4>Пошук доменів</h4>
                    <p>Знайдіть доступні домени</p>
                    <a href="/domains/register" class="btn btn-outline-primary">Знайти домен</a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="tool-card text-center h-100">
                    <div class="tool-icon">
                        <i class="bi bi-arrow-right-circle"></i>
                    </div>
                    <h4>Перенесення доменів</h4>
                    <p>Перенесіть домен до нас</p>
                    <a href="/domains/transfer" class="btn btn-outline-primary">Перенести</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Константы для скрипта
window.whoisConfig = {
    lookupUrl: '?ajax=1',
    csrfToken: '<?php echo generateCSRFToken(); ?>',
    servers: <?php echo json_encode($whois_servers); ?>,
    translations: {
        searching: 'Виконуємо WHOIS запит...',
        error: 'Помилка запиту',
        notFound: 'Домен не знайдено',
        available: 'Домен доступен для реєстрації'
    }
};
</script>

 <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>