</main>
    
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
        }
        
        body {
    margin: 0;
    padding: 0;
}

/* Скрываем возможные стрелки навигации или debug элементы */
.arrow-up,
.arrow-down,
.scroll-indicator,
.back-to-top,
.floating-arrow {
    display: none !important;
}

/* Убираем возможные margin/padding снизу страницы */
html, body {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
        
        .footer {
            background: #343a40;
            color: white;
            padding: 50px 0 20px;
        }
        
        .footer a {
            color: #adb5bd;
            text-decoration: none;
        }
        
        .footer a:hover {
            color: white;
        }
    </style>
    <!-- Footer -->
   <footer class="footer">
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
                    <li><a href="/hosting">Хостинг</a></li>
                    <li><a href="/vds">VDS/VPS</a></li>
                    <li><a href="/domains">Домени</a></li>
                    <li><a href="#">SSL сертифікати</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Підтримка</h6>
                <ul class="list-unstyled">
                    <li><a href="#">FAQ</a></li>
                    <li><a href="/contacts">Контакти</a></li>
                    <li><a href="#">Документація</a></li>
                    <li><a href="#">Статус серверів</a></li>
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
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> StormHosting UA. Всі права захищені.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">Розроблено з ❤️ в Україні</small>
            </div>
        </div>
    </div>
</footer>
    
    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top" aria-label="<?php echo t('back_to_top'); ?>">
        <i class="bi bi-arrow-up"></i>
    </button>
    
    <!-- Cookie Notice -->
    <div id="cookie-notice" class="cookie-notice" style="display: none;">
        <div class="container">
            <div class="cookie-content">
                <p><?php echo t('cookie_notice_text'); ?></p>
                <div class="cookie-buttons">
                    <button id="accept-cookies" class="btn btn-primary btn-sm"><?php echo t('cookie_accept'); ?></button>
                    <button id="decline-cookies" class="btn btn-outline-secondary btn-sm"><?php echo t('cookie_decline'); ?></button>
                    <a href="/info/privacy" class="btn btn-link btn-sm"><?php echo t('cookie_learn_more'); ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js?v=<?php echo filemtime('assets/js/main.js'); ?>"></script>
    <script src="/assets/js/animations.js?v=<?php echo filemtime('assets/js/animations.js'); ?>"></script>
    
    <?php if (isset($page_js) && !empty($page_js)): ?>
        <script src="/assets/js/pages/<?php echo $page_js; ?>.js?v=<?php echo filemtime("assets/js/pages/{$page_js}.js"); ?>"></script>
    <?php endif; ?>
    
    <!-- Дополнительные скрипты для калькуляторов -->
    <?php if (in_array($page, ['hosting', 'vds']) || (isset($need_calculator) && $need_calculator)): ?>
        <script src="/assets/js/calculators.js?v=<?php echo filemtime('assets/js/calculators.js'); ?>"></script>
    <?php endif; ?>
    
    <!-- API скрипты для инструментов -->
    <?php if ($page === 'tools' || (isset($need_api) && $need_api)): ?>
        <script src="/assets/js/api.js?v=<?php echo filemtime('assets/js/api.js'); ?>"></script>
    <?php endif; ?>
    
    <!-- Google Analytics (замените на ваш ID) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID');
    </script>
    
    <!-- Inline скрипты -->
    <script>
        // CSRF токен для AJAX запросов
        window.csrfToken = '<?php echo generateCSRFToken(); ?>';
        
        // Конфигурация для скриптов
        window.siteConfig = {
            lang: '<?php echo $current_lang; ?>',
            baseUrl: '<?php echo SITE_URL; ?>',
            recaptchaSiteKey: '<?php echo defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : ''; ?>'
        };
        
        // Переводы для JavaScript
        window.translations = {
            loading: '<?php echo t('loading'); ?>',
            error: '<?php echo t('error'); ?>',
            success: '<?php echo t('success'); ?>',
            confirm: '<?php echo t('confirm'); ?>',
            cancel: '<?php echo t('cancel'); ?>',
            close: '<?php echo t('btn_close'); ?>',
            domain_available: '<?php echo t('domain_available'); ?>',
            domain_unavailable: '<?php echo t('domain_unavailable'); ?>',
            site_online: '<?php echo t('tools_site_online'); ?>',
            site_offline: '<?php echo t('tools_site_offline'); ?>',
            form_required: '<?php echo t('form_required'); ?>',
            form_invalid_email: '<?php echo t('form_invalid_email'); ?>',
            error_csrf_token: '<?php echo t('error_csrf_token'); ?>'
        };
    </script>
    
    <!-- Структурированные данные для локального бизнеса -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "StormHosting UA",
        "image": "<?php echo SITE_URL; ?>/assets/images/logo.png",
        "description": "<?php echo t('site_slogan'); ?>",
        "url": "<?php echo SITE_URL; ?>",
        "telephone": "+380-XX-XXX-XX-XX",
        "email": "<?php echo SITE_EMAIL; ?>",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "UA",
            "addressRegion": "Дніпропетровська область",
            "addressLocality": "Дніпро"
        },
        "openingHours": "Mo-Su 00:00-23:59",
        "sameAs": [
            "https://t.me/stormhosting_ua",
            "https://facebook.com/stormhosting.ua"
        ],
        "offers": {
            "@type": "AggregateOffer",
            "priceCurrency": "UAH",
            "lowPrice": "99",
            "highPrice": "2999",
            "description": "Послуги хостингу та реєстрації доменів"
        }
    }
    </script>
    
    <!-- Дополнительные мета-теги для поисковых систем -->
    <?php if ($page === 'home' || $page === ''): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "StormHosting UA",
        "url": "<?php echo SITE_URL; ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo SITE_URL; ?>/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <?php endif; ?>
    

