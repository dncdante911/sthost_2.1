<?php
/**
 * StormHosting UA - Верификация подписки на рассылку
 * Файл: /api/newsletter/verify.php
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Подключение конфигурации
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Функция для отправки приветственного email
function sendWelcomeEmail($email, $name = '') {
    $to = $email;
    $subject = 'Ласкаво просимо до StormHosting UA!';
    
    $display_name = $name ? $name : 'Друже';
    
    $message = "
    <html>
    <head>
        <title>Ласкаво просимо!</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            .content { padding: 30px 20px; background: #f8f9fa; }
            .feature { background: white; padding: 20px; margin: 15px 0; border-left: 4px solid #667eea; }
            .cta-button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9em; }
            .unsubscribe { font-size: 0.8em; color: #999; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Ласкаво просимо до StormHosting UA!</h1>
                <p>Дякуємо за підписку на нашу розсилку</p>
            </div>
            
            <div class='content'>
                <h2>Привіт, {$display_name}!</h2>
                <p>Вітаємо вас у спільноті StormHosting UA! Ваша підписка успішно підтверджена, і тепер ви будете першими дізнаватися про всі наші новини та спеціальні пропозиції.</p>
                
                <div class='feature'>
                    <h3>🚀 Що вас чекає:</h3>
                    <ul>
                        <li><strong>Ексклюзивні знижки</strong> до 50% на наші послуги</li>
                        <li><strong>Ранній доступ</strong> до нових продуктів та функцій</li>
                        <li><strong>Корисні поради</strong> з веб-розробки та хостингу</li>
                        <li><strong>Технічні гайди</strong> від наших експертів</li>
                        <li><strong>Інсайдерська інформація</strong> про тренди в IT</li>
                    </ul>
                </div>
                
                <div class='feature'>
                    <h3>💡 Рекомендуємо почати з:</h3>
                    <p><strong>Веб-хостинг</strong> - від 99 грн/міс з безкоштовним SSL</p>
                    <p><strong>VPS сервери</strong> - від 299 грн/міс з NVMe SSD</p>
                    <p><strong>Домени .ua</strong> - від 150 грн/рік</p>
                </div>
                
                <div style='text-align: center;'>
                    <a href='" . SITE_URL . "' class='cta-button'>Перейти на сайт</a>
                </div>
                
                <p>Якщо у вас є питання, наша команда підтримки працює 24/7 та завжди готова допомогти!</p>
                
                <p>З найкращими побажаннями,<br>
                <strong>Команда StormHosting UA</strong></p>
            </div>
            
            <div class='footer'>
                <p>© " . date('Y') . " StormHosting UA. Всі права захищені.</p>
                <p>Україна, м. Дніпро | support@stormhosting.ua | +38 (067) 123-45-67</p>
                <p class='unsubscribe'>
                    <a href='" . SITE_URL . "/api/newsletter/unsubscribe.php?email=" . urlencode($email) . "'>Відписатися від розсилки</a>
                </p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: StormHosting UA <noreply@stormhosting.ua>',
        'Reply-To: support@stormhosting.ua',
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($to, $subject, $message, implode("\r\n", $headers));
}

try {
    // Получение токена
    $token = $_GET['token'] ?? '';
    
    if (empty($token) || strlen($token) !== 64) {
        throw new Exception('Некоректний токен верифікації');
    }

    // Поиск подписки по токену
    $stmt = $pdo->prepare("
        SELECT id, email, name, is_verified 
        FROM newsletter_subscribers 
        WHERE verification_token = ? AND is_active = 1
    ");
    $stmt->execute([$token]);
    $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subscriber) {
        throw new Exception('Токен не знайдено або вже використано');
    }

    if ($subscriber['is_verified']) {
        // Уже верифицирован
        $message = 'Ваша підписка вже підтверджена раніше.';
        $status = 'already_verified';
    } else {
        // Верификация подписки
        $stmt = $pdo->prepare("
            UPDATE newsletter_subscribers 
            SET is_verified = 1, verification_token = NULL 
            WHERE id = ?
        ");
        
        if ($stmt->execute([$subscriber['id']])) {
            // Отправка приветственного email
            sendWelcomeEmail($subscriber['email'], $subscriber['name']);
            
            $message = 'Дякуємо! Ваша підписка успішно підтверджена.';
            $status = 'verified';
            
            // Логирование
            error_log("Newsletter verification success: " . $subscriber['email'], 3, '/tmp/newsletter.log');
        } else {
            throw new Exception('Помилка підтвердження підписки');
        }
    }

} catch (Exception $e) {
    $message = $e->getMessage();
    $status = 'error';
    
    // Логирование ошибки
    error_log("Newsletter verification error: " . $e->getMessage(), 3, '/tmp/newsletter.log');
}

// HTML страница результата
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Підтвердження підписки - StormHosting UA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .verification-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .card-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .card-body {
            padding: 2rem;
            text-align: center;
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .status-icon.success { color: #28a745; }
        .status-icon.warning { color: #ffc107; }
        .status-icon.error { color: #dc3545; }
        
        .btn-home {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .footer-links {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 1rem;
            font-size: 0.9rem;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 p-3">
        <div class="verification-card">
            <div class="card-header">
                <h1 class="h3 mb-0">
                    <i class="bi bi-envelope-check"></i>
                    StormHosting UA
                </h1>
                <p class="mb-0 mt-2 opacity-75">Підтвердження підписки</p>
            </div>
            
            <div class="card-body">
                <?php if ($status === 'verified'): ?>
                    <div class="status-icon success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h2 class="h4 text-success mb-3">Успішно підтверджено!</h2>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($message); ?></p>
                    <p class="small text-muted">Ви будете отримувати наші новини та спеціальні пропозиції на вказану email адресу.</p>
                    
                <?php elseif ($status === 'already_verified'): ?>
                    <div class="status-icon warning">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <h2 class="h4 text-warning mb-3">Вже підтверджено</h2>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($message); ?></p>
                    <p class="small text-muted">Ви продовжуєте отримувати наші розсилки.</p>
                    
                <?php else: ?>
                    <div class="status-icon error">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <h2 class="h4 text-danger mb-3">Помилка підтвердження</h2>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($message); ?></p>
                    <p class="small text-muted">Якщо проблема повторюється, зв'яжіться з нашою підтримкою.</p>
                <?php endif; ?>
                
                <a href="<?php echo SITE_URL; ?>" class="btn-home">
                    <i class="bi bi-house"></i>
                    Перейти на головну
                </a>
                
                <div class="footer-links">
                    <a href="<?php echo SITE_URL; ?>/pages/hosting/shared.php">
                        <i class="bi bi-server"></i>
                        Хостинг
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/domains/register.php">
                        <i class="bi bi-globe"></i>
                        Домени
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/info/contacts.php">
                        <i class="bi bi-telephone"></i>
                        Контакти
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Автоматическое перенаправление через 10 секунд для успешной верификации
        <?php if ($status === 'verified'): ?>
        setTimeout(function() {
            window.location.href = '<?php echo SITE_URL; ?>';
        }, 10000);
        <?php endif; ?>
    </script>
</body>
</html>