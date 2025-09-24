/**
 * SSL Сертифікати - JavaScript функціональність
 * /assets/js/ssl.js
 * StormHosting UA
 */

(function() {
    'use strict';

    // Глобальні змінні
    let isInitialized = false;
    let notificationContainer = null;
    let scrollObserver = null;

    /**
     * Ініціалізація при завантаженні DOM
     */
    document.addEventListener('DOMContentLoaded', function() {
        if (isInitialized) return;
        
        try {
            initializeSSLPage();
            isInitialized = true;
        } catch (error) {
            console.error('SSL Page Initialization Error:', error);
        }
    });

    /**
     * Головна функція ініціалізації
     */
    function initializeSSLPage() {
        createNotificationContainer();
        initFAQ();
        initOrderButtons();
        initFormValidation();
        initSmoothScrolling();
        initScrollAnimations();
        initParallaxEffect();
        initTooltips();
        initKeyboardNavigation();
        
        console.log('SSL Page initialized successfully');
    }

    /**
     * Створення контейнера для сповіщень
     */
    function createNotificationContainer() {
        if (document.getElementById('ssl-notifications')) return;
        
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'ssl-notifications';
        notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            pointer-events: none;
        `;
        document.body.appendChild(notificationContainer);
    }

    /**
     * FAQ функціональність
     */
    function initFAQ() {
        const faqQuestions = document.querySelectorAll('.faq-question');
        
        faqQuestions.forEach(question => {
            question.addEventListener('click', handleFAQClick);
            question.addEventListener('keydown', handleFAQKeydown);
        });
    }

    function handleFAQClick() {
        const faqId = this.getAttribute('data-faq');
        const answer = document.getElementById(`faq-${faqId}`);
        const isActive = answer && answer.classList.contains('active');
        
        // Закриваємо всі відкриті FAQ
        closeAllFAQ();
        
        // Відкриваємо поточний FAQ якщо він не був активним
        if (!isActive && answer) {
            answer.classList.add('active');
            this.classList.add('active');
            this.setAttribute('aria-expanded', 'true');
            
            // Анімація прокрутки до FAQ
            setTimeout(() => {
                answer.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest' 
                });
            }, 100);
        }
    }

    function handleFAQKeydown(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    }

    function closeAllFAQ() {
        document.querySelectorAll('.faq-answer.active').forEach(answer => {
            answer.classList.remove('active');
        });
        
        document.querySelectorAll('.faq-question.active').forEach(question => {
            question.classList.remove('active');
            question.setAttribute('aria-expanded', 'false');
        });
    }

    /**
     * Кнопки замовлення SSL
     */
    function initOrderButtons() {
        const orderButtons = document.querySelectorAll('.order-ssl-btn, [data-ssl-type]');
        
        orderButtons.forEach(button => {
            button.addEventListener('click', handleOrderClick);
        });
    }

    function handleOrderClick(e) {
        e.preventDefault();
        
        const sslType = this.getAttribute('data-ssl-type');
        const sslName = this.getAttribute('data-ssl-name') || this.textContent.trim();
        const sslPrice = this.getAttribute('data-ssl-price');
        
        // Анімація натискання
        animateButtonPress(this);
        
        // Показуємо форму замовлення
        showOrderForm(sslType, sslName, sslPrice);
    }

    function animateButtonPress(button) {
        button.style.transform = 'scale(0.95)';
        button.style.transition = 'transform 0.1s ease';
        
        setTimeout(() => {
            button.style.transform = '';
            button.style.transition = '';
        }, 150);
    }

    function showOrderForm(type, name, price) {
        const orderForm = document.querySelector('.ssl-order-form');
        if (!orderForm) {
            showNotification('Форма замовлення недоступна', 'error');
            return;
        }
        
        // Заповнюємо дані про SSL
        fillOrderFormData(orderForm, type, name, price);
        
        // Прокручуємо до форми з анімацією
        orderForm.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        
        // Підсвічуємо форму
        highlightForm(orderForm);
        
        // Фокусуємо на першому полі
        focusFirstField(orderForm);
        
        // Показуємо успішне сповіщення
        showNotification(
            `Обрано: ${name}${price ? ` за ${formatPrice(price)} грн/рік` : ''}`, 
            'success'
        );
    }

    function fillOrderFormData(form, type, name, price) {
        const typeSelect = form.querySelector('select[name="ssl_type"]');
        if (typeSelect && type) {
            typeSelect.value = type;
        }
        
        // Зберігаємо дані в data-атрибутах форми
        form.dataset.selectedType = type || '';
        form.dataset.selectedName = name || '';
        form.dataset.selectedPrice = price || '';
    }

    function highlightForm(form) {
        form.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.3)';
        form.style.transition = 'box-shadow 0.3s ease';
        
        setTimeout(() => {
            form.style.boxShadow = '';
            setTimeout(() => {
                form.style.transition = '';
            }, 300);
        }, 2000);
    }

    function focusFirstField(form) {
        setTimeout(() => {
            const firstInput = form.querySelector('input[type="text"], input[type="email"], select');
            if (firstInput) {
                firstInput.focus();
            }
        }, 500);
    }

    /**
     * Валідація форм
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('.ssl-order-form, form[data-ssl-form]');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            
            // Валідація при втраті фокусу
            inputs.forEach(input => {
                input.addEventListener('blur', () => validateField(input));
                input.addEventListener('input', () => clearFieldError(input));
            });
            
            // Валідація при відправці
            form.addEventListener('submit', (e) => handleFormSubmit(e, form));
        });
    }

    function handleFormSubmit(e, form) {
        e.preventDefault();
        
        const inputs = form.querySelectorAll('input, select, textarea');
        let isValid = true;
        
        // Валідуємо всі поля
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            showNotification('Будь ласка, виправте помилки у формі', 'error');
            
            // Фокусуємо на першому полі з помилкою
            const firstError = form.querySelector('.form-input.error, .form-select.error');
            if (firstError) {
                firstError.focus();
            }
            return;
        }
        
        // Відправляємо форму
        submitSSLOrder(form);
    }

    function submitSSLOrder(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton ? submitButton.innerHTML : '';
        
        // Показуємо стан завантаження
        if (submitButton) {
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span>Відправка...</span>';
        }
        
        // Симуляція відправки (замінити на реальний AJAX)
        setTimeout(() => {
            try {
                // Тут має бути реальна відправка на сервер
                const formData = new FormData(form);
                
                // Для демо - показуємо успіх
                showNotification(
                    'Заявку успішно відправлено! Ми зв\'яжемося з вами найближчим часом.', 
                    'success'
                );
                
                // Очищуємо форму
                form.reset();
                clearAllFieldErrors(form);
                
                // Відправляємо аналітику
                trackSSLOrder(formData);
                
            } catch (error) {
                console.error('Form submission error:', error);
                showNotification('Помилка при відправці. Спробуйте ще раз.', 'error');
            } finally {
                // Повертаємо кнопку в початковий стан
                if (submitButton) {
                    submitButton.classList.remove('loading');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            }
        }, 2000);
    }

    function trackSSLOrder(formData) {
        // Відправка даних в Google Analytics або інші системи аналітики
        if (typeof gtag !== 'undefined') {
            gtag('event', 'ssl_order_submitted', {
                event_category: 'SSL',
                event_label: formData.get('ssl_type') || 'unknown',
                value: parseInt(formData.get('price')) || 0
            });
        }
    }

    /**
     * Валідація окремого поля
     */
    function validateField(field) {
        const value = field.value.trim();
        const fieldType = field.type;
        const fieldName = field.name;
        const isRequired = field.hasAttribute('required');
        
        let isValid = true;
        let errorMessage = '';
        
        // Перевірка обов'язкових полів
        if (isRequired && !value) {
            isValid = false;
            errorMessage = 'Це поле є обов\'язковим';
        }
        
        // Специфічні валідації
        if (value && isValid) {
            switch (fieldType) {
                case 'email':
                    if (!isValidEmail(value)) {
                        isValid = false;
                        errorMessage = 'Введіть коректний email';
                    }
                    break;
                    
                case 'tel':
                    if (!isValidPhone(value)) {
                        isValid = false;
                        errorMessage = 'Введіть коректний номер телефону';
                    }
                    break;
                    
                case 'text':
                    if (fieldName === 'domain' && !isValidDomain(value)) {
                        isValid = false;
                        errorMessage = 'Введіть коректне доменне ім\'я (наприклад: example.com)';
                    }
                    break;
            }
        }
        
        // Додаткові перевірки
        if (value && isValid) {
            if (fieldName === 'phone' && value.length > 0 && value.length < 10) {
                isValid = false;
                errorMessage = 'Номер телефону занадто короткий';
            }
        }
        
        // Показуємо або приховуємо помилку
        if (!isValid) {
            showFieldError(field, errorMessage);
        } else {
            clearFieldError(field);
        }
        
        return isValid;
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhone(phone) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        return phoneRegex.test(phone);
    }

    function isValidDomain(domain) {
        const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/;
        return domainRegex.test(domain);
    }

    function showFieldError(field, message) {
        clearFieldError(field);
        
        field.classList.add('error');
        field.setAttribute('aria-invalid', 'true');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.innerHTML = `<i aria-hidden="true">⚠</i> ${message}`;
        errorElement.setAttribute('role', 'alert');
        
        field.parentNode.appendChild(errorElement);
    }

    function clearFieldError(field) {
        field.classList.remove('error');
        field.removeAttribute('aria-invalid');
        
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }

    function clearAllFieldErrors(form) {
        const errorFields = form.querySelectorAll('.error');
        const errorMessages = form.querySelectorAll('.field-error');
        
        errorFields.forEach(field => {
            field.classList.remove('error');
            field.removeAttribute('aria-invalid');
        });
        
        errorMessages.forEach(error => error.remove());
    }

    /**
     * Плавний скролінг
     */
    function initSmoothScrolling() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', handleSmoothScroll);
        });
    }

    function handleSmoothScroll(e) {
        const href = this.getAttribute('href');
        
        if (href === '#' || href === '#top') {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            
            const offsetTop = target.getBoundingClientRect().top + window.pageYOffset - 100;
            
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
            
            // Оновлюємо URL без перезавантаження
            if (history.pushState) {
                history.pushState(null, null, href);
            }
        }
    }

    /**
     * Анімації при скролінгу
     */
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        scrollObserver = new IntersectionObserver(handleScrollAnimation, observerOptions);
        
        // Елементи для спостереження
        const animatedElements = document.querySelectorAll(`
            .benefit-card, 
            .package-card, 
            .timeline-item, 
            .indicator-item, 
            .faq-item,
            .support-feature
        `);
        
        animatedElements.forEach(el => {
            scrollObserver.observe(el);
        });
    }

    function handleScrollAnimation(entries) {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Затримка для послідовної анімації
                setTimeout(() => {
                    entry.target.classList.add('animate-in');
                    
                    // Спеціальні анімації для різних типів елементів
                    if (entry.target.classList.contains('indicator-number')) {
                        animateNumber(entry.target);
                    }
                    
                    if (entry.target.classList.contains('progress-bar')) {
                        animateProgressBar(entry.target);
                    }
                    
                    // Припиняємо спостереження після анімації
                    scrollObserver.unobserve(entry.target);
                }, index * 100);
            }
        });
    }

    /**
     * Анімація чисел
     */
    function animateNumber(element) {
        const text = element.textContent;
        const number = parseInt(text.replace(/[^\d]/g, ''));
        
        if (isNaN(number) || number === 0) return;
        
        const duration = 2000;
        const startTime = performance.now();
        
        function updateNumber(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Використовуємо easeOut функцію для плавності
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(number * easeOut);
            
            element.textContent = text.replace(/[\d,]+/, current.toLocaleString('uk-UA'));
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        }
        
        requestAnimationFrame(updateNumber);
    }

    /**
     * Анімація прогрес-барів
     */
    function animateProgressBar(element) {
        const percentage = element.getAttribute('data-percentage') || 100;
        const bar = element.querySelector('.progress-fill');
        
        if (bar) {
            setTimeout(() => {
                bar.style.width = percentage + '%';
            }, 200);
        }
    }

    /**
     * Паралакс ефект
     */
    function initParallaxEffect() {
        const hero = document.querySelector('.ssl-hero');
        if (!hero) return;
        
        let ticking = false;
        
        function updateParallax() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.3;
            
            hero.style.transform = `translateY(${rate}px)`;
            ticking = false;
        }
        
        function requestParallaxUpdate() {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', requestParallaxUpdate, { passive: true });
    }

    /**
     * Тултипи
     */
    function initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', () => showTooltip(element));
            element.addEventListener('mouseleave', hideTooltip);
            element.addEventListener('focus', () => showTooltip(element));
            element.addEventListener('blur', hideTooltip);
        });
    }

    function showTooltip(element) {
        const text = element.getAttribute('data-tooltip');
        if (!text) return;
        
        // Видаляємо існуючі тултипи
        hideTooltip();
        
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.setAttribute('role', 'tooltip');
        
        document.body.appendChild(tooltip);
        
        // Позиціонуємо тултип
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        let top = rect.top - tooltipRect.height - 10;
        
        // Перевіряємо межі екрану
        if (left < 10) left = 10;
        if (left + tooltipRect.width > window.innerWidth - 10) {
            left = window.innerWidth - tooltipRect.width - 10;
        }
        if (top < 10) {
            top = rect.bottom + 10;
            tooltip.classList.add('tooltip-bottom');
        }
        
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + window.pageYOffset + 'px';
        
        // Показуємо з анімацією
        setTimeout(() => {
            tooltip.classList.add('show');
        }, 10);
    }

    function hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.classList.remove('show');
            setTimeout(() => {
                if (tooltip.parentNode) {
                    tooltip.remove();
                }
            }, 200);
        }
    }

    /**
     * Навігація з клавіатури
     */
    function initKeyboardNavigation() {
        // ESC для закриття модальних вікон та тултипів
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideTooltip();
                closeAllFAQ();
            }
        });

        // Tab navigation для пакетів SSL
        const packageCards = document.querySelectorAll('.package-card');
        packageCards.forEach(card => {
            const button = card.querySelector('.btn');
            if (button) {
                button.setAttribute('tabindex', '0');
            }
        });
    }

    /**
     * Сповіщення
     */
    function showNotification(message, type = 'info', duration = 5000) {
        if (!notificationContainer) {
            createNotificationContainer();
        }
        
        const notification = createNotificationElement(message, type);
        notificationContainer.appendChild(notification);
        
        // Показуємо з анімацією
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Автоматичне приховування
        const hideTimeout = setTimeout(() => {
            hideNotification(notification);
        }, duration);
        
        // Можливість закрити вручну
        const closeButton = notification.querySelector('.notification-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                clearTimeout(hideTimeout);
                hideNotification(notification);
            });
        }
        
        return notification;
    }

    function createNotificationElement(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.pointerEvents = 'auto';
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'polite');
        
        const icon = getNotificationIcon(type);
        
        notification.innerHTML = `
            <div class="notification-content">
                <i class="${icon}" aria-hidden="true"></i>
                <span>${escapeHtml(message)}</span>
            </div>
            <button class="notification-close" aria-label="Закрити сповіщення">
                <span aria-hidden="true">×</span>
            </button>
        `;
        
        return notification;
    }

    function hideNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }

    function getNotificationIcon(type) {
        const icons = {
            'success': '✓',
            'error': '⚠',
            'warning': '⚠',
            'info': 'ℹ'
        };
        
        return icons[type] || icons.info;
    }

    /**
     * Утиліти
     */
    function formatPrice(price) {
        const number = parseInt(price);
        if (isNaN(number)) return price;
        return number.toLocaleString('uk-UA');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).then(() => {
                showNotification('Скопійовано в буфер обміну', 'success');
                return true;
            }).catch(() => {
                return fallbackCopyToClipboard(text);
            });
        } else {
            return fallbackCopyToClipboard(text);
        }
    }

    function fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            
            if (successful) {
                showNotification('Скопійовано в буфер обміну', 'success');
                return Promise.resolve(true);
            } else {
                showNotification('Не вдалося скопіювати', 'error');
                return Promise.resolve(false);
            }
        } catch (err) {
            document.body.removeChild(textArea);
            showNotification('Не вдалося скопіювати', 'error');
            return Promise.resolve(false);
        }
    }

    /**
     * Калькулятор SSL вартості
     */
    function calculateSSLPrice(type, years = 1, addons = []) {
        const basePrices = {
            'dv': 990,
            'ov': 2490,
            'ev': 7990,
            'wildcard': 4990
        };
        
        const addonPrices = {
            'backup': 500,
            'monitoring': 300,
            'support': 1000
        };
        
        const basePrice = basePrices[type] || 0;
        let discount = 1;
        
        // Знижки за кількість років
        if (years >= 3) discount = 0.85; // 15%
        else if (years >= 2) discount = 0.9; // 10%
        
        // Розрахунок вартості додатків
        const addonsPrice = addons.reduce((total, addon) => {
            return total + (addonPrices[addon] || 0);
        }, 0);
        
        const totalPrice = (basePrice + addonsPrice) * years * discount;
        const savings = (basePrice + addonsPrice) * years - totalPrice;
        
        return {
            basePrice,
            addonsPrice,
            totalPrice: Math.round(totalPrice),
            savings: Math.round(savings),
            discount: Math.round((1 - discount) * 100)
        };
    }

    /**
     * Ленива загрузка зображень
     */
    function initLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px'
        });
        
        images.forEach(img => {
            imageObserver.observe(img);
        });
    }

    /**
     * Оптимізація продуктивності
     */
    function initPerformanceOptimizations() {
        // Debounce для scroll events
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(() => {
                // Scroll logic here
            }, 16); // ~60fps
        }, { passive: true });
        
        // Preload критичних ресурсів
        preloadCriticalResources();
    }

    function preloadCriticalResources() {
        const criticalResources = [
            '/assets/css/main.css',
            '/assets/js/main.js'
        ];
        
        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = resource.endsWith('.css') ? 'style' : 'script';
            link.href = resource;
            document.head.appendChild(link);
        });
    }

    /**
     * Обробка помилок
     */
    function initErrorHandling() {
        window.addEventListener('error', function(event) {
            console.error('SSL Page Error:', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error
            });
            
            // Відправляємо помилку в систему моніторингу (опціонально)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'exception', {
                    description: event.message,
                    fatal: false
                });
            }
        });
        
        // Обробка неперехоплених Promise rejections
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled Promise Rejection:', event.reason);
        });
    }

    /**
     * Публічний API
     */
    const SSLPageAPI = {
        // Основні функції
        showNotification,
        hideNotification,
        copyToClipboard,
        formatPrice,
        calculateSSLPrice,
        
        // Валідація
        validateField,
        isValidEmail,
        isValidPhone,
        isValidDomain,
        
        // Утиліти
        escapeHtml,
        animateNumber,
        
        // Стан
        isInitialized: () => isInitialized,
        
        // Переініціалізація (для SPA)
        reinitialize() {
            if (isInitialized) {
                // Очищуємо попередні слухачі подій
                if (scrollObserver) {
                    scrollObserver.disconnect();
                }
                isInitialized = false;
            }
            initializeSSLPage();
        }
    };

    // Експортуємо API в глобальну область
    window.SSLPage = SSLPageAPI;

    // Ініціалізуємо додаткові компоненти
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            initLazyLoading();
            initPerformanceOptimizations();
            initErrorHandling();
        }, 100);
    });

    // Очищення при виході зі сторінки
    window.addEventListener('beforeunload', function() {
        if (scrollObserver) {
            scrollObserver.disconnect();
        }
        
        // Очищуємо таймери та слухачі подій
        document.querySelectorAll('.notification').forEach(notification => {
            if (notification.parentNode) {
                notification.remove();
            }
        });
    });

})();

/**
 * Глобальні функції для зручності використання
 */
function showSSLNotification(message, type) {
    if (window.SSLPage) {
        return window.SSLPage.showNotification(message, type);
    }
    console.warn('SSL Page not initialized');
}

function copySSLText(text) {
    if (window.SSLPage) {
        return window.SSLPage.copyToClipboard(text);
    }
    console.warn('SSL Page not initialized');
}

function formatSSLPrice(price) {
    if (window.SSLPage) {
        return window.SSLPage.formatPrice(price);
    }
    return price;
}

/* Кінець файлу /assets/js/ssl.js */