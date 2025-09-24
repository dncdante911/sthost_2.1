/**
 * EPIC JavaScript для FAQ/Wiki страницы
 * /assets/js/pages/info-faq.js
 */

// Глобальные переменные
let searchTimeout;
let currentTheme = localStorage.getItem('theme') || 'light';
let fontSize = parseInt(localStorage.getItem('fontSize')) || 16;
let bookmarkedArticles = JSON.parse(localStorage.getItem('bookmarks')) || [];
let chatExpanded = false;
let fabMenuOpen = false;

// Данные для поиска (в реальном проекте загружается с сервера)
const searchData = [
    { title: 'Налаштування email на мобільному', category: 'hosting', tags: ['email', 'мобільний', 'imap', 'smtp'] },
    { title: 'Встановлення SSL сертифіката', category: 'ssl', tags: ['ssl', 'https', 'безпека', 'сертифікат'] },
    { title: 'Налаштування DNS записів', category: 'domains', tags: ['dns', 'домен', 'записи', 'налаштування'] },
    { title: 'Перше налаштування VPS', category: 'vps', tags: ['vps', 'сервер', 'linux', 'налаштування'] },
    { title: 'Встановлення WordPress', category: 'apps', tags: ['wordpress', 'cms', 'встановлення'] }
];

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    initTheme();
    initSearch();
    initScrollAnimations();
    initStatCounters();
    initLiveChat();
    initFloatingActions();
    initBookmarks();
    loadArticleContent();
    setupEventListeners();
}

// Инициализация темы
function initTheme() {
    document.documentElement.setAttribute('data-theme', currentTheme);
    document.documentElement.style.fontSize = fontSize + 'px';
    
    // Обновляем иконку темы
    const themeIcon = document.querySelector('.fab-item [class*="bi-moon"], .fab-item [class*="bi-sun"]');
    if (themeIcon) {
        themeIcon.className = currentTheme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
    }
}

// Система поиска
function initSearch() {
    const searchInput = document.getElementById('knowledgeSearch');
    const suggestionsContainer = document.getElementById('searchSuggestions');
    
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length > 0) {
            searchTimeout = setTimeout(() => {
                showSearchSuggestions(query, suggestionsContainer);
            }, 300);
        } else {
            hideSuggestions(suggestionsContainer);
        }
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
    
    // Клик вне поиска скрывает подсказки
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            hideSuggestions(suggestionsContainer);
        }
    });
}

function showSearchSuggestions(query, container) {
    const suggestions = searchData.filter(item => 
        item.title.toLowerCase().includes(query.toLowerCase()) ||
        item.tags.some(tag => tag.toLowerCase().includes(query.toLowerCase()))
    ).slice(0, 5);
    
    if (suggestions.length > 0) {
        container.innerHTML = suggestions.map(item => `
            <div class="suggestion-item" onclick="selectSuggestion('${item.title}')">
                <strong>${highlightQuery(item.title, query)}</strong>
                <small class="d-block text-muted">${item.category}</small>
            </div>
        `).join('');
        container.style.display = 'block';
    } else {
        hideSuggestions(container);
    }
}

function hideSuggestions(container) {
    if (container) {
        container.style.display = 'none';
    }
}

function highlightQuery(text, query) {
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}

function selectSuggestion(title) {
    document.getElementById('knowledgeSearch').value = title;
    hideSuggestions(document.getElementById('searchSuggestions'));
    performSearch();
}

function performSearch() {
    const query = document.getElementById('knowledgeSearch').value.trim();
    if (query) {
        showNotification(`Пошук: "${query}"`, 'info');
        // В реальном проекте здесь будет AJAX запрос
        trackUserAction('search', { query: query });
    }
}

function searchFor(query) {
    document.getElementById('knowledgeSearch').value = query;
    performSearch();
}

