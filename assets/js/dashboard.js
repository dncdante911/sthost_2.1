/* ============================================
   DASHBOARD JAVASCRIPT
   Организованный код с классами и модулями
============================================ */

// ============================================
// DASHBOARD CONTROLLER - Главный контроллер
// ============================================
class DashboardController {
    constructor() {
        // Данные из PHP
        this.userStats = {
            vps: <?php echo $services_stats['vps']; ?>,
            activeServices: <?php echo $services_stats['active_services']; ?>,
            domains: <?php echo $services_stats['domains']; ?>,
            hosting: <?php echo $services_stats['hosting']; ?>
        };
        
        // Состояние приложения
        this.isInitialized = false;
        this.animationObserver = null;
        this.updateInterval = null;
        
        // Инициализация
        this.init();
    }
    
    init() {
        if (this.isInitialized) return;
        
        console.log('🚀 Initializing Dashboard Controller...');
        
        // Основные модули
        this.animations = new AnimationManager(this);
        this.vpsManager = new VPSManager(this);
        this.notificationManager = new NotificationManager();
        this.tooltipManager = new TooltipManager();
        
        // Инициализация компонентов
        this.setupEventListeners();
        this.initializeComponents();
        this.startUpdateTimers();
        
        this.isInitialized = true;
        
        console.log('✅ Dashboard Controller initialized successfully!');
        console.log('💻 StormHosting UA Dashboard v1.0');
    }
    
    setupEventListeners() {
        // Обновление кнопок
        document.querySelectorAll('.btn-icon').forEach(button => {
            button.addEventListener('click', this.handleButtonClick.bind(this));
        });
        
        // Плавная прокрутка
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', this.handleSmoothScroll.bind(this));
        });
        
        // Клавиатурные события
        document.addEventListener('keydown', this.handleKeyboard.bind(this));
        
        // События изменения размера окна
        window.addEventListener('resize', this.debounce(this.handleResize.bind(this), 250));
    }
    
    initializeComponents() {
        this.animations.initScrollAnimations();
        this.animations.initParallax();
        this.vpsManager.initVPSFeatures();
        this.tooltipManager.init();
        
        // Обновление цветов в зависимости от времени
        this.updateTimeBasedColors();
    }
    
    startUpdateTimers() {
        // Обновление VPS статистики
        if (this.userStats.vps > 0) {
            this.updateInterval = setInterval(() => {
                this.updateVPSStats();
            }, 30000); // Каждые 30 секунд
        }
        
        // Обновление цветов каждый час
        setInterval(() => {
            this.updateTimeBasedColors();
        }, 3600000);
    }
    
    // ============================================
    // EVENT HANDLERS - Обработчики событий
    // ============================================
    handleButtonClick(event) {
        event.preventDefault();
        const button = event.currentTarget;
        
        // Анимация нажатия
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = '';
        }, 150);
        
        // Логика в зависимости от типа кнопки
        if (button.title === 'Оновити') {
            this.refreshData();
        }
    }
    
    handleSmoothScroll(event) {
        event.preventDefault();
        const target = document.querySelector(event.currentTarget.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    
    handleKeyboard(event) {
        // Konami Code Easter Egg
        this.vpsManager.konamiHandler(event);
    }
    
    handleResize() {
        // Пересчет параметров при изменении размера
        this.animations.recalculateParallax();
    }
    
    // ============================================
    // DATA UPDATES - Обновление данных
    // ============================================
    async updateVPSStats() {
        try {
            const response = await fetch('/client/vps/api/dashboard-stats.php');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.stats) {
                this.processStatsUpdate(data.stats);
            }
        } catch (error) {
            console.log('VPS stats update failed:', error);
        }
    }
    
    processStatsUpdate(newStats) {
        // Обновляем VPS статистику
        if (newStats.vps_count !== undefined && newStats.vps_count !== this.userStats.vps) {
            const vpsElement = document.querySelector('.vps-stats-card .stats-number');
            if (vpsElement) {
                this.animateNumber(vpsElement, this.userStats.vps, newStats.vps_count);
                this.userStats.vps = newStats.vps_count;
            }
        }
        
        // Обновляем активные сервисы
        if (newStats.active_services !== undefined && newStats.active_services !== this.userStats.activeServices) {
            const activeElement = document.querySelector('.active-icon').closest('.stats-card').querySelector('.stats-number');
            if (activeElement) {
                this.animateNumber(activeElement, this.userStats.activeServices, newStats.active_services);
                this.userStats.activeServices = newStats.active_services;
            }
        }
    }
    
    animateNumber(element, from, to) {
        if (from === to) return;
        
        const duration = 1000;
        const start = Date.now();
        const range = to - from;
        
        const update = () => {
            const progress = Math.min((Date.now() - start) / duration, 1);
            const current = Math.floor(from + range * this.easeOutCubic(progress));
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        };
        
        requestAnimationFrame(update);
    }
    
    refreshData() {
        // Показываем индикатор обновления
        this.notificationManager.show({
            type: 'info',
            title: 'Оновлення даних...',
            duration: 2000
        });
        
        // Обновляем статистику
        this.updateVPSStats();
    }
    
    updateTimeBasedColors() {
        const hour = new Date().getHours();
        const root = document.documentElement;
        
        let primaryGradient;
        
        if (hour >= 6 && hour < 12) {
            // Утренние цвета
            primaryGradient = 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)';
        } else if (hour >= 12 && hour < 18) {
            // Дневные цвета  
            primaryGradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        } else if (hour >= 18 && hour < 22) {
            // Вечерние цвета
            primaryGradient = 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)';
        } else {
            // Ночные цвета
            primaryGradient = 'linear-gradient(135deg, #2c3e50 0%, #3498db 100%)';
        }
        
        root.style.setProperty('--primary-gradient', primaryGradient);
    }
    
    // ============================================
    // UTILITY METHODS - Вспомогательные методы
    // ============================================
    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }
    
    debounce(func, wait) {
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
}

