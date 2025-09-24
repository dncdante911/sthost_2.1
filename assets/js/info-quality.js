/**
 * JavaScript для страницы гарантии качества
 * /assets/js/pages/info-quality.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех компонентов
    initCounterAnimations();
    initScrollAnimations();
    initCompensationCalculator();
    initInteractiveElements();
    initRealTimeUpdates();
    initMonitoringDashboard();
});

// Анимация счетчиков
function initCounterAnimations() {
    const counterElements = document.querySelectorAll('.metric-value, .sla-percentage');
    
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

    counterElements.forEach(element => {
        observer.observe(element);
    });
}

function animateCounter(element) {
    const text = element.textContent;
    const number = parseFloat(text.replace(/[^\d.,]/g, '').replace(',', '.'));
    
    if (isNaN(number)) return;
    
    const isPercentage = text.includes('%');
    const hasMs = text.includes('ms') || text.includes('мс');
    const hasUnit = text.includes('<') || text.includes('хв');
    
    const duration = 2000;
    const start = performance.now();

    function animate(currentTime) {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const current = number * easeOutQuart;
        
        if (isPercentage) {
            element.textContent = current.toFixed(1) + '%';
        } else if (hasMs) {
            element.textContent = '< ' + Math.round(current) + 'ms';
        } else if (hasUnit) {
            element.textContent = Math.round(current) + ' хв';
        } else if (number >= 1000) {
            element.textContent = formatNumber(current);
        } else {
            element.textContent = current.toFixed(1);
        }

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }

    requestAnimationFrame(animate);
}

function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return Math.round(num).toString();
}

// Анимации при скролле
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.sla-card, .security-card, .certificate-card, .monitoring-feature');
    
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

    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'all 0.6s ease';
        observer.observe(element);
    });
}

// Калькулятор компенсации
function initCompensationCalculator() {
    const uptimeInput = document.getElementById('uptimeInput');
    const monthlyFeeInput = document.getElementById('monthlyFeeInput');
    const compensationResult = document.getElementById('compensationResult');
    
    if (!uptimeInput || !monthlyFeeInput || !compensationResult) return;
    
    // Автоматический расчет при изменении значений
    uptimeInput.addEventListener('input', calculateCompensation);
    monthlyFeeInput.addEventListener('input', calculateCompensation);
    
    // Начальный расчет
    calculateCompensation();
}

function calculateCompensation() {
    const uptimeInput = document.getElementById('uptimeInput');
    const monthlyFeeInput = document.getElementById('monthlyFeeInput');
    const compensationResult = document.getElementById('compensationResult');
    
    if (!uptimeInput || !monthlyFeeInput || !compensationResult) return;
    
    const uptime = parseFloat(uptimeInput.value);
    const monthlyFee = parseFloat(monthlyFeeInput.value);
    
    if (isNaN(uptime) || isNaN(monthlyFee)) return;
    
    let compensationPercent = 0;
    
    if (uptime >= 99.0 && uptime <= 99.8) {
        compensationPercent = 10;
    } else if (uptime >= 95.0 && uptime <= 98.9) {
        compensationPercent = 25;
    } else if (uptime >= 90.0 && uptime <= 94.9) {
        compensationPercent = 50;
    } else if (uptime < 90.0) {
        compensationPercent = 100;
    }
    
    const compensation = (monthlyFee * compensationPercent) / 100;
    compensationResult.textContent = compensation.toFixed(2) + ' грн';
    
    // Анимация изменения
    compensationResult.style.transform = 'scale(1.1)';
    setTimeout(() => {
        compensationResult.style.transform = 'scale(1)';
    }, 200);
}

// Интерактивные элементы
function initInteractiveElements() {
    // Копирование SLA информации
    window.copySLAInfo = function(type) {
        const slaInfo = {
            uptime: 'Гарантія аптайма: 99.9%\nКомпенсація при порушенні: від 10% до 100% місячної плати\nМоніторинг: 24/7 автоматичний',
            support: 'Технічна підтримка: 24/7/365\nЧас відповіді:\n- Критичні: до 15 хвилин\n- Високі: до 1 години\n- Звичайні: до 4 годин\n- Низькі: до 24 годин',
            backup: 'Резервне копіювання: щоденно\nЗберігання копій: 30 днів\nВідновлення: протягом 2 годин\nБезкоштовне відновлення: так'
        };

        if (slaInfo[type]) {
            navigator.clipboard.writeText(slaInfo[type]).then(() => {
                showNotification('SLA інформацію скопійовано в буфер обміну', 'success');
            }).catch(() => {
                showNotification('Помилка копіювання', 'error');
            });
        }
    };
    
    // Hover эффекты для карточек
    const cards = document.querySelectorAll('.sla-card, .security-card, .certificate-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Обновления в реальном времени
function initRealTimeUpdates() {
    // Обновляем метрики каждые 30 секунд
    setInterval(updateMetrics, 30000);
    
    // Обновляем статус системы
    setInterval(updateSystemStatus, 10000);
    
    // Начальное обновление
    updateMetrics();
    updateSystemStatus();
}

function updateMetrics() {
    const metricValues = document.querySelectorAll('.metric-value');
    
    metricValues.forEach(metric => {
        const currentText = metric.textContent;
        const currentValue = parseFloat(currentText.replace(/[^\d.,]/g, '').replace(',', '.'));
        
        if (isNaN(currentValue)) return;
        
        // Небольшие случайные изменения (±2%)
        const variation = (Math.random() - 0.5) * 0.04;
        const newValue = currentValue * (1 + variation);
        
        // Плавное обновление
        smoothUpdateValue(metric, currentValue, newValue);
    });
}

function smoothUpdateValue(element, from, to) {
    const duration = 1000;
    const start = performance.now();
    const originalText = element.textContent;
    const isPercentage = originalText.includes('%');
    const hasMs = originalText.includes('ms') || originalText.includes('мс');
    const hasLessThan = originalText.includes('<');

    function animate(currentTime) {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);
        const current = from + (to - from) * progress;
        
        if (isPercentage) {
            element.textContent = current.toFixed(2) + '%';
        } else if (hasMs) {
            element.textContent = (hasLessThan ? '< ' : '') + Math.round(current) + 'ms';
        } else {
            element.textContent = formatNumber(current);
        }

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }

    requestAnimationFrame(animate);
}

function updateSystemStatus() {
    const statusElements = document.querySelectorAll('.status-indicator');
    const statusLights = document.querySelectorAll('.status-dot');
    
    // Симуляция проверки статуса (в реальном приложении это был бы AJAX запрос)
    const isOnline = Math.random() > 0.05; // 95% времени онлайн
    
    statusElements.forEach(element => {
        const textElement = element.querySelector('.status-text') || element;
        const lightElement = element.querySelector('.status-dot');
        
        if (isOnline) {
            if (textElement.textContent !== 'Всі системи працюють') {
                textElement.textContent = 'Всі системи працюють';
                textElement.style.color = '#22c55e';
            }
            if (lightElement) {
                lightElement.style.background = '#22c55e';
            }
        } else {
            textElement.textContent = 'Технічні роботи';
            textElement.style.color = '#f59e0b';
            if (lightElement) {
                lightElement.style.background = '#f59e0b';
            }
        }
    });
}

// Мониторинг дашборд
function initMonitoringDashboard() {
    updateServerMetrics();
    updateRecentEvents();
    
    // Обновляем метрики серверов каждые 5 секунд
    setInterval(updateServerMetrics, 5000);
    
    // Добавляем новые события каждые 30 секунд
    setInterval(addRandomEvent, 30000);
}

function updateServerMetrics() {
    const metricElements = document.querySelectorAll('.monitor-metrics span');
    
    metricElements.forEach(element => {
        const text = element.textContent;
        
        if (text.includes('CPU:')) {
            const newCpu = Math.floor(Math.random() * 40) + 20; // 20-60%
            element.textContent = `CPU: ${newCpu}%`;
        } else if (text.includes('RAM:')) {
            const newRam = Math.floor(Math.random() * 30) + 40; // 40-70%
            element.textContent = `RAM: ${newRam}%`;
        } else if (text.includes('Зв\'язків:')) {
            const newConn = Math.floor(Math.random() * 200) + 700; // 700-900
            element.textContent = `Зв'язків: ${newConn}`;
        } else if (text.includes('Запитів/с:')) {
            const newReq = Math.floor(Math.random() * 100) + 200; // 200-300
            element.textContent = `Запитів/с: ${newReq}`;
        } else if (text.includes('Пінг:')) {
            const newPing = Math.floor(Math.random() * 10) + 8; // 8-18ms
            element.textContent = `Пінг: ${newPing}ms`;
        }
    });
}

function addRandomEvent() {
    const eventsContainer = document.querySelector('.recent-events');
    if (!eventsContainer) return;
    
    const events = [
        'Автоматичне оновлення безпеки виконано',
        'Резервне копіювання завершено',
        'Моніторинг продуктивності оновлено',
        'SSL сертифікат оновлено',
        'Очищення логів виконано',
        'Перевірка цілісності даних завершена'
    ];
    
    const eventTypes = ['success', 'info', 'success', 'success', 'info', 'success'];
    const eventSymbols = ['✓', 'i', '✓', '✓', 'i', '✓'];
    
    const randomIndex = Math.floor(Math.random() * events.length);
    const now = new Date();
    const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                      now.getMinutes().toString().padStart(2, '0');
    
    const eventHTML = `
        <div class="event-item">
            <span class="event-time">${timeString}</span>
            <span class="event-text">${events[randomIndex]}</span>
            <span class="event-status ${eventTypes[randomIndex]}">${eventSymbols[randomIndex]}</span>
        </div>
    `;
    
    const existingEvents = eventsContainer.querySelectorAll('.event-item');
    if (existingEvents.length >= 3) {
        existingEvents[existingEvents.length - 1].remove();
    }
    
    const eventsTitle = eventsContainer.querySelector('.events-title');
    eventsTitle.insertAdjacentHTML('afterend', eventHTML);
}

// Уведомления
function showNotification(message, type = 'success') {
    // Удаляем существующие уведомления
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
    const bgColor = type === 'success' ? '#22c55e' : '#ef4444';
    
    notification.innerHTML = `
        <i class="bi ${icon}"></i>
        <span>${message}</span>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        max-width: 300px;
    `;

    document.body.appendChild(notification);

    // Анимация появления
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Удаление через 4 секунды
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// Параллакс эффект для hero секции
function initParallaxEffect() {
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.quality-hero::before');
        
        parallaxElements.forEach(element => {
            const speed = 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
}

// Анимация появления элементов при загрузке
function initLoadAnimations() {
    const heroElements = document.querySelectorAll('.quality-badge, .hero-content h1, .hero-content p, .guarantee-item');
    
    heroElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 200);
    });
}

// Инициализация дополнительных эффектов
setTimeout(() => {
    initParallaxEffect();
    initLoadAnimations();
}, 100);

// Обработка ошибок
window.addEventListener('error', (e) => {
    console.error('Quality page error:', e.error);
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        calculateCompensation,
        showNotification,
        updateMetrics,
        updateSystemStatus
    };
}