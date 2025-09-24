/**
 * Скарги і пропозиції - JavaScript функціональність
 * /assets/js/complaints.js
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
            initializeComplaintsPage();
            isInitialized = true;
        } catch (error) {
            console.error('Complaints Page Initialization Error:', error);
        }
    });

    /**
     * Головна функція ініціалізації
     */
    function initializeComplaintsPage() {
        createNotificationContainer();
        initTypeCards();
        initComplaintForm();
        initFormValidation();
        initScrollAnimations();
        initCharacterCounter();
        initAutoSave();
        initKeyboardNavigation();
        
        console.log('Complaints Page initialized successfully');
    }

    /**
     * Створення контейнера для сповіщень
     */
    function createNotificationContainer() {
        if (document.getElementById('complaints-notifications')) return;
        
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'complaints-notifications';
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
     * Ініціалізація карточок типів звернень
     */
    function initTypeCards() {
        const typeCards = document.querySelectorAll('.type-card');
        const complaintTypeSelect = document.getElementById('complaint_type');
        
        typeCards.forEach(card => {
            card.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                
                // Оновлюємо select
                if (complaintTypeSelect && type) {
                    complaintTypeSelect.value = type;
                    complaintTypeSelect.dispatchEvent(new Event('change'));
                }
                
                // Прокручуємо до форми
                const form = document.querySelector('.complaint-form-section');
                if (form) {
                    form.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }
                
                // Анімація вибору
                typeCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                
                // Показуємо сповіщення
                const typeName = this.querySelector('.type-title').textContent;
                showNotification(`Обрано тип звернення: ${typeName}`, 'success');
                
                // Фокусуємо на формі
                setTimeout(() => {
                    const firstInput = document.querySelector('#name');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 500);
            });
            
            // Додаємо hover ефекти
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = '';
                }
            });
        });
    }

    /**
     * Ініціалізація форми скарг
     */
    function initComplaintForm() {
        const form = document.getElementById('complaintForm');
        if (!form) return;
        
        // Обробка відправки форми
        form.addEventListener('submit', handleFormSubmit);
        
        // Обробка зміни типу скарги
        const typeSelect = document.getElementById('complaint_type');
        if (typeSelect) {
            typeSelect.addEventListener('change', handleTypeChange);
        }
        
        // Обробка пріоритету
        const prioritySelect = document.getElementById('priority');
        if (prioritySelect) {
            prioritySelect.addEventListener('change', handlePriorityChange);
        }
        
        // Кнопка очищення
        const resetButton = form.querySelector('button[type="reset"]');
        if (resetButton) {
            resetButton.addEventListener('click', handleFormReset);
        }
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Валідація форми
        if (!validateComplaintForm(form)) {
            showNotification('Будь ласка, виправте помилки у формі', 'error');
            return;
        }
        
        // Показуємо стан завантаження
        showLoadingState(submitButton);
        
        // Збираємо дані форми
        const formData = new FormData(form);
        const complaintData = Object.fromEntries(formData.entries());
        
        // Відправляємо форму
        submitComplaint(complaintData, form, submitButton);
    }

    function submitComplaint(data, form, button) {
        // Симуляція відправки (замінити на реальний AJAX)
        setTimeout(() => {
            try {
                // Генеруємо номер звернення
                const complaintId = generateComplaintId();
                
                // Очищуємо автозбереження
                clearAutoSave();
                
                // Показуємо успіх
                showNotification(
                    `Дякуємо за звернення! Номер: #${complaintId}. Ми зв'яжемося з вами протягом 24 годин.`, 
                    'success', 
                    7000
                );
                
                // Очищуємо форму
                form.reset();
                clearAllFieldErrors(form);
                
                // Відправляємо аналітику
                trackComplaintSubmission(data);
                
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

    function handleTypeChange(e) {
        const type = e.target.value;
        const prioritySelect = document.getElementById('priority');
        
        // Автоматично встановлюємо пріоритет залежно від типу
        if (prioritySelect) {
            switch (type) {
                case 'complaint':
                    prioritySelect.value = 'high';
                    break;
                case 'question':
                    prioritySelect.value = 'normal';
                    break;
                case 'suggestion':
                case 'feedback':
                    prioritySelect.value = 'low';
                    break;
            }
        }
        
        // Оновлюємо placeholder повідомлення
        updateMessagePlaceholder(type);
    }

    function handlePriorityChange(e) {
        const priority = e.target.value;
        const form = e.target.closest('form');
        
        // Візуальні індикатори пріоритету
        form.classList.remove('priority-low', 'priority-normal', 'priority-high', 'priority-urgent');
        form.classList.add(`priority-${priority}`);
        
        // Показуємо інформацію про час обробки
        showPriorityInfo(priority);
    }

    function handleFormReset(e) {
        e.preventDefault();
        
        if (confirm('Ви впевнені, що хочете очистити всі поля форми?')) {
            const form = e.target.closest('form');
            form.reset();
            clearAllFieldErrors(form);
            clearAutoSave();
            showNotification('Форму очищено', 'info');
        }
    }

    /**
     * Валідація форми скарг
     */
    function initFormValidation() {
        const form = document.getElementById('complaintForm');
        if (!form) return;
        
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                clearFieldError(input);
                // Автозбереження при введенні
                debounce(saveFormData, 1000)();
            });
        });
    }

    function validateComplaintForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        // Додаткові перевірки
        const message = form.querySelector('#message');
        if (message && message.value.trim().length < 20) {
            showFieldError(message, 'Повідомлення має містити принаймні 20 символів');
            isValid = false;
        }
        
        return isValid;
    }

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
            }
            
            // Перевірка довжини для specific полів
            if (fieldName === 'name' && value.length < 2) {
                isValid = false;
                errorMessage = 'Ім\'я має містити принаймні 2 символи';
            }
            
            if (fieldName === 'subject' && value.length < 5) {
                isValid = false;
                errorMessage = 'Тема має містити принаймні 5 символів';
            }
            
            if (fieldName === 'message' && value.length < 20) {
                isValid = false;
                errorMessage = 'Повідомлення має містити принаймні 20 символів';
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

    /**
     * Лічильник символів
     */
    function initCharacterCounter() {
        const messageField = document.getElementById('message');
        if (!messageField) return;
        
        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.style.cssText = `
            text-align: right;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 5px;
        `;
        
        messageField.parentNode.appendChild(counter);
        
        function updateCounter() {
            const length = messageField.value.length;
            const minLength = 20;
            const maxLength = 2000;
            
            counter.textContent = `${length}/${maxLength} символів`;
            
            if (length < minLength) {
                counter.style.color = '#ef4444';
                counter.textContent += ` (мінімум ${minLength})`;
            } else if (length > maxLength) {
                counter.style.color = '#ef4444';
                counter.textContent = `Перевищено ліміт на ${length - maxLength} символів`;
            } else {
                counter.style.color = '#10b981';
            }
        }
        
        messageField.addEventListener('input', updateCounter);
        updateCounter();
    }

    /**
     * Автозбереження форми
     */
    function initAutoSave() {
        const form = document.getElementById('complaintForm');
        if (!form) return;
        
        // Завантажуємо збережені дані
        loadFormData();
        
        // Показуємо індикатор автозбереження
        createAutoSaveIndicator();
    }

    function saveFormData() {
        const form = document.getElementById('complaintForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Видаляємо службові поля
        delete data.csrf_token;
        delete data.submit_complaint;
        
        try {
            localStorage.setItem('complaints_form_data', JSON.stringify({
                data: data,
                timestamp: Date.now()
            }));
            
            showAutoSaveIndicator('Збережено');
        } catch (error) {
            console.error('Auto-save error:', error);
        }
    }

    function loadFormData() {
        try {
            const saved = localStorage.getItem('complaints_form_data');
            if (!saved) return;
            
            const { data, timestamp } = JSON.parse(saved);
            
            // Перевіряємо, чи дані не застарілі (24 години)
            if (Date.now() - timestamp > 24 * 60 * 60 * 1000) {
                localStorage.removeItem('complaints_form_data');
                return;
            }
            
            // Заповнюємо форму
            const form = document.getElementById('complaintForm');
            if (!form) return;
            
            Object.entries(data).forEach(([name, value]) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field && value) {
                    field.value = value;
                }
            });
            
            showNotification('Відновлено збережені дані форми', 'info');
            
        } catch (error) {
            console.error('Load form data error:', error);
            localStorage.removeItem('complaints_form_data');
        }
    }

    function clearAutoSave() {
        localStorage.removeItem('complaints_form_data');
    }

    function createAutoSaveIndicator() {
        const form = document.getElementById('complaintForm');
        if (!form) return;
        
        const indicator = document.createElement('div');
        indicator.id = 'autosave-indicator';
        indicator.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
        `;
        
        document.body.appendChild(indicator);
    }

    function showAutoSaveIndicator(text) {
        const indicator = document.getElementById('autosave-indicator');
        if (!indicator) return;
        
        indicator.textContent = text;
        indicator.style.opacity = '1';
        
        setTimeout(() => {
            indicator.style.opacity = '0';
        }, 2000);
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
            .type-card, 
            .step-item, 
            .contact-option,
            .quality-card
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
     * Навігація з клавіатури
     */
    function initKeyboardNavigation() {
        // ESC для закриття сповіщень
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideAllNotifications();
            }
        });
        
        // Ctrl+S для збереження форми
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveFormData();
                showNotification('Форму збережено', 'info');
            }
        });
    }

    /**
     * Допоміжні функції
     */
    function updateMessagePlaceholder(type) {
        const messageField = document.getElementById('message');
        if (!messageField) return;
        
        const placeholders = {
            'complaint': 'Детально опишіть проблему: що саме сталося, коли, які дії ви вживали...',
            'suggestion': 'Опишіть вашу ідею або пропозицію щодо покращення наших послуг...',
            'feedback': 'Поділіться вашими враженнями від роботи з нашою компанією...',
            'question': 'Сформулюйте ваше питання максимально детально...'
        };
        
        messageField.placeholder = placeholders[type] || messageField.placeholder;
    }

    function showPriorityInfo(priority) {
        const infoTexts = {
            'low': 'Звернення буде розглянуто протягом 7 робочих днів',
            'normal': 'Звернення буде розглянуто протягом 24 годин',
            'high': 'Звернення буде розглянуто протягом 4 годин',
            'urgent': 'Звернення буде розглянуто протягом 1 години'
        };
        
        const text = infoTexts[priority];
        if (text) {
            showNotification(text, 'info', 3000);
        }
    }

    function generateComplaintId() {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substr(2, 3).toUpperCase();
        return timestamp + random;
    }

    function trackComplaintSubmission(data) {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'complaint_submitted', {
                event_category: 'Complaints',
                event_label: data.complaint_type || 'unknown',
                value: 1
            });
        }
    }

    // Utility functions
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhone(phone) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        return phoneRegex.test(phone);
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
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
        notification.style.cssText = `
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border: 1px solid #e5e7eb;
            padding: 16px 20px;
            margin-bottom: 10px;
            min-width: 300px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            pointer-events: auto;
            position: relative;
        `;
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
                <span style="flex: 1; color: #1f2937;">${escapeHtml(message)}</span>
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
        
        notification.classList.add('show');
        return notification;
    }

    function hideNotification(notification) {
        notification.style.transform = 'translateX(400px)';
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
    const ComplaintsPageAPI = {
        showNotification,
        validateField,
        saveFormData,
        loadFormData,
        clearAutoSave,
        isValidEmail,
        isValidPhone,
        isInitialized: () => isInitialized
    };

    // Експортуємо API в глобальну область
    window.ComplaintsPage = ComplaintsPageAPI;

    // Очищення при виході зі сторінки
    window.addEventListener('beforeunload', function() {
        if (scrollObserver) {
            scrollObserver.disconnect();
        }
        
        // Збереження форми перед виходом
        saveFormData();
        
        // Очищення сповіщень
        hideAllNotifications();
    });

})();

/* Кінець файлу /assets/js/complaints.js */