<!-- Простой чат - добавьте в footer.php -->
<style>
.simple-chat-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    border: none;
    color: white;
    cursor: pointer;
    z-index: 9999;
    font-size: 24px;
}
.simple-chat-window {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    display: none;
    flex-direction: column;
    z-index: 9999;
}
.chat-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 20px;
    border-radius: 20px 20px 0 0;
    text-align: center;
}
.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8fafc;
}
.chat-input {
    padding: 20px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}
.chat-input input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 20px;
}
.chat-input button {
    background: #667eea;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
}
.message {
    margin-bottom: 15px;
    padding: 10px 15px;
    border-radius: 15px;
    max-width: 80%;
}
.user-msg {
    background: #667eea;
    color: white;
    margin-left: auto;
}
.bot-msg {
    background: white;
    border: 1px solid #eee;
}
</style>

<button class="simple-chat-btn" onclick="toggleSimpleChat()" id="chatBtn">💬</button>

<div class="simple-chat-window" id="chatWindow">
    <div class="chat-header">
        <h4>Техподдержка StormHosting</h4>
        <button onclick="toggleSimpleChat()" style="background: none; border: none; color: white; float: right; cursor: pointer;">×</button>
    </div>
    <div class="chat-messages" id="chatMessages">
        <div class="message bot-msg">Привет! Чем могу помочь?</div>
    </div>
    <div class="chat-input">
        <input type="text" id="chatInput" placeholder="Напишите сообщение..." onkeypress="if(event.key==='Enter') sendSimpleMessage()">
        <button onclick="sendSimpleMessage()">Отправить</button>
    </div>
</div>



<!-- ОНОВЛЕНИЙ КЛІЄНТСЬКИЙ ЧАТ ДЛЯ FOOTER.PHP -->

<style>
:root {
    --chat-primary: linear-gradient(135deg, #667eea, #764ba2);
    --chat-primary-color: #667eea;
    --chat-success: #22c55e;
    --chat-text: #1e293b;
    --chat-text-light: #64748b;
    --chat-bg: #ffffff;
    --chat-bg-light: #f8fafc;
    --chat-border: #e2e8f0;
    --chat-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.stormchat-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.stormchat-toggle {
    width: 64px;
    height: 64px;
    background: var(--chat-primary);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: var(--chat-shadow);
    transition: all 0.3s ease;
    position: relative;
}

.stormchat-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 15px 35px rgba(102,126,234,0.4);
}

.stormchat-toggle.has-messages::after {
    content: '';
    position: absolute;
    top: 5px;
    right: 5px;
    width: 12px;
    height: 12px;
    background: #ef4444;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.stormchat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 600px;
    background: var(--chat-bg);
    border-radius: 20px;
    box-shadow: var(--chat-shadow);
    display: none;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(20px) scale(0.9);
    opacity: 0;
    transition: all 0.3s ease;
}

.stormchat-window.active {
    display: flex;
    transform: translateY(0) scale(1);
    opacity: 1;
}

.stormchat-header {
    background: var(--chat-primary);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stormchat-header-info h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.stormchat-status {
    display: flex;
    align-items: center;
    font-size: 12px;
    opacity: 0.9;
    margin-top: 4px;
}

.stormchat-status-dot {
    width: 8px;
    height: 8px;
    background: var(--chat-success);
    border-radius: 50%;
    margin-right: 6px;
    animation: blink 2s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.3; }
}

.stormchat-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.3s;
}

.stormchat-close:hover {
    opacity: 1;
}

.stormchat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: var(--chat-bg-light);
}

.stormchat-message {
    margin-bottom: 16px;
    display: flex;
    align-items: flex-end;
}

.stormchat-message.user {
    flex-direction: row-reverse;
}

.stormchat-message-content {
    max-width: 80%;
    padding: 12px 16px;
    border-radius: 18px;
    position: relative;
    word-wrap: break-word;
}

