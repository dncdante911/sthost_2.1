/**
 * Modern Header JavaScript for StormHosting UA
 * File: /assets/js/header.js
 * Version: 2.0
 */

class ModernHeader {
    constructor() {
        this.header = document.querySelector('.site-header');
        this.navbar = document.querySelector('.navbar');
        this.navbarToggler = document.querySelector('.navbar-toggler');
        this.navbarCollapse = document.querySelector('.navbar-collapse');
        this.dropdowns = document.querySelectorAll('.dropdown');
        this.flashContainer = document.getElementById('flash-messages');
        
        this.isScrolled = false;
        this.isMenuOpen = false;
        this.activeDropdown = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initScrollEffects();
        this.initDropdowns();
        this.initMobileMenu();
        this.initScrollToTop();
        this.initKeyboardNavigation();
        this.initFlashMessages();
        this.loadLanguagePreference();
    }
    
    bindEvents() {
        // Scroll event with throttling
        let scrollTimer = null;
        window.addEventListener('scroll', () => {
            if (scrollTimer) return;
            scrollTimer = setTimeout(() => {
                this.handleScroll();
                scrollTimer = null;
            }, 16); // ~60fps
        });
        
        // Resize event
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));
        
        // Click outside to close dropdowns
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                this.closeAllDropdowns();
            }
        });
        
        // Language change
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[name="language"]')) {
                this.changeLanguage(e.target.value);
            }
        });
    }
    
    handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const shouldBeScrolled = scrollTop > 50;
        
        if (shouldBeScrolled !== this.isScrolled) {
            this.isScrolled = shouldBeScrolled;
            this.header.classList.toggle('scrolled', this.isScrolled);
            
            // Add parallax effect to brand
            const brand = document.querySelector('.navbar-brand');
            if (brand) {
                const parallaxOffset = scrollTop * 0.1;
                brand.style.transform = `translateY(${parallaxOffset}px)`;
            }
        }
        
        // Update scroll to top button
        this.updateScrollToTop(scrollTop);
    }
    
    initScrollEffects() {
        // Smooth reveal animation for navigation items
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        navLinks.forEach((link, index) => {
            link.style.animationDelay = `${index * 0.1}s`;
            link.classList.add('animate-slide-in');
        });
        
        // Add CSS for animation
        if (!document.querySelector('#nav-animations')) {
            const style = document.createElement('style');
            style.id = 'nav-animations';
            style.textContent = `
                .animate-slide-in {
                    opacity: 0;
                    transform: translateY(-20px);
                    animation: slideInDown 0.6s ease-out forwards;
                }
                
                @keyframes slideInDown {
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    initDropdowns() {
        this.dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (!toggle || !menu) return;
            
            // Hover events for desktop
            if (window.innerWidth > 991) {
                dropdown.addEventListener('mouseenter', () => {
                    this.openDropdown(dropdown);
                });
                
                dropdown.addEventListener('mouseleave', () => {
                    this.closeDropdown(dropdown);
                });
            }
            
            // Click events for mobile and accessibility
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                if (dropdown.classList.contains('show')) {
                    this.closeDropdown(dropdown);
                } else {
                    this.closeAllDropdowns();
                    this.openDropdown(dropdown);
                }
            });
            
            // Keyboard navigation
            toggle.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle.click();
                } else if (e.key === 'Escape') {
                    this.closeDropdown(dropdown);
                    toggle.focus();
                }
            });
        });
    }
    
    openDropdown(dropdown) {
        this.closeAllDropdowns();
        dropdown.classList.add('show');
        dropdown.querySelector('.dropdown-menu').classList.add('show');
        dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'true');
        this.activeDropdown = dropdown;
        
        // Add smooth animation
        const menu = dropdown.querySelector('.dropdown-menu');
        menu.style.animation = 'dropdownSlideIn 0.3s ease-out forwards';
    }
    
    closeDropdown(dropdown) {
        if (!dropdown) return;
        
        const menu = dropdown.querySelector('.dropdown-menu');
        menu.style.animation = 'dropdownSlideOut 0.2s ease-in forwards';
        
        setTimeout(() => {
            dropdown.classList.remove('show');
            menu.classList.remove('show');
            dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            menu.style.animation = '';
        }, 200);
        
        if (this.activeDropdown === dropdown) {
            this.activeDropdown = null;
        }
    }
    
    closeAllDropdowns() {
        this.dropdowns.forEach(dropdown => {
            if (dropdown.classList.contains('show')) {
                this.closeDropdown(dropdown);
            }
        });
    }
    
    initMobileMenu() {
        if (!this.navbarToggler) return;
        
        this.navbarToggler.addEventListener('click', () => {
            this.toggleMobileMenu();
        });
        
        // Close mobile menu when clicking on nav links
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (this.isMenuOpen) {
                    this.closeMobileMenu();
                }
            });
        });
    }
    
    toggleMobileMenu() {
        this.isMenuOpen = !this.isMenuOpen;
        
        if (this.isMenuOpen) {
            this.openMobileMenu();
        } else {
            this.closeMobileMenu();
        }
    }
    
    openMobileMenu() {
        this.navbarCollapse.classList.add('show');
        this.navbarToggler.classList.add('active');
        this.navbarToggler.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        
        // Animate menu items
        const navItems = this.navbarCollapse.querySelectorAll('.nav-item');
        navItems.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
            item.classList.add('animate-slide-in');
        });
    }
    
    closeMobileMenu() {
        this.navbarCollapse.classList.remove('show');
        this.navbarToggler.classList.remove('active');
        this.navbarToggler.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        this.closeAllDropdowns();
    }
    
    handleResize() {
        if (window.innerWidth > 991 && this.isMenuOpen) {
            this.closeMobileMenu();
        }
        
        // Reinitialize dropdown behavior based on screen size
        this.initDropdowns();
    }
    
    initScrollToTop() {
        // Create scroll to top button if it doesn't exist
        let scrollBtn = document.querySelector('.scroll-to-top');
        if (!scrollBtn) {
            scrollBtn = document.createElement('button');
            scrollBtn.className = 'scroll-to-top';
            scrollBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
            scrollBtn.setAttribute('aria-label', 'Scroll to top');
            document.body.appendChild(scrollBtn);
        }
        
        scrollBtn.addEventListener('click', () => {
            this.scrollToTop();
        });
        
        this.scrollBtn = scrollBtn;
    }
    
    updateScrollToTop(scrollTop) {
        if (!this.scrollBtn) return;
        
        const shouldShow = scrollTop > 300;
        this.scrollBtn.classList.toggle('visible', shouldShow);
    }
    
    scrollToTop() {
        const startPosition = window.pageYOffset;
        const startTime = performance.now();
        const duration = 800;
        
        const easeInOutCubic = (t) => {
            return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
        };
        
        const animateScroll = (currentTime) => {
            const timeElapsed = currentTime - startTime;
            const progress = Math.min(timeElapsed / duration, 1);
            const ease = easeInOutCubic(progress);
            
            window.scrollTo(0, startPosition * (1 - ease));
            
            if (progress < 1) {
                requestAnimationFrame(animateScroll);
            }
        };
        
        requestAnimationFrame(animateScroll);
    }
    
    initKeyboardNavigation() {
        // Enhanced keyboard navigation
        document.addEventListener('keydown', (e) => {
            // ESC to close all dropdowns and mobile menu
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
                if (this.isMenuOpen) {
                    this.closeMobileMenu();
                    this.navbarToggler.focus();
                }
            }
            
            // Tab navigation improvements
            if (e.key === 'Tab') {
                if (this.activeDropdown && !this.activeDropdown.contains(e.target)) {
                    this.closeDropdown(this.activeDropdown);
                }
            }
        });
    }
    
    initFlashMessages() {
        if (!this.flashContainer) return;
        
        // Auto-hide flash messages
        const alerts = this.flashContainer.querySelectorAll('.alert');
        alerts.forEach(alert => {
            this.setupFlashMessage(alert);
        });
        
        // Observer for dynamically added messages
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1 && node.classList.contains('alert')) {
                        this.setupFlashMessage(node);
                    }
                });
            });
        });
        
        observer.observe(this.flashContainer, { childList: true });
    }
    
    setupFlashMessage(alert) {
        // Add close button if it doesn't exist
        if (!alert.querySelector('.btn-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'btn-close btn-close-white';
            closeBtn.setAttribute('aria-label', 'Close');
            closeBtn.addEventListener('click', () => {
                this.hideFlashMessage(alert);
            });
            alert.appendChild(closeBtn);
        }
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                this.hideFlashMessage(alert);
            }
        }, 5000);
    }
    
    hideFlashMessage(alert) {
        alert.style.animation = 'slideOutRight 0.3s ease-in forwards';
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 300);
    }
    
    showFlashMessage(message, type = 'info') {
        if (!this.flashContainer) return;
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close btn-close-white" aria-label="Close"></button>
        `;
        
        this.flashContainer.appendChild(alert);
        this.setupFlashMessage(alert);
    }
    
    changeLanguage(language) {
        // Save language preference
        localStorage.setItem('preferred_language', language);
        
        // Show loading state
        const langToggle = document.querySelector('.language-selector .dropdown-toggle');
        if (langToggle) {
            const originalText = langToggle.textContent;
            langToggle.innerHTML = '<span class="loading-spinner"></span> Загрузка...';
            
            // Submit form or make AJAX request
            const form = document.querySelector('#language-form');
            if (form) {
                const languageInput = form.querySelector('input[name="language"]');
                if (languageInput) {
                    languageInput.value = language;
                    form.submit();
                }
            } else {
                // AJAX language change
                this.ajaxChangeLanguage(language).then(() => {
                    location.reload();
                }).catch(() => {
                    langToggle.textContent = originalText;
                    this.showFlashMessage('Помилка зміни мови', 'danger');
                });
            }
        }
    }
    
    async ajaxChangeLanguage(language) {
        const response = await fetch('/api/change-language.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ language })
        });
        
        if (!response.ok) {
            throw new Error('Language change failed');
        }
        
        return response.json();
    }
    
    loadLanguagePreference() {
        const savedLang = localStorage.getItem('preferred_language');
        if (savedLang) {
            const currentLang = document.documentElement.lang;
            if (savedLang !== currentLang) {
                // Optional: Auto-switch to saved language
                // this.changeLanguage(savedLang);
            }
        }
    }
    
    // Utility functions
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
    
    // Public API
    static getInstance() {
        if (!ModernHeader.instance) {
            ModernHeader.instance = new ModernHeader();
        }
        return ModernHeader.instance;
    }
    
    // Public methods for external use
    openDropdownById(id) {
        const dropdown = document.getElementById(id);
        if (dropdown && dropdown.classList.contains('dropdown')) {
            this.openDropdown(dropdown);
        }
    }
    
    closeDropdownById(id) {
        const dropdown = document.getElementById(id);
        if (dropdown && dropdown.classList.contains('dropdown')) {
            this.closeDropdown(dropdown);
        }
    }
    
    showNotification(message, type = 'info', duration = 5000) {
        this.showFlashMessage(message, type);
    }
    
    updateCartCount(count) {
        const cartBadge = document.querySelector('.cart-badge');
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }
}

// Add necessary CSS animations
const headerAnimations = `
    @keyframes dropdownSlideIn {
        from {
            opacity: 0;
            transform: translateY(-10px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    @keyframes dropdownSlideOut {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        to {
            opacity: 0;
            transform: translateY(-10px) scale(0.95);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    .navbar-toggler.active .navbar-toggler-icon {
        transform: rotate(45deg);
    }
    
    .navbar-toggler.active .navbar-toggler-icon::before {
        transform: rotate(90deg);
        top: 0;
    }
    
    .navbar-toggler.active .navbar-toggler-icon::after {
        transform: rotate(90deg);
        top: 0;
    }
`;

// Inject animations CSS
if (!document.querySelector('#header-animations')) {
    const style = document.createElement('style');
    style.id = 'header-animations';
    style.textContent = headerAnimations;
    document.head.appendChild(style);
}

// Initialize header when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        ModernHeader.getInstance();
    });
} else {
    ModernHeader.getInstance();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernHeader;
}

// Global access
window.ModernHeader = ModernHeader;