// ============================================
// ANIMATION MANAGER - Менеджер анимаций
// ============================================
class AnimationManager {
    constructor(dashboard) {
        this.dashboard = dashboard;
        this.observers = [];
        this.parallaxElements = [];
    }
    
    initScrollAnimations() {
        const cards = document.querySelectorAll('.stats-card, .action-card, .content-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        }, { 
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });
        
        this.observers.push(observer);
    }
    
    initParallax() {
        const welcomeSection = document.querySelector('.welcome-section');
        if (!welcomeSection) return;
        
        this.parallaxElements.push({
            element: welcomeSection,
            speed: 0.3
        });
        
        let ticking = false;
        
        const updateParallax = () => {
            const scrolled = window.pageYOffset;
            
            this.parallaxElements.forEach(item => {
                const yPos = scrolled * item.speed;
                item.element.style.transform = `translateY(${yPos}px)`;
            });
            
            ticking = false;
        };
        
        const requestTick = () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        };
        
        window.addEventListener('scroll', requestTick);
    }
    
    recalculateParallax() {
        // Пересчет при изменении размера окна
        this.parallaxElements.forEach(item => {
            item.element.style.transform = 'translateY(0)';
        });
    }
}

// ============================================
// VPS MANAGER - Менеджер VPS функций
// ============================================
class VPSManager {
    constructor(dashboard) {
        this.dashboard = dashboard;
        this.konamiCode = [];
        this.konamiSequence = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 
                              'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 
                              'KeyB', 'KeyA'];
    }
    
    initVPSFeatures() {
        const vpsCard = document.querySelector('.vps-card');
        if (!vpsCard) return;
        
        // Дополнительные эффекты для VPS карточки
        vpsCard.addEventListener('mouseenter', () => {
            this.resetVPSAnimation(vpsCard);
        });
        
        // Анимация блисков для новых пользователей
        if (this.dashboard.userStats.vps === 0) {
            this.initSparkleAnimation(vpsCard);
            
            // Показать уведомление через 5 секунд
            setTimeout(() => {
                this.dashboard.notificationManager.showVPSPromo();
            }, 5000);
        }
    }
    
    resetVPSAnimation(card) {
        card.style.animation = 'none';
        setTimeout(() => {
            card.style.animation = '';
        }, 10);
    }
    
    initSparkleAnimation(vpsCard) {
        const sparkles = vpsCard.querySelectorAll('.sparkle');
        
        const animateSparkles = () => {
            sparkles.forEach(sparkle => {
                if (Math.random() > 0.7) {
                    sparkle.style.animation = 'none';
                    setTimeout(() => {
                        sparkle.style.animation = 'sparkle-move 3s ease-in-out infinite';
                    }, 10);
                }
            });
        };
        
        setInterval(animateSparkles, 3000);
    }
    
    konamiHandler(event) {
        this.konamiCode.push(event.code);
        
        if (this.konamiCode.length > this.konamiSequence.length) {
            this.konamiCode.shift();
        }
        
        if (this.konamiCode.join(',') === this.konamiSequence.join(',')) {
            this.activateEasterEgg();
            this.konamiCode = [];
        }
    }
    
    activateEasterEgg() {
        // Радужный эффект
        document.body.style.animation = 'rainbow 2s ease infinite';
        
        setTimeout(() => {
            document.body.style.animation = '';
        }, 10000);
        
        // Уведомление
        this.dashboard.notificationManager.show({
            type: 'success',
            title: '🎉 Секретний режим активовано!',
            message: 'Ви знайшли пасхальне яйце StormHosting UA! 🚀',
            duration: 3000
        });
        
        // Добавляем CSS анимацию если её нет
        if (!document.getElementById('rainbow-animation')) {
            const style = document.createElement('style');
            style.id = 'rainbow-animation';
            style.textContent = `
                @keyframes rainbow {
                    0% { filter: hue-rotate(0deg); }
                    100% { filter: hue-rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// ============================================
// NOTIFICATION MANAGER - Менеджер уведомлений
// ============================================
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.container = this.createContainer();
    }
    
    createContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            pointer-events: none;
        `;
        document.body.appendChild(container);
        return container;
    }
    
    show({ type = 'info', title, message = '', duration = 5000 }) {
        const notification = this.createNotification({ type, title, message, duration });
        this.container.appendChild(notification);
        this.notifications.push(notification);
        
        // Анимация появления
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 10);
        
        // Автоудаление
        setTimeout(() => {
            this.remove(notification);
        }, duration);
        
        return notification;
    }
    
    createNotification({ type, title, message, duration }) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            background: ${this.getBackgroundColor(type)};
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 10px;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: auto;
            position: relative;
        `;
        
        const icon = this.getIcon(type);
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="font-size: 1.5rem;">${icon}</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: ${message ? '4px' : '0'};">${title}</div>
                    ${message ? `<div style="font-size: 0.9rem; opacity: 0.9;">${message}</div>` : ''}
                </div>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; color: white; 
                               font-size: 1.2rem; cursor: pointer; opacity: 0.7;
                               width: 24px; height: 24px; display: flex; 
                               align-items: center; justify-content: center;">
                    ×
                </button>
            </div>
        `;
        
        return notification;
    }
    
    showVPSPromo() {
        const notification = this.createNotification({
            type: 'success',
            title: 'Нові VPS сервери! 🚀',
            message: 'Спробуйте наші віртуальні сервери з KVM віртуалізацією',
            duration: 8000
        });
        
        // Добавляем кнопку действия
        const actionButton = document.createElement('button');
        actionButton.style.cssText = `
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            margin-top: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            width: 100%;
            transition: all 0.2s ease;
        `;
        actionButton.textContent = 'Дізнатись більше →';
        actionButton.onclick = () => window.location.href = '/client/vps/';
        
        actionButton.addEventListener('mouseenter', () => {
            actionButton.style.background = 'rgba(255,255,255,0.3)';
        });
        actionButton.addEventListener('mouseleave', () => {
            actionButton.style.background = 'rgba(255,255,255,0.2)';
        });
        
        const contentDiv = notification.querySelector('div[style*="flex: 1"]');
        contentDiv.appendChild(actionButton);
        
        this.container.appendChild(notification);
        
        // Анимация появления
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 10);
        
        // Автоудаление
        setTimeout(() => {
            this.remove(notification);
        }, 8000);
    }
    
    remove(notification) {
        if (notification.parentNode) {
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
                
                const index = this.notifications.indexOf(notification);
                if (index > -1) {
                    this.notifications.splice(index, 1);
                }
            }, 300);
        }
    }
    
    getBackgroundColor(type) {
        const colors = {
            'success': 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
            'info': 'linear-gradient(135deg, #17a2b8 0%, #007bff 100%)',
            'warning': 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)',
            'error': 'linear-gradient(135deg, #dc3545 0%, #e83e8c 100%)'
        };
        return colors[type] || colors.info;
    }
    
    getIcon(type) {
        const icons = {
            'success': '✅',
            'info': 'ℹ️',
            'warning': '⚠️',
            'error': '❌'
        };
        return icons[type] || icons.info;
    }
}