// Популярные вопросы
function toggleQuestion(button) {
    const card = button.closest('.question-card');
    const answer = card.querySelector('.question-answer');
    const isExpanded = answer.classList.contains('expanded');
    
    // Закрываем все другие вопросы
    document.querySelectorAll('.question-answer.expanded').forEach(el => {
        if (el !== answer) {
            el.classList.remove('expanded');
            el.closest('.question-card').querySelector('.expand-btn').classList.remove('expanded');
        }
    });
    
    // Переключаем текущий
    answer.classList.toggle('expanded');
    button.classList.toggle('expanded');
    
    if (!isExpanded) {
        // Плавная прокрутка к вопросу
        setTimeout(() => {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 200);
        
        trackUserAction('question_expand', { question: card.querySelector('.question-title').textContent });
    }
}

function markHelpful(button) {
    if (button.classList.contains('active')) return;
    
    button.classList.add('active');
    const currentCount = parseInt(button.textContent.match(/\d+/) || [0])[0];
    button.innerHTML = `<i class="bi bi-hand-thumbs-up"></i> Корисно (${currentCount + 1})`;
    
    showNotification('Дякуємо за відгук!', 'success');
    trackUserAction('mark_helpful', { button_text: button.textContent });
}

function shareQuestion(index) {
    const url = window.location.href + '#question-' + index;
    
    if (navigator.share) {
        navigator.share({
            title: 'Корисне питання з StormHosting',
            url: url
        });
    } else {
        copyToClipboard(url);
        showNotification('Посилання скопійовано!', 'success');
    }
    
    trackUserAction('share_question', { index: index });
}

function showAllQuestions() {
    showNotification('Завантаження всіх питань...', 'info');
    // В реальном проекте загружаем больше вопросов
    trackUserAction('show_all_questions');
}

// Категории
function openCategory(categoryKey) {
    showNotification(`Відкриття категорії: ${categoryKey}`, 'info');
    
    // Анимация клика
    const card = document.querySelector(`[data-category="${categoryKey}"]`);
    card.style.transform = 'scale(0.95)';
    setTimeout(() => {
        card.style.transform = '';
    }, 150);
    
    // В реальном проекте переход на страницу категории
    trackUserAction('open_category', { category: categoryKey });
}

// Статьи
function toggleView(viewType) {
    const container = document.getElementById('articlesContainer');
    const buttons = document.querySelectorAll('.toggle-btn');
    
    buttons.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.view === viewType);
    });
    
    container.className = viewType === 'list' ? 'articles-container list-view' : 'articles-container';
    
    trackUserAction('toggle_view', { view: viewType });
}

function sortArticles(sortBy) {
    showNotification(`Сортування: ${sortBy}`, 'info');
    
    const container = document.getElementById('articlesContainer');
    const articles = Array.from(container.children);
    
    // Простая сортировка (в реальном проекте - серверная)
    articles.sort((a, b) => {
        switch(sortBy) {
            case 'recent':
                return new Date(b.dataset.updated || 0) - new Date(a.dataset.updated || 0);
            case 'helpful':
                return parseInt(b.dataset.likes || 0) - parseInt(a.dataset.likes || 0);
            default: // popular
                return parseInt(b.dataset.views || 0) - parseInt(a.dataset.views || 0);
        }
    });
    
    // Перестраиваем DOM с анимацией
    articles.forEach((article, index) => {
        setTimeout(() => {
            container.appendChild(article);
        }, index * 50);
    });
    
    trackUserAction('sort_articles', { sort_by: sortBy });
}

function toggleBookmark(button) {
    const articleCard = button.closest('.article-card');
    const articleId = articleCard.dataset.articleId || 'article-' + Date.now();
    
    button.classList.toggle('active');
    
    if (button.classList.contains('active')) {
        bookmarkedArticles.push(articleId);
        showNotification('Додано в закладки', 'success');
    } else {
        bookmarkedArticles = bookmarkedArticles.filter(id => id !== articleId);
        showNotification('Видалено з закладок', 'info');
    }
    
    localStorage.setItem('bookmarks', JSON.stringify(bookmarkedArticles));
    trackUserAction('toggle_bookmark', { article_id: articleId, bookmarked: button.classList.contains('active') });
}

