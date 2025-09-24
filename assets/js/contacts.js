/**
 * Контакти - JavaScript функціональність
 * /assets/js/contacts.js
 * StormHosting UA
 */

(function() {
    'use strict';

    // Глобальні змінні
    let isInitialized = false;
    let notificationContainer = null;
    let scrollObserver = null;
    let clockInterval = null;
    let mapLoaded = false;

    /**
     * Ініціалізація при завантаженні DOM
     */
    document.addEventListener('DOMContentLoaded', function() {
        if (isInitialized) return;
        
        try {
            initializeContactsPage();
            isInitialized = true;
        } catch (error) {
            console.error('Contacts Page Initialization Error:', error);
        }
    });

    /**
     * Головна функція ініціалізації
     */
    function initializeContactsPage() {
        createNotificationContainer();
        initContactForm();
        initFormValidation();
        initServerStatus();
        initClock();
        initMapFunctionality();
        initMessengers();
        initScrollAnimations();
        initKeyboardShortcuts();
        
        console.log('Contacts Page initialized successfully');
    }

    /**
     * Створення контейнера для сповіщень
     */
    function createNotificationContainer() {
        if (document.getElementById('contacts-notifications')) return;
        
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'contacts-notifications';
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
     * Ініціалізація форми контактів
     */
    function initContactForm() {
        const form = document.getElementById('contactForm');
        if (!form) return;
        
        form.addEventListener('submit', handleFormSubmit);
        
        // Автозаповнення залежно від відділу
        const departmentSelect = document.getElementById('department');
        if (departmentSelect) {
            departmentSelect.addEventListener('change', handleDepartmentChange);
        }
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Валідація
        if (!validateContactForm(form)) {
            showNotification('Будь ласка, виправте помилки у формі', 'error');
            return;
        }
        
        // Показуємо завантаження
        showLoadingState(submitButton);
        
        // Збираємо дані
        const formData = new FormData(form);
        const contactData = Object.fromEntries(formData.entries());
        
        // Відправляємо
        submitContactForm(contactData, form, submitButton);
    }

    function submitContactForm(data, form, button) {
        // Симуляція відправки
        setTimeout(() => {
            try {
                showNotification('Дякуємо за звернення! Ми зв\'яжемося з вами найближчим часом.', 'success', 7000);
                
                form.reset();
                clearAllFieldErrors(form);
                
                // Аналітика
                trackContactFormSubmission(data);
                
                // Прокручуємо вгору
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
            } catch (error) {
                console.error('Form submission error:', error);
                showNotification('Помилка при відправці. Спробуйте ще раз.', 'error');
            } finally {
                hideLoadingState(button);
            }
        }, 2000);
    }

    function handleDepartmentChange(e) {
        const department = e.target.value;
        const subjectField = document.getElementById('subject');
        
        if (!subjectField || subjectField.value) return;
        
        const suggestions = {
            'support': 'Технічна проблема з ',
            'sales': 'Питання щодо тарифів та послуг',
            'billing': 'Питання по оплаті та рахунках',
            'general': 'Загальне питання'
        };
        
        if (suggestions[department]) {
            subjectField.placeholder = suggestions[department];
        }
    }

    /**
     * Валідація форми
     */
    function initFormValidation() {
        const form = document.getElementById('contactForm');
        if (!form) return;
        
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => clearFieldError(input));
        });
    }

    function validateContactForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    function validateField(field) {
        const value = field.value.trim();
        const fieldType = field.type;
        const isRequired = field.hasAttribute('required');
        
        let isValid = true;
        let errorMessage = '';
        
        if (isRequired && !value) {
            isValid = false;
            errorMessage = 'Це поле є обов\'язковим';
        }
        
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
            }
        }
        
        if (!isValid) {
            showFieldError(field, errorMessage);
        } else {
            clearFieldError(field);
        }
        
        return isValid;
    }

    /**
     * Статус серверів
     */
    function initServerStatus() {
        // Оновлюємо статус кожні 30 секунд
        updateAllServerStatus();
        setInterval(updateAllServerStatus, 30000);
        
        // Додаємо слухачі для кнопок оновлення
        document.querySelectorAll('[onclick*="refreshServerStatus"]').forEach(button => {
            const serverId = button.getAttribute('onclick').match(/'([^']+)'/)[1];
            button.onclick = () => refreshServerStatus(serverId);
        });
    }

    function updateAllServerStatus() {
        const serverCards = document.querySelectorAll('.status-card');
        
        serverCards.forEach(card => {
            const serverId = card.getAttribute('data-server');
            updateServerStatus(serverId, card);
        });
    }

    function updateServerStatus(serverId, card) {
        // Симуляція перевірки статусу сервера
        setTimeout(() => {
            const randomUptime = (99.5 + Math.random() * 0.4).toFixed(1) + '%';
            const randomResponseTime = (10 + Math.random() * 20).toFixed(0) + 'ms';
            const randomLoad = (15 + Math.random() * 30).toFixed(0) + '%';
            
            const uptimeElement = card.querySelector('.uptime-value');
            const responseElement = card.querySelector('.metric-value');
            const loadElement = card.querySelectorAll('.metric-value')[1];
            
            if (uptimeElement) {
                animateValue(uptimeElement, uptimeElement.textContent, randomUptime);
            }
            
            if (responseElement) {
                animateValue(responseElement, responseElement.textContent, randomResponseTime);
            }
            
            if (loadElement) {
                animateValue(loadElement, loadElement.textContent, randomLoad);
            }
            
        }, Math.random() * 1000);
    }

    window.refreshServerStatus = function(serverId) {
        const card = document.querySelector(`[data-server="${serverId}"]`);
        if (!card) return;
        
        // Анімація оновлення
        card.style.transform = 'scale(0.98)';
        setTimeout(() => {
            card.style.transform = '';
        }, 150);
        
        updateServerStatus(serverId, card);
        showNotification(`Статус сервера ${serverId} оновлено`, 'info', 3000);
    };

    /**
     * Годинник та статус офісу
     */
    function initClock() {
        updateClock();
        clockInterval = setInterval(updateClock, 1000);
    }

    function updateClock() {
        const timeElement = document.querySelector('.current-time .time');
        const dateElement = document.querySelector('.current-time .date');
        const statusElement = document.getElementById('officeStatus');
        
        if (!timeElement || !dateElement) return;
        
        const now = new Date();
        const ukraineTime = new Date(now.toLocaleString("en-US", {timeZone: "Europe/Kiev"}));
        
        // Час
        const timeString = ukraineTime.toLocaleTimeString('uk-UA', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        // Дата
        const dateString = ukraineTime.toLocaleDateString('uk-UA', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        timeElement.textContent = timeString;
        dateElement.textContent = dateString;
        
        // Статус офісу
        if (statusElement) {
            updateOfficeStatus(ukraineTime, statusElement);
        }
    }

    function updateOfficeStatus(time, statusElement) {
        const hour = time.getHours();
        const day = time.getDay(); // 0 = неділя, 6 = субота
        
        let isOpen = false;
        let statusText = '';
        
        if (day >= 1 && day <= 5) { // Пн-Пт
            isOpen = hour >= 9 && hour < 18;
            statusText = isOpen ? 'Офіс відкритий' : 'Офіс закритий';
        } else if (day === 6 || day === 0) { // Сб-Нд
            isOpen = hour >= 10 && hour < 16;
            statusText = isOpen ? 'Офіс відкритий (вихідний)' : 'Офіс закритий (вихідний)';
        }
        
        statusElement.className = `office-status ${isOpen ? 'open' : 'closed'}`;
        statusElement.querySelector('.status-text').textContent = statusText;
    }

    /**
     * Функціональність карти
     */
    function initMapFunctionality() {
        const mapContainer = document.getElementById('mapContainer');
        if (!mapContainer) return;
        
        // Lazy loading карти при кліку
        mapContainer.addEventListener('click', loadMap);
    }

    window.loadMap = function() {
        if (mapLoaded) return;
        
        const mapContainer = document.getElementById('mapContainer');
        if (!mapContainer) return;
        
        // Показуємо завантаження
        mapContainer.innerHTML = `
            <div class="map-loading">
                <div style="text-align: center; padding: 50px; color: #6b7280;">
                    <div style="font-size: 2rem; margin-bottom: 20px;">🗺️</div>
                    <div>Завантаження карти...</div>
                </div>
            </div>
        `;
        
        // Симуляція завантаження карти
        setTimeout(() => {
            mapContainer.innerHTML = `
                <div class="map-embed">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2645.123456789!2d35.046127!3d48.464717!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDjCsDI3JzUzLjAiTiAzNcKwMDInNDYuMCJF!5e0!3m2!1suk!2sua!4v1234567890123!5m2!1suk!2sua"
                        width="100%" 
                        height="300" 
                        style="border:0; border-radius: 8px;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            `;
            mapLoaded = true;
            showNotification('Карту завантажено', 'success', 3000);
        }, 1500);
    };

    window.openMap = function() {
        const address = encodeURIComponent('м. Дніпро, пл. Академика Стародубова 1');
        const url = `https://www.google.com/maps/search/${address}`;
        window.open(url, '_blank');
    };

    /**
     * Месенджери
     */
    function initMessengers() {
        // WhatsApp
        window.openWhatsApp = function() {
            const phone = '380671234567';
            const message = encodeURIComponent('Привіт! У мене питання щодо ваших послуг.');
            const url = `https://wa.me/${phone}?text=${message}`;
            window.open(url, '_blank');
        };
        
        // Живий чат
        window.startLiveChat = function() {
            // Тут має бути інтеграція з чат-системою
            showNotification('Функція живого чату буде доступна незабаром', 'info');
        };
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
        
        const animatedElements = document.querySelectorAll(`
            .method-card, 
            .status-card, 
            .contact-form-wrapper,
            .map-wrapper,
            .current-time-widget
        `);
        
        animatedElements.forEach(el => {
            scrollObserver.observe(el);
        });
    }

    function handleScrollAnimation(entries) {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('animate-in');
                    scrollObserver.unobserve(entry.target);
                }, index * 100);
            }
        });
    }

    /**
     * Клавіатурні комбінації
     */
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Ctrl+M - відкрити карту
            if (e.ctrlKey && e.key === 'm') {
                e.preventDefault();
                loadMap();
            }
            
            // Ctrl+Enter - відправити форму
            if (e.ctrlKey && e.key === 'Enter') {
                const form = document.getElementById('contactForm');
                if (form && document.activeElement.closest('form') === form) {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            }
            
            // ESC - закрити сповіщення
            if (e.key === 'Escape') {
                hideAllNotifications();
            }
        });
    }

    /**
     * Утилітарні функції
     */
    function animateValue(element, start, end) {
        const startNum = parseFloat(start) || 0;
        const endNum = parseFloat(end) || 0;
        const duration = 1000;
        const startTime = performance.now();
        
        function updateValue(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = startNum + (endNum - startNum) * progress;
            const suffix = end.replace(/[\d.]/g, '');
            
            element.textContent = currentValue.toFixed(1) + suffix;
            
            if (progress < 1) {
                requestAnimationFrame(updateValue);
            }
        }
        
        requestAnimationFrame(updateValue);
    }

    function trackContactFormSubmission(data) {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'contact_form_submitted', {
                event_category: 'Contact',
                event_label: data.department || 'general',
                value: 1
            });
        }
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhone(phone) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        return phoneRegex.test(phone);
    }

    function showFieldError(field, message) {
        clearFieldError(field);
        
        field.classList.add('error');
        field.setAttribute('aria-invalid', 'true');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.innerHTML = `⚠ ${message}`;
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

    function showLoadingState(button) {
        button.classList.add('loading');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        
        const originalText = button.innerHTML;
        button.dataset.originalText = originalText;
        button.innerHTML = '<span>Відправка...</span>';
    }

    function hideLoadingState(button) {
        button.classList.remove('loading');
        button.disabled = false;
        button.removeAttribute('aria-busy');
        
        const originalText = button.dataset.originalText;
        if (originalText) {
            button.innerHTML = originalText;
        }
    }

    function showNotification(message, type = 'info', duration = 5000) {
        if (!notificationContainer) {
            createNotificationContainer();
        }
        
        const notification = createNotificationElement(message, type);
        notificationContainer.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        const hideTimeout = setTimeout(() => {
            hideNotification(notification);
        }, duration);
        
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
        notification.setAttribute('role', 'alert');
        
        const colors = {
            'success': '#10b981',
            'error': '#ef4444',
            'warning': '#f59e0b',
            'info': '#3b82f6'
        };
        
        const icons = {
            'success': '✓',
            'error': '⚠',
            'warning': '⚠',
            'info': 'ℹ'
        };
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="color: ${colors[type]}; font-weight: bold; font-size: 1.2rem;">
                    ${icons[type]}
                </div>
                <span style="flex: 1; color: #111827;">${escapeHtml(message)}</span>
                <button class="notification-close" style="
                    background: none; 
                    border: none; 
                    cursor: pointer; 
                    color: #6b7280;
                    font-size: 1.2rem;
                    padding: 0;
                    width: 20px;
                    height: 20px;
                ">×</button>
            </div>
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

    function hideAllNotifications() {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(hideNotification);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Публічний API
     */
    const ContactsPageAPI = {
        showNotification,
        validateField,
        updateServerStatus,
        loadMap,
        openMap,
        isValidEmail,
        isValidPhone,
        isInitialized: () => isInitialized
    };

    // Експортуємо API в глобальну область
    window.ContactsPage = ContactsPageAPI;

    // Очищення при виході зі сторінки
    window.addEventListener('beforeunload', function() {
        if (scrollObserver) {
            scrollObserver.disconnect();
        }
        
        if (clockInterval) {
            clearInterval(clockInterval);
        }
        
        hideAllNotifications();
    });

})();

/* Кінець файлу /assets/js/contacts.js */