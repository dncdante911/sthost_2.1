/**
 * SSL Сертифікати - JavaScript функціональність
 */

document.addEventListener('DOMContentLoaded', function() {
    // Ініціалізація всіх компонентів
    initFAQ();
    initOrderModals();
    initFormValidation();
    initSmoothScrolling();
    initAnimations();
    initTooltips();
});

/**
 * FAQ Секція
 */
function initFAQ() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const faqId = this.getAttribute('data-faq');
            const answer = document.getElementById(`faq-${faqId}`);
            const isActive = answer.classList.contains('active');
            
            // Закриваємо всі інші відкриті FAQ
            document.querySelectorAll('.faq-answer.active').forEach(activeAnswer => {
                activeAnswer.classList.remove('active');
            });
            
            document.querySelectorAll('.faq-question.active').forEach(activeQuestion => {
                activeQuestion.classList.remove('active');
            });
            
            // Відкриваємо/закриваємо поточний FAQ
            if (!isActive) {
                answer.classList.add('active');
                this.classList.add('active');
            }
        });
    });
}

/**
 * Модальні вікна замовлення
 */
function initOrderModals() {
    const orderButtons = document.querySelectorAll('.order-ssl-btn');
    const modal = document.getElementById('orderModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const modalClose = document.getElementById('modalClose');
    const modalCancel = document.getElementById('modalCancel');
    
    if (!modal) return;
    
    // Відкриття модального вікна
    orderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sslType = this.getAttribute('data-ssl-type');
            const sslName = this.getAttribute('data-ssl-name');
            const sslPrice = this.getAttribute('data-ssl-price');
            
            // Заповнення інформації про обраний SSL
            document.getElementById('selectedSslName').textContent = sslName;
            document.getElementById('selectedSslPrice').textContent = formatPrice(sslPrice);
            document.getElementById('modalSslType').value = sslType;
            
            // Відкриття модального вікна
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Фокус на першому полі
            setTimeout(() => {
                document.getElementById('modalDomain').focus();
            }, 300);
        });
    });
    
    // Закриття модального вікна
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Очищення форми
        document.getElementById('modalOrderForm').reset();
    }
    
    modalClose.addEventListener('click', closeModal);
    modalCancel.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);
    
    // Закриття по ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
    
    // Обробка форми замовлення в модальному вікні
    const modalForm = document.getElementById('modalOrderForm');
    if (modalForm) {
        modalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Показуємо індикатор завантаження
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="icon-loader"></i> Відправка...';
            submitButton.disabled = true;
            
            // Імітація відправки (замініть на реальний AJAX запит)
            setTimeout(() => {
                // Успішне замовлення
                showNotification('Замовлення прийнято! Ми зв\'яжемося з вами найближчим часом.', 'success');
                closeModal();
                
                // Повертаємо кнопку в початковий стан
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                
                // Відправка даних на сервер (розкоментуйте для реального використання)
                /*
                fetch('/api/ssl-order', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Замовлення прийнято!', 'success');
                        closeModal();
                    } else {
                        showNotification('Помилка при відправці замовлення', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Помилка мережі', 'error');
                })
                .finally(() => {
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                });
                */
            }, 2000);
        });
    }
}

/**
 * Валідація форм
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.ssl-order-form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Будь ласка, виправте помилки у формі', 'error');
            }
        });
    });
}

/**
 * Валідація окремого поля
 */
function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const fieldName = field.name;
    let isValid = true;
    let errorMessage = '';
    
    // Обов'язкові поля
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Це поле є обов\'язковим';
    }
    
    // Специфічні валідації
    if (value && isValid) {
        switch (fieldType) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Введіть коректний email';
                }
                break;
                
            case 'tel':
                const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
                if (value && !phoneRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Введіть коректний номер телефону';
                }
                break;
                
            case 'text':
                if (fieldName === 'domain') {
                    const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/;
                    if (!domainRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Введіть коректне доменне ім\'я (наприклад: example.com)';
                    }
                }
                break;
        }
    }
    
    // Відображення помилки
    if (!isValid) {
        showFieldError(field, errorMessage);
    } else {
        clearFieldError(field);
    }
    
    return isValid;
}

/**
 * Показ помилки поля
 */
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    
    field.parentNode.appendChild(errorElement);
}

/**
 * Очищення помилки поля
 */
function clearFieldError(field) {
    field.classList.remove('error');
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Плавний скролінг
 */
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                
                const offsetTop = target.getBoundingClientRect().top + window.pageYOffset - 100;
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
                
                // Оновлення URL без перезавантаження
                history.pushState(null, null, href);
            }
        });
    });
}

