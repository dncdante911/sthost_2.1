<?php
/**
 * Панель управления VPS для клиентов
 * Файл: /client/vps.php или /client/vps/index.php
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Начинаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем авторизацию
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: /?login_required=1');
    exit;
}

// Основные переменные страницы
$page_title = 'Мої VPS сервери - StormHosting UA';
$meta_description = 'Панель управління VPS серверами в StormHosting UA';
$additional_css = [
    '/assets/css/pages/client-vps.css'
];
$additional_js = [
    '/assets/js/pages/client-vps.js'
];

// Подключение к БД
try {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
    }
    
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
        $pdo = DatabaseConnection::getSiteConnection();
    } else {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=sthostsitedb;charset=utf8mb4",
            "sthostdb",
            "3344Frz@q0607Dm\$157",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Помилка підключення до бази даних');
}

// Подключаем VPS Manager
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/classes/VPSManager.php';

$user_id = $_SESSION['user_id'];
$vpsManager = new VPSManager($pdo);

// Получаем список VPS пользователя
$vps_result = $vpsManager->getUserVPS($user_id);
$user_vps_list = $vps_result['success'] ? $vps_result['vps_list'] : [];

// Подключение header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 sidebar">
            <div class="sidebar-content">
                <div class="user-info">
                    <h5>Панель управління</h5>
                    <p class="text-muted">Добро пожаловать, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Користувач') ?>!</p>
                </div>
                
                <nav class="nav-menu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/client/vps">
                                <i class="fas fa-server"></i> Мої VPS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/client/profile">
                                <i class="fas fa-user"></i> Профіль
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/client/billing">
                                <i class="fas fa-credit-card"></i> Біллінг
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/client/support">
                                <i class="fas fa-headset"></i> Підтримка
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <div class="quick-actions mt-4">
                    <h6>Швидкі дії</h6>
                    <div class="d-grid gap-2">
                        <a href="/vps" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Замовити новий VPS
                        </a>
                        <a href="/client/support" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-ticket-alt"></i> Створити тікет
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="col-lg-9 col-md-8 main-content">
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Мої VPS сервери</h2>
                        <p class="text-muted">Управління віртуальними приватними серверами</p>
                    </div>
                    <div>
                        <a href="/vps" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Замовити VPS
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if (empty($user_vps_list)): ?>
                <!-- Пустое состояние -->
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-4">
                        <i class="fas fa-server fa-4x text-muted"></i>
                    </div>
                    <h4>У вас поки немає VPS серверів</h4>
                    <p class="text-muted mb-4">
                        Замовте ваш перший VPS сервер та отримайте повний контроль над віртуальним сервером 
                        з гарантованими ресурсами.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/vps" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Замовити VPS
                        </a>
                        <a href="/client/support" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-question-circle me-2"></i>Консультація
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Список VPS -->
                <div class="vps-list">
                    <?php foreach ($user_vps_list as $vps): ?>
                        <div class="vps-card card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="fas fa-server me-2"></i>
                                            <?= htmlspecialchars($vps['hostname']) ?>
                                        </h5>
                                        <small class="text-muted">
                                            План: <?= htmlspecialchars($vps['plan_name']) ?> | 
                                            IP: <?= htmlspecialchars($vps['ip_address']) ?>
                                        </small>
                                    </div>
                                    <div class="vps-status">
                                        <?php
                                        $status_classes = [
                                            'active' => 'badge-success',
                                            'stopped' => 'badge-secondary', 
                                            'suspended' => 'badge-warning',
                                            'creating' => 'badge-info',
                                            'error' => 'badge-danger'
                                        ];
                                        $status_texts = [
                                            'active' => 'Працює',
                                            'stopped' => 'Зупинено',
                                            'suspended' => 'Призупинено',
                                            'creating' => 'Створюється',
                                            'error' => 'Помилка'
                                        ];
                                        $status_class = $status_classes[$vps['status']] ?? 'badge-secondary';
                                        $status_text = $status_texts[$vps['status']] ?? 'Невідомо';
                                        ?>
                                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <!-- Спецификации -->
                                    <div class="col-lg-4">
                                        <h6>Характеристики</h6>
                                        <div class="specs-list">
                                            <div class="spec-item">
                                                <i class="fas fa-microchip text-primary"></i>
                                                <span><?= $vps['cpu_cores'] ?> CPU ядр<?= $vps['cpu_cores'] > 1 ? 'а' : 'о' ?></span>
                                            </div>
                                            <div class="spec-item">
                                                <i class="fas fa-memory text-primary"></i>
                                                <span><?= round($vps['ram_mb'] / 1024, 1) ?> GB RAM</span>
                                            </div>
                                            <div class="spec-item">
                                                <i class="fas fa-hdd text-primary"></i>
                                                <span><?= $vps['disk_gb'] ?> GB SSD</span>
                                            </div>
                                            <div class="spec-item">
                                                <i class="fas fa-network-wired text-primary"></i>
                                                <span><?= $vps['bandwidth_gb'] ?> GB трафік</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Операционная система -->
                                    <div class="col-lg-4">
                                        <h6>Операційна система</h6>
                                        <div class="os-info">
                                            <div class="os-item">
                                                <i class="fab fa-<?= $vps['os_type'] === 'linux' ? 'linux' : 'windows' ?> me-2"></i>
                                                <div>
                                                    <div class="os-name"><?= htmlspecialchars($vps['os_name'] ?? $vps['os_template']) ?></div>
                                                    <?php if (!empty($vps['os_version'])): ?>
                                                        <small class="text-muted"><?= htmlspecialchars($vps['os_version']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                Створено: <?= date('d.m.Y H:i', strtotime($vps['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Использование ресурсов -->
                                    <div class="col-lg-4">
                                        <h6>Використання ресурсів</h6>
                                        <?php if (!empty($vps['resource_usage'])): ?>
                                            <div class="resource-usage">
                                                <!-- CPU Usage -->
                                                <div class="usage-item">
                                                    <div class="d-flex justify-content-between">
                                                        <span>CPU:</span>
                                                        <span><?= round($vps['resource_usage']['cpu_usage'], 1) ?>%</span>
                                                    </div>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar" style="width: <?= min($vps['resource_usage']['cpu_usage'], 100) ?>%"></div>
                                                    </div>
                                                </div>
                                                
                                                <!-- RAM Usage -->
                                                <div class="usage-item">
                                                    <div class="d-flex justify-content-between">
                                                        <span>RAM:</span>
                                                        <span><?= round($vps['resource_usage']['memory_usage'], 1) ?>%</span>
                                                    </div>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar" style="width: <?= min($vps['resource_usage']['memory_usage'], 100) ?>%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">Статистика недоступна</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Действия -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="vps-actions">
                                            <div class="btn-group me-2" role="group">
                                                <?php if ($vps['status'] === 'active'): ?>
                                                    <button class="btn btn-outline-warning btn-sm vps-action-btn" 
                                                            data-vps-id="<?= $vps['id'] ?>" 
                                                            data-action="stop">
                                                        <i class="fas fa-stop"></i> Зупинити
                                                    </button>
                                                    <button class="btn btn-outline-primary btn-sm vps-action-btn" 
                                                            data-vps-id="<?= $vps['id'] ?>" 
                                                            data-action="restart">
                                                        <i class="fas fa-redo"></i> Перезапуск
                                                    </button>
                                                <?php elseif ($vps['status'] === 'stopped'): ?>
                                                    <button class="btn btn-outline-success btn-sm vps-action-btn" 
                                                            data-vps-id="<?= $vps['id'] ?>" 
                                                            data-action="start">
                                                        <i class="fas fa-play"></i> Запустити
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="btn-group me-2" role="group">
                                                <?php if (isset($vps['vnc']) && $vps['status'] === 'active'): ?>
                                                    <button class="btn btn-outline-info btn-sm" 
                                                            onclick="openVNC(<?= $vps['id'] ?>)">
                                                        <i class="fas fa-desktop"></i> VNC консоль
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-outline-secondary btn-sm" 
                                                        onclick="showVPSDetails(<?= $vps['id'] ?>)">
                                                    <i class="fas fa-info-circle"></i> Деталі
                                                </button>
                                            </div>
                                            
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                        data-bs-toggle="dropdown">
                                                    <i class="fas fa-cog"></i> Дії
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="showReinstallModal(<?= $vps['id'] ?>)">
                                                            <i class="fas fa-download"></i> Переустановити ОС
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="showBackupModal(<?= $vps['id'] ?>)">
                                                            <i class="fas fa-save"></i> Створити бекап
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="showResizeModal(<?= $vps['id'] ?>)">
                                                            <i class="fas fa-expand-arrows-alt"></i> Змінити план
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           onclick="confirmDeleteVPS(<?= $vps['id'] ?>)">
                                                            <i class="fas fa-trash"></i> Видалити VPS
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- VNC Modal -->
<div class="modal fade" id="vncModal" tabindex="-1" aria-labelledby="vncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vncModalLabel">VNC Console</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="vncContainer" style="height: 600px; background: #000;">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Завантаження...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VPS Details Modal -->
<div class="modal fade" id="vpsDetailsModal" tabindex="-1" aria-labelledby="vpsDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vpsDetailsModalLabel">Деталі VPS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="vpsDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Reinstall Modal -->
<div class="modal fade" id="reinstallModal" tabindex="-1" aria-labelledby="reinstallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reinstallModalLabel">Переустановка ОС</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reinstallForm">
                    <input type="hidden" id="reinstallVpsId" name="vps_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Увага!</strong> Переустановка ОС видалить всі дані на сервері. 
                        Рекомендуємо створити бекап перед продовженням.
                    </div>
                    
                    <div class="mb-3">
                        <label for="newOS" class="form-label">Оберіть нову ОС</label>
                        <select class="form-select" id="newOS" name="os_template" required>
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="createBackup" checked>
                            <label class="form-check-label" for="createBackup">
                                Створити бекап перед переустановкою
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-danger" onclick="confirmReinstall()">Переустановити</button>
            </div>
        </div>
    </div>
</div>

<script>
// VPS Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // VPS action buttons
    document.querySelectorAll('.vps-action-btn').forEach(button => {
        button.addEventListener('click', function() {
            const vpsId = this.dataset.vpsId;
            const action = this.dataset.action;
            performVPSAction(vpsId, action, this);
        });
    });
    
    // Auto-refresh status every 30 seconds
    setInterval(refreshVPSStatus, 30000);
});

function performVPSAction(vpsId, action, button) {
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обробка...';
    
    fetch('/api/vps/control', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            vps_id: vpsId,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => refreshVPSStatus(), 2000);
        } else {
            showError(data.error);
        }
    })
    .catch(error => {
        showError('Помилка мережі');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function openVNC(vpsId) {
    const modal = new bootstrap.Modal(document.getElementById('vncModal'));
    modal.show();
    
    // Initialize VNC connection
    fetch(`/api/vps/${vpsId}/vnc`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            initVNC(data.vnc_url, data.password);
        } else {
            document.getElementById('vncContainer').innerHTML = 
                '<div class="alert alert-danger">Не вдалося підключитися до VNC консолі</div>';
        }
    });
}

function showVPSDetails(vpsId) {
    const modal = new bootstrap.Modal(document.getElementById('vpsDetailsModal'));
    
    document.getElementById('vpsDetailsContent').innerHTML = 
        '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    modal.show();
    
    fetch(`/api/vps/${vpsId}/details`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('vpsDetailsContent').innerHTML = generateVPSDetailsHTML(data.vps);
        } else {
            document.getElementById('vpsDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Помилка завантаження даних</div>';
        }
    });
}

function showReinstallModal(vpsId) {
    document.getElementById('reinstallVpsId').value = vpsId;
    
    // Load OS templates
    fetch('/api/vps/os-templates')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('newOS');
            select.innerHTML = '<option value="">Оберіть ОС</option>';
            
            data.templates.forEach(os => {
                const option = document.createElement('option');
                option.value = os.name;
                option.textContent = `${os.display_name} ${os.version || ''}`;
                select.appendChild(option);
            });
        }
    });
    
    const modal = new bootstrap.Modal(document.getElementById('reinstallModal'));
    modal.show();
}

function confirmReinstall() {
    const form = document.getElementById('reinstallForm');
    const formData = new FormData(form);
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    if (!confirm('Ви впевнені? Всі дані на сервері будуть видалені!')) {
        return;
    }
    
    fetch('/api/vps/reinstall', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Переустановка розпочата. Це може зайняти кілька хвилин.');
            bootstrap.Modal.getInstance(document.getElementById('reinstallModal')).hide();
            setTimeout(() => refreshVPSStatus(), 3000);
        } else {
            showError(data.error);
        }
    });
}

function refreshVPSStatus() {
    // Refresh page to update status
    location.reload();
}

function generateVPSDetailsHTML(vps) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6>Основна інформація</h6>
                <table class="table table-sm">
                    <tr><td>Hostname:</td><td>${vps.hostname}</td></tr>
                    <tr><td>IP адреса:</td><td>${vps.ip_address}</td></tr>
                    <tr><td>Статус:</td><td><span class="badge badge-${vps.status}">${vps.status}</span></td></tr>
                    <tr><td>Створено:</td><td>${new Date(vps.created_at).toLocaleString()}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Ресурси</h6>
                <table class="table table-sm">
                    <tr><td>CPU:</td><td>${vps.cpu_cores} ядра</td></tr>
                    <tr><td>RAM:</td><td>${Math.round(vps.ram_mb / 1024 * 10) / 10} GB</td></tr>
                    <tr><td>Диск:</td><td>${vps.disk_gb} GB SSD</td></tr>
                    <tr><td>Трафік:</td><td>${vps.bandwidth_gb} GB/міс</td></tr>
                </table>
            </div>
        </div>
    `;
}

function showSuccess(message) {
    // Implement your success notification
    alert(message);
}

function showError(message) {
    // Implement your error notification
    alert(message);
}
</script>

<?php
// Подключение footer
include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
?>