function openArticle(articleId) {
    const modal = new bootstrap.Modal(document.getElementById('articleModal'));
    
    // Загружаем содержимое статьи
    loadArticleContent(articleId);
    
    // Показываем модальное окно
    modal.show();
    
    trackUserAction('open_article', { article_id: articleId });
}

function loadArticleContent(articleId = null) {
    if (!articleId) return;
    
    // В реальном проекте - AJAX запрос
    const mockContent = {
        title: 'Налаштування email на мобільному',
        category: 'Веб-хостинг',
        difficulty: 'Початківець',
        readingTime: '5 хв',
        lastUpdated: '15.01.2024',
        content: `
            <h2>Вступ</h2>
            <p>У цій статті ми розглянемо детальний процес налаштування електронної пошти на мобільних пристроях для роботи з хостингом StormHosting UA.</p>
            
            <h3>Необхідні дані</h3>
            <p>Перед початком налаштування переконайтеся, що у вас є:</p>
            <ul>
                <li>Адреса електронної пошти</li>
                <li>Пароль від поштової скриньки</li>
                <li>Налаштування IMAP/SMTP серверу</li>
            </ul>
            
            <h3>Налаштування для Android</h3>
            <p>Для Android пристроїв виконайте наступні кроки...</p>
            
            <pre><code>
Сервер вхідної пошти (IMAP): mail.yourdomain.com
Порт: 993
Безпека: SSL/TLS

Сервер вихідної пошти (SMTP): mail.yourdomain.com  
Порт: 465
Безпека: SSL/TLS
            </code></pre>
            
            <h3>Налаштування для iOS</h3>
            <p>Для iPhone та iPad процедура дещо відрізняється...</p>
        `
    };
    
    // Заповняем модальное окно
    document.getElementById('modalCategory').textContent = mockContent.category;
    document.getElementById('modalTitle').textContent = mockContent.title;
    document.getElementById('articleDifficulty').textContent = mockContent.difficulty;
    document.getElementById('readingTime').textContent = mockContent.readingTime;
    document.getElementById('lastUpdated').textContent = mockContent.lastUpdated;
    document.getElementById('articleBody').innerHTML = mockContent.content;
    
    // Генерируем содержание
    generateTableOfContents();
    
    // Загружаем связанные статьи
    loadRelatedArticles();
}

function generateTableOfContents() {
    const articleBody = document.getElementById('articleBody');
    const tocList = document.getElementById('articleTOC');
    const headings = articleBody.querySelectorAll('h2, h3');
    
    if (headings.length === 0) {
        tocList.innerHTML = '<li>Немає заголовків</li>';
        return;
    }
    
    const tocHTML = Array.from(headings).map((heading, index) => {
        const id = 'heading-' + index;
        heading.id = id;
        
        const level = heading.tagName === 'H2' ? 'toc-h2' : 'toc-h3';
        return `<li class="${level}"><a href="#${id}" onclick="scrollToHeading('${id}')">${heading.textContent}</a></li>`;
    }).join('');
    
    tocList.innerHTML = tocHTML;
}

function scrollToHeading(headingId) {
    const heading = document.getElementById(headingId);
    if (heading) {
        heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Подсвечиваем активный заголовок
        document.querySelectorAll('#articleTOC a').forEach(link => link.classList.remove('active'));
        document.querySelector(`#articleTOC a[href="#${headingId}"]`).classList.add('active');
    }
}