/**
 * Анімації при скролінгу
 */
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                
                // Анімація чисел
                if (entry.target.classList.contains('indicator-number')) {
                    animateNumber(entry.target);
                }
                
                // Анімація прогрес-барів (якщо є)
                if (entry.target.classList.contains('progress-bar')) {
                    animateProgressBar(entry.target);
                }
            }
        });
    }, observerOptions);
    
    // Спостереження за елементами
    const animatedElements = document.querySelectorAll(
        '.benefit-card, .package-card, .timeline-item, .indicator-item, .faq-item'
    );
    
    animatedElements.forEach(el => {
        observer.observe(el);
    });
}

/**
 * Анімація чисел
 */
function animateNumber(element) {
    const finalNumber = element.textContent.replace(/[^\d]/g, '');
    const duration = 2000; // 2 секунди
    const increment = finalNumber / (duration / 16); // 60 FPS
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        
        if (current >= finalNumber) {
            current = finalNumber;
            clearInterval(timer);
        }
        
        element.textContent = element.textContent.replace(/[\d,]+/, Math.floor(current).toLocaleString());
    }, 16);
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
 * Тултипи
 */
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

/**
 * Показ тултипу
 */
function showTooltip(element) {
    const text = element.getAttribute('data-tooltip');
    
    if (!text) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltipRect.width / 2) + 'px';
    tooltip.style.top = rect.top - tooltipRect.height - 10 + 'px';
    
    setTimeout(() => {
        tooltip.classList.add('show');
    }, 10);
}

/**
 * Приховування тултипу
 */
function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.classList.remove('show');
        setTimeout(() => {
            tooltip.remove();
        }, 200);
    }
}

/**
 * Показ сповіщень
 */
function showNotification(message, type = 'info') {
    // Видалення існуючих сповіщень
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icon = getNotificationIcon(type);
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="${icon}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">
            <i class="icon-x"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Показ з анімацією
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Автоматичне приховування через 5 секунд
    setTimeout(() => {
        hideNotification(notification);
    }, 5000);
    
    // Закриття по кліку
    notification.querySelector('.notification-close').addEventListener('click', () => {
        hideNotification(notification);
    });
}

/**
 * Приховування сповіщення
 */
function hideNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 300);
}

/**
 * Іконка для сповіщення
 */
function getNotificationIcon(type) {
    const icons = {
        'success': 'icon-check-circle',
        'error': 'icon-alert-circle',
        'warning': 'icon-alert-triangle',
        'info': 'icon-info'
    };
    
    return icons[type] || icons.info;
}

/**
 * Форматування ціни
 */
function formatPrice(price) {
    return parseInt(price).toLocaleString('uk-UA');
}

/**
 * Копіювання тексту в буфер обміну
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Скопійовано в буфер обміну', 'success');
        });
    } else {
        // Fallback для старих браузерів
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Скопійовано в буфер обміну', 'success');
    }
}

/**
 * Фільтрація SSL пакетів
 */
function filterSSLPackages(filterType) {
    const packages = document.querySelectorAll('.package-card');
    
    packages.forEach(packageCard => {
        const packageType = packageCard.getAttribute('data-ssl-type');
        
        if (filterType === 'all' || packageType === filterType) {
            packageCard.style.display = 'block';
            packageCard.classList.add('animate-in');
        } else {
            packageCard.style.display = 'none';
            packageCard.classList.remove('animate-in');
        }
    });
}

/**
 * Калькулятор вартості SSL
 */
function initSSLCalculator() {
    const calculator = document.getElementById('sslCalculator');
    if (!calculator) return;
    
    const sslTypeSelect = calculator.querySelector('#calcSslType');
    const yearsSelect = calculator.querySelector('#calcYears');
    const quantityInput = calculator.querySelector('#calcQuantity');
    const resultElement = calculator.querySelector('#calcResult');
    
    function calculatePrice() {
        const sslType = sslTypeSelect.value;
        const years = parseInt(yearsSelect.value) || 1;
        const quantity = parseInt(quantityInput.value) || 1;
        
        const prices = {
            'dv': 990,
            'ov': 2490,
            'ev': 7990,
            'wildcard': 4990
        };
        
        const basePrice = prices[sslType] || 0;
        let yearlyDiscount = 1;
        
        // Знижки за кількість років
        if (years >= 3) yearlyDiscount = 0.85; // 15% знижка
        else if (years >= 2) yearlyDiscount = 0.9; // 10% знижка
        
        // Знижки за кількість
        let quantityDiscount = 1;
        if (quantity >= 10) quantityDiscount = 0.8; // 20% знижка
        else if (quantity >= 5) quantityDiscount = 0.9; // 10% знижка
        
        const totalPrice = basePrice * years * quantity * yearlyDiscount * quantityDiscount;
        const savings = (basePrice * years * quantity) - totalPrice;
        
        resultElement.innerHTML = `
            <div class="calc-result-price">${formatPrice(totalPrice)} грн</div>
            ${savings > 0 ? `<div class="calc-result-savings">Економія: ${formatPrice(savings)} грн</div>` : ''}
            <div class="calc-result-details">${quantity} сертифікат(и) на ${years} рік(и)</div>
        `;
    }
    
    [sslTypeSelect, yearsSelect, quantityInput].forEach(element => {
        element.addEventListener('change', calculatePrice);
        element.addEventListener('input', calculatePrice);
    });
    
    // Початковий розрахунок
    calculatePrice();
}