.stormchat-message.bot .stormchat-message-content,
.stormchat-message.operator .stormchat-message-content {
    background: white;
    color: var(--chat-text);
    border-bottom-left-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stormchat-message.user .stormchat-message-content {
    background: var(--chat-primary-color);
    color: white;
    border-bottom-right-radius: 6px;
}

.stormchat-message.system .stormchat-message-content {
    background: #f3f4f6;
    color: var(--chat-text-light);
    font-style: italic;
    text-align: center;
    border-radius: 12px;
    font-size: 12px;
    margin: 0 auto;
}

.stormchat-message-sender {
    font-size: 11px;
    color: var(--chat-text-light);
    margin-bottom: 4px;
    padding: 0 8px;
}

.stormchat-message-time {
    font-size: 11px;
    color: var(--chat-text-light);
    margin: 4px 8px 0;
}

.stormchat-typing {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: white;
    border-radius: 18px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stormchat-typing-dots {
    display: flex;
    gap: 4px;
}

.stormchat-typing-dot {
    width: 6px;
    height: 6px;
    background: var(--chat-text-light);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.stormchat-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.stormchat-typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.stormchat-input-area {
    padding: 20px;
    background: white;
    border-top: 1px solid var(--chat-border);
    display: flex;
    gap: 12px;
    align-items: center;
}

.stormchat-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid var(--chat-border);
    border-radius: 20px;
    outline: none;
    font-size: 14px;
    transition: border-color 0.3s;
    resize: none;
    min-height: 20px;
    max-height: 80px;
    overflow-y: auto;
}

.stormchat-input:focus {
    border-color: var(--chat-primary-color);
}

.stormchat-send {
    width: 40px;
    height: 40px;
    background: var(--chat-primary-color);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.stormchat-send:hover {
    background: #5a6fd8;
    transform: scale(1.1);
}

.stormchat-send:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    transform: none;
}

.stormchat-quick-actions {
    padding: 16px 20px;
    background: white;
    border-top: 1px solid var(--chat-border);
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.stormchat-quick-btn {
    padding: 8px 12px;
    background: var(--chat-bg-light);
    border: 1px solid var(--chat-border);
    border-radius: 16px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s;
    color: var(--chat-text-light);
}

.stormchat-quick-btn:hover {
    background: var(--chat-primary-color);
    color: white;
    border-color: var(--chat-primary-color);
}

.stormchat-connection-status {
    position: absolute;
    top: 10px;
    right: 50px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--chat-success);
}

.stormchat-connection-status.disconnected {
    background: #ef4444;
}

.stormchat-quick-replies {
    margin-bottom: 1rem;
}

.quick-reply-btn {
    width: 100%;
    padding: 0.75rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: left;
    margin-bottom: 0.5rem;
    font-family: inherit;
}

.quick-reply-btn:hover {
    background: linear-gradient(135deg, #5a6fd8, #6a42a0);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.quick-reply-btn:active {
    transform: translateY(0);
}

/* Улучшенные системные сообщения */
.stormchat-message.system .stormchat-message-content {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    color: #0c4a6e;
    border: 1px solid #0ea5e9;
    border-radius: 12px;
    font-size: 13px;
    text-align: center;
    margin: 0 auto;
    max-width: 90%;
}

/* Анимация для новых сообщений */
.stormchat-message {
    animation: slideInMessage 0.3s ease-out;
}

@keyframes slideInMessage {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Дополнительные стили для файлов в чате */
.stormchat-file-input {
    display: none;
}

.stormchat-file-btn {
    width: 40px;
    height: 40px;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 50%;
    color: #6b7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    font-size: 1.2rem;
}

.stormchat-file-btn:hover {
    background: #e5e7eb;
    color: #374151;
}

.stormchat-file-message {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin: 0.5rem 0;
}

.stormchat-file-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.stormchat-file-info {
    flex: 1;
}

.stormchat-file-name {
    font-weight: 500;
    color: #1e293b;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stormchat-file-size {
    font-size: 0.8rem;
    color: #64748b;
}

.stormchat-file-download {
    padding: 0.5rem 1rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
    text-decoration: none;
    display: inline-block;
}

.stormchat-file-download:hover {
    background: #5a6fd8;
}

.stormchat-upload-progress {
    width: 100%;
    height: 4px;
    background: #f3f4f6;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.stormchat-upload-progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    width: 0%;
    transition: width 0.3s;
}

/* Улучшенный индикатор набора текста */
.stormchat-typing {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px solid #e2e8f0;
}

/* Стили для автоматических ответов */
.stormchat-message.auto-reply .stormchat-message-content {
    background: linear-gradient(135deg, #ecfdf5, #dcfce7);
    color: #166534;
    border-left: 4px solid #22c55e;
}

/* Подсветка важных системных сообщений */
.stormchat-message.system.important .stormchat-message-content {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
    border: 1px solid #f59e0b;
    font-weight: 500;
}

/* Responsive для мобильных */
@media (max-width: 480px) {
    .quick-reply-btn {
        font-size: 0.85rem;
        padding: 0.6rem;
    }
    
    .stormchat-message.system .stormchat-message-content {
        font-size: 12px;
    }
}
</style>

<!-- HTML структура -->
<div class="stormchat-container">
    <button class="stormchat-toggle" onclick="StormChat.toggle()" id="stormchatBtn">
        💬
    </button>

    <div class="stormchat-window" id="stormchatWindow">
        <div class="stormchat-header">
            <div class="stormchat-header-info">
                <h4 id="stormchatTitle">Техпідтримка StormHosting</h4>
                <div class="stormchat-status">
                    <span class="stormchat-status-dot"></span>
                    <span id="stormchatStatusText">Підключення...</span>
                </div>
            </div>
            <div class="stormchat-connection-status" id="stormchatConnection"></div>
            <button class="stormchat-close" onclick="StormChat.toggle()">×</button>
        </div>

        <div class="stormchat-messages" id="stormchatMessages">
            <div class="stormchat-message system">
                <div class="stormchat-message-content">
                    Ініціалізація чату...
                </div>
            </div>
        </div>

        <div class="stormchat-quick-actions" id="stormchatQuickActions" style="display: none;">
            <button class="stormchat-quick-btn" onclick="StormChat.sendQuickMessage('У мене проблеми з хостингом')">🛠 Проблеми з хостингом</button>
            <button class="stormchat-quick-btn" onclick="StormChat.sendQuickMessage('Питання по домену')">🌐 Питання по домену</button>
            <button class="stormchat-quick-btn" onclick="StormChat.sendQuickMessage('SSL сертифікат')">🔒 SSL сертифікат</button>
            <button class="stormchat-quick-btn" onclick="StormChat.sendQuickMessage('Зв\'язатися з оператором')">👤 Оператор</button>
        </div>

        <div class="stormchat-input-area">
            <textarea class="stormchat-input" id="stormchatInput" placeholder="Напишіть повідомлення..." 
                      rows="1" onkeypress="StormChat.handleKeyPress(event)" 
                      oninput="StormChat.autoResize(this)"></textarea>
            <button class="stormchat-send" onclick="StormChat.sendMessage()" id="stormchatSendBtn">
                ➤
            </button>
        </div>
    </div>
</div>

<script>
// ПОЛНАЯ СИСТЕМА ОЧИСТКИ ЧАТА
// Замените весь JavaScript блок в footer.php

class StormChatWidget {
    constructor() {
        this.session = null;
        this.messages = [];
        this.isConnected = false;
        this.lastMessageId = 0;
        this.pollInterval = null;
        this.isTyping = false;
        this.operatorInfo = null;
        this.isFirstLoad = true;
        this.isWindowClosing = false;
        this.lastSessionCheck = null;
        
        this.init();
    }
    
    init() {
        this.updateConnectionStatus(false);
        this.checkAndCleanChat();
        this.loadSession();
        this.startPolling();
        this.setupPageCloseHandlers();
        this.setupVisibilityHandler();
        this.setupFileUpload();
    }
    
    setupFileUpload() {
        // Создаем input для файлов
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.id = 'stormchatFileInput';
        fileInput.className = 'stormchat-file-input';
        fileInput.accept = 'image/*,.pdf,.doc,.docx,.txt,.zip,.rar';
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFileUpload(e.target.files[0]);
            }
        });
        
        document.body.appendChild(fileInput);
        
        // Обновляем область ввода, добавляя кнопку файла
        this.updateInputArea();
    }
    
    updateInputArea() {
        const inputArea = document.querySelector('.stormchat-input-area');
        if (inputArea) {
            inputArea.innerHTML = `
                <button class="stormchat-file-btn" onclick="StormChat.openFileDialog()" title="Прикріпити файл">
                    📎
                </button>
                <textarea class="stormchat-input" id="stormchatInput" placeholder="Напишіть повідомлення..." 
                          rows="1" onkeypress="StormChat.handleKeyPress(event)" 
                          oninput="StormChat.autoResize(this)"></textarea>
                <button class="stormchat-send" onclick="StormChat.sendMessage()" id="stormchatSendBtn">
                    ➤
                </button>
            `;
        }
    }
    
    openFileDialog() {
        document.getElementById('stormchatFileInput').click();
    }
    
    async handleFileUpload(file) {
        // Проверяем размер файла (макс 5MB для чата)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            this.showMessage('❌ Файл занадто великий. Максимальний розмір: 5MB', 'system');
            return;
        }
        
        // Проверяем тип файла
        const allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'text/plain',
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', 'application/x-rar-compressed'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            this.showMessage('❌ Непідтримуваний тип файлу', 'system');
            return;
        }
        
        // Создаем сессию если нет
        if (!this.session) {
            await this.createSession();
            if (!this.session) return;
        }
        
        try {
            // Показываем прогресс загрузки
            const progressMessage = this.showFileUploadProgress(file.name);
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'upload');
            formData.append('session_id', this.session.id);
            
            const xhr = new XMLHttpRequest();
            
            // Отслеживание прогресса
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const progress = (e.loaded / e.total) * 100;
                    this.updateFileUploadProgress(progressMessage, progress);
                }
            });
            
            // Обработка завершения
            xhr.addEventListener('load', () => {
                const result = JSON.parse(xhr.responseText);
                
                if (result.success) {
                    // Удаляем сообщение о прогрессе
                    progressMessage.remove();
                    
                    // Отправляем сообщение с файлом
                    this.sendFileMessage(result.data);
                } else {
                    this.updateFileUploadProgress(progressMessage, 100, true, result.message);
                }
            });
            
            xhr.addEventListener('error', () => {
                this.updateFileUploadProgress(progressMessage, 100, true, 'Помилка завантаження');
            });
            
            xhr.open('POST', '/api/chat/files.php');
            xhr.send(formData);
            
        } catch (error) {
            console.error('File upload error:', error);
            this.showMessage('❌ Помилка завантаження файлу', 'system');
        }
    }
    
    showFileUploadProgress(fileName) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'stormchat-message user';
        messageDiv.innerHTML = `
            <div class="stormchat-message-content">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span>📤</span>
                    <span>Завантаження: ${fileName}</span>
                </div>
                <div class="stormchat-upload-progress">
                    <div class="stormchat-upload-progress-bar"></div>
                </div>
                <div class="upload-status" style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem;">
                    Підготовка...
                </div>
            </div>
        `;
        
        document.getElementById('stormchatMessages').appendChild(messageDiv);
        this.scrollToBottom();
        
        return messageDiv;
    }
    
    updateFileUploadProgress(messageDiv, progress, isError = false, errorMessage = '') {
        const progressBar = messageDiv.querySelector('.stormchat-upload-progress-bar');
        const status = messageDiv.querySelector('.upload-status');
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
        
        if (status) {
            if (isError) {
                status.textContent = '❌ ' + errorMessage;
                status.style.color = '#ef4444';
            } else if (progress === 100) {
                status.textContent = '✅ Завантажено';
                status.style.color = '#22c55e';
            } else {
                status.textContent = `Завантаження... ${Math.round(progress)}%`;
            }
        }
    }
    
    async sendFileMessage(fileData) {
        try {
            const response = await fetch('/api/chat/messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: this.session.id,
                    message: `[FILE:${fileData.id}:${fileData.original_name}:${fileData.file_url}]`,
                    sender_type: 'user',
                    message_type: 'file'
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.addMessageToUI(result.data);
                this.lastMessageId = Math.max(this.lastMessageId, result.data.id);
                this.scrollToBottom();
                this.updateLastActivity();
            } else {
                this.showMessage('Помилка відправки файлу: ' + result.message, 'system');
            }
        } catch (error) {
            console.error('Send file message error:', error);
            this.showMessage('Помилка відправки файлу', 'system');
        }
    }
    
    formatMessage(message) {
        // Обработка файловых сообщений
        if (message.startsWith('[FILE:')) {
            const fileMatch = message.match(/\[FILE:(\d+):(.+?):(.+?)\]/);
            if (fileMatch) {
                const [, fileId, fileName, fileUrl] = fileMatch;
                return this.formatFileMessage(fileName, fileUrl, fileId);
            }
        }
        
        // Базовое форматирование
        message = message.replace(/\n/g, '<br>');
        
        // Добавляем эмодзи поддержку
        const emojiMap = {
            ':)': '😊',
            ':D': '😃',
            ':(': '😞',
            ':P': '😛',
            ';)': '😉'
        };
        
        Object.keys(emojiMap).forEach(emoji => {
            message = message.replace(new RegExp(emoji.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), emojiMap[emoji]);
        });
        
        return message;
    }
    
    formatFileMessage(fileName, fileUrl, fileId) {
        const fileExtension = fileName.split('.').pop().toLowerCase();
        let fileIcon = '📄';
        
        // Определяем иконку по расширению
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
            fileIcon = '🖼️';
        } else if (['pdf'].includes(fileExtension)) {
            fileIcon = '📄';
        } else if (['doc', 'docx'].includes(fileExtension)) {
            fileIcon = '📘';
        } else if (['zip', 'rar'].includes(fileExtension)) {
            fileIcon = '📦';
        } else if (['txt'].includes(fileExtension)) {
            fileIcon = '📝';
        }
        
        return `
            <div class="stormchat-file-message">
                <div class="stormchat-file-icon">${fileIcon}</div>
                <div class="stormchat-file-info">
                    <div class="stormchat-file-name">${fileName}</div>
                    <div class="stormchat-file-size">Натисніть для завантаження</div>
                </div>
                <a href="${fileUrl}" target="_blank" class="stormchat-file-download">
                    📥 Завантажити
                </a>
            </div>
        `;
    }
    
    // Все остальные методы остаются без изменений...
    // (Добавьте все методы из предыдущей версии)
    
    checkAndCleanChat() {
        const shouldClean = this.shouldCleanChat();
        
        if (shouldClean) {
            this.performFullCleanup();
        }
        
        this.clearMessages();
        this.resetState();
    }
    
    shouldCleanChat() {
        if (!sessionStorage.getItem('chat_active')) {
            return true;
        }
        
        const lastActivity = localStorage.getItem('chat_last_activity');
        if (lastActivity) {
            const timeDiff = Date.now() - parseInt(lastActivity);
            if (timeDiff > 30 * 60 * 1000) {
                return true;
            }
        }
        
        if (performance.navigation.type === performance.navigation.TYPE_RELOAD) {
            return true;
        }
        
        if (document.hidden && this.wasHiddenLongTime()) {
            return true;
        }
        
        return false;
    }
    
    wasHiddenLongTime() {
        const hiddenTime = localStorage.getItem('chat_hidden_time');
        if (hiddenTime) {
            const timeDiff = Date.now() - parseInt(hiddenTime);
            return timeDiff > 10 * 60 * 1000;
        }
        return false;
    }
    
    setupPageCloseHandlers() {
        window.addEventListener('beforeunload', (e) => {
            this.isWindowClosing = true;
            this.handlePageClose();
        });
        
        window.addEventListener('unload', () => {
            this.handlePageClose();
        });
        
        window.addEventListener('pagehide', () => {
            this.handlePageClose();
        });
        
        window.addEventListener('blur', () => {
            localStorage.setItem('chat_hidden_time', Date.now().toString());
        });
        
        window.addEventListener('focus', () => {
            localStorage.removeItem('chat_hidden_time');
            this.updateLastActivity();
        });
    }
    
    setupVisibilityHandler() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                localStorage.setItem('chat_hidden_time', Date.now().toString());
                this.pausePolling();
            } else {
                const hiddenTime = localStorage.getItem('chat_hidden_time');
                if (hiddenTime) {
                    const timeDiff = Date.now() - parseInt(hiddenTime);
                    if (timeDiff > 10 * 60 * 1000) {
                        this.performFullCleanup();
                        this.showWelcomeMessage();
                    }
                }
                localStorage.removeItem('chat_hidden_time');
                this.resumePolling();
                this.updateLastActivity();
            }
        });
    }
    
    handlePageClose() {
        if (this.session) {
            this.sendCloseSignal();
        }
        this.clearAllData();
    }
    
    sendCloseSignal() {
        const data = JSON.stringify({
            action: 'user_disconnect',
            session_id: this.session.id,
            session_key: this.session.session_key
        });
        
        try {
            if (navigator.sendBeacon) {
                navigator.sendBeacon('/api/chat/session.php', data);
            } else {
                fetch('/api/chat/session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: data,
                    keepalive: true
                });
            }
        } catch (error) {
            console.log('Close signal error:', error);
        }
    }
    
    performFullCleanup() {
        this.clearAllData();
        this.resetState();
        this.clearMessages();
        
        if (this.session) {
            this.resetServerSession();
        }
    }
    
    clearAllData() {
        localStorage.removeItem('chat_session_key');
        localStorage.removeItem('chat_last_activity');
        localStorage.removeItem('chat_hidden_time');
        sessionStorage.removeItem('chat_active');
        sessionStorage.removeItem('chat_session_key');
        
        this.session = null;
        this.lastMessageId = 0;
        this.messages = [];
    }
    
    resetState() {
        this.session = null;
        this.messages = [];
        this.lastMessageId = 0;
        this.isFirstLoad = true;
        this.lastSessionCheck = null;
    }
    
    clearMessages() {
        const messagesContainer = document.getElementById('stormchatMessages');
        if (messagesContainer) {
            messagesContainer.innerHTML = '';
        }
    }
    
    updateLastActivity() {
        localStorage.setItem('chat_last_activity', Date.now().toString());
        sessionStorage.setItem('chat_active', 'true');
    }
    
    pausePolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
    
    resumePolling() {
        if (!this.pollInterval && this.session && this.isConnected) {
            this.startPolling();
        }
    }
    
    async resetServerSession() {
        try {
            await fetch('/api/chat/session.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'reset_session',
                    session_key: this.session?.session_key
                })
            });
        } catch (error) {
            console.log('Reset session error:', error);
        }
    }
    
    async loadSession() {
        try {
            const response = await fetch('/api/chat/session.php', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (result.data.status === 'closed') {
                    this.handleSessionClosed();
                    return;
                }
                
                this.session = result.data;
                this.updateUI();
                
                if (!this.isFirstLoad) {
                    this.loadMessages();
                } else {
                    this.isFirstLoad = false;
                    this.showWelcomeMessage();
                }
                
                this.updateLastActivity();
            } else {
                this.showQuickActions();
                this.updateStatus('Натисніть кнопку нижче для початку чату');
                this.showWelcomeMessage();
            }
            
            this.updateConnectionStatus(true);
        } catch (error) {
            console.error('Session load error:', error);
            this.updateConnectionStatus(false);
            this.updateStatus('Помилка підключення. Спробуйте пізніше.');
            this.showWelcomeMessage();
        }
    }
    
    handleSessionClosed() {
        this.showMessage('💬 Чат було закрито оператором', 'system');
        this.showMessage('Дякуємо за звернення! Ви можете створити новий чат у будь-який час.', 'system');
        
        setTimeout(() => {
            this.performFullCleanup();
            this.showWelcomeMessage();
            this.showQuickActions();
        }, 3000);
    }
    
    showWelcomeMessage() {
        this.clearMessages();
        this.showMessage('Вітаємо у техпідтримці StormHosting! 👋', 'system');
        this.showMessage('Як ми можемо вам допомогти сьогодні?', 'system');
    }
    
    async createSession(subject = 'Загальне питання') {
        try {
            this.clearMessages();
            
            const guestData = this.getGuestData();
            
            const response = await fetch('/api/chat/session.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    subject: subject,
                    guest_name: guestData.name,
                    guest_email: guestData.email,
                    priority: 'normal'
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.session = result.data;
                this.lastMessageId = 0;
                this.updateUI();
                this.hideQuickActions();
                this.showMessage('Чат створено! Очікуйте підключення оператора...', 'system');
                
                localStorage.setItem('chat_session_key', this.session.session_key);
                sessionStorage.setItem('chat_session_key', this.session.session_key);
                this.updateLastActivity();
                
                this.showQuickReplies();
            } else {
                this.showMessage('Помилка створення чату: ' + result.message, 'system');
            }
        } catch (error) {
            console.error('Session creation error:', error);
            this.showMessage('Помилка створення чату. Спробуйте пізніше.', 'system');
        }
    }
    
    showQuickReplies() {
        setTimeout(() => {
            if (this.session && this.session.status === 'waiting') {
                this.showMessage('Ви можете вибрати одну з популярних тем або написати своє питання:', 'system');
                this.addQuickReplyButtons();
            }
        }, 2000);
    }
    
    addQuickReplyButtons() {
        const messagesContainer = document.getElementById('stormchatMessages');
        const quickRepliesDiv = document.createElement('div');
        quickRepliesDiv.className = 'stormchat-quick-replies';
        quickRepliesDiv.innerHTML = `
            <div class="stormchat-message system">
                <div class="stormchat-message-content">
                    <div style="margin-bottom: 0.75rem; font-weight: 500;">Оберіть тему або напишіть своє питання:</div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <button class="quick-reply-btn" onclick="StormChat.sendQuickMessage('У мене проблеми з хостингом')">
                            🛠 Проблеми з хостингом
                        </button>
                        <button class="quick-reply-btn" onclick="StormChat.sendQuickMessage('Питання по домену')">
                            🌐 Питання по домену
                        </button>
                        <button class="quick-reply-btn" onclick="StormChat.sendQuickMessage('Потрібен SSL сертифікат')">
                            🔒 SSL сертифікат
                        </button>
                        <button class="quick-reply-btn" onclick="StormChat.sendQuickMessage('Зв\\'язатися з оператором')">
                            👤 Зв'язатися з оператором
                        </button>
                        <button class="quick-reply-btn" onclick="StormChat.sendQuickMessage('Інше питання')">
                            💬 Інше питання
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        messagesContainer.appendChild(quickRepliesDiv);
        this.scrollToBottom();
        this.addQuickReplyStyles();
    }
    
    addQuickReplyStyles() {
        if (!document.getElementById('quickReplyStyles')) {
            const style = document.createElement('style');
            style.id = 'quickReplyStyles';
            style.textContent = `
                .quick-reply-btn {
                    width: 100%;
                    padding: 0.75rem;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 0.9rem;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-align: left;
                }
                
                .quick-reply-btn:hover {
                    background: linear-gradient(135deg, #5a6fd8, #6a42a0);
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(102,126,234,0.3);
                }
                
                .stormchat-quick-replies {
                    margin-bottom: 1rem;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    async sendQuickMessage(message) {
        const quickReplies = document.querySelector('.stormchat-quick-replies');
        if (quickReplies) {
            quickReplies.remove();
        }
        
        await this.sendMessage(message);
        this.showAutomaticReply(message);
    }
    
    showAutomaticReply(userMessage) {
        setTimeout(() => {
            let autoReply = '';
            
            if (userMessage.includes('хостинг')) {
                autoReply = 'Дякуємо за звернення! Оператор розгляне ваше питання щодо хостингу. Опишіть, будь ласка, детальніше проблему.';
            } else if (userMessage.includes('домен')) {
                autoReply = 'Питання по доменах - це наша спеціальність! Оператор незабаром підключиться для допомоги з доменом.';
            } else if (userMessage.includes('SSL')) {
                autoReply = 'SSL сертифікати - важлива частина безпеки сайту. Оператор допоможе вам з налаштуванням SSL.';
            } else if (userMessage.includes('оператор')) {
                autoReply = 'Зв\'язуємо вас з оператором! Зазвичай це займає 1-3 хвилини в робочий час.';
            } else {
                autoReply = 'Дякуємо за ваше звернення! Оператор незабаром підключиться для вирішення вашого питання.';
            }
            
            this.showMessage(autoReply, 'system');
            
            setTimeout(() => {
                this.showMessage('⏳ Шукаємо вільного оператора...', 'system');
            }, 5000);
            
        }, 1500);
    }
    
    async loadMessages() {
        if (!this.session) return;
        
        try {
            const url = `/api/chat/messages.php?session_id=${this.session.id}&last_message_id=${this.lastMessageId}`;
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success && result.data.messages) {
                const newMessages = result.data.messages;
                
                if (newMessages.length > 0) {
                    newMessages.forEach(message => {
                        this.addMessageToUI(message);
                        this.lastMessageId = Math.max(this.lastMessageId, message.id);
                    });
                    
                    this.scrollToBottom();
                    this.updateUnreadIndicator();
                }
                
                if (result.data.session) {
                    if (result.data.session.status === 'closed' && this.session.status !== 'closed') {
                        this.handleSessionClosed();
                        return;
                    }
                    this.session = result.data.session;
                    this.updateUI();
                }
            }
        } catch (error) {
            console.error('Messages load error:', error);
        }
    }
    
    async sendMessage(message = null) {
        if (!message) {
            message = document.getElementById('stormchatInput').value.trim();
        }
        
        if (!message) return;
        
        if (!this.session) {
            await this.createSession();
            if (!this.session) return;
        }
        
        try {
            this.disableSending(true);
            document.getElementById('stormchatInput').value = '';
            this.autoResize(document.getElementById('stormchatInput'));
            
            const response = await fetch('/api/chat/messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: this.session.id,
                    message: message,
                    sender_type: 'user'
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.addMessageToUI(result.data);
                this.lastMessageId = Math.max(this.lastMessageId, result.data.id);
                this.scrollToBottom();
                this.updateLastActivity();
            } else {
                this.showMessage('Помилка відправки: ' + result.message, 'system');
                document.getElementById('stormchatInput').value = message;
            }
        } catch (error) {
            console.error('Send message error:', error);
            this.showMessage('Помилка відправки повідомлення', 'system');
            document.getElementById('stormchatInput').value = message;
        } finally {
            this.disableSending(false);
        }
    }
    
    addMessageToUI(message) {
        const messagesContainer = document.getElementById('stormchatMessages');
        const messageDiv = document.createElement('div');
        
        let senderClass = message.sender_type;
        if (message.sender_type === 'operator') senderClass = 'operator';
        if (message.message_type === 'system') senderClass = 'system';
        
        messageDiv.className = `stormchat-message ${senderClass}`;
        
        let senderName = '';
        if (message.sender_type === 'operator' && message.sender_name) {
            senderName = `<div class="stormchat-message-sender">${message.sender_name}</div>`;
        }
        
        const time = new Date(message.created_at).toLocaleTimeString('uk-UA', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        messageDiv.innerHTML = `
            ${senderName}
            <div class="stormchat-message-content">${this.formatMessage(message.message)}</div>
            <div class="stormchat-message-time">${time}</div>
        `;
        
        messagesContainer.appendChild(messageDiv);
    }
    
    showMessage(text, type = 'system') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `stormchat-message ${type}`;
        messageDiv.innerHTML = `
            <div class="stormchat-message-content">${text}</div>
            <div class="stormchat-message-time">${new Date().toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'})}</div>
        `;
        
        document.getElementById('stormchatMessages').appendChild(messageDiv);
        this.scrollToBottom();
    }
    
    updateUI() {
        if (!this.session) return;
        
        const title = document.getElementById('stormchatTitle');
        const status = document.getElementById('stormchatStatusText');
        
        if (this.session.operator_name) {
            title.textContent = `Чат з ${this.session.operator_name}`;
            status.innerHTML = '<span class="stormchat-status-dot"></span>Оператор онлайн';
        } else {
            title.textContent = 'Техпідтримка StormHosting';
            status.innerHTML = '<span class="stormchat-status-dot"></span>Очікування оператора...';
        }
    }
    
    updateStatus(text) {
        document.getElementById('stormchatStatusText').textContent = text;
    }
    
    updateConnectionStatus(connected) {
        this.isConnected = connected;
        const indicator = document.getElementById('stormchatConnection');
        
        if (connected) {
            indicator.classList.remove('disconnected');
            if (!this.session) {
                this.updateStatus('Готовий до чату');
            }
        } else {
            indicator.classList.add('disconnected');
            this.updateStatus('Відсутнє підключення');
        }
    }
    
    showQuickActions() {
        const quickActions = document.getElementById('stormchatQuickActions');
        quickActions.style.display = 'flex';
    }
    
    hideQuickActions() {
        const quickActions = document.getElementById('stormchatQuickActions');
        quickActions.style.display = 'none';
    }
    
    startPolling() {
        this.pollInterval = setInterval(() => {
            if (this.session && this.isConnected && !document.hidden) {
                this.loadMessages();
            }
        }, 3000);
    }
    
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
    
    toggle() {
        const window = document.getElementById('stormchatWindow');
        const btn = document.getElementById('stormchatBtn');
        const isActive = window.classList.contains('active');
        
        if (isActive) {
            window.classList.remove('active');
            btn.classList.remove('has-messages');
        } else {
            window.classList.add('active');
            btn.classList.remove('has-messages');
            this.scrollToBottom();
            
            if (this.session && this.lastMessageId > 0) {
                this.markAsRead();
            }
            
            this.updateLastActivity();
        }
    }
    
    async markAsRead() {
        try {
            await fetch('/api/chat/messages.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: this.session.id,
                    message_ids: [this.lastMessageId],
                    reader_type: 'user'
                })
            });
        } catch (error) {
            console.error('Mark as read error:', error);
        }
    }
    
    updateUnreadIndicator() {
        const btn = document.getElementById('stormchatBtn');
        const window = document.getElementById('stormchatWindow');
        
        if (!window.classList.contains('active')) {
            btn.classList.add('has-messages');
        }
    }
    
    handleKeyPress(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            this.sendMessage();
        }
    }
    
    autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
    
    disableSending(disabled) {
        const sendBtn = document.getElementById('stormchatSendBtn');
        const input = document.getElementById('stormchatInput');
        
        sendBtn.disabled = disabled;
        input.disabled = disabled;
        
        if (disabled) {
            sendBtn.innerHTML = '⏳';
        } else {
            sendBtn.innerHTML = '➤';
        }
    }
    
    scrollToBottom() {
        const container = document.getElementById('stormchatMessages');
        container.scrollTop = container.scrollHeight;
    }
    
    getGuestData() {
        return {
            name: 'Гість',
            email: null
        };
    }
}

// Инициализация чата
const StormChat = new StormChatWidget();

// Экспорт для внешнего использования
window.StormChat = StormChat;
</script>


</body>
</html>

<?php
// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}
?>