// ============================================
// TOOLTIP MANAGER - Менеджер подсказок
// ============================================
class TooltipManager {
    constructor() {
        this.activeTooltips = new Map();
    }
    
    init() {
        const tooltipElements = document.querySelectorAll('[title]');
        tooltipElements.forEach(element => {
            this.attachTooltip(element);
        });
    }
    
    attachTooltip(element) {
        element.addEventListener('mouseenter', (e) => this.show(e));
        element.addEventListener('mouseleave', (e) => this.hide(e));
        element.addEventListener('focus', (e) => this.show(e));
        element.addEventListener('blur', (e) => this.hide(e));
    }
    
    show(event) {
        const element = event.target;
        const title = element.getAttribute('title');
        
        if (!title || this.activeTooltips.has(element)) return;
        
        // Скрываем оригинальный title
        element.setAttribute('data-original-title', title);
        element.removeAttribute('title');
        
        const tooltip = this.createTooltip(title);
        document.body.appendChild(tooltip);
        
        this.positionTooltip(tooltip, element);
        this.activeTooltips.set(element, tooltip);
        
        // Анимация появления
        setTimeout(() => {
            tooltip.style.opacity = '1';
            tooltip.style.transform = 'translateY(0)';
        }, 10);
    }
    
    hide(event) {
        const element = event.target;
        const tooltip = this.activeTooltips.get(element);
        
        if (!tooltip) return;
        
        // Возвращаем оригинальный title
        const originalTitle = element.getAttribute('data-original-title');
        if (originalTitle) {
            element.setAttribute('title', originalTitle);
            element.removeAttribute('data-original-title');
        }
        
        // Анимация скрытия
        tooltip.style.opacity = '0';
        tooltip.style.transform = 'translateY(-5px)';
        
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
            this.activeTooltips.delete(element);
        }, 200);
    }
    
    createTooltip(text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: #2c3e50;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            z-index: 10000;
            pointer-events: none;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            max-width: 250px;
            word-wrap: break-word;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;
        
        // Стрелка
        const arrow = document.createElement('div');
        arrow.style.cssText = `
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #2c3e50;
        `;
        tooltip.appendChild(arrow);
        
        return tooltip;
    }
    
    positionTooltip(tooltip, element) {
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        let top = rect.top - tooltipRect.height - 10;
        
        // Проверяем границы экрана
        if (left < 10) left = 10;
        if (left + tooltipRect.width > window.innerWidth - 10) {
            left = window.innerWidth - tooltipRect.width - 10;
        }
        
        if (top < 10) {
            top = rect.bottom + 10;
            // Переворачиваем стрелку
            const arrow = tooltip.querySelector('div');
            arrow.style.top = '-5px';
            arrow.style.borderTop = 'none';
            arrow.style.borderBottom = '5px solid #2c3e50';
        }
        
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }
}