function loadRelatedArticles() {
    const relatedContainer = document.getElementById('relatedArticles');
    
    const mockRelated = [
        { title: 'Налаштування SMTP серверу', category: 'Хостинг' },
        { title: 'Проблеми з отриманням пошти', category: 'Хостинг' },
        { title: 'Безпека електронної пошти', category: 'Безпека' }
    ];
    
    relatedContainer.innerHTML = mockRelated.map(article => `
        <div class="related-item" onclick="openRelatedArticle('${article.title}')">
            <div>
                <strong>${article.title}</strong>
                <small class="d-block text-muted">${article.category}</small>
            </div>
            <i class="bi bi-arrow-right"></i>
        </div>
    `).join('');
}

function openRelatedArticle(title) {
    showNotification(`Відкриття: ${title}`, 'info');
    trackUserAction('open_related_article', { title: title });
}

// Действия с статьями
function printArticle() {
    window.print();
    trackUserAction('print_article');
}

function shareArticle() {
    const title = document.getElementById('modalTitle').textContent;
    const url = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: 'Корисна стаття з бази знань StormHosting',
            url: url
        });
    } else {
        copyToClipboard(url);
        showNotification('Посилання скопійовано!', 'success');
    }
    
    trackUserAction('share_article', { title: title });
}

// Обратная связь
function submitFeedback(type) {
    const buttons = document.querySelectorAll('.feedback-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    const clickedButton = document.querySelector(`.feedback-btn.${type}`);
    clickedButton.classList.add('active');
    
    if (type === 'negative') {
        document.getElementById('feedbackForm').style.display = 'block';
    } else {
        showNotification('Дякуємо за відгук!', 'success');
    }
    
    trackUserAction('article_feedback', { type: type });
}

function sendDetailedFeedback() {
    const textarea = document.querySelector('#feedbackForm textarea');
    const feedback = textarea.value.trim();
    
    if (feedback) {
        showNotification('Відгук відправлено! Дякуємо!', 'success');
        document.getElementById('feedbackForm').style.display = 'none';
        textarea.value = '';
        
        trackUserAction('detailed_feedback', { feedback: feedback });
    }
}

// Предложение темы
function suggestTopic() {
    const modal = new bootstrap.Modal(document.getElementById('suggestModal'));
    modal.show();
}

function submitSuggestion() {
    const form = document.getElementById('suggestForm');
    const formData = new FormData(form);
    
    // Валидация
    const title = formData.get('topicTitle') || document.getElementById('topicTitle').value;
    const category = formData.get('topicCategory') || document.getElementById('topicCategory').value;
    const email = formData.get('contactEmail') || document.getElementById('contactEmail').value;
    
    if (!title || !category || !email) {
        showNotification('Будь ласка, заповніть всі обов\'язкові поля', 'error');
        return;
    }
    
    // Показываем загрузку
    showLoadingOverlay('Відправка пропозиції...');
    
    // Симуляция отправки
    setTimeout(() => {
        hideLoadingOverlay();
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('suggestModal'));
        modal.hide();
        
        showNotification('Пропозицію відправлено! Ми розглянемо її протягом 24 годин.', 'success');
        form.reset();
        
        trackUserAction('suggest_topic', { title: title, category: category });
    }, 2000);
}

// Живой чат
function initLiveChat() {
    const widget = document.getElementById('liveChatWidget');
    const header = widget.querySelector('.chat-header');
    const body = widget.querySelector('.chat-body');
    const toggle = widget.querySelector('.chat-toggle');
    
    // Позиционирование чата
    adjustChatPosition();
    
    window.addEventListener('resize', adjustChatPosition);
}

function adjustChatPosition() {
    const widget = document.getElementById('liveChatWidget');
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        widget.style.width = 'calc(100vw - 20px)';
        widget.style.left = '10px';
        widget.style.right = '10px';
    } else {
        widget.style.width = '350px';
        widget.style.left = 'auto';
        widget.style.right = '20px';
    }
}

