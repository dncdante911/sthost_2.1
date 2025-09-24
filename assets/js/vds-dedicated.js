/**
 * VDS Dedicated Servers Page JavaScript
 * File: /assets/js/pages/vds-dedicated.js
 * Version: 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    initDedicatedPage();
});

function initDedicatedPage() {
    // Инициализация всех функций
    initServerInteractions();
    initOrderModal();
    initAnimations();
    initScrollEffects();
    initFormValidation();
    
    console.log('Dedicated servers page initialized');
}

// Интерактивность серверов
function initServerInteractions() {
    // 3D сервер анимации
    const serverView = document.querySelector('.server-3d-view');
    if (serverView) {
        const serverFront = serverView.querySelector('.server-front');
        
        serverView.addEventListener('mouseenter', function() {
            serverFront.style.transform = 'rotateY(-25deg) rotateX(10deg) scale(1.05)';
        });
        
        serverView.addEventListener('mouseleave', function() {
            serverFront.style.transform = 'rotateY(-15deg) rotateX(5deg) scale(1)';
        });
    }
    
    // Анимация кнопки питания
    const powerButton = document.querySelector('.power-button');
    if (powerButton) {
        powerButton.addEventListener('click', function() {
            this.style.background = '#22c55e';
            this.style.borderColor = '#16a34a';
            this.style.boxShadow = '0 0 15px rgba(34, 197, 94, 0.6)';
            
            // Анимация включения дисков
            const drives = document.querySelectorAll('.drive:not(.active)');
            drives.forEach((drive, index) => {
                setTimeout(() => {
                    drive.classList.add('active');
                }, index * 200);
            });
            
            setTimeout(() => {
                this.style.background = '#ef4444';
                this.style.borderColor = '#dc2626';
                this.style.boxShadow = 'none';
            }, 3000);
        });
    }
    
    // Hover эффекты для карточек серверов
    const serverCards = document.querySelectorAll('.server-card');
    serverCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 15px 40px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.08)';
        });
    });
}

// Модальное окно заказа
function initOrderModal() {
    const orderModal = document.getElementById('orderModal');
    const orderButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#orderModal"]');
    
    if (!orderModal || orderButtons.length === 0) return;
    
    orderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const serverId = this.dataset.serverId;
            const serverName = this.dataset.serverName;
            const serverPrice = parseInt(this.dataset.serverPrice);
            const setupFee = parseInt(this.dataset.setupFee || 0);
            
            updateOrderModal(serverId, serverName, serverPrice, setupFee);
        });
    });
    
    // Обработка формы заказа
    const orderForm = document.getElementById('serverOrderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleOrderSubmit(this);
        });
    }
}

function updateOrderModal(serverId, serverName, serverPrice, setupFee) {
    // Обновляем информацию о сервере
    const serverInfo = document.getElementById('selectedServerInfo');
    if (serverInfo) {
        serverInfo.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">${serverName}</h6>
                    <small class="text-muted">ID: ${serverId}</small>
                </div>
                <div class="text-end">
                    <div class="h6 mb-0">${formatPrice(serverPrice)} ₴/мес</div>
                    ${setupFee > 0 ? `<small class="text-danger">Активация: ${formatPrice(setupFee)} ₴</small>` : ''}
                </div>
            </div>
        `;
    }
    
    // Обновляем цены
    updatePrices(serverPrice, setupFee);
    
    // Сохраняем данные в форму
    const form = document.getElementById('serverOrderForm');
    if (form) {
        // Добавляем скрытые поля
        let hiddenServerId = form.querySelector('input[name="server_id"]');
        if (!hiddenServerId) {
            hiddenServerId = document.createElement('input');
            hiddenServerId.type = 'hidden';
            hiddenServerId.name = 'server_id';
            form.appendChild(hiddenServerId);
        }
        hiddenServerId.value = serverId;
        
        let hiddenServerPrice = form.querySelector('input[name="server_price"]');
        if (!hiddenServerPrice) {
            hiddenServerPrice = document.createElement('input');
            hiddenServerPrice.type = 'hidden';
            hiddenServerPrice.name = 'server_price';
            form.appendChild(hiddenServerPrice);
        }
        hiddenServerPrice.value = serverPrice;
        
        let hiddenSetupFee = form.querySelector('input[name="setup_fee"]');
        if (!hiddenSetupFee) {
            hiddenSetupFee = document.createElement('input');
            hiddenSetupFee.type = 'hidden';
            hiddenSetupFee.name = 'setup_fee';
            form.appendChild(hiddenSetupFee);
        }
        hiddenSetupFee.value = setupFee;
    }
}

function updatePrices(monthlyPrice, setupFee) {
    const monthlyPriceEl = document.getElementById('monthlyPrice');
    const setupFeeEl = document.getElementById('setupFeePrice');
    const setupFeeRow = document.getElementById('setupFeeRow');
    const totalPriceEl = document.getElementById('totalPrice');
    
    if (monthlyPriceEl) {
        monthlyPriceEl.textContent = formatPrice(monthlyPrice) + ' ₴';
    }
    
    if (setupFeeEl && setupFeeRow) {
        if (setupFee > 0) {
            setupFeeEl.textContent = formatPrice(setupFee) + ' ₴';
            setupFeeRow.style.display = 'flex';
        } else {
            setupFeeRow.style.display = 'none';
        }
    }
    
    if (totalPriceEl) {
        const total = monthlyPrice + setupFee;
        totalPriceEl.textContent = formatPrice(total) + ' ₴';
    }
}

function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

// Обработка отправки формы заказа
function handleOrderSubmit(form) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Показываем состояние загрузки
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Обработка...';
    submitButton.disabled = true;
    
    // Имитация отправки (замените на реальный AJAX запрос)
    setTimeout(() => {
        // Симуляция успешной отправки
        showToast('Заказ успешно отправлен! Мы свяжемся с вами в ближайшее время.', 'success');
        
        // Закрываем модальное окно
        const modal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
        modal.hide();
        
        // Сбрасываем форму
        form.reset();
        
        // Восстанавливаем кнопку
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        
    }, 2000);
    
    // Реальный AJAX запрос (раскомментируйте и настройте)
    /*
    fetch('/api/order-server.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Заказ успешно отправлен!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
            modal.hide();
            form.reset();
        } else {
            showToast(data.message || 'Произошла ошибка при отправке заказа', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Произошла ошибка при отправке заказа', 'error');
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
    */
}