/**
 * Живий чат (заглушка)
 */
function initLiveChat() {
    const chatButton = document.getElementById('liveChatButton');
    if (!chatButton) return;
    
    chatButton.addEventListener('click', function() {
        // Тут можна підключити реальний віджет чату (Intercom, Zendesk, тощо)
        showNotification('Чат тимчасово недоступний. Зв\'яжіться з нами через форму зворотного зв\'язку.', 'info');
    });
}

/**
 * Порівняння SSL сертифікатів
 */
function initSSLComparison() {
    const comparisonButtons = document.querySelectorAll('.add-to-comparison');
    const comparisonPanel = document.getElementById('comparisonPanel');
    const selectedSSLs = new Set();
    
    comparisonButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sslType = this.getAttribute('data-ssl-type');
            
            if (selectedSSLs.has(sslType)) {
                selectedSSLs.delete(sslType);
                this.classList.remove('active');
            } else if (selectedSSLs.size < 3) { // Максимум 3 для порівняння
                selectedSSLs.add(sslType);
                this.classList.add('active');
            } else {
                showNotification('Можна порівнювати максимум 3 сертифікати', 'warning');
                return;
            }
            
            updateComparisonPanel();
        });
    });
    
    function updateComparisonPanel() {
        if (selectedSSLs.size > 0) {
            comparisonPanel.classList.add('show');
            comparisonPanel.innerHTML = `
                <div class="comparison-content">
                    <span>Обрано для порівняння: ${selectedSSLs.size}</span>
                    <button class="btn btn-primary" onclick="showComparison()">Порівняти</button>
                    <button class="btn btn-outline" onclick="clearComparison()">Очистити</button>
                </div>
            `;
        } else {
            comparisonPanel.classList.remove('show');
        }
    }
    
    // Глобальні функції для кнопок
    window.showComparison = function() {
        const comparisonData = Array.from(selectedSSLs);
        // Тут можна відкрити модальне вікно з детальним порівнянням
        console.log('Порівняння SSL:', comparisonData);
        showNotification('Функція порівняння в розробці', 'info');
    };
    
    window.clearComparison = function() {
        selectedSSLs.clear();
        comparisonButtons.forEach(btn => btn.classList.remove('active'));
        updateComparisonPanel();
    };
}

/**
 * Лічильник зворотного відліку для акцій
 */
function initCountdownTimer() {
    const timerElement = document.getElementById('countdownTimer');
    if (!timerElement) return;
    
    const endDate = new Date(timerElement.getAttribute('data-end-date'));
    
    function updateTimer() {
        const now = new Date();
        const timeLeft = endDate - now;
        
        if (timeLeft <= 0) {
            timerElement.innerHTML = '<span class="timer-expired">Акція завершена</span>';
            return;
        }
        
        const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
        
        timerElement.innerHTML = `
            <div class="timer-block">
                <span class="timer-number">${days}</span>
                <span class="timer-label">днів</span>
            </div>
            <div class="timer-block">
                <span class="timer-number">${hours.toString().padStart(2, '0')}</span>
                <span class="timer-label">годин</span>
            </div>
            <div class="timer-block">
                <span class="timer-number">${minutes.toString().padStart(2, '0')}</span>
                <span class="timer-label">хвилин</span>
            </div>
            <div class="timer-block">
                <span class="timer-number">${seconds.toString().padStart(2, '0')}</span>
                <span class="timer-label">секунд</span>
            </div>
        `;
    }
    
    updateTimer();
    setInterval(updateTimer, 1000);
}

/**
 * Ініціалізація всіх додаткових компонентів
 */
function initAdditionalComponents() {
    initSSLCalculator();
    initLiveChat();
    initSSLComparison();
    initCountdownTimer();
}

// Ініціалізація додаткових компонентів після завантаження DOM
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initAdditionalComponents, 100);
});

/**
 * Обробка помилок JavaScript
 */
window.addEventListener('error', function(event) {
    console.error('JavaScript Error:', event.error);
    // Можна відправити помилку на сервер для логування
});

/**
 * Експорт функцій для глобального використання
 */
window.SSLPage = {
    showNotification,
    copyToClipboard,
    filterSSLPackages,
    formatPrice
};