function toggleChat() {
    const body = document.getElementById('chatBody');
    const toggle = document.querySelector('.chat-toggle');
    
    chatExpanded = !chatExpanded;
    
    body.classList.toggle('expanded', chatExpanded);
    toggle.style.transform = chatExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
    
    if (chatExpanded) {
        trackUserAction('chat_open');
    }
}

function openLiveChat() {
    if (!chatExpanded) {
        toggleChat();
    }
    trackUserAction('open_live_chat');
}

function requestCallback() {
    showNotification('Запит на зворотний дзвінок відправлено', 'success');
    trackUserAction('request_callback');
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
    }
}

function sendChatMessage() {
    const input = document.getElementById('chatInputField');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Добавляем сообщение пользователя
    addChatMessage(message, 'user');
    input.value = '';
    
    // Симулируем ответ бота
    setTimeout(() => {
        const botResponse = generateBotResponse(message);
        addChatMessage(botResponse, 'bot');
    }, 1000);
    
    trackUserAction('send_chat_message', { message: message });
}

function addChatMessage(message, sender) {
    const messagesContainer = document.getElementById('chatMessages');
    const messageElement = document.createElement('div');
    messageElement.className = `message ${sender}-message`;
    
    const avatar = sender === 'bot' ? '<i class="bi bi-robot"></i>' : '<i class="bi bi-person"></i>';
    
    messageElement.innerHTML = `
        <div class="message-avatar">${avatar}</div>
        <div class="message-content">
            <p>${message}</p>
        </div>
    `;
    
    messagesContainer.appendChild(messageElement);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function generateBotResponse(userMessage) {
    const responses = {
        'привіт': 'Привіт! Чим можу допомогти?',
        'хостинг': 'З хостингом допомжу! Яка саме проблема?',
        'домен': 'Питання по доменах? Розкажіть детальніше.',
        'ssl': 'SSL сертифікати - моя спеціальність! Що цікавить?',
        'підтримка': 'Наша підтримка працює 24/7. Переведу вас на оператора?'
    };
    
    const lowerMessage = userMessage.toLowerCase();
    
    for (const [keyword, response] of Object.entries(responses)) {
        if (lowerMessage.includes(keyword)) {
            return response;
        }
    }
    
    return 'Дякую за повідомлення! Наш оператор зв\'яжеться з вами найближчим часом.';
}

function selectQuickReply(reply) {
    document.getElementById('chatInputField').value = reply;
    sendChatMessage();
}

// Плавающие действия
function initFloatingActions() {
    const fabMain = document.querySelector('.fab-main');
    const fabMenu = document.querySelector('.fab-menu');
    
    // Показываем FAB при скролле
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        fabMain.style.opacity = scrolled > 300 ? '1' : '0.7';
    });
}

function toggleFabMenu() {
    const fabMain = document.querySelector('.fab-main');
    const fabMenu = document.querySelector('.fab-menu');
    
    fabMenuOpen = !fabMenuOpen;
    
    fabMain.classList.toggle('active', fabMenuOpen);
    fabMenu.classList.toggle('active', fabMenuOpen);
    
    trackUserAction('toggle_fab_menu', { open: fabMenuOpen });
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    trackUserAction('scroll_to_top');
}

function toggleDarkMode() {
    currentTheme = currentTheme === 'light' ? 'dark' : 'light';
    localStorage.setItem('theme', currentTheme);
    initTheme();
    
    showNotification(`Тема змінена на ${currentTheme === 'dark' ? 'темну' : 'світлу'}`, 'success');
    trackUserAction('toggle_theme', { theme: currentTheme });
}

function increaseFontSize() {
    fontSize = fontSize >= 20 ? 14 : fontSize + 2;
    localStorage.setItem('fontSize', fontSize);
    document.documentElement.style.fontSize = fontSize + 'px';
    
    showNotification(`Розмір шрифту: ${fontSize}px`, 'info');
    trackUserAction('change_font_size', { size: fontSize });
}

