<?php
// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Получаем код ошибки
$error_code = $_GET['code'] ?? '404';

// Настройки ошибок
$errors = [
    '400' => [
        'title' => 'Неправильный запрос',
        'message' => 'Сервер не может обработать запрос из-за неправильного синтаксиса.',
        'icon' => 'bi-exclamation-triangle'
    ],
    '401' => [
        'title' => 'Не авторизован',
        'message' => 'Для доступа к этой странице требуется авторизация.',
        'icon' => 'bi-lock'
    ],
    '403' => [
        'title' => 'Доступ запрещен',
        'message' => 'У вас нет прав для просмотра этой страницы.',
        'icon' => 'bi-shield-x'
    ],
    '404' => [
        'title' => 'Страница не найдена',
        'message' => 'К сожалению, запрашиваемая страница не существует или была удалена.',
        'icon' => 'bi-file-earmark-x'
    ],
    '500' => [
        'title' => 'Внутренняя ошибка сервера',
        'message' => 'Произошла ошибка на сервере. Пожалуйста, попробуйте позже.',
        'icon' => 'bi-server'
    ],
    '502' => [
        'title' => 'Плохой шлюз',
        'message' => 'Сервер получил недействительный ответ от вышестоящего сервера.',
        'icon' => 'bi-router'
    ],
    '503' => [
        'title' => 'Сервис недоступен',
        'message' => 'Сервер временно недоступен из-за технического обслуживания.',
        'icon' => 'bi-tools'
    ]
];

$current_error = $errors[$error_code] ?? $errors['404'];

// Устанавливаем правильный HTTP код
http_response_code((int)$error_code);

// Мета данные для страницы
$page_title = $current_error['title'] . ' - StormHosting UA';
$meta_description = $current_error['message'];
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .error-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        
        .error-code {
            font-size: 3rem;
            font-weight: 700;
            color: #495057;
            margin-bottom: 1rem;
        }
        
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 1rem;
        }
        
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn-home {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            color: white;
        }
        
        .btn-back {
            background: transparent;
            border: 2px solid #dee2e6;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: #6c757d;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }
        
        .btn-back:hover {
            border-color: #495057;
            color: #495057;
            background: #f8f9fa;
        }
        
        @media (max-width: 576px) {
            .error-card {
                padding: 2rem;
                margin: 1rem;
            }
            
            .error-code {
                font-size: 2.5rem;
            }
            
            .error-title {
                font-size: 1.25rem;
            }
            
            .btn-home, .btn-back {
                display: block;
                text-align: center;
                margin: 0.5rem 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <i class="<?php echo $current_error['icon']; ?> error-icon"></i>
            <div class="error-code"><?php echo $error_code; ?></div>
            <h1 class="error-title"><?php echo htmlspecialchars($current_error['title']); ?></h1>
            <p class="error-message"><?php echo htmlspecialchars($current_error['message']); ?></p>
            
            <div class="error-actions">
                <a href="javascript:history.back()" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                    Назад
                </a>
                <a href="/" class="btn-home">
                    <i class="bi bi-house"></i>
                    На главную
                </a>
            </div>
            
            <?php if ($error_code == '404'): ?>
            <div class="mt-4">
                <p class="small text-muted">
                    Возможно, вы ищете:
                    <a href="/domains" class="text-decoration-none">Домены</a> | 
                    <a href="/hosting" class="text-decoration-none">Хостинг</a> | 
                    <a href="/contacts" class="text-decoration-none">Контакты</a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>