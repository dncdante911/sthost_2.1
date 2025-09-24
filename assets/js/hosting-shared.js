// hosting-shared.js - Скрипти для сторінки віртуального хостингу

document.addEventListener('DOMContentLoaded', function() {
    // Ініціалізація
    initBillingToggle();
    initOrderButtons();
    initOrderForm();
    initPriceCalculator();
    initAnimations();
});

// Перемикач тарифів (місячний/річний)
function initBillingToggle() {
    const billingToggle = document.querySelectorAll('input[name="billing"]');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const yearlyPrices = document.querySelectorAll('.yearly-price');
    
    billingToggle.forEach(toggle => {
        toggle.addEventListener('change', function() {
            if (this.id === 'yearly') {
                monthlyPrices.forEach(price => {
                    price.style.display = 'none';
                    price.classList.remove('active');
                });
                yearlyPrices.forEach(price => {
                    price.style.display = 'block';
                    price.classList.add('active');
                });
                
                // Анімація зміни
                animatePriceChange();
            } else {
                monthlyPrices.forEach(price => {
                    price.style.display = 'block';
                    price.classList.add('active');
                });
                yearlyPrices.forEach(price => {
                    price.style.display = 'none';
                    price.classList.remove('active');
                });
                
                // Анімація зміни
                animatePriceChange();
            }
        });
    });
}

// Анімація зміни ціни
function animatePriceChange() {
    const activePrices = document.querySelectorAll('.plan-price.active');
    activePrices.forEach(price => {
        price.style.animation = 'none';
        setTimeout(() => {
            price.style.animation = 'fadeIn 0.3s ease';
        }, 10);
    });
}

// Кнопки замовлення
function initOrderButtons() {
    const orderButtons = document.querySelectorAll('.order-btn');
    const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    
    orderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const planId = this.dataset.planId;
            const planName = this.dataset.planName;
            const planPrice = this.dataset.planPrice;
            
            // Заповнення даних в модальному вікні
            document.getElementById('planId').value = planId;
            document.getElementById('selectedPlan').textContent = planName;
            document.getElementById('planPrice').textContent = planPrice;
            
            // Перерахунок загальної вартості
            calculateTotal();
            
            // Показ модального вікна
            orderModal.show();
        });
    });
}

// Калькулятор ціни
function initPriceCalculator() {
    const billingPeriod = document.getElementById('billingPeriod');
    
    if (billingPeriod) {
        billingPeriod.addEventListener('change', calculateTotal);
    }
}

// Розрахунок загальної вартості
function calculateTotal() {
    const planPrice = parseFloat(document.getElementById('planPrice').textContent) || 0;
    const period = parseInt(document.getElementById('billingPeriod').value) || 1;
    
    let discount = 0;
    switch(period) {
        case 3:
            discount = 0.05; // 5% знижка
            break;
        case 6:
            discount = 0.10; // 10% знижка
            break;
        case 12:
            discount = 0.20; // 20% знижка
            break;
    }
    
    const subtotal = planPrice * period;
    const discountAmount = subtotal * discount;
    const total = subtotal - discountAmount;
    
    // Оновлення відображення
    document.getElementById('hostingTotal').textContent = `₴${subtotal.toFixed(0)}`;
    document.getElementById('discountAmount').textContent = `-₴${discountAmount.toFixed(0)}`;
    document.getElementById('totalPrice').textContent = `₴${total.toFixed(0)}`;
}

// Обробка форми замовлення
function initOrderForm() {
    const orderForm = document.getElementById('orderForm');
    
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Валідація форми
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }
            
            // Збір даних форми
            const formData = new FormData(this);
            const orderData = {
                plan_id: formData.get('plan_id'),
                billing_period: formData.get('billing_period'),
                domain: formData.get('domain'),
                domain_action: formData.get('domain_action'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                total_price: document.getElementById('totalPrice').textContent
            };
            
            // Показ індикатора завантаження
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Обробка...';
            
            // Відправка даних на сервер
            fetch('/api/orders/shared-hosting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Успішне замовлення
                    showNotification('success', 'Замовлення успішно оформлено! Ми зв\'яжемось з вами найближчим часом.');
                    
                    // Закриття модального вікна
                    bootstrap.Modal.getInstance(document.getElementById('orderModal')).hide();
                    
                    // Очищення форми
                    orderForm.reset();
                    orderForm.classList.remove('was-validated');
                    
                    // Перенаправлення на сторінку підтвердження (опціонально)
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                } else {
                    // Помилка
                    showNotification('error', data.message || 'Виникла помилка при оформленні замовлення. Спробуйте ще раз.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Помилка з\'єднання. Перевірте інтернет-підключення та спробуйте ще раз.');
            })
            .finally(() => {
                // Відновлення кнопки
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
}

// Показ повідомлень
function showNotification(type, message) {
    // Створення елемента повідомлення
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show notification`;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Стилі для позиціонування
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Автоматичне закриття через 5 секунд
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Анімації при скролі
function initAnimations() {
    // Intersection Observer для анімацій при появі елементів
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                
                // Для план-карток - послідовна анімація
                if (entry.target.classList.contains('plan-card')) {
                    const cards = document.querySelectorAll('.plan-card');
                    cards.forEach((card, index) => {
                        setTimeout(() => {
                            card.style.animation = 'fadeInUp 0.5s ease forwards';
                        }, index * 100);
                    });
                }
                
                // Для feature-карток
                if (entry.target.classList.contains('feature-card')) {
                    const cards = document.querySelectorAll('.feature-card');
                    cards.forEach((card, index) => {
                        setTimeout(() => {
                            card.style.animation = 'fadeIn 0.5s ease forwards';
                        }, index * 50);
                    });
                }
            }
        });
    }, observerOptions);
    
    // Спостереження за елементами
    document.querySelectorAll('.plan-card, .feature-card').forEach(el => {
        observer.observe(el);
    });
    
    // Анімація браузер-мокапу при ховері
    const browserMockup = document.querySelector('.browser-mockup');
    if (browserMockup) {
        browserMockup.addEventListener('mouseenter', function() {
            this.style.transform = 'perspective(1000px) rotateY(0deg) scale(1.05)';
        });
        
        browserMockup.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateY(-5deg) scale(1)';
        });
    }
    
    // Паралакс ефект для hero секції
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.shared-hero');
        
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });
}

// Додаткові стилі для анімацій
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
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
    
    .notification {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
`;
document.head.appendChild(style);

// Плавна прокрутка до якорів
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Додавання інтерактивності до cPanel секцій
document.querySelectorAll('.cpanel-section').forEach(section => {
    section.addEventListener('click', function() {
        // Анімація кліку
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.style.transform = '';
        }, 100);
        
        // Показ тултіпу
        const tooltip = document.createElement('div');
        tooltip.className = 'cpanel-tooltip';
        tooltip.textContent = 'Доступно в панелі управління';
        tooltip.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            pointer-events: none;
            z-index: 1000;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = this.getBoundingClientRect();
        tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
        
        setTimeout(() => {
            tooltip.remove();
        }, 1500);
    });
});

// Додавання hover ефектів для план-карток
document.querySelectorAll('.plan-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        if (!this.classList.contains('popular')) {
            this.style.borderColor = '#667eea';
        }
    });
    
    card.addEventListener('mouseleave', function() {
        if (!this.classList.contains('popular')) {
            this.style.borderColor = 'transparent';
        }
    });
});