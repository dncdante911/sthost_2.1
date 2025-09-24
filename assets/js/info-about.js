/**
 * About Page JavaScript
 * Анимации и интерактивность для страницы "Про компанію"
 */

class AboutPage {
    constructor() {
        this.counters = document.querySelectorAll('.counter');
        this.timelineItems = document.querySelectorAll('.timeline-item');
        this.achievementCards = document.querySelectorAll('.achievement-card');
        this.teamCards = document.querySelectorAll('.team-card');
        this.missionCards = document.querySelectorAll('.mission-card');
        
        this.isCounterAnimated = false;
        this.animatedElements = new Set();
        this.startTime = Date.now();
        
        this.init();
    }
    
    init() {
        this.setupIntersectionObserver();
        this.setupTimelineAnimation();
        this.setupHoverEffects();
        this.setupScrollAnimations();
        this.setupCounterAnimations();
    }
    
    setupIntersectionObserver() {
        // Настройка наблюдателя для анимаций при скролле
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateElement(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        // Добавляем элементы для наблюдения
        this.timelineItems.forEach(item => this.observer.observe(item));
        this.achievementCards.forEach(card => this.observer.observe(card));
        this.teamCards.forEach(card => this.observer.observe(card));
        this.missionCards.forEach(card => this.observer.observe(card));
        
        // Добавляем секцию счетчиков
        const achievementsSection = document.querySelector('.achievements');
        if (achievementsSection) {
            this.observer.observe(achievementsSection);
        }
    }
    
    animateElement(element) {
        if (this.animatedElements.has(element)) return;
        
        this.animatedElements.add(element);
        
        // Анимация timeline элементов
        if (element.classList.contains('timeline-item')) {
            this.animateTimelineItem(element);
        }
        
        // Анимация карточек команды
        if (element.classList.contains('team-card')) {
            this.animateTeamCard(element);
        }
        
        // Анимация карточек миссии
        if (element.classList.contains('mission-card')) {
            this.animateMissionCard(element);
        }
        
        // Анимация счетчиков достижений
        if (element.classList.contains('achievements')) {
            this.startCounterAnimations();
        }
    }
    
    animateTimelineItem(item) {
        const index = Array.from(this.timelineItems).indexOf(item);
        
        item.style.setProperty('--i', index);
        item.style.animationDelay = `${index * 0.2}s`;
        item.classList.add('animate-in');
        
        // Добавляем эффект появления контента
        const content = item.querySelector('.timeline-content');
        if (content) {
            setTimeout(() => {
                content.style.opacity = '0';
                content.style.transform = 'translateY(20px)';
                content.style.transition = 'all 0.6s ease';
                
                requestAnimationFrame(() => {
                    content.style.opacity = '1';
                    content.style.transform = 'translateY(0)';
                });
            }, index * 200);
        }
    }
    
    animateTeamCard(card) {
        const index = Array.from(this.teamCards).indexOf(card);
        
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px) scale(0.9)';
        card.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0) scale(1)';
        }, index * 150);
        
        // Анимация аватара
        const avatar = card.querySelector('.team-avatar');
        if (avatar) {
            setTimeout(() => {
                avatar.style.animation = 'teamAvatarPulse 0.8s ease';
            }, index * 150 + 300);
        }
    }
    
    animateMissionCard(card) {
        const index = Array.from(this.missionCards).indexOf(card);
        
        card.style.opacity = '0';
        card.style.transform = 'translateY(40px)';
        card.style.transition = 'all 0.8s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 200);
        
        // Анимация иконки
        const icon = card.querySelector('.mission-icon');
        if (icon) {
            setTimeout(() => {
                icon.style.animation = 'missionIconRotate 1s ease';
            }, index * 200 + 400);
        }
    }
    
    setupTimelineAnimation() {
        // Добавляем CSS анимации для timeline
        const style = document.createElement('style');
        style.textContent = `
            @keyframes teamAvatarPulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
            
            @keyframes missionIconRotate {
                0% { transform: rotate(0deg) scale(1); }
                50% { transform: rotate(180deg) scale(1.1); }
                100% { transform: rotate(360deg) scale(1); }
            }
            
            .timeline-item.animate-in {
                animation: timelineSlideIn 0.6s ease forwards;
            }
        `;
        document.head.appendChild(style);
    }
    
    setupCounterAnimations() {
        // Настройка анимации счетчиков
        this.counters.forEach(counter => {
            counter.textContent = '0';
        });
    }
    
    startCounterAnimations() {
        if (this.isCounterAnimated) return;
        this.isCounterAnimated = true;
        
        this.counters.forEach((counter, index) => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000; // 2 секунды
            const delay = index * 200; // Задержка между счетчиками
            
            setTimeout(() => {
                this.animateCounter(counter, target, duration);
            }, delay);
        });
    }
    
    animateCounter(counter, target, duration) {
        const start = 0;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Используем easing функцию для плавности
            const easeProgress = this.easeOutCubic(progress);
            const current = Math.floor(start + (target - start) * easeProgress);
            
            counter.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                counter.textContent = target.toLocaleString();
                
                // Событие завершения счетчика
                const event = new CustomEvent('counterComplete');
                document.dispatchEvent(event);
            }
        };
        
        requestAnimationFrame(animate);
    }
    
    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }
    
    setupHoverEffects() {
        // Эффекты наведения для карточек команды
        this.teamCards.forEach(card => {
            const avatar = card.querySelector('.team-avatar');
            const socialLinks = card.querySelectorAll('.social-link');
            
            card.addEventListener('mouseenter', () => {
                if (avatar) {
                    avatar.style.transform = 'scale(1.1) rotate(5deg)';
                    avatar.style.transition = 'transform 0.3s ease';
                }
                
                socialLinks.forEach((link, index) => {
                    setTimeout(() => {
                        link.style.transform = 'translateY(-3px) scale(1.1)';
                    }, index * 50);
                });
            });
            
            card.addEventListener('mouseleave', () => {
                if (avatar) {
                    avatar.style.transform = 'scale(1) rotate(0deg)';
                }
                
                socialLinks.forEach(link => {
                    link.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
        
        // Эффекты для карточек достижений
        this.achievementCards.forEach(card => {
            const icon = card.querySelector('.achievement-icon');
            const number = card.querySelector('.achievement-number');
            
            card.addEventListener('mouseenter', () => {
                if (icon) {
                    icon.style.animation = 'achievementIconBounce 0.6s ease';
                }
                if (number) {
                    number.style.transform = 'scale(1.1)';
                    number.style.transition = 'transform 0.3s ease';
                }
            });
            
            card.addEventListener('mouseleave', () => {
                if (number) {
                    number.style.transform = 'scale(1)';
                }
            });
        });
        
        // Добавляем CSS для анимации иконок
        const style = document.createElement('style');
        style.textContent = `
            @keyframes achievementIconBounce {
                0%, 100% { transform: scale(1) rotate(0deg); }
                25% { transform: scale(1.1) rotate(-5deg); }
                75% { transform: scale(1.1) rotate(5deg); }
            }
        `;
        document.head.appendChild(style);
    }
    
    setupScrollAnimations() {
        // Параллакс эффект для hero секции
        const hero = document.querySelector('.about-hero');
        const heroContent = document.querySelector('.hero-content');
        const heroImage = document.querySelector('.hero-image');
        
        if (hero) {
            const scrollHandler = () => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                
                if (heroContent) {
                    heroContent.style.transform = `translateY(${rate * 0.3}px)`;
                }
                
                if (heroImage) {
                    heroImage.style.transform = `translateY(${rate * 0.2}px)`;
                }
            };
            
            window.addEventListener('scroll', scrollHandler);
            this.scrollHandler = scrollHandler; // Сохраняем для удаления
        }
        
        // Анимация серверной стойки
        this.animateServerRack();
        
        // Анимация сетевых подключений
        this.animateNetworkConnections();
    }
    
    animateServerRack() {
        const serverUnits = document.querySelectorAll('.server-unit');
        
        if (serverUnits.length > 0) {
            this.serverInterval = setInterval(() => {
                // Случайно включаем/выключаем серверы
                serverUnits.forEach(unit => {
                    if (Math.random() > 0.8) {
                        unit.classList.toggle('active');
                    }
                });
            }, 2000);
        }
        
        const serverLights = document.querySelectorAll('.server-light');
        if (serverLights.length > 0) {
            this.lightsInterval = setInterval(() => {
                serverLights.forEach(light => {
                    if (Math.random() > 0.7) {
                        light.classList.toggle('active');
                    }
                });
            }, 1500);
        }
    }
    
    animateNetworkConnections() {
        const connectionLines = document.querySelectorAll('.connection-line');
        
        connectionLines.forEach((line, index) => {
            // Устанавливаем CSS переменную для поворота
            const rotations = [-30, 45, 15];
            line.style.setProperty('--rotation', `${rotations[index]}deg`);
            
            // Добавляем случайные импульсы данных
            const connectionInterval = setInterval(() => {
                line.style.animation = 'none';
                requestAnimationFrame(() => {
                    line.style.animation = `dataFlow 3s infinite`;
                });
            }, 3000 + index * 1000);
            
            // Сохраняем интервалы для очистки
            if (!this.connectionIntervals) {
                this.connectionIntervals = [];
            }
            this.connectionIntervals.push(connectionInterval);
        });
    }
    
    // Метод для инициализации эффектов при загрузке
    initLoadEffects() {
        // Анимация появления статистики
        const stats = document.querySelectorAll('.stat-item');
        stats.forEach((stat, index) => {
            stat.style.opacity = '0';
            stat.style.transform = 'translateY(20px)';
            stat.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                stat.style.opacity = '1';
                stat.style.transform = 'translateY(0)';
            }, 1000 + index * 200);
        });
        
        // Пульсация компании badge
        const badge = document.querySelector('.company-badge');
        if (badge) {
            setTimeout(() => {
                badge.style.animation = 'badgePulse 2s ease infinite';
            }, 500);
        }
        
        // Добавляем CSS для badge анимации
        const style = document.createElement('style');
        style.textContent = `
            @keyframes badgePulse {
                0%, 100% { 
                    transform: scale(1); 
                    box-shadow: 0 0 0 0 rgba(255, 217, 61, 0.4); 
                }
                50% { 
                    transform: scale(1.05); 
                    box-shadow: 0 0 0 10px rgba(255, 217, 61, 0); 
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Метод для создания частиц на фоне
    createParticles() {
        const hero = document.querySelector('.about-hero');
        if (!hero) return;
        
        const particlesContainer = document.createElement('div');
        particlesContainer.className = 'particles-container';
        particlesContainer.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 1;
        `;
        
        hero.appendChild(particlesContainer);
        
        // Создаем частицы
        for (let i = 0; i < 15; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                position: absolute;
                width: 4px;
                height: 4px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                animation: particleFloat ${5 + Math.random() * 10}s infinite linear;
                left: ${Math.random() * 100}%;
                animation-delay: ${Math.random() * 5}s;
            `;
            
            particlesContainer.appendChild(particle);
        }
        
        // Добавляем CSS анимацию для частиц
        const particleStyle = document.createElement('style');
        particleStyle.textContent = `
            @keyframes particleFloat {
                0% {
                    transform: translateY(100vh) rotate(0deg);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(-100px) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(particleStyle);
    }
    
    // Метод для обработки кликов по социальным ссылкам
    setupSocialInteractions() {
        const socialLinks = document.querySelectorAll('.social-link');
        
        socialLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Создаем эффект "волны" при клике
                const ripple = document.createElement('span');
                ripple.style.cssText = `
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 0;
                    height: 0;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    transform: translate(-50%, -50%);
                    animation: rippleEffect 0.6s ease-out;
                    pointer-events: none;
                `;
                
                link.style.position = 'relative';
                link.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
                
                // Показываем уведомление
                this.showNotification('Социальная ссылка (демо)', 'info');
            });
        });
        
        // Добавляем CSS для эффекта волны
        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            @keyframes rippleEffect {
                0% {
                    width: 0;
                    height: 0;
                    opacity: 1;
                }
                100% {
                    width: 60px;
                    height: 60px;
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(rippleStyle);
    }
    
    // Метод для показа уведомлений
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification alert alert-${type === 'info' ? 'primary' : type} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        `;
        
        notification.innerHTML = `
            <i class="bi bi-${type === 'info' ? 'info-circle' : 'check-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Автоматически скрываем через 3 секунды
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 3000);
        
        // Добавляем CSS для анимации уведомлений
        const notificationStyle = document.createElement('style');
        notificationStyle.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(notificationStyle);
    }
    
    // Метод для отслеживания прогресса чтения
    setupReadingProgress() {
        const progressBar = document.createElement('div');
        progressBar.id = 'reading-progress';
        progressBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            z-index: 9999;
            transition: width 0.3s ease;
        `;
        
        document.body.appendChild(progressBar);
        
        const updateProgress = () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            
            progressBar.style.width = Math.min(scrolled, 100) + '%';
        };
        
        window.addEventListener('scroll', updateProgress);
        this.progressHandler = updateProgress; // Сохраняем для удаления
    }
    
    // Метод для инициализации всех эффектов
    initializeAllEffects() {
        // Задержки для поэтапной загрузки эффектов
        setTimeout(() => this.initLoadEffects(), 100);
        setTimeout(() => this.createParticles(), 500);
        setTimeout(() => this.setupSocialInteractions(), 800);
        setTimeout(() => this.setupReadingProgress(), 1200);
    }
    
    // Метод для очистки ресурсов
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        
        // Удаляем обработчики событий
        if (this.scrollHandler) {
            window.removeEventListener('scroll', this.scrollHandler);
        }
        
        if (this.progressHandler) {
            window.removeEventListener('scroll', this.progressHandler);
        }
        
        // Очищаем интервалы
        if (this.serverInterval) {
            clearInterval(this.serverInterval);
        }
        
        if (this.lightsInterval) {
            clearInterval(this.lightsInterval);
        }
        
        if (this.connectionIntervals) {
            this.connectionIntervals.forEach(interval => clearInterval(interval));
        }
        
        // Удаляем прогресс бар
        const progressBar = document.getElementById('reading-progress');
        if (progressBar) {
            progressBar.remove();
        }
        
        // Удаляем частицы
        const particlesContainer = document.querySelector('.particles-container');
        if (particlesContainer) {
            particlesContainer.remove();
        }
    }
}

// Дополнительные утилиты для страницы
const AboutUtils = {
    // Функция для форматирования чисел
    formatNumber: function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    },
    
    // Функция для создания конфетти эффекта
    createConfetti: function() {
        const colors = ['#4A3AFF', '#6B4EFF', '#FFD93D', '#10B981', '#EF4444'];
        const confettiContainer = document.createElement('div');
        confettiContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        `;
        
        document.body.appendChild(confettiContainer);
        
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: absolute;
                width: 10px;
                height: 10px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                top: -10px;
                left: ${Math.random() * 100}%;
                animation: confettiFall ${Math.random() * 3 + 2}s linear forwards;
                transform: rotate(${Math.random() * 360}deg);
            `;
            
            confettiContainer.appendChild(confetti);
        }
        
        // Добавляем CSS анимацию
        const style = document.createElement('style');
        style.textContent = `
            @keyframes confettiFall {
                to {
                    transform: translateY(100vh) rotate(720deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Удаляем контейнер через 5 секунд
        setTimeout(() => {
            confettiContainer.remove();
            style.remove();
        }, 5000);
    }
};

// Функция для пасхалки (при тройном клике на логотип)
let logoClickCount = 0;
let logoClickTimer = null;

document.addEventListener('click', function(e) {
    if (e.target.closest('.company-badge') || e.target.closest('.tool-icon')) {
        logoClickCount++;
        
        if (logoClickTimer) {
            clearTimeout(logoClickTimer);
        }
        
        logoClickTimer = setTimeout(() => {
            logoClickCount = 0;
        }, 2000);
        
        if (logoClickCount === 3) {
            logoClickCount = 0;
            AboutUtils.createConfetti();
            
            if (window.aboutPage) {
                window.aboutPage.showNotification('🎉 Ви знайшли пасхалку! Дякуємо за увагу до деталей!', 'success');
            }
        }
    }
});

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, что мы на странице about
    if (document.querySelector('.about-hero')) {
        window.aboutPage = new AboutPage();
        
        // Запускаем все эффекты после полной загрузки
        window.addEventListener('load', () => {
            if (window.aboutPage) {
                window.aboutPage.initializeAllEffects();
            }
        });
    }
});

// Очистка при переходе на другую страницу
window.addEventListener('beforeunload', () => {
    if (window.aboutPage) {
        window.aboutPage.destroy();
    }
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        AboutPage,
        AboutUtils
    };
}

// Глобальные функции для совместимости
window.AboutUtils = AboutUtils;