// hosting-reseller.js - Скрипти для сторінки реселерського хостингу

// Конфігурація планів
const resellerPlans = {
    start: {
        name: 'Reseller Start',
        price: 1499,
        commission: 30,
        accounts: 25
    },
    pro: {
        name: 'Reseller Pro',
        price: 2999,
        commission: 40,
        accounts: 75
    },
    business: {
        name: 'Reseller Business',
        price: 4999,
        commission: 45,
        accounts: 150
    },
    enterprise: {
        name: 'Reseller Enterprise',
        price: 9999,
        commission: 50,
        accounts: 999
    }
};

// Додаткові послуги
const additionalServices = {
    ssl: 50,
    domain: 100,
    backup: 30,
    support: 75
};

// Поточні параметри калькулятора
let calculatorState = {
    clients: 25,
    averagePrice: 300,
    plan: 'pro',
    services: {
        ssl: true,
        domain: true,
        backup: false,
        support: false
    }
};

// Графік прибутку
let profitChart = null;

// Ініціалізація при завантаженні сторінки
document.addEventListener('DOMContentLoaded', function() {
    initCalculator();
    initChart();
    updateCalculations();
    initAnimations();
});

// Ініціалізація калькулятора
function initCalculator() {
    // Слайдер кількості клієнтів
    const clientsSlider = document.getElementById('clients-slider');
    if (clientsSlider) {
        clientsSlider.addEventListener('input', function() {
            calculatorState.clients = parseInt(this.value);
            document.getElementById('clients-value').textContent = this.value;
            document.getElementById('summary-clients').textContent = this.value;
            updateCalculations();
        });
    }
    
    // Слайдер середньої вартості
    const priceSlider = document.getElementById('price-slider');
    if (priceSlider) {
        priceSlider.addEventListener('input', function() {
            calculatorState.averagePrice = parseInt(this.value);
            document.getElementById('price-value').textContent = this.value;
            document.getElementById('summary-price').textContent = this.value;
            updateCalculations();
        });
    }
    
    // Вибір плану
    const planSelect = document.getElementById('reseller-plan');
    if (planSelect) {
        planSelect.addEventListener('change', function() {
            calculatorState.plan = this.value;
            const commission = resellerPlans[this.value].commission;
            document.getElementById('summary-commission').textContent = commission + '%';
            updateCalculations();
        });
    }
    
    // Додаткові послуги
    document.querySelectorAll('#ssl-sales, #domain-sales, #backup-sales, #support-sales').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const serviceName = this.id.replace('-sales', '');
            calculatorState.services[serviceName] = this.checked;
            updateCalculations();
        });
    });
}

// Оновлення розрахунків
function updateCalculations() {
    const plan = resellerPlans[calculatorState.plan];
    const commission = plan.commission / 100;
    
    // Базовий прибуток
    const baseRevenue = calculatorState.clients * calculatorState.averagePrice;
    const baseProfit = baseRevenue * commission;
    
    // Додаткові послуги
    let additionalRevenue = 0;
    if (calculatorState.services.ssl) {
        additionalRevenue += calculatorState.clients * additionalServices.ssl;
    }
    if (calculatorState.services.domain) {
        additionalRevenue += calculatorState.clients * additionalServices.domain;
    }
    if (calculatorState.services.backup) {
        additionalRevenue += calculatorState.clients * additionalServices.backup;
    }
    if (calculatorState.services.support) {
        additionalRevenue += calculatorState.clients * additionalServices.support;
    }
    
    // Загальний прибуток
    const totalMonthlyProfit = baseProfit + additionalRevenue;
    const totalYearlyProfit = totalMonthlyProfit * 12;
    
    // Оновлення відображення
    document.getElementById('summary-additional').textContent = additionalRevenue;
    document.getElementById('monthly-profit').textContent = Math.round(totalMonthlyProfit).toLocaleString('uk-UA');
    document.getElementById('yearly-profit').textContent = Math.round(totalYearlyProfit).toLocaleString('uk-UA');
    
    // Оновлення графіка
    updateChart(totalMonthlyProfit);
}

// Ініціалізація графіка
function initChart() {
    const ctx = document.getElementById('profitChart');
    if (!ctx) return;
    
    profitChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
            datasets: [{
                label: 'Прибуток (₴)',
                data: [],
                borderColor: 'rgba(255, 255, 255, 0.8)',
                backgroundColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₴' + context.parsed.y.toLocaleString('uk-UA');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    title: {
                        display: true,
                        text: 'Місяць',
                        color: 'rgba(255, 255, 255, 0.7)'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.7)',
                        callback: function(value) {
                            return '₴' + value.toLocaleString('uk-UA');
                        }
                    }
                }
            }
        }
    });
}

