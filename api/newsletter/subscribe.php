<?php
/**
 * StormHosting UA - API подписки на рассылку
 * Файл: /api/newsletter/subscribe.php
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Подключение конфигурации
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Установка заголовков
header('Content-Type: application/json');
header('X-Robots-Tag: noindex');

// Функция для логирования
function logSubscription($email, $success, $error = null) {
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'email' => $email,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'success' => $success,
        'error' => $error
    ];
    
    error_log('Newsletter subscription: ' . json_encode($log_data), 3, '/tmp/newsletter.log');
}

// Функция для отправки email подтверждения
function sendVerificationEmail($email, $token) {
    $to = $email;
    $subject = 'Підтвердження підписки на розсилку StormHosting UA';
    
    $message = "
    <html>
    <head>
        <title>Підтвердження підписки</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { padding: 30px 20px; background: #f8f9fa; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>StormHosting UA</h1>
                <p>Підтвердження підписки на розсилку</p>
            </div>
            
            <div class='content'>
                <h2>Вітаємо!</h2>
                <p>Дякуємо за підписку на розсилку StormHosting UA. Щоб завершити процес підписки, будь ласка, підтвердіть вашу email адресу, натиснувши на кнопку нижче:</p>
                
                <a href='" . SITE_URL . "/api/newsletter/verify.php?token=" . $token . "' class='button'>Підтвердити підписку</a>
                
                <p>Якщо кнопка не працює, скопіюйте та вставте це посилання у ваш браузер:</p>
                <p><a href='" . SITE_URL . "/api/newsletter/verify.php?token=" . $token . "'>" . SITE_URL . "/api/newsletter/verify.php?token=" . $token . "</a></p>
                
                <p>Після підтвердження ви будете отримувати:</p>
                <ul>
                    <li>Останні новини та оновлення</li>
                    <li>Спеціальні пропозиції та знижки</li>
                    <li>Корисні поради з хостингу та веб-розробки</li>
                    <li>Інформацію про нові послуги</li>
                </ul>
                
                <p>Якщо ви не підписувалися на розсилку, просто ігноруйте це повідомлення.</p>
            </div>
            
            <div class='footer'>
                <p>© " . date('Y') . " StormHosting UA. Всі права захищені.</p>
                <p>Україна, м. Дніпро | support@stormhosting.ua</p>
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
    // Проверка метода запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не дозволений', 405);
    }

    // Получение данных
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Некоректний JSON', 400);
    }

    // Валидация email
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Некоректна email адреса', 400);
    }

    // Проверка длины email
    if (strlen($email) > 255) {
        throw new Exception('Email адреса занадто довга', 400);
    }

    // Получение дополнительных данных
    $name = trim($input['name'] ?? '');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Обрезка user_agent до 500 символов
    if (strlen($user_agent) > 500) {
        $user_agent = substr($user_agent, 0, 500);
    }

    // Rate limiting - не более 5 подписок с одного IP в час
    if ($ip_address) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM newsletter_subscribers 
            WHERE ip_address = ? 
            AND subscribed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$ip_address]);
        
        if ($stmt->fetchColumn() >= 5) {
            throw new Exception('Забагато спроб. Спробуйте пізніше.', 429);
        }
    }

    // Проверка, не подписан ли уже email
    $stmt = $pdo->prepare("SELECT id, is_active, is_verified FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if ($existing['is_active'] && $existing['is_verified']) {
            throw new Exception('Ця email адреса вже підписана на розсилку', 409);
        } elseif ($existing['is_active'] && !$existing['is_verified']) {
            throw new Exception('Підписка вже існує. Перевірте вашу пошту для підтвердження.', 409);
        } else {
            // Реактивация подписки
            $verification_token = bin2hex(random_bytes(32));
            
            $stmt = $pdo->prepare("
                UPDATE newsletter_subscribers 
                SET is_active = 1, 
                    verification_token = ?, 
                    is_verified = 0,
                    subscribed_at = NOW(),
                    ip_address = ?,
                    user_agent = ?,
                    name = ?,
                    unsubscribed_at = NULL
                WHERE email = ?
            ");
            
            $stmt->execute([$verification_token, $ip_address, $user_agent, $name, $email]);
            
            // Отправка email подтверждения
            if (sendVerificationEmail($email, $verification_token)) {
                logSubscription($email, true);
                echo json_encode([
                    'success' => true,
                    'message' => 'Підписка оновлена! Перевірте вашу пошту для підтвердження.',
                    'requires_verification' => true
                ]);
            } else {
                throw new Exception('Помилка відправки email підтвердження', 500);
            }
            exit;
        }
    }

    // Создание нового токена подтверждения
    $verification_token = bin2hex(random_bytes(32));

    // Добавление новой подписки
    $stmt = $pdo->prepare("
        INSERT INTO newsletter_subscribers 
        (email, name, ip_address, user_agent, verification_token, is_verified, is_active) 
        VALUES (?, ?, ?, ?, ?, 0, 1)
    ");
    
    if (!$stmt->execute([$email, $name, $ip_address, $user_agent, $verification_token])) {
        throw new Exception('Помилка збереження підписки', 500);
    }

    // Отправка email подтверждения
    if (sendVerificationEmail($email, $verification_token)) {
        logSubscription($email, true);
        
        echo json_encode([
            'success' => true,
            'message' => 'Дякуємо за підписку! Перевірте вашу пошту для підтвердження.',
            'requires_verification' => true
        ]);
    } else {
        // Если email не отправился, удаляем подписку
        $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE email = ? AND verification_token = ?");
        $stmt->execute([$email, $verification_token]);
        
        throw new Exception('Помилка відправки email підтвердження', 500);
    }

} catch (Exception $e) {
    logSubscription($input['email'] ?? 'unknown', false, $e->getMessage());
    
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $code
    ]);
}
?>