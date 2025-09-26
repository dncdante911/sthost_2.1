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

// Получаем ID VPS
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid VPS ID');
}

$vpsId = (int)$_GET['id'];

// Проверяем, что VPS принадлежит пользователю
try {
    $stmt = $pdo->prepare("SELECT * FROM vps_instances WHERE id = ? AND user_id = ?");
    $stmt->execute([$vpsId, $user_id]);
    $vps = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vps) {
        die('VPS not found or access denied');
    }
    
    // Получаем VNC информацию
    $vpsManager = new VPSManager();
    $vncInfo = $vpsManager->getVNCInfo($vpsId, $user_id);
    
    if (!$vncInfo['success']) {
        $vncError = $vncInfo['message'];
        $vncInfo = null;
    }
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNC Консоль - <?php echo htmlspecialchars($vps['hostname']); ?> - StormHosting UA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #1e1e1e;
            color: #ffffff;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            margin: 0;
            padding: 0;
        }
        
        .vnc-header {
            background: #333333;
            padding: 10px 20px;
            border-bottom: 1px solid #555555;
            display: flex;
            justify-content: between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 50px;
        }
        
        .vnc-title {
            font-size: 14px;
            font-weight: bold;
            color: #00ff00;
        }
        
        .vnc-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .vnc-btn {
            background: #555555;
            border: none;
            color: white;
            padding: 5px 12px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .vnc-btn:hover {
            background: #666666;
        }
        
        .vnc-btn.active {
            background: #007bff;
        }
        
        .vnc-container {
            margin-top: 50px;
            padding: 20px;
            height: calc(100vh - 50px);
            display: flex;
            flex-direction: column;
        }
        
        .vnc-canvas-container {
            flex: 1;
            background: #000000;
            border: 2px solid #555555;
            border-radius: 5px;
            overflow: auto;
            position: relative;
            min-height: 600px;
        }
        
        #vnc-canvas {
            display: block;
            margin: 0;
            padding: 0;
            cursor: none;
        }
        
        .vnc-status {
            background: #333333;
            padding: 8px 15px;
            border-radius: 3px;
            font-size: 12px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
        }
        
        .vnc-status.connected {
            border-left-color: #28a745;
        }
        
        .vnc-status.error {
            border-left-color: #dc3545;
        }
        
        .vnc-info {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 12px;
        }
        
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 400px;
            flex-direction: column;
        }
        
        .spinner-border {
            color: #007bff;
        }
        
        .keyboard-shortcuts {
            background: #2d2d2d;
            padding: 10px;
            border-radius: 3px;
            font-size: 11px;
            margin-top: 10px;
        }
        
        .shortcut {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        
        .shortcut kbd {
            background: #555;
            color: #fff;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="vnc-header">
        <div class="vnc-title">
            <i class="bi bi-display me-2"></i>VNC Консоль: <?php echo htmlspecialchars($vps['hostname']); ?>
        </div>
        <div class="vnc-controls">
            <button class="vnc-btn" onclick="sendCtrlAltDel()">
                <i class="bi bi-power me-1"></i>Ctrl+Alt+Del
            </button>
            <button class="vnc-btn" onclick="toggleFullscreen()">
                <i class="bi bi-fullscreen me-1"></i>Повний екран
            </button>
            <button class="vnc-btn" onclick="screenshot()">
                <i class="bi bi-camera me-1"></i>Скріншот
            </button>
            <button class="vnc-btn" onclick="reconnect()">
                <i class="bi bi-arrow-clockwise me-1"></i>Перепідключити
            </button>
            <button class="vnc-btn" onclick="window.close()">
                <i class="bi bi-x-circle me-1"></i>Закрити
            </button>
        </div>
    </div>

    <div class="vnc-container">
        <?php if (isset($vncError)): ?>
        <div class="vnc-status error">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Помилка підключення до VNC консолі: <?php echo htmlspecialchars($vncError); ?>
        </div>
        <div class="vnc-info">
            <h6>Можливі причини:</h6>
            <ul class="mb-0">
                <li>VPS вимкнений або перезавантажується</li>
                <li>VNC сервер не налаштований</li>
                <li>Проблеми з мережею</li>
            </ul>
        </div>
        <?php else: ?>
        <div class="vnc-status" id="vnc-status">
            <i class="bi bi-circle-fill me-2" style="color: #ffc107;"></i>
            Підключення до VNC...
        </div>
        
        <div class="vnc-info">
            <div class="row">
                <div class="col-md-6">
                    <strong>Сервер:</strong> <?php echo htmlspecialchars($vncInfo['host']); ?><br>
                    <strong>Порт:</strong> <?php echo htmlspecialchars($vncInfo['port']); ?>
                </div>
                <div class="col-md-6">
                    <strong>IP адреса VPS:</strong> <?php echo htmlspecialchars($vps['ip_address']); ?><br>
                    <strong>Статус:</strong> <?php echo htmlspecialchars($vps['power_state']); ?>
                </div>
            </div>
        </div>

        <div class="vnc-canvas-container">
            <div class="loading-spinner" id="loading-spinner">
                <div class="spinner-border mb-3" role="status"></div>
                <div>Завантаження VNC клієнта...</div>
                <small class="text-muted">Це може зайняти кілька секунд</small>
            </div>
            <canvas id="vnc-canvas" style="display: none;"></canvas>
        </div>

        <div class="keyboard-shortcuts">
            <strong>Гарячі клавіші:</strong>
            <span class="shortcut"><kbd>Ctrl+Alt+Del</kbd> - Перезавантаження</span>
            <span class="shortcut"><kbd>F11</kbd> - Повний екран</span>
            <span class="shortcut"><kbd>Ctrl+Alt+F1</kbd> - Консоль 1</span>
            <span class="shortcut"><kbd>Ctrl+Alt+F2</kbd> - Консоль 2</span>
            <span class="shortcut"><kbd>Alt+Tab</kbd> - Переключення вікон</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- noVNC JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (!isset($vncError)): ?>
    <script>
        // VNC клиент (спрощена версія)
        // В реальному проекті тут буде підключення noVNC бібліотеки
        
        let vncConnected = false;
        let reconnectAttempts = 0;
        const maxReconnectAttempts = 5;
        
        // Параметри VNC підключення
        const vncHost = '<?php echo $vncInfo['host']; ?>';
        const vncPort = '<?php echo $vncInfo['port']; ?>';
        const vncPassword = '<?php echo $vncInfo['password'] ?? ''; ?>';
        
        // Ініціалізація VNC після завантаження сторінки
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initVNC, 1000);
        });
        
        function initVNC() {
            // Тут би було справжнє підключення до VNC
            // Для демонстрації показуємо успішне підключення через 3 секунди
            
            updateStatus('connecting', 'Встановлення з\'єднання...');
            
            setTimeout(function() {
                if (Math.random() > 0.3) { // 70% шанс успіху
                    connectSuccess();
                } else {
                    connectError('Не вдалося підключитися до VNC сервера');
                }
            }, 3000);
        }
        
        function connectSuccess() {
            vncConnected = true;
            reconnectAttempts = 0;
            
            updateStatus('connected', 'Підключено до VNC консолі');
            
            // Приховуємо spinner та показуємо canvas
            document.getElementById('loading-spinner').style.display = 'none';
            document.getElementById('vnc-canvas').style.display = 'block';
            
            // Ініціалізуємо canvas
            const canvas = document.getElementById('vnc-canvas');
            const ctx = canvas.getContext('2d');
            
            // Встановлюємо розмір canvas
            canvas.width = 1024;
            canvas.height = 768;
            
            // Малюємо заглушку рабочего стола
            drawMockDesktop(ctx, canvas.width, canvas.height);
            
            // Додаємо обробники подій
            addCanvasEventListeners(canvas);
        }
        
        function connectError(message) {
            vncConnected = false;
            updateStatus('error', 'Помилка підключення: ' + message);
            
            if (reconnectAttempts < maxReconnectAttempts) {
                reconnectAttempts++;
                updateStatus('error', `Помилка підключення. Спроба ${reconnectAttempts}/${maxReconnectAttempts}...`);
                setTimeout(initVNC, 5000);
            } else {
                updateStatus('error', 'Не вдалося підключитися до VNC після ' + maxReconnectAttempts + ' спроб');
            }
        }
        
        function updateStatus(type, message) {
            const status = document.getElementById('vnc-status');
            const icons = {
                'connecting': '<i class="bi bi-circle-fill me-2" style="color: #ffc107;"></i>',
                'connected': '<i class="bi bi-circle-fill me-2" style="color: #28a745;"></i>',
                'error': '<i class="bi bi-circle-fill me-2" style="color: #dc3545;"></i>'
            };
            
            status.className = 'vnc-status ' + type;
            status.innerHTML = icons[type] + message;
        }
        
        function drawMockDesktop(ctx, width, height) {
            // Рисуем фон рабочего стола
            const gradient = ctx.createLinearGradient(0, 0, 0, height);
            gradient.addColorStop(0, '#1e3c72');
            gradient.addColorStop(1, '#2a5298');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, width, height);
            
            // Рисуем панель задач
            ctx.fillStyle = '#333333';
            ctx.fillRect(0, height - 40, width, 40);
            
            // Рисуем кнопку "Пуск"
            ctx.fillStyle = '#0078d4';
            ctx.fillRect(5, height - 35, 60, 30);
            ctx.fillStyle = '#ffffff';
            ctx.font = '12px Arial';
            ctx.fillText('Start', 15, height - 18);
            
            // Рисуем часы
            const now = new Date();
            const timeStr = now.toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'});
            ctx.fillStyle = '#ffffff';
            ctx.font = '11px Arial';
            ctx.fillText(timeStr, width - 60, height - 18);
            
            // Рисуем иконки на рабочем столе
            drawDesktopIcon(ctx, 50, 50, 'My Computer');
            drawDesktopIcon(ctx, 50, 120, 'Recycle Bin');
            drawDesktopIcon(ctx, 50, 190, 'Network');
            
            // Добавляем текст статуса
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('VNC Console Active', width / 2, height / 2 - 20);
            
            ctx.font = '12px Arial';
            ctx.fillText('<?php echo htmlspecialchars($vps['hostname']); ?>', width / 2, height / 2);
            ctx.fillText('IP: <?php echo htmlspecialchars($vps['ip_address']); ?>', width / 2, height / 2 + 20);
        }
        
        function drawDesktopIcon(ctx, x, y, label) {
            // Рисуем иконку
            ctx.fillStyle = '#4CAF50';
            ctx.fillRect(x, y, 32, 32);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(x + 2, y + 2, 28, 28);
            ctx.fillStyle = '#2196F3';
            ctx.fillRect(x + 4, y + 4, 24, 24);
            
            // Подпись
            ctx.fillStyle = '#ffffff';
            ctx.font = '10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(label, x + 16, y + 45);
            ctx.textAlign = 'left';
        }
        
        function addCanvasEventListeners(canvas) {
            // Обработчики мыши
            canvas.addEventListener('mousedown', handleMouseEvent);
            canvas.addEventListener('mouseup', handleMouseEvent);
            canvas.addEventListener('mousemove', handleMouseEvent);
            canvas.addEventListener('wheel', handleWheelEvent);
            
            // Обработчики клавиатуры
            window.addEventListener('keydown', handleKeyEvent);
            window.addEventListener('keyup', handleKeyEvent);
            
            // Фокус на canvas для получения событий клавиатуры
            canvas.setAttribute('tabindex', '0');
            canvas.focus();
        }
        
        function handleMouseEvent(event) {
            if (!vncConnected) return;
            
            // Здесь бы была отправка событий мыши на VNC сервер
            console.log('Mouse event:', event.type, event.offsetX, event.offsetY);
        }
        
        function handleWheelEvent(event) {
            if (!vncConnected) return;
            
            event.preventDefault();
            // Здесь бы была отправка событий скролла на VNC сервер
            console.log('Wheel event:', event.deltaY);
        }
        
        function handleKeyEvent(event) {
            if (!vncConnected) return;
            
            // Блокируем стандартные горячие клавиши браузера
            if (event.ctrlKey && (event.key === 'r' || event.key === 'R' || event.key === 'w' || event.key === 'W')) {
                event.preventDefault();
            }
            
            // Здесь бы была отправка событий клавиатуры на VNC сервер
            console.log('Key event:', event.type, event.key, event.code);
        }
        
        // Функции управления
        function sendCtrlAltDel() {
            if (!vncConnected) {
                alert('VNC не подключен');
                return;
            }
            
            // Здесь бы была отправка Ctrl+Alt+Del на сервер
            updateStatus('connected', 'Отправлено Ctrl+Alt+Del');
            console.log('Sending Ctrl+Alt+Del');
            
            setTimeout(() => {
                updateStatus('connected', 'Подключено к VNC консоли');
            }, 2000);
        }
        
        function toggleFullscreen() {
            const container = document.querySelector('.vnc-canvas-container');
            
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                container.requestFullscreen().catch(err => {
                    console.error('Error entering fullscreen:', err);
                });
            }
        }
        
        function screenshot() {
            if (!vncConnected) {
                alert('VNC не подключен');
                return;
            }
            
            const canvas = document.getElementById('vnc-canvas');
            const link = document.createElement('a');
            link.download = `vnc_screenshot_${new Date().getTime()}.png`;
            link.href = canvas.toDataURL();
            link.click();
        }
        
        function reconnect() {
            reconnectAttempts = 0;
            vncConnected = false;
            
            document.getElementById('vnc-canvas').style.display = 'none';
            document.getElementById('loading-spinner').style.display = 'flex';
            
            initVNC();
        }
        
        // Обработчик закрытия окна
        window.addEventListener('beforeunload', function(event) {
            if (vncConnected) {
                // Здесь бы было закрытие VNC подключения
                console.log('Closing VNC connection');
            }
        });
        
        // Обработка изменения размера окна
        window.addEventListener('resize', function() {
            // Здесь бы было изменение размера VNC
        });
    </script>
    <?php endif; ?>
</body>
</html>