// ============================================
// PERFORMANCE UTILITIES - Утилиты производительности
// ============================================
class PerformanceMonitor {
    static init() {
        // Lazy loading для изображений
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
        
        // Prefetch важных страниц
        PerformanceMonitor.prefetchPages();
        
        // Мониторинг производительности
        if ('performance' in window) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData) {
                        console.log(`📊 Page load time: ${Math.round(perfData.loadEventEnd - perfData.loadEventStart)}ms`);
                        console.log(`📊 DOM ready: ${Math.round(perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart)}ms`);
                    }
                }, 0);
            });
        }
    }
    
    static prefetchPages() {
        const importantPages = [
            '/client/vps/',
            '/pages/hosting/',
            '/client/profile.php'
        ];
        
        importantPages.forEach(page => {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = page;
            document.head.appendChild(link);
        });
    }
    
    static measureFunction(fn, name) {
        return function(...args) {
            const start = performance.now();
            const result = fn.apply(this, args);
            const end = performance.now();
            console.log(`⏱️ ${name}: ${(end - start).toFixed(2)}ms`);
            return result;
        };
    }
}

// ============================================
// INITIALIZATION - Инициализация приложения
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 Starting Dashboard initialization...');
    
    // Инициализация основного контроллера
    const dashboard = new DashboardController();
    
    // Инициализация утилит производительности
    PerformanceMonitor.init();
    
    // Добавляем в глобальную область видимости для отладки
    window.dashboard = dashboard;
    window.DashboardController = DashboardController;
    
    console.log('🎉 Dashboard fully loaded and ready!');
    console.log('💻 StormHosting UA - VPS Integration Active');
});

// ============================================
// ERROR HANDLING - Обработка ошибок
// ============================================
window.addEventListener('error', (event) => {
    console.error('Dashboard Error:', {
        message: event.message,
        source: event.filename,
        line: event.lineno,
        column: event.colno,
        error: event.error
    });
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason);
});

// ============================================
// CLEANUP - Очистка при уходе со страницы
// ============================================
window.addEventListener('beforeunload', () => {
    // Очищаем интервалы
    if (window.dashboard && window.dashboard.updateInterval) {
        clearInterval(window.dashboard.updateInterval);
    }
    
    // Очищаем наблюдатели
    if (window.dashboard && window.dashboard.animations) {
        window.dashboard.animations.observers.forEach(observer => {
            observer.disconnect();
        });
    }
});

// ============================================
// DEVELOPMENT UTILITIES - Утилиты для разработки
// ============================================
if (typeof console !== 'undefined' && console.log) {
    console.log(`
    🚀 StormHosting UA Dashboard
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━
    Version: 1.0
    Features: VPS Management, Statistics, Animations
    Status: ✅ Active
    
    Available commands:
    - dashboard.updateVPSStats() - Update VPS statistics
    - dashboard.vpsManager.activateEasterEgg() - Activate easter egg
    - dashboard.notificationManager.show({...}) - Show notification
    `);
}