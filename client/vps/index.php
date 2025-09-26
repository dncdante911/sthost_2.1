<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/client/vps/includes/VPSManager.php';

// Проверяем авторизацию
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    header('Location: /auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Создаем экземпляр VPS Manager
$vpsManager = new VPSManager();

// Получаем список VPS пользователя
$vpsList = $vpsManager->getUserVPSList($user_id);

// Получаем планы и шаблоны ОС для модального окна создания
$vpsPlans = [];
$osTemplates = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM vps_plans WHERE is_active = 1 ORDER BY price_monthly ASC");
    $stmt->execute();
    $vpsPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM vps_os_templates WHERE is_active = 1 ORDER BY is_popular DESC, display_name_ua ASC");
    $stmt->execute();
    $osTemplates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Таблицы не созданы, используем заглушки
    $vpsPlans = [
        ['id' => 1, 'name_ua' => 'VPS Start', 'cpu_cores' => 1, 'ram_mb' => 1024, 'storage_gb' => 20, 'price_monthly' => 299],
        ['id' => 2, 'name_ua' => 'VPS Basic', 'cpu_cores' => 2, 'ram_mb' => 2048, 'storage_gb' => 40, 'price_monthly' => 499],
        ['id' => 3, 'name_ua' => 'VPS Pro', 'cpu_cores' => 4, 'ram_mb' => 4096, 'storage_gb' => 80, 'price_monthly' => 899]
    ];
    $osTemplates = [
        ['id' => 1, 'display_name_ua' => 'Ubuntu 22.04 LTS', 'category' => 'linux', 'is_popular' => 1],
        ['id' => 2, 'display_name_ua' => 'Windows 10 Pro', 'category' => 'windows', 'is_popular' => 1],
        ['id' => 3, 'display_name_ua' => 'CentOS Stream 8', 'category' => 'linux', 'is_popular' => 1]
    ];
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Керування VPS - StormHosting UA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .vps-container {
            padding: 30px 0;
        }
        
        .vps-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .vps-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .vps-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .vps-card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px 25px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .vps-card-body {
            padding: 25px;
        }
        
        .vps-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-creating { background: #cce5ff; color: #004085; }
        .status-stopped { background: #f8d7da; color: #721c24; }
        .status-error { background: #f5c6cb; color: #721c24; }
        .status-suspended { background: #fff3cd; color: #856404; }
        
        .power-running { background: #d4edda; color: #155724; }
        .power-shutoff { background: #f8d7da; color: #721c24; }
        .power-paused { background: #fff3cd; color: #856404; }
        
        .vps-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .spec-item {
            text-align: center;
            padding: 15px 10px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .spec-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
        }
        
        .spec-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .vps-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .btn-vps-action {
            padding: 8px 16px;
            font-size: 0.875rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .create-vps-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .create-vps-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .feature-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .plan-selector {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }
        
        .plan-selector:hover {
            border-color: #28a745;
            background: #f8fff8;
        }
        
        .plan-selector.selected {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .os-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 8px;
        }
        
        .os-selector:hover {
            border-color: #007bff;
            background: #f8f9ff;
        }
        
        .os-selector.selected {
            border-color: #007bff;
            background: #e3f2fd;
        }
        
        .os-icon {
            width: 24px;
            height: 24px;
            background: #f0f0f0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Breadcrumbs -->
    <div class="breadcrumbs" style="background: rgba(255,255,255,0.1); padding: 10px 0;">
        <div class="container">
            <a href="/" class="text-white-50 text-decoration-none">Головна</a>
            <span class="text-white-50 mx-2">/</span>
            <a href="/client/dashboard.php" class="text-white-50 text-decoration-none">Кабінет</a>
            <span class="text-white-50 mx-2">/</span>
            <span class="text-white">VPS сервери</span>
        </div>
    </div>

    <div class="vps-container">
        <div class="container">
            <!-- VPS Header -->
            <div class="vps-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1><i class="bi bi-hdd-rack me-3"></i>Керування VPS</h1>
                        <p class="mb-0 opacity-90">
                            Віртуальні приватні сервери з повним root доступом та KVM віртуалізацією
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn create-vps-btn" data-bs-toggle="modal" data-bs-target="#createVPSModal">
                            <i class="bi bi-plus-circle me-2"></i>Створити VPS
                        </button>
                    </div>
                </div>
            </div>

            <!-- VPS List -->
            <?php if (empty($vpsList)): ?>
            <div class="vps-card">
                <div class="empty-state">
                    <i class="bi bi-hdd-rack"></i>
                    <h4>У вас поки немає VPS серверів</h4>
                    <p class="lead">Створіть ваш перший віртуальний сервер та отримайте повний контроль над системою</p>
                    <div class="mt-4">
                        <button class="btn create-vps-btn btn-lg" data-bs-toggle="modal" data-bs-target="#createVPSModal">
                            <i class="bi bi-rocket me-2"></i>Створити перший VPS
                        </button>
                    </div>
                    <div class="mt-4">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <i class="bi bi-lightning-charge text-success" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">KVM віртуалізація</h6>
                                <small class="text-muted">Повна ізоляція ресурсів</small>
                            </div>
                            <div class="col-md-4">
                                <i class="bi bi-hdd text-primary" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">NVMe SSD</h6>
                                <small class="text-muted">Швидкі дискі</small>
                            </div>
                            <div class="col-md-4">
                                <i class="bi bi-shield-check text-warning" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Root доступ</h6>
                                <small class="text-muted">Повний контроль</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($vpsList as $vps): ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="vps-card">
                        <div class="vps-card-header">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($vps['hostname']); ?></h5>
                                <small class="text-muted"><?php echo htmlspecialchars($vps['plan_name']); ?></small>
                            </div>
                            <div class="text-end">
                                <span class="vps-status status-<?php echo $vps['status']; ?>">
                                    <?php echo ucfirst($vps['status']); ?>
                                </span>
                                <br>
                                <small class="power-<?php echo $vps['power_state']; ?> mt-1" style="padding: 2px 8px; border-radius: 10px; font-size: 10px;">
                                    <?php echo ucfirst($vps['power_state']); ?>
                                </small>
                            </div>
                        </div>
                        <div class="vps-card-body">
                            <div class="vps-specs">
                                <div class="spec-item">
                                    <div class="spec-value"><?php echo $vps['cpu_cores']; ?></div>
                                    <div class="spec-label">vCPU</div>
                                </div>
                                <div class="spec-item">
                                    <div class="spec-value"><?php echo round($vps['ram_mb'] / 1024, 1); ?></div>
                                    <div class="spec-label">ГБ RAM</div>
                                </div>
                                <div class="spec-item">
                                    <div class="spec-value"><?php echo $vps['storage_gb']; ?></div>
                                    <div class="spec-label">ГБ SSD</div>
                                </div>
                                <div class="spec-item">
                                    <div class="spec-value"><?php echo $vps['ip_address'] ?: '—'; ?></div>
                                    <div class="spec-label">IP адреса</div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">ОС:</small><br>
                                    <strong><?php echo htmlspecialchars($vps['os_name']); ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Створено:</small><br>
                                    <strong><?php echo date('d.m.Y', strtotime($vps['created_at'])); ?></strong>
                                </div>
                            </div>
                            
                            <div class="vps-actions">
                                <?php if ($vps['power_state'] === 'running'): ?>
                                <button class="btn btn-danger btn-vps-action btn-sm" onclick="controlVPS(<?php echo $vps['id']; ?>, 'stop')">
                                    <i class="bi bi-power"></i> Зупинити
                                </button>
                                <button class="btn btn-warning btn-vps-action btn-sm" onclick="controlVPS(<?php echo $vps['id']; ?>, 'restart')">
                                    <i class="bi bi-arrow-clockwise"></i> Перезапустити
                                </button>
                                <?php else: ?>
                                <button class="btn btn-success btn-vps-action btn-sm" onclick="controlVPS(<?php echo $vps['id']; ?>, 'start')">
                                    <i class="bi bi-play"></i> Запустити
                                </button>
                                <?php endif; ?>
                                
                                <a href="/client/vps/manage.php?id=<?php echo $vps['id']; ?>" class="btn btn-primary btn-vps-action btn-sm">
                                    <i class="bi bi-gear"></i> Керувати
                                </a>
                                
                                <button class="btn btn-info btn-vps-action btn-sm" onclick="openVNC(<?php echo $vps['id']; ?>)">
                                    <i class="bi bi-display"></i> Консоль
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create VPS Modal -->
    <div class="modal fade" id="createVPSModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Створити новий VPS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="createVPSForm" onsubmit="createVPS(event)">
                    <div class="modal-body">
                        <!-- Вкладки -->
                        <ul class="nav nav-tabs mb-4" id="createVPSTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="plan-tab" data-bs-toggle="tab" data-bs-target="#plan-pane" type="button">
                                    <i class="bi bi-hdd-rack me-2"></i>План
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="os-tab" data-bs-toggle="tab" data-bs-target="#os-pane" type="button">
                                    <i class="bi bi-disc me-2"></i>Операційна система
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-pane" type="button">
                                    <i class="bi bi-gear me-2"></i>Налаштування
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="createVPSTabContent">
                            <!-- План VPS -->
                            <div class="tab-pane fade show active" id="plan-pane">
                                <h6 class="mb-3">Оберіть план VPS:</h6>
                                <?php foreach ($vpsPlans as $plan): ?>
                                <div class="plan-selector" onclick="selectPlan(<?php echo $plan['id']; ?>)">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center">
                                                <input type="radio" name="plan_id" value="<?php echo $plan['id']; ?>" class="me-3">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($plan['name_ua']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo $plan['cpu_cores']; ?> vCPU, 
                                                        <?php echo round($plan['ram_mb'] / 1024, 1); ?> ГБ RAM, 
                                                        <?php echo $plan['storage_gb']; ?> ГБ SSD
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="h5 text-success mb-0"><?php echo number_format($plan['price_monthly'], 0); ?> ₴</div>
                                            <small class="text-muted">на місяць</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Операційна система -->
                            <div class="tab-pane fade" id="os-pane">
                                <h6 class="mb-3">Оберіть операційну систему:</h6>
                                
                                <!-- Linux -->
                                <h6 class="text-muted mb-2">Linux:</h6>
                                <?php foreach ($osTemplates as $os): ?>
                                <?php if ($os['category'] === 'linux'): ?>
                                <div class="os-selector" onclick="selectOS(<?php echo $os['id']; ?>)">
                                    <input type="radio" name="os_template_id" value="<?php echo $os['id']; ?>">
                                    <div class="os-icon">
                                        <i class="bi bi-ubuntu text-orange"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($os['display_name_ua']); ?></strong>
                                        <?php if ($os['is_popular']): ?>
                                        <span class="badge bg-success ms-2">Популярна</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>

                                <!-- Windows -->
                                <h6 class="text-muted mb-2 mt-4">Windows:</h6>
                                <?php foreach ($osTemplates as $os): ?>
                                <?php if ($os['category'] === 'windows'): ?>
                                <div class="os-selector" onclick="selectOS(<?php echo $os['id']; ?>)">
                                    <input type="radio" name="os_template_id" value="<?php echo $os['id']; ?>">
                                    <div class="os-icon">
                                        <i class="bi bi-windows text-primary"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($os['display_name_ua']); ?></strong>
                                        <span class="badge bg-warning ms-2">Ліцензія окремо</span>
                                        <?php if ($os['is_popular']): ?>
                                        <span class="badge bg-success ms-2">Популярна</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>Windows Server ліцензії оплачуються окремо згідно з тарифами Microsoft</small>
                                </div>
                            </div>

                            <!-- Налаштування -->
                            <div class="tab-pane fade" id="settings-pane">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hostname" class="form-label">Hostname:</label>
                                            <input type="text" class="form-control" id="hostname" name="hostname" 
                                                   placeholder="my-server" pattern="[a-zA-Z0-9-]+" required>
                                            <small class="form-text text-muted">
                                                Тільки англійські букви, цифри та дефіси
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="domain_name" class="form-label">Доменне ім'я (опціонально):</label>
                                            <input type="text" class="form-control" id="domain_name" name="domain_name" 
                                                   placeholder="example.com">
                                            <small class="form-text text-muted">
                                                Повне доменне ім'я для сервера
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h6><i class="bi bi-exclamation-triangle me-2"></i>Важливо:</h6>
                                    <ul class="mb-0 ps-3">
                                        <li>Після створення VPS ви отримаете дані для доступу на email</li>
                                        <li>Створення займає 5-15 хвилин</li>
                                        <li>Root пароль буде згенерований автоматично</li>
                                        <li>Рахунок на оплату буде створений автоматично</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-rocket me-2"></i>Створити VPS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Выбор плана
        function selectPlan(planId) {
            document.querySelectorAll('.plan-selector').forEach(el => el.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            document.querySelector(`input[name="plan_id"][value="${planId}"]`).checked = true;
            
            // Переходим на следующую вкладку
            document.getElementById('os-tab').click();
        }

        // Выбор ОС
        function selectOS(osId) {
            document.querySelectorAll('.os-selector').forEach(el => el.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            document.querySelector(`input[name="os_template_id"][value="${osId}"]`).checked = true;
            
            // Переходим на следующую вкладку
            document.getElementById('settings-tab').click();
        }

        // Создание VPS
        async function createVPS(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Створюємо...';
            
            try {
                const response = await fetch('/client/vps/api/create.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Закрываем модальное окно
                    bootstrap.Modal.getInstance(document.getElementById('createVPSModal')).hide();
                    
                    // Показываем сообщение об успехе
                    showAlert('success', 'VPS успішно створено! Дані для доступу надіслані на ваш email.');
                    
                    // Перезагружаем страницу через 2 секунды
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('danger', 'Помилка створення VPS: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', 'Помилка з\'єднання з сервером');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // Управление VPS
        async function controlVPS(vpsId, action) {
            const actionNames = {
                'start': 'запуск',
                'stop': 'зупинка',
                'restart': 'перезапуск'
            };
            
            if (!confirm(`Ви впевнені, що хочете виконати ${actionNames[action]} VPS?`)) {
                return;
            }
            
            try {
                const response = await fetch('/client/vps/api/control.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        vps_id: vpsId,
                        action: action
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', result.message);
                    // Перезагружаем страницу через 1 секунду
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('danger', 'Помилка: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', 'Помилка з\'єднання з сервером');
            }
        }

        // Открытие VNC консоли
        function openVNC(vpsId) {
            // Откроем в новом окне страницу VNC консоли
            window.open(`/client/vps/vnc.php?id=${vpsId}`, 'VNC_' + vpsId, 'width=1024,height=768,scrollbars=yes,resizable=yes');
        }

        // Показ уведомлений
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Автоматически скрываем через 5 секунд
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Обновление статуса VPS каждые 30 секунд
        setInterval(async function() {
            const vpsCards = document.querySelectorAll('[data-vps-id]');
            
            for (const card of vpsCards) {
                const vpsId = card.getAttribute('data-vps-id');
                
                try {
                    const response = await fetch(`/client/vps/api/status.php?id=${vpsId}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        // Обновляем статус в карточке
                        const statusElement = card.querySelector('.vps-status');
                        const powerElement = card.querySelector('[class*="power-"]');
                        
                        if (statusElement) {
                            statusElement.className = `vps-status status-${result.data.status}`;
                            statusElement.textContent = result.data.status;
                        }
                        
                        if (powerElement) {
                            powerElement.className = `power-${result.data.power_state}`;
                            powerElement.textContent = result.data.power_state;
                        }
                    }
                } catch (error) {
                    console.error('Status update error:', error);
                }
            }
        }, 30000);
    </script>
</body>
</html>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>