// Оновлення графіка
function updateChart(monthlyProfit) {
    if (!profitChart) return;
    
    // Генерація даних з урахуванням росту
    const data = [];
    let cumulative = 0;
    for (let i = 0; i < 12; i++) {
        cumulative += monthlyProfit;
        // Додаємо невеликий рост (5% на місяць)
        const growth = 1 + (i * 0.05);
        data.push(Math.round(cumulative * growth));
    }
    
    profitChart.data.datasets[0].data = data;
    profitChart.update();
}

// Замовлення реселерського плану
function orderReseller(planName) {
    const plan = resellerPlans[planName];
    if (!plan) return;
    
    // Показ модального вікна замовлення
    const modal = new bootstrap.Modal(document.getElementById('orderModal'));
    if (modal) {
        // Заповнення даних плану
        const planDetails = `
            <div class="alert alert-info">
                <strong>Обраний план:</strong> ${plan.name}<br>
                <strong>Вартість:</strong> ₴${plan.price}/міс<br>
                <strong>Комісія:</strong> ${plan.commission}%<br>
                <strong>Кількість акаунтів:</strong> ${plan.accounts === 999 ? 'Необмежено' : 'До ' + plan.accounts}
            </div>
        `;
        
        if (document.getElementById('order-plan-details')) {
            document.getElementById('order-plan-details').innerHTML = planDetails;
        }
        
        modal.show();
    } else {
        // Якщо модального вікна немає, перенаправлення на сторінку контактів
        window.location.href = `/pages/info/contacts.php?service=reseller&plan=${planName}`;
    }
}

// Початок партнерства
function startPartnership() {
    showPartnerForm();
}

// Показ форми партнерства
function showPartnerForm() {
    const modal = new bootstrap.Modal(document.getElementById('partnerModal'));
    modal.show();
}

// Відправка форми партнерства
function submitPartnerForm() {
    const form = document.getElementById('partnerForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const partnerData = {
        type: 'reseller_application',
        name: formData.get('name'),
        surname: formData.get('surname'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        company: formData.get('company'),
        website: formData.get('website'),
        telegram: formData.get('telegram'),
        plan: formData.get('plan'),
        experience: formData.get('experience'),
        calculatedProfit: document.getElementById('monthly-profit').textContent
    };
    
    // Відправка даних через AJAX
    fetch('/api/partners/apply.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(partnerData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Закриття модального вікна
            bootstrap.Modal.getInstance(document.getElementById('partnerModal')).hide();
            
            // Показ повідомлення про успіх
            showNotification('Заявку успішно відправлено! Наш менеджер зв\'яжеться з вами протягом 24 годин.', 'success');
            
            // Очищення форми
            form.reset();
            
            // Відправка події в Google Analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'partner_application', {
                    'event_category': 'engagement',
                    'event_label': partnerData.plan
                });
            }
        } else {
            showNotification('Помилка при відправці заявки. Спробуйте ще раз.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback - відправка через mailto
        const mailtoLink = `mailto:partners@stormhosting.ua?subject=Заявка на партнерство&body=${encodeURIComponent(
            `Ім'я: ${partnerData.name} ${partnerData.surname}\n` +
            `Email: ${partnerData.email}\n` +
            `Телефон: ${partnerData.phone}\n` +
            `Компанія: ${partnerData.company || 'Не вказано'}\n` +
            `План: ${partnerData.plan}\n` +
            `Досвід: ${partnerData.experience || 'Не вказано'}`
        )}`;
        window.location.href = mailtoLink;
    });
}

// Ініціалізація анімацій
function initAnimations() {
    // Анімація при скролі
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
                
                // Анімація чисел
                if (entry.target.classList.contains('counter')) {
                    animateCounter(entry.target);
                }
            }
        });
    }, observerOptions);
    
    // Спостереження за елементами
    document.querySelectorAll('.benefit-card, .plan-card, .step-card').forEach(card => {
        observer.observe(card);
    });
}

// Анімація лічильників
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target.toLocaleString('uk-UA');
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString('uk-UA');
        }
    }, 16);
}

// Показ сповіщень
function showNotification(message, type = 'info') {
    // Створення елемента сповіщення
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show notification`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматичне закриття через 5 секунд
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

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