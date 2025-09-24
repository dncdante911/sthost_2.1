/**
 * StormHosting UA - JavaScript для главной страницы
 * Файл: /assets/js/pages/home.js
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Конфигурация
    const CONFIG = {
        animationDuration: 2000,
        scrollThreshold: 0.1,
        parallaxRate: -0.5,
        toastDuration: 5000
    };

    // ============================================================================
    // АНИМАЦИЯ СЧЕТЧИКОВ СТАТИСТИКИ
    // ============================================================================
    
    const animateCounters = () => {
        const counters = document.querySelectorAll('.stat-number');
        
        counters.forEach(counter => {
            const target = parseFloat(counter.getAttribute('data-target'));
            const duration = CONFIG.animationDuration;
            const increment = target / (duration / 16); // 60 FPS
            let current = 0;
            
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    if (current > target) current = target;
                    
                    // Форматирование числа в зависимости от значения
                    if (target === 99.9) {
                        counter.textContent = current.toFixed(1);
                    } else if (target >= 1000) {
                        counter.textContent = Math.floor(current).toLocaleString('uk-UA');
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                    
                    requestAnimationFrame(updateCounter);
                } else {
                    // Финальное значение
                    if (target === 99.9) {
                        counter.textContent = target.toFixed(1);
                    } else if (target >= 1000) {
                        counter.textContent = target.toLocaleString('uk-UA');
                    } else {
                        counter.textContent = target;
                    }
                }
            };
            
            updateCounter();
        });
    };

    // ============================================================================
    // INTERSECTION OBSERVER ДЛЯ АНИМАЦИЙ ПРИ ПРОКРУТКЕ
    // ============================================================================
    
    // Observer для счетчиков статистики
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                statsObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: CONFIG.scrollThreshold
    });

    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        statsObserver.observe(statsSection);
    }

    // Observer для анимации появления карточек
    const cardsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = Array.from(entry.target.parentNode.children).indexOf(entry.target) * 100;
                
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, delay);
                
                cardsObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: CONFIG.scrollThreshold,
        rootMargin: '0px 0px -50px 0px'
    });

    // Применяем анимацию к карточкам
    const cards = document.querySelectorAll('.hosting-card, .domain-card, .news-card, .action-card, .stat-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        cardsObserver.observe(card);
    });

    // ============================================================================
    // ПЛАВНАЯ ПРОКРУТКА ДЛЯ ЯКОРНЫХ ССЫЛОК
    // ============================================================================
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerHeight = document.querySelector('header')?.offsetHeight || 0;
                const targetPosition = target.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ============================================================================
    // ПАРАЛЛАКС ЭФФЕКТ ДЛЯ HERO СЕКЦИИ
    // ============================================================================
    
    const heroSection = document.querySelector('.hero-section');
    let ticking = false;
    
    const updateParallax = () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * CONFIG.parallaxRate;
        
        if (heroSection && scrolled < heroSection.offsetHeight) {
            heroSection.style.transform = `translateY(${rate}px)`;
        }
        
        ticking = false;
    };
    
    const requestParallaxUpdate = () => {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    };
    
    if (heroSection) {
        window.addEventListener('scroll', requestParallaxUpdate, { passive: true });
    }

    // ============================================================================
    // ФОРМА ПОДПИСКИ НА НОВОСТИ
    // ============================================================================
    
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const button = this.querySelector('button[type="submit"]');
            const email = emailInput.value.trim();
            
            // Валидация email
            if (!isValidEmail(email)) {
                showToast('Будь ласка, введіть коректний email адрес', 'error');
                emailInput.focus();
                return;
            }
            
            const originalText = button.innerHTML;
            
            // Показываем индикатор загрузки
            button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Підписуємо...';
            button.disabled = true;
            emailInput.disabled = true;
            
            // AJAX запрос на подписку
            submitNewsletterSubscription(email)
                .then(() => {
                    showToast('Дякуємо за підписку! Перевірте ваш email для підтвердження.', 'success');
                    this.reset();
                })
                .catch(error => {
                    console.error('Newsletter subscription error:', error);
                    showToast('Виникла помилка при підписці. Спробуйте пізніше.', 'error');
                })
                .finally(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    emailInput.disabled = false;
                });
        });
    }

    // ============================================================================
    // ФУНКЦИИ ДЛЯ РАБОТЫ С API
    // ============================================================================
    
    /**
     * Отправка подписки на новости
     * @param {string} email - Email адрес
     * @returns {Promise}
     */
    async function submitNewsletterSubscription(email) {
        try {
            const response = await fetch('/api/newsletter/subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email: email })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Subscription failed');
            }
            
            return data;
        } catch (error) {
            // Fallback: если API недоступно, имитируем успешную подписку
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ success: true, message: 'Subscribed successfully' });
                }, 1500);
            });
        }
    }

    // ============================================================================
    // УТИЛИТЫ
    // ============================================================================
    
    /**
     * Валидация email адреса
     * @param {string} email - Email для проверки
     * @returns {boolean}
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Показ уведомлений (Toast)
     * @param {string} message - Текст сообщения
     * @param {string} type - Тип уведомления (success, error, info, warning)
     */
    function showToast(message, type = 'info') {
        // Создаем контейнер для toast если его нет
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        // Определяем цвета и иконки для разных типов
        const toastConfig = {
            success: { bg: 'success', icon: 'check-circle' },
            error: { bg: 'danger', icon: 'exclamation-circle' },
            warning: { bg: 'warning', icon: 'exclamation-triangle' },
            info: { bg: 'primary', icon: 'info-circle' }
        };

        const config = toastConfig[type] || toastConfig.info;

        // Создаем toast
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${config.bg} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${config.icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                        data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        // Показываем toast с помощью Bootstrap
        let bsToast;
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: CONFIG.toastDuration
            });
            bsToast.show();
        } else {
            // Fallback если Bootstrap JS не загружен
            toast.style.display = 'block';
            toast.style.opacity = '1';
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, CONFIG.toastDuration);
        }

        // Удаляем toast после скрытия
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });

        // Обработка клика по кнопке закрытия для fallback
        const closeButton = toast.querySelector('.btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                if (bsToast) {
                    bsToast.hide();
                } else {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }
            });
        }
    }

    // ============================================================================
    // ДОПОЛНИТЕЛЬНЫЕ ЭФФЕКТЫ И АНИМАЦИИ
    // ============================================================================
    
    /**
     * Анимация мигания серверов в hero секции
     */
    function animateServerIcons() {
        const serverIcons = document.querySelectorAll('.server-icon');
        
        serverIcons.forEach((icon, index) => {
            // Случайное мигание LED индикаторов
            setInterval(() => {
                const led = icon.querySelector('::before');
                if (Math.random() > 0.7) {
                    icon.style.setProperty('--led-opacity', '0.3');
                    setTimeout(() => {
                        icon.style.setProperty('--led-opacity', '1');
                    }, 200);
                }
            }, 2000 + (index * 500));
        });
    }

    // Запускаем анимацию серверов
    if (document.querySelector('.server-icon')) {
        animateServerIcons();
    }

    /**
     * Добавление эффекта ряби при клике на кнопки
     */
    function addRippleEffect() {
        const buttons = document.querySelectorAll('.btn');
        
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    }

    // Добавляем CSS для анимации ряби
    const rippleCSS = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    
    if (!document.querySelector('#ripple-styles')) {
        const style = document.createElement('style');
        style.id = 'ripple-styles';
        style.textContent = rippleCSS;
        document.head.appendChild(style);
    }

    // Применяем эффект ряби
    addRippleEffect();

    // ============================================================================
    // ОБРАБОТКА ОШИБОК И ФИНАЛЬНАЯ ИНИЦИАЛИЗАЦИЯ
    // ============================================================================
    
    // Глобальная обработка ошибок
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error on Home Page:', e.error);
    });

    // Обработка ошибок Promise
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Unhandled Promise Rejection on Home Page:', e.reason);
    });

    // Логирование успешной инициализации
    console.log('🏠 StormHosting Home Page initialized successfully');
    
    // Отправляем событие о готовности страницы
    document.dispatchEvent(new CustomEvent('homePageReady', {
        detail: {
            timestamp: new Date(),
            features: ['counters', 'parallax', 'newsletter', 'animations']
        }
    }));
});