// Анимации при скролле
function initScrollEffects() {
    // Intersection Observer для анимаций
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Наблюдаем за элементами
    const elementsToAnimate = document.querySelectorAll(
        '.server-card, .feature-card, .use-case-card, .dc-feature'
    );
    
    elementsToAnimate.forEach(el => {
        observer.observe(el);
    });
    
    // Параллакс эффект для hero секции
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.dedicated-hero');
        if (hero) {
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    });
}

// Общие анимации
function initAnimations() {
    // Добавляем CSS классы для анимаций
    const style = document.createElement('style');
    style.textContent = `
        .animate-in {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .server-card,
        .feature-card,
        .use-case-card,
        .dc-feature {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
    `;
    document.head.appendChild(style);
}

// Валидация формы
function initFormValidation() {
    const form = document.getElementById('serverOrderForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
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
            showToast('Пожалуйста, заполните все обязательные поля корректно', 'error');
        }
    });
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Проверка обязательных полей
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Это поле обязательно для заполнения';
    }
    
    // Проверка email
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Введите корректный email адрес';
        }
    }
    
    // Проверка телефона
    if (field.type === 'tel' && value) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            isValid = false;
            errorMessage = 'Введите корректный номер телефона';
        }
    }
    
    // Проверка чекбокса согласия
    if (field.type === 'checkbox' && field.hasAttribute('required')) {
        if (!field.checked) {
            isValid = false;
            errorMessage = 'Необходимо согласиться с условиями';
        }
    }
    
    // Применяем стили валидации
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        removeFieldError(field);
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    removeFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function removeFieldError(field) {
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
}

// Toast уведомления
function showToast(message, type = 'info') {
    // Создаем контейнер для toast, если его нет
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1060';
        document.body.appendChild(toastContainer);
    }
    
    // Определяем цвета для разных типов
    const typeColors = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    };
    
    // Создаем toast
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white ${typeColors[type] || typeColors.info} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toastEl);
    
    // Показываем toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: 5000
    });
    toast.show();
    
    // Удаляем элемент после скрытия
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

// Дополнительные утилиты
function smoothScrollTo(target) {
    const element = document.querySelector(target);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Обработка кликов по кнопкам "Обрать сервер"
document.addEventListener('click', function(e) {
    if (e.target.matches('a[href="#servers"]')) {
        e.preventDefault();
        smoothScrollTo('#servers');
    }
});

// Обработка изменения операционной системы
document.addEventListener('change', function(e) {
    if (e.target.matches('select[name="os"]')) {
        const selectedOS = e.target.value;
        console.log('Selected OS:', selectedOS);
        
        // Здесь можно добавить логику изменения цены в зависимости от ОС
        // Например, Windows Server может стоить дороже
        if (selectedOS.includes('windows')) {
            showToast('Windows Server требует дополнительной лицензии (+500₴/мес)', 'warning');
        }
    }
});

// Экспорт функций для использования в других скриптах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showToast,
        formatPrice,
        smoothScrollTo
    };
}