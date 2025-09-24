<?php
/**
 * Тестовая страница для IP Check API
 * Файл: /test-ip-check-api.php
 * Удалите этот файл после тестирования!
 */

// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест IP Check API</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .ip-examples { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; }
        .ip-example { background: #e9ecef; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 12px; }
        .ip-example:hover { background: #dee2e6; }
        .feature-toggle { display: flex; align-items: center; gap: 10px; margin: 10px 0; }
        .feature-toggle input[type="checkbox"] { width: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Тест IP Check API</h1>
        <p><strong>⚠️ Важно:</strong> Удалите этот файл после тестирования!</p>
        
        <form id="testForm">
            <div class="form-group">
                <label for="testIP">IP адрес для тестирования:</label>
                <div class="ip-examples">
                    <span class="ip-example" onclick="setIP('8.8.8.8')">8.8.8.8 (Google DNS)</span>
                    <span class="ip-example" onclick="setIP('1.1.1.1')">1.1.1.1 (Cloudflare)</span>
                    <span class="ip-example" onclick="setIP('208.67.222.222')">208.67.222.222 (OpenDNS)</span>
                    <span class="ip-example" onclick="setIP('<?= $_SERVER['REMOTE_ADDR'] ?>')">Ваш IP: <?= $_SERVER['REMOTE_ADDR'] ?></span>
                    <span class="ip-example" onclick="setIP('185.220.101.1')">185.220.101.1 (TOR Exit)</span>
                </div>
                <input type="text" id="testIP" name="ip" value="8.8.8.8" required>
            </div>
            
            <div class="form-group">
                <label>Опции проверки:</label>
                <div class="feature-toggle">
                    <input type="checkbox" id="checkBlacklists" name="check_blacklists" checked>
                    <label for="checkBlacklists">Проверка черных списков</label>
                </div>
                <div class="feature-toggle">
                    <input type="checkbox" id="checkThreats" name="check_threat_intel" checked>
                    <label for="checkThreats">Анализ угроз</label>
                </div>
                <div class="feature-toggle">
                    <input type="checkbox" id="checkDistance" name="check_distance" checked>
                    <label for="checkDistance">Расчет расстояния</label>
                </div>
            </div>
            
            <button type="submit">🚀 Тестировать IP Check API</button>
        </form>
        
        <div id="result"></div>
        
        <hr style="margin: 30px 0;">
        
        <h2>📋 Проверка файлов:</h2>
        <div class="result">
            <?php
            $files = [
                '/api/tools/ip-check.php' => 'IP Check API файл',
                '/assets/css/pages/tools-ip-check2.css' => 'CSS стили',
                '/assets/js/tools-ip-check2.js' => 'JavaScript',
                '/pages/tools/ip-check.php' => 'HTML страница',
                '/includes/ip-check-config.php' => 'Конфигурация (опционально)'
            ];
            
            foreach ($files as $file => $description) {
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $file;
                if (file_exists($fullPath)) {
                    echo "✅ <strong>$description</strong>: $file<br>";
                } else {
                    echo "❌ <strong>$description</strong>: $file - <span style='color: red;'>НЕ НАЙДЕН</span><br>";
                }
            }
            ?>
        </div>
        
        <h2>🗄️ Проверка базы данных:</h2>
        <div class="result">
            <?php
            // Проверка подключения к БД
            $dbHost = 'localhost';
            $dbName = 'sthostsitedb';
            $dbUser = 'sthostdb'; // измените на ваши данные
            $dbPass = '3344Frz@q0607Dm$157';     // измените на ваши данные
            
            try {
                $pdo = new PDO(
                    "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                echo "✅ <strong>Подключение к БД:</strong> Успешно (sthostsitedb)<br>";
                
                // Проверяем/создаем таблицу ip_check_logs
                try {
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS ip_check_logs (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            checked_ip VARCHAR(45) NOT NULL,
                            ip_address VARCHAR(45) NOT NULL,
                            user_agent TEXT,
                            results_json JSON,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_ip_time (ip_address, created_at),
                            INDEX idx_checked_ip (checked_ip)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    
                    echo "✅ <strong>Таблица ip_check_logs:</strong> Готова к использованию<br>";
                    
                    // Тест записи в БД
                    $stmt = $pdo->prepare("
                        INSERT INTO ip_check_logs 
                        (checked_ip, ip_address, user_agent, results_json, created_at) 
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $testData = json_encode(['test' => true, 'timestamp' => date('c')]);
                    $stmt->execute(['127.0.0.1', '127.0.0.1', 'Test-Agent', $testData]);
                    
                    echo "✅ <strong>Тест записи в БД:</strong> Успешно<br>";
                    
                    // Удаляем тестовую запись
                    $pdo->exec("DELETE FROM ip_check_logs WHERE checked_ip = '127.0.0.1' AND ip_address = '127.0.0.1'");
                    
                } catch (PDOException $e) {
                    echo "❌ <strong>Ошибка работы с таблицей:</strong> " . $e->getMessage() . "<br>";
                }
                
            } catch (PDOException $e) {
                echo "❌ <strong>Ошибка подключения к БД:</strong> " . $e->getMessage() . "<br>";
            }
            ?>
        </div>
        
        <h2>🌐 Прямой тест API:</h2>
        <div class="result">
            <p>Тест cURL запроса к IP Check API:</p>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direct_test'])) {
                $testIP = '8.8.8.8';
                $testOptions = [
                    'checkBlacklists' => true,
                    'checkThreatIntel' => true,
                    'checkDistance' => false
                ];
                
                $postData = [
                    'ip' => $testIP,
                    'options' => json_encode($testOptions)
                ];
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => 'http://' . $_SERVER['HTTP_HOST'] . '/api/tools/ip-check.php',
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $postData,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                echo "<strong>HTTP Код:</strong> $httpCode<br>";
                if ($error) {
                    echo "<strong>Ошибка cURL:</strong> $error<br>";
                }
                echo "<strong>Ответ:</strong><br>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
            ?>
            
            <form method="post">
                <input type="hidden" name="direct_test" value="1">
                <button type="submit">🧪 Прямой тест API через cURL</button>
            </form>
        </div>
        
        <h2>⚙️ Информация о сервере:</h2>
        <div class="result">
            <strong>PHP версия:</strong> <?= PHP_VERSION ?><br>
            <strong>cURL включен:</strong> <?= extension_loaded('curl') ? '✅ Да' : '❌ Нет' ?><br>
            <strong>JSON включен:</strong> <?= extension_loaded('json') ? '✅ Да' : '❌ Нет' ?><br>
            <strong>PDO MySQL:</strong> <?= extension_loaded('pdo_mysql') ? '✅ Да' : '❌ Нет' ?><br>
            <strong>OpenSSL включен:</strong> <?= extension_loaded('openssl') ? '✅ Да' : '❌ Нет' ?><br>
            <strong>Поддержка IPv6:</strong> <?= defined('AF_INET6') ? '✅ Да' : '❌ Нет' ?><br>
            <strong>DNS функции:</strong> <?= function_exists('gethostbyname') ? '✅ Да' : '❌ Нет' ?><br>
            <strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?><br>
            <strong>Current URL:</strong> <?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?><?= $_SERVER['REQUEST_URI'] ?><br>
            <strong>User IP:</strong> <?= $_SERVER['REMOTE_ADDR'] ?><br>
        </div>
        
        <h2>🔧 Тест DNS и сетевых функций:</h2>
        <div class="result">
            <?php
            // Тест DNS функций
            echo "<h4>Тест DNS резолвинга:</h4>";
            $testHosts = ['google.com', 'cloudflare.com', 'github.com'];
            
            foreach ($testHosts as $host) {
                $ip = gethostbyname($host);
                if ($ip !== $host) {
                    echo "✅ <strong>{$host}:</strong> {$ip}<br>";
                } else {
                    echo "❌ <strong>{$host}:</strong> Не удалось разрешить<br>";
                }
            }
            
            // Тест RBL проверки
            echo "<h4>Тест RBL DNS запросов:</h4>";
            $testIP = '127.0.0.2'; // тестовый IP для RBL
            $rblHost = '2.0.0.127.zen.spamhaus.org';
            
            $old = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', 3);
            
            $rblResult = gethostbyname($rblHost);
            
            ini_set('default_socket_timeout', $old);
            
            if ($rblResult !== $rblHost) {
                echo "✅ <strong>RBL тест:</strong> {$rblHost} = {$rblResult}<br>";
            } else {
                echo "⏱️ <strong>RBL тест:</strong> Таймаут или не найден (это нормально)<br>";
            }
            
            // Тест HTTP запросов
            echo "<h4>Тест HTTP запросов:</h4>";
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://ipapi.co/8.8.8.8/json/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'Test-Agent'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode === 200 && !$error) {
                echo "✅ <strong>HTTP API тест:</strong> ipapi.co доступен<br>";
                $data = json_decode($response, true);
                if ($data && isset($data['country'])) {
                    echo "&nbsp;&nbsp;&nbsp;Результат: {$data['country']}, {$data['city']}<br>";
                }
            } else {
                echo "❌ <strong>HTTP API тест:</strong> Ошибка - {$error} (HTTP: {$httpCode})<br>";
            }
            ?>
        </div>
    </div>

    <script>
        function setIP(ip) {
            document.getElementById('testIP').value = ip;
        }
        
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const resultDiv = document.getElementById('result');
            const formData = new FormData(this);
            
            // Собираем опции
            const options = {
                checkBlacklists: document.getElementById('checkBlacklists').checked,
                checkThreatIntel: document.getElementById('checkThreats').checked,
                checkDistance: document.getElementById('checkDistance').checked
            };
            
            const postData = new FormData();
            postData.append('ip', formData.get('ip'));
            postData.append('options', JSON.stringify(options));
            
            // Добавляем тестовую локацию пользователя для расчета расстояния
            if (options.checkDistance) {
                postData.append('user_location', JSON.stringify({
                    lat: 50.4501, // Киев
                    lng: 30.5234
                }));
            }
            
            resultDiv.innerHTML = '<div class="result">🔄 Отправляем запрос к IP Check API...</div>';
            
            try {
                const startTime = Date.now();
                
                const response = await fetch('/api/tools/ip-check.php', {
                    method: 'POST',
                    body: postData
                });
                
                const endTime = Date.now();
                const responseTime = endTime - startTime;
                
                const responseText = await response.text();
                
                let resultClass = response.ok ? 'success' : 'error';
                let resultContent = `
                    <h3>📊 Результат теста:</h3>
                    <p><strong>HTTP Status:</strong> ${response.status} ${response.statusText}</p>
                    <p><strong>Content-Type:</strong> ${response.headers.get('content-type')}</p>
                    <p><strong>Response Time:</strong> ${responseTime}ms</p>
                    <h4>Ответ сервера:</h4>
                    <pre>${responseText}</pre>
                `;
                
                // Пытаемся парсить JSON для красивого отображения
                try {
                    const jsonData = JSON.parse(responseText);
                    resultContent += `
                        <h4>Парсированный JSON:</h4>
                        <pre>${JSON.stringify(jsonData, null, 2)}</pre>
                    `;
                    
                    // Показываем краткую сводку
                    if (jsonData && !jsonData.error) {
                        let summary = '<h4>📋 Краткая сводка:</h4><ul>';
                        
                        if (jsonData.general) {
                            summary += `<li><strong>IP:</strong> ${jsonData.general.ip} (${jsonData.general.ip_type})</li>`;
                        }
                        
                        if (jsonData.location) {
                            summary += `<li><strong>Локация:</strong> ${jsonData.location.city}, ${jsonData.location.country}</li>`;
                        }
                        
                        if (jsonData.network) {
                            summary += `<li><strong>Провайдер:</strong> ${jsonData.network.isp}</li>`;
                            summary += `<li><strong>ASN:</strong> ${jsonData.network.asn}</li>`;
                        }
                        
                        if (jsonData.blacklists) {
                            const listedCount = jsonData.blacklists.filter(bl => bl.listed).length;
                            const totalCount = jsonData.blacklists.length;
                            summary += `<li><strong>Черные списки:</strong> ${listedCount}/${totalCount} обнаружили угрозу</li>`;
                        }
                        
                        if (jsonData.threats) {
                            summary += `<li><strong>Уровень угрозы:</strong> ${jsonData.threats.risk_level} (${jsonData.threats.confidence}%)</li>`;
                        }
                        
                        if (jsonData.distance) {
                            summary += `<li><strong>Расстояние:</strong> ${jsonData.distance.km} км</li>`;
                        }
                        
                        summary += '</ul>';
                        resultContent = summary + resultContent;
                    }
                    
                } catch (e) {
                    resultContent += `<p><strong>⚠️ Ответ не является валидным JSON</strong></p>`;
                }
                
                resultDiv.innerHTML = `<div class="result ${resultClass}">${resultContent}</div>`;
                
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="result error">
                        <h3>❌ Ошибка:</h3>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>