// Анимации при скролле
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, observerOptions);
    
    // Наблюдаем за элементами
    const elements = document.querySelectorAll('.question-card, .category-card, .article-card');
    elements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'all 0.6s ease';
        observer.observe(element);
    });
}

// Анимация счетчиков
function initStatCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element) {
    const text = element.textContent;
    const number = parseInt(text.replace(/\D/g, ''));
    
    if (isNaN(number)) return;
    
    const isK = text.includes('k') || text.includes('K');
    const isPercent = text.includes('%');
    const isPlus = text.includes('+');
    
    const duration = 2000;
    const start = performance.now();
    
    function animate(currentTime) {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);
        
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const current = Math.round(number * easeOutQuart);
        
        let displayText = current.toString();
        if (isK) displayText += 'k';
        if (isPercent) displayText += '%';
        if (isPlus) displayText += '+';
        
        element.textContent = displayText;
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }
    
    requestAnimationFrame(animate);
}

// Закладки
function initBookmarks() {
    // Восстанавливаем закладки из localStorage
    document.querySelectorAll('.bookmark').forEach(button => {
        const articleCard = button.closest('.article-card');
        const articleId = articleCard.dataset.articleId || 'article-' + Math.random();
        
        if (bookmarkedArticles.includes(articleId)) {
            button.classList.add('active');
        }
    });
}

// Настройка обработчиков событий
function setupEventListeners() {
    // Клавиатурные сокращения
    document.addEventListener('keydown', function(e) {
        // Ctrl+K для поиска
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            document.getElementById('knowledgeSearch').focus();
        }
        
        // Ctrl+D для темной темы
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            toggleDarkMode();
        }
        
        // Escape для закрытия модальных окон
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
            });
        }
    });
    
    // Отслеживание времени на странице
    let startTime = Date.now();
    window.addEventListener('beforeunload', function() {
        const timeSpent = Math.round((Date.now() - startTime) / 1000);
        trackUserAction('page_time', { seconds: timeSpent });
    });
}

// Утилиты
function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notif => notif.remove());
    
    const toast = document.createElement('div');
    toast.className = `notification-toast alert alert-${type === 'error' ? 'danger' : type} alert-dismissible`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 400px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    `;
    
    const iconMap = {
        success: 'bi-check-circle',
        error: 'bi-exclamation-triangle',
        warning: 'bi-exclamation-triangle',
        info: 'bi-info-circle'
    };
    
    toast.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="bi ${iconMap[type]} me-2 mt-1"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 4000);
}

function showLoadingOverlay(message = 'Завантаження...') {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    
    overlay.innerHTML = `
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status"></div>
            <div style="font-size: 16px; font-weight: 500;">${message}</div>
        </div>
    `;
    
    document.body.appendChild(overlay);
}

function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        return navigator.clipboard.writeText(text);
    } else {
        // Fallback для старых браузеров
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            return Promise.resolve();
        } catch (err) {
            return Promise.reject(err);
        } finally {
            document.body.removeChild(textArea);
        }
    }
}

function trackUserAction(action, data = {}) {
    // Отправка аналитики
    if (typeof gtag !== 'undefined') {
        gtag('event', 'faq_user_action', {
            action: action,
            ...data,
            timestamp: new Date().toISOString()
        });
    }
    
    // Логирование
    console.log('User action:', action, data);
    
    // Отправка на сервер (в реальном проекте)
    /*
    fetch('/api/track-user-action', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, data, timestamp: new Date().toISOString() })
    }).catch(err => console.log('Tracking failed:', err));
    */
}

// Обработка ошибок
window.addEventListener('error', (e) => {
    console.error('FAQ page error:', e.error);
    trackUserAction('javascript_error', { message: e.error.message, stack: e.error.stack });
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        searchFor,
        toggleQuestion,
        openCategory,
        openArticle,
        showNotification,
        trackUserAction
    };
}