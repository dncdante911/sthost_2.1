/**
 * Modern Homepage JavaScript for StormHosting UA
 * File: /assets/js/pages/home.js
 * Version: 2.0
 */

class StormHomePage {
    constructor() {
        this.isLoaded = false;
        this.animations = {
            counters: new Map(),
            observers: new Map()
        };
        this.newsletter = {
            form: null,
            isSubmitting: false
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initAnimations();
        this.initCounters();
        this.initParallax();
        this.initNewsletter();
        this.initLazyLoading();
        this.initTypingEffect();
        this.startServerAnimation();
        
        // Mark as loaded
        this.isLoaded = true;
        document.body.classList.add('page-loaded');
    }
    
    bindEvents() {
        // Window events
        window.addEventListener('scroll', this.throttle(this.handleScroll.bind(this), 16));
        window.addEventListener('resize', this.debounce(this.handleResize.bind(this), 250));
        
        // Hero scroll indicator
        const scrollIndicator = document.querySelector('.scroll-indicator');
        if (scrollIndicator) {
            scrollIndicator.addEventListener('click', () => {
                this.scrollToSection('.stats-section');
            });
        }
        
        // Service links smooth scroll
        document.querySelectorAll('.service-link').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href.startsWith('#')) {
                    e.preventDefault();
                    this.scrollToSection(href);
                }
            });
        });
        
        // Domain search form
        const domainForm = document.querySelector('.domain-search-form form');
        if (domainForm) {
            domainForm.addEventListener('submit', this.handleDomainSearch.bind(this));
        }
        
        // Plan selection
        document.querySelectorAll('[data-plan]').forEach(element => {
            element.addEventListener('click', this.handlePlanSelection.bind(this));
        });
    }
    
    initAnimations() {
        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const delay = element.dataset.delay || 0;
                    
                    setTimeout(() => {
                        element.classList.add('animate-in');
                        
                        // Trigger counter animation if it's a stat number
                        if (element.classList.contains('stat-number')) {
                            this.animateCounter(element);
                        }
                    }, delay);
                    
                    // Stop observing this element
                    animationObserver.unobserve(element);
                }
            });
        }, observerOptions);
        
        // Observe all animated elements
        document.querySelectorAll('[data-aos]').forEach(element => {
            animationObserver.observe(element);
        });
        
        this.animations.observers.set('main', animationObserver);
    }
    
    initCounters() {
        const counters = document.querySelectorAll('.stat-number[data-count]');
        counters.forEach(counter => {
            // Store initial state
            this.animations.counters.set(counter, {
                target: parseInt(counter.dataset.count),
                current: 0,
                suffix: counter.dataset.suffix || '',
                animated: false
            });
        });
    }
    
    animateCounter(element) {
        const counterData = this.animations.counters.get(element);
        if (!counterData || counterData.animated) return;
        
        counterData.animated = true;
        const duration = 2000; // 2 seconds
        const steps = 60; // 60 FPS
        const increment = counterData.target / steps;
        const stepDuration = duration / steps;
        
        let currentStep = 0;
        
        const updateCounter = () => {
            currentStep++;
            const progress = currentStep / steps;
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const currentValue = Math.floor(counterData.target * easeOutQuart);
            
            element.textContent = this.formatNumber(currentValue) + counterData.suffix;
            
            if (currentStep < steps) {
                setTimeout(updateCounter, stepDuration);
            } else {
                element.textContent = this.formatNumber(counterData.target) + counterData.suffix;
            }
        };
        
        updateCounter();
    }
    
    formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    
    initParallax() {
        // Floating elements parallax
        const floatingElements = document.querySelectorAll('.floating-element');
        
        this.parallaxElements = Array.from(floatingElements).map(element => ({
            element,
            speed: parseFloat(element.dataset.speed) || 0.5,
            offset: 0
        }));
    }
    
    handleScroll() {
        const scrollTop = window.pageYOffset;
        
        // Update parallax elements
        this.parallaxElements?.forEach(item => {
            const offset = scrollTop * item.speed;
            item.element.style.transform = `translate3d(0, ${offset}px, 0)`;
        });
        
        // Update scroll indicator visibility
        const scrollIndicator = document.querySelector('.scroll-indicator');
        if (scrollIndicator) {
            const opacity = Math.max(0, 1 - (scrollTop / window.innerHeight));
            scrollIndicator.style.opacity = opacity;
        }
        
        // Parallax for hero content
        const heroContent = document.querySelector('.hero-content');
        if (heroContent) {
            const parallaxOffset = scrollTop * 0.3;
            heroContent.style.transform = `translateY(${parallaxOffset}px)`;
        }
    }
    
    handleResize() {
        // Recalculate animations and layouts
        this.initParallax();
        
        // Update server animation if needed
        this.updateServerAnimation();
    }
    
    initNewsletter() {
        this.newsletter.form = document.getElementById('newsletter-form');
        if (!this.newsletter.form) return;
        
        this.newsletter.form.addEventListener('submit', this.handleNewsletterSubmit.bind(this));
        
        // Email validation
        const emailInput = this.newsletter.form.querySelector('input[type="email"]');
        if (emailInput) {
            emailInput.addEventListener('input', this.validateEmail.bind(this));
        }
    }
    
    async handleNewsletterSubmit(e) {
        e.preventDefault();
        
        if (this.newsletter.isSubmitting) return;
        
        const formData = new FormData(this.newsletter.form);
        const email = formData.get('email');
        
        if (!this.isValidEmail(email)) {
            this.showToast('Будь ласка, введіть коректну електронну адресу', 'error');
            return;
        }
        
        this.newsletter.isSubmitting = true;
        const submitButton = this.newsletter.form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Show loading state
        submitButton.innerHTML = '<span class="loading-spinner"></span> Підписка...';
        submitButton.disabled = true;
        
        try {
            const response = await fetch('/api/newsletter-subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showToast('Дякуємо за підписку! Перевірте вашу пошту для підтвердження.', 'success');
                this.newsletter.form.reset();
            } else {
                throw new Error(data.message || 'Помилка підписки');
            }
        } catch (error) {
            console.error('Newsletter subscription error:', error);
            this.showToast(error.message || 'Виникла помилка при підписці. Спробуйте пізніше.', 'error');
        } finally {
            this.newsletter.isSubmitting = false;
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    }
    
    validateEmail(e) {
        const email = e.target.value;
        const isValid = this.isValidEmail(email);
        
        e.target.classList.toggle('is-valid', isValid && email.length > 0);
        e.target.classList.toggle('is-invalid', !isValid && email.length > 0);
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    initLazyLoading() {
        // Lazy load images
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
        this.animations.observers.set('images', imageObserver);
    }
    
    initTypingEffect() {
        const typingElements = document.querySelectorAll('[data-typing]');
        
        typingElements.forEach(element => {
            const text = element.dataset.typing;
            const speed = parseInt(element.dataset.typingSpeed) || 100;
            
            element.textContent = '';
            this.typeWriter(element, text, speed);
        });
    }
    
    typeWriter(element, text, speed) {
        let i = 0;
        
        const type = () => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(type, speed);
            }
        };
        
        type();
    }
    
    startServerAnimation() {
        const servers = document.querySelectorAll('.server');
        
        servers.forEach((server, index) => {
            // Animate server lights with random delays
            const lights = server.querySelectorAll('.light');
            lights.forEach((light, lightIndex) => {
                const delay = (index * 500) + (lightIndex * 200);
                light.style.animationDelay = `${delay}ms`;
            });
            
            // Add random server activity
            setInterval(() => {
                if (Math.random() > 0.7) {
                    server.classList.add('activity');
                    setTimeout(() => {
                        server.classList.remove('activity');
                    }, 1000);
                }
            }, 3000 + (index * 1000));
        });
    }
    
    updateServerAnimation() {
        // Update animations based on screen size
        const isMobile = window.innerWidth < 768;
        const servers = document.querySelectorAll('.server');
        
        servers.forEach(server => {
            if (isMobile) {
                server.style.animationDuration = '4s';
            } else {
                server.style.animationDuration = '2s';
            }
        });
    }
    
    handleDomainSearch(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const domain = formData.get('domain');
        
        if (!domain) {
            this.showToast('Будь ласка, введіть назву домену', 'warning');
            return;
        }
        
        // Add loading state
        const submitButton = e.target.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<span class="loading-spinner"></span> Пошук...';
        submitButton.disabled = true;
        
        // Redirect to domain registration page with search query
        setTimeout(() => {
            window.location.href = `/pages/domains/register.php?domain=${encodeURIComponent(domain)}`;
        }, 500);
    }
    
    handlePlanSelection(e) {
        const planId = e.target.dataset.plan;
        const planName = e.target.closest('.pricing-card').querySelector('.plan-name').textContent;
        
        // Store selected plan in localStorage for the order form
        localStorage.setItem('selectedPlan', JSON.stringify({
            id: planId,
            name: planName,
            timestamp: Date.now()
        }));
        
        // Add visual feedback
        e.target.innerHTML = '<i class="bi bi-check-circle"></i> Вибрано';
        e.target.classList.add('btn-success');
        e.target.classList.remove('btn-primary');
        
        setTimeout(() => {
            e.target.innerHTML = '<i class="bi bi-cart-plus"></i> Замовити тариф';
            e.target.classList.remove('btn-success');
            e.target.classList.add('btn-primary');
        }, 2000);
        
        this.showToast(`Тариф "${planName}" додано до кошика`, 'success');
    }
    
    scrollToSection(selector) {
        const element = document.querySelector(selector);
        if (!element) return;
        
        const headerOffset = 100; // Account for fixed header
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
        
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
    
    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">
                    <i class="bi bi-${this.getToastIcon(type)}"></i>
                </div>
                <div class="toast-message">${message}</div>
                <button class="toast-close" aria-label="Закрити">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        
        // Add styles if not already present
        this.addToastStyles();
        
        // Add to container
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove
        const autoRemoveTimeout = setTimeout(() => {
            this.removeToast(toast);
        }, 5000);
        
        // Manual close
        toast.querySelector('.toast-close').addEventListener('click', () => {
            clearTimeout(autoRemoveTimeout);
            this.removeToast(toast);
        });
    }
    
    removeToast(toast) {
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
    
    getToastIcon(type) {
        const icons = {
            success: 'check-circle-fill',
            error: 'exclamation-circle-fill',
            warning: 'exclamation-triangle-fill',
            info: 'info-circle-fill'
        };
        return icons[type] || icons.info;
    }
    
    addToastStyles() {
        if (document.querySelector('#toast-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'toast-styles';
        styles.textContent = `
            .toast-container {
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 1060;
                max-width: 400px;
            }
            
            .toast-notification {
                background: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                margin-bottom: 1rem;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease-out;
                border-left: 4px solid;
            }
            
            .toast-notification.show {
                opacity: 1;
                transform: translateX(0);
            }
            
            .toast-notification.hide {
                opacity: 0;
                transform: translateX(100%);
            }
            
            .toast-success { border-left-color: #28a745; }
            .toast-error { border-left-color: #dc3545; }
            .toast-warning { border-left-color: #ffc107; }
            .toast-info { border-left-color: #17a2b8; }
            
            .toast-content {
                display: flex;
                align-items: center;
                padding: 1rem;
            }
            
            .toast-icon {
                margin-right: 0.75rem;
                font-size: 1.25rem;
            }
            
            .toast-success .toast-icon { color: #28a745; }
            .toast-error .toast-icon { color: #dc3545; }
            .toast-warning .toast-icon { color: #ffc107; }
            .toast-info .toast-icon { color: #17a2b8; }
            
            .toast-message {
                flex: 1;
                font-weight: 500;
            }
            
            .toast-close {
                background: none;
                border: none;
                font-size: 1.25rem;
                color: #6c757d;
                cursor: pointer;
                padding: 0;
                margin-left: 0.75rem;
                transition: color 0.2s;
            }
            
            .toast-close:hover {
                color: #495057;
            }
            
            @media (max-width: 575.98px) {
                .toast-container {
                    left: 10px;
                    right: 10px;
                    max-width: none;
                }
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    // Utility functions
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
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
    
    // Public API
    static getInstance() {
        if (!StormHomePage.instance) {
            StormHomePage.instance = new StormHomePage();
        }
        return StormHomePage.instance;
    }
    
    // Cleanup method
    destroy() {
        this.animations.observers.forEach(observer => observer.disconnect());
        this.animations.observers.clear();
        this.animations.counters.clear();
    }
}

// Add additional CSS for server activity animation
const additionalStyles = `
    .server.activity {
        background: rgba(40, 167, 69, 0.2) !important;
        transform: scale(1.02);
    }
    
    .server.activity .light {
        animation-duration: 0.5s !important;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: currentColor;
        animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
    
    [data-aos] {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease-out;
    }
    
    img.loaded {
        opacity: 1;
        transition: opacity 0.3s ease;
    }
    
    img[data-src] {
        opacity: 0;
    }
`;

// Inject additional styles
if (!document.querySelector('#home-additional-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'home-additional-styles';
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        StormHomePage.getInstance();
    });
} else {
    StormHomePage.getInstance();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StormHomePage;
}

// Global access
window.StormHomePage = StormHomePage;