/**
 * StormHosting UA - Header JavaScript
 * Functionality for navigation, dropdowns, mobile menu, and user interactions
 */

class StormHeader {
    constructor() {
        this.navbar = null;
        this.preloader = null;
        this.promoBanner = null;
        this.dropdowns = [];
        this.isScrolled = false;
        this.isMobile = window.innerWidth < 992;
        
        this.init();
    }

    /**
     * Initialize all header functionality
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.handlePreloader();
        this.initializeScrollEffects();
        this.initializeDropdowns();
        this.initializeMobileMenu();
        this.initializeLanguageSwitcher();
        this.initializePromoBanner();
        this.initializeAccessibility();
    }

    /**
     * Cache DOM elements for better performance
     */
    cacheElements() {
        this.navbar = document.getElementById('mainNavbar');
        this.preloader = document.getElementById('preloader');
        this.promoBanner = document.getElementById('promoBanner');
        this.mobileToggle = document.querySelector('.navbar-toggler');
        this.navbarCollapse = document.querySelector('.navbar-collapse');
        this.dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        this.dropdownMenus = document.querySelectorAll('.dropdown-menu');
        this.navLinks = document.querySelectorAll('.nav-link');
        this.languageSwitcher = document.querySelector('.language-switcher');
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Window events
        window.addEventListener('scroll', this.handleScroll.bind(this));
        window.addEventListener('resize', this.handleResize.bind(this));
        window.addEventListener('load', this.handlePageLoad.bind(this));

        // Navigation events
        this.bindNavigationEvents();
        this.bindDropdownEvents();
        this.bindMobileEvents();

        // Accessibility events
        this.bindKeyboardEvents();
    }

    /**
     * Handle page scroll effects
     */
    handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const shouldBeScrolled = scrollTop > 50;

        if (shouldBeScrolled !== this.isScrolled) {
            this.isScrolled = shouldBeScrolled;
            
            if (this.navbar) {
                this.navbar.classList.toggle('scrolled', this.isScrolled);
            }
        }

        // Throttle scroll events for better performance
        this.throttle(this.updateScrollProgress.bind(this), 16)();
    }

    /**
     * Update scroll progress for any progress indicators
     */
    updateScrollProgress() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        
        // Dispatch custom event for scroll progress
        window.dispatchEvent(new CustomEvent('scrollProgress', {
            detail: { progress: scrolled }
        }));
    }

    /**
     * Handle window resize
     */
    handleResize() {
        const wasMobile = this.isMobile;
        this.isMobile = window.innerWidth < 992;

        if (wasMobile !== this.isMobile) {
            this.handleResponsiveChanges();
        }
    }

    /**
     * Handle responsive layout changes
     */
    handleResponsiveChanges() {
        if (!this.isMobile) {
            // Desktop view - close mobile menu if open
            if (this.navbarCollapse?.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(this.navbarCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }
        }

        // Re-initialize dropdowns for current viewport
        this.initializeDropdowns();
    }

    /**
     * Handle preloader
     */
    handlePreloader() {
        if (!this.preloader) return;

        const hidePreloader = () => {
            this.preloader.classList.add('hidden');
            setTimeout(() => {
                if (this.preloader && this.preloader.parentNode) {
                    this.preloader.parentNode.removeChild(this.preloader);
                }
            }, 500);
        };

        // Hide preloader after minimum display time
        const minDisplayTime = 1000;
        const startTime = Date.now();

        const checkReady = () => {
            const elapsed = Date.now() - startTime;
            if (elapsed >= minDisplayTime) {
                hidePreloader();
            } else {
                setTimeout(hidePreloader, minDisplayTime - elapsed);
            }
        };

        if (document.readyState === 'complete') {
            checkReady();
        } else {
            window.addEventListener('load', checkReady);
        }
    }

    /**
     * Initialize scroll effects
     */
    initializeScrollEffects() {
        // Smooth scrolling for anchor links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href^="#"]');
            if (!link) return;

            const targetId = link.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (!targetElement) return;

            e.preventDefault();
            this.smoothScrollTo(targetElement);
        });
    }

    /**
     * Smooth scroll to element
     */
    smoothScrollTo(element, offset = 80) {
        const elementPosition = element.offsetTop;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }

    /**
     * Initialize dropdown functionality
     */
    initializeDropdowns() {
        this.dropdownToggles.forEach(toggle => {
            // Desktop hover behavior
            if (!this.isMobile) {
                const dropdownElement = toggle.closest('.dropdown');
                if (dropdownElement) {
                    this.setupDropdownHover(dropdownElement, toggle);
                }
            }

            // Click behavior for all devices
            toggle.addEventListener('click', (e) => {
                if (!this.isMobile) {
                    e.preventDefault();
                }
                this.toggleDropdown(toggle);
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                this.closeAllDropdowns();
            }
        });
    }

    /**
     * Setup dropdown hover behavior for desktop
     */
    setupDropdownHover(dropdownElement, toggle) {
        let hoverTimeout;

        const showDropdown = () => {
            clearTimeout(hoverTimeout);
            const dropdown = bootstrap.Dropdown.getOrCreateInstance(toggle);
            dropdown.show();
        };

        const hideDropdown = () => {
            hoverTimeout = setTimeout(() => {
                const dropdown = bootstrap.Dropdown.getInstance(toggle);
                if (dropdown) {
                    dropdown.hide();
                }
            }, 150);
        };

        dropdownElement.addEventListener('mouseenter', showDropdown);
        dropdownElement.addEventListener('mouseleave', hideDropdown);
    }

    /**
     * Toggle dropdown state
     */
    toggleDropdown(toggle) {
        const dropdown = bootstrap.Dropdown.getOrCreateInstance(toggle);
        const isShown = toggle.getAttribute('aria-expanded') === 'true';
        
        if (isShown) {
            dropdown.hide();
        } else {
            this.closeAllDropdowns();
            dropdown.show();
        }
    }

    /**
     * Close all open dropdowns
     */
    closeAllDropdowns() {
        this.dropdownToggles.forEach(toggle => {
            const dropdown = bootstrap.Dropdown.getInstance(toggle);
            if (dropdown) {
                dropdown.hide();
            }
        });
    }

    /**
     * Bind navigation events
     */
    bindNavigationEvents() {
        this.navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Close mobile menu when navigation link is clicked
                if (this.isMobile && this.navbarCollapse?.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(this.navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }

                // Add loading state for external links
                if (link.hostname !== window.location.hostname) {
                    this.addLoadingState(link);
                }
            });

            // Preload linked pages on hover
            link.addEventListener('mouseenter', () => {
                this.preloadPage(link.href);
            });
        });
    }

    /**
     * Bind dropdown events
     */
    bindDropdownEvents() {
        this.dropdownMenus.forEach(menu => {
            // Prevent dropdown from closing when clicking inside
            menu.addEventListener('click', (e) => {
                if (e.target.classList.contains('dropdown-item')) {
                    return; // Allow dropdown-item clicks to close dropdown
                }
                e.stopPropagation();
            });

            // Add keyboard navigation
            menu.addEventListener('keydown', (e) => {
                this.handleDropdownKeyboard(e, menu);
            });
        });
    }

    /**
     * Handle dropdown keyboard navigation
     */
    handleDropdownKeyboard(e, menu) {
        const items = menu.querySelectorAll('.dropdown-item:not(.disabled)');
        const currentIndex = Array.from(items).indexOf(document.activeElement);

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                const nextIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0;
                items[nextIndex]?.focus();
                break;

            case 'ArrowUp':
                e.preventDefault();
                const prevIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
                items[prevIndex]?.focus();
                break;

            case 'Escape':
                e.preventDefault();
                const toggle = menu.previousElementSibling;
                if (toggle) {
                    const dropdown = bootstrap.Dropdown.getInstance(toggle);
                    if (dropdown) {
                        dropdown.hide();
                        toggle.focus();
                    }
                }
                break;
        }
    }

    /**
     * Initialize mobile menu
     */
    initializeMobileMenu() {
        if (!this.mobileToggle) return;

        this.mobileToggle.addEventListener('click', () => {
            // Add haptic feedback for mobile devices
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
        });

        // Handle mobile menu state changes
        if (this.navbarCollapse) {
            this.navbarCollapse.addEventListener('show.bs.collapse', () => {
                document.body.style.overflow = 'hidden';
                this.mobileToggle?.setAttribute('aria-expanded', 'true');
            });

            this.navbarCollapse.addEventListener('hide.bs.collapse', () => {
                document.body.style.overflow = '';
                this.mobileToggle?.setAttribute('aria-expanded', 'false');
            });
        }
    }

    /**
     * Bind mobile events
     */
    bindMobileEvents() {
        // Handle mobile touch events for better UX
        let touchStartY = 0;
        let touchEndY = 0;

        document.addEventListener('touchstart', (e) => {
            touchStartY = e.changedTouches[0].screenY;
        });

        document.addEventListener('touchend', (e) => {
            touchEndY = e.changedTouches[0].screenY;
            this.handleSwipeGesture(touchStartY, touchEndY);
        });
    }

    /**
     * Handle swipe gestures
     */
    handleSwipeGesture(startY, endY) {
        const swipeThreshold = 100;
        const diff = startY - endY;

        // Swipe up to close mobile menu
        if (diff > swipeThreshold && this.navbarCollapse?.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getInstance(this.navbarCollapse);
            if (bsCollapse) {
                bsCollapse.hide();
            }
        }
    }

    /**
     * Initialize language switcher
     */
    initializeLanguageSwitcher() {
        if (!this.languageSwitcher) return;

        const languageLinks = this.languageSwitcher.querySelectorAll('.dropdown-item');
        
        languageLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.changeLanguage(link.textContent.trim().toLowerCase());
            });
        });
    }

    /**
     * Change site language
     */
    changeLanguage(language) {
        // Add loading state
        const switcher = this.languageSwitcher.querySelector('.btn');
        if (switcher) {
            switcher.classList.add('loading');
        }

        // Update URL with language parameter
        const url = new URL(window.location);
        url.searchParams.set('lang', language);

        // Navigate to new URL
        window.location.href = url.toString();
    }

    /**
     * Initialize promo banner
     */
    initializePromoBanner() {
        if (!this.promoBanner) return;

        const closeButton = this.promoBanner.querySelector('.btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                this.hidePromoBanner();
            });
        }

        // Auto-hide after delay (optional)
        // setTimeout(() => this.hidePromoBanner(), 10000);
    }

    /**
     * Hide promo banner
     */
    hidePromoBanner() {
        if (!this.promoBanner) return;

        this.promoBanner.style.transform = 'translateY(-100%)';
        this.promoBanner.style.opacity = '0';

        setTimeout(() => {
            this.promoBanner.style.display = 'none';
        }, 300);

        // Store preference in localStorage
        try {
            localStorage.setItem('promoBannerHidden', 'true');
        } catch (e) {
            // Handle localStorage errors gracefully
            console.warn('Could not save promo banner state');
        }
    }

    /**
     * Initialize accessibility features
     */
    initializeAccessibility() {
        // Skip to content functionality
        const skipLink = document.querySelector('.skip-to-content');
        if (skipLink) {
            skipLink.addEventListener('click', (e) => {
                e.preventDefault();
                const mainContent = document.getElementById('main-content');
                if (mainContent) {
                    mainContent.focus();
                    mainContent.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }

        // Enhanced focus management
        this.setupFocusManagement();
    }

    /**
     * Setup focus management for keyboard navigation
     */
    setupFocusManagement() {
        // Trap focus in mobile menu when open
        if (this.navbarCollapse) {
            this.navbarCollapse.addEventListener('shown.bs.collapse', () => {
                const firstFocusable = this.navbarCollapse.querySelector('a, button');
                if (firstFocusable) {
                    firstFocusable.focus();
                }
            });
        }

        // Improve focus visibility
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });
    }

    /**
     * Bind keyboard events
     */
    bindKeyboardEvents() {
        document.addEventListener('keydown', (e) => {
            // ESC key to close mobile menu and dropdowns
            if (e.key === 'Escape') {
                this.handleEscapeKey();
            }

            // Alt + M to toggle mobile menu
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                if (this.isMobile && this.mobileToggle) {
                    this.mobileToggle.click();
                }
            }
        });
    }

    /**
     * Handle escape key press
     */
    handleEscapeKey() {
        // Close dropdowns
        this.closeAllDropdowns();

        // Close mobile menu
        if (this.navbarCollapse?.classList.contains('show')) {
            const bsCollapse = bootstrap.Collapse.getInstance(this.navbarCollapse);
            if (bsCollapse) {
                bsCollapse.hide();
            }
        }
    }

    /**
     * Add loading state to element
     */
    addLoadingState(element) {
        element.classList.add('loading');
        element.style.pointerEvents = 'none';

        // Remove loading state after a delay
        setTimeout(() => {
            element.classList.remove('loading');
            element.style.pointerEvents = '';
        }, 2000);
    }

    /**
     * Preload page for faster navigation
     */
    preloadPage(url) {
        if (!url || url === '#' || url.startsWith('mailto:') || url.startsWith('tel:')) {
            return;
        }

        // Only preload internal links
        try {
            const linkUrl = new URL(url, window.location.origin);
            if (linkUrl.origin === window.location.origin) {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                document.head.appendChild(link);
            }
        } catch (e) {
            // Handle invalid URLs gracefully
        }
    }

    /**
     * Handle page load completion
     */
    handlePageLoad() {
        // Add loaded class to body for CSS animations
        document.body.classList.add('page-loaded');

        // Initialize any additional functionality that requires full page load
        this.initializeAdvancedFeatures();
    }

    /**
     * Initialize advanced features after page load
     */
    initializeAdvancedFeatures() {
        // Add intersection observer for navbar visibility
        this.setupIntersectionObserver();

        // Initialize performance monitoring
        this.initializePerformanceMonitoring();
    }

    /**
     * Setup intersection observer for advanced animations
     */
    setupIntersectionObserver() {
        if (!('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                } else {
                    entry.target.classList.remove('animate-in');
                }
            });
        }, { threshold: 0.1 });

        // Observe dropdown menus for animation triggers
        this.dropdownMenus.forEach(menu => observer.observe(menu));
    }

    /**
     * Initialize performance monitoring
     */
    initializePerformanceMonitoring() {
        // Monitor navigation timing
        if ('performance' in window && 'getEntriesByType' in performance) {
            const navigationEntries = performance.getEntriesByType('navigation');
            if (navigationEntries.length > 0) {
                const loadTime = navigationEntries[0].loadEventEnd - navigationEntries[0].fetchStart;
                console.log(`Page load time: ${loadTime}ms`);
            }
        }
    }

    /**
     * Utility function for throttling
     */
    throttle(func, delay) {
        let timeoutId;
        let lastExecTime = 0;
        
        return function (...args) {
            const currentTime = Date.now();
            
            if (currentTime - lastExecTime > delay) {
                func.apply(this, args);
                lastExecTime = currentTime;
            } else {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    func.apply(this, args);
                    lastExecTime = Date.now();
                }, delay - (currentTime - lastExecTime));
            }
        };
    }

    /**
     * Utility function for debouncing
     */
    debounce(func, delay) {
        let timeoutId;
        
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }
}

// Initialize header when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new StormHeader();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StormHeader;
}