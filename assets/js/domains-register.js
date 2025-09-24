/**
 * StormHosting UA - Domain Registration Page JavaScript
 * Functionality for domain search, validation, and user interactions
 */

class DomainRegistration {
    constructor() {
        this.searchForm = null;
        this.searchResults = null;
        this.bulkSearchActive = false;
        this.searchCache = new Map();
        this.searchTimeout = null;
        
        this.init();
    }

    /**
     * Initialize all functionality
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.initializeAnimations();
        this.setupValidation();
    }

    /**
     * Cache DOM elements for better performance
     */
    cacheElements() {
        this.searchForm = document.getElementById('domainSearchForm');
        this.domainInput = document.getElementById('domainName');
        this.zoneSelect = document.getElementById('domainZone');
        this.searchResults = document.getElementById('searchResults');
        this.bulkToggle = document.getElementById('toggleBulkSearch');
        this.csrfToken = document.getElementById('csrf_token');
        this.quickSearchButtons = document.querySelectorAll('.btn-check-domain');
        this.domainCards = document.querySelectorAll('.domain-card');
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Main search form
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', this.handleSearch.bind(this));
        }

        // Real-time domain validation
        if (this.domainInput) {
            this.domainInput.addEventListener('input', this.handleDomainInput.bind(this));
            this.domainInput.addEventListener('keydown', this.handleKeyDown.bind(this));
        }

        // Zone selector change
        if (this.zoneSelect) {
            this.zoneSelect.addEventListener('change', this.handleZoneChange.bind(this));
        }

        // Bulk search toggle
        if (this.bulkToggle) {
            this.bulkToggle.addEventListener('click', this.toggleBulkSearch.bind(this));
        }

        // Quick search buttons
        this.quickSearchButtons.forEach(button => {
            button.addEventListener('click', this.handleQuickSearch.bind(this));
        });

        // Domain card interactions
        this.domainCards.forEach(card => {
            card.addEventListener('mouseenter', this.handleCardHover.bind(this));
            card.addEventListener('mouseleave', this.handleCardLeave.bind(this));
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleGlobalKeyDown.bind(this));
    }

    /**
     * Handle domain input with real-time validation
     */
    handleDomainInput(event) {
        const value = event.target.value;
        
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Real-time validation
        this.validateDomainInput(value);

        // Auto-search after user stops typing
        if (value.length >= 2) {
            this.searchTimeout = setTimeout(() => {
                if (this.domainInput.value === value) {
                    this.performQuickCheck(value);
                }
            }, 1000);
        }
    }

    /**
     * Validate domain input in real-time
     */
    validateDomainInput(value) {
        const input = this.domainInput;
        const isValid = this.isValidDomainName(value);
        
        // Update input styling
        input.classList.toggle('is-invalid', value.length > 0 && !isValid);
        input.classList.toggle('is-valid', value.length > 0 && isValid);

        // Show validation message
        this.showValidationFeedback(value, isValid);
    }

    /**
     * Check if domain name is valid
     */
    isValidDomainName(domain) {
        if (!domain || domain.length < 2 || domain.length > 63) return false;
        if (domain.startsWith('-') || domain.endsWith('-')) return false;
        if (domain.includes('--')) return false;
        
        const validPattern = /^[a-zA-Z0-9-]+$/;
        return validPattern.test(domain);
    }

    /**
     * Show validation feedback to user
     */
    showValidationFeedback(value, isValid) {
        // Remove existing feedback
        const existingFeedback = document.querySelector('.domain-validation-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        if (value.length > 0 && !isValid) {
            const feedback = document.createElement('div');
            feedback.className = 'domain-validation-feedback';
            feedback.innerHTML = `
                <div class="validation-message error">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Недопустимі символи або формат доменного імені</span>
                </div>
            `;
            
            this.domainInput.parentNode.appendChild(feedback);
        }
    }

    /**
     * Handle keyboard navigation
     */
    handleKeyDown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            this.handleSearch(event);
        }
    }

    /**
     * Handle global keyboard shortcuts
     */
    handleGlobalKeyDown(event) {
        // Ctrl/Cmd + / to focus search
        if ((event.ctrlKey || event.metaKey) && event.key === '/') {
            event.preventDefault();
            this.domainInput?.focus();
        }

        // Escape to clear search
        if (event.key === 'Escape') {
            this.clearSearch();
        }
    }

    /**
     * Handle zone selector change
     */
    handleZoneChange(event) {
        const selectedOption = event.target.selectedOptions[0];
        const price = selectedOption.dataset.price;
        const renewal = selectedOption.dataset.renewal;

        // Update price display if needed
        this.updatePriceDisplay(price, renewal);

        // Re-search if domain is entered
        if (this.domainInput.value.trim()) {
            this.performQuickCheck(this.domainInput.value.trim());
        }
    }

    /**
     * Handle main search form submission
     */
    async handleSearch(event) {
        event.preventDefault();

        const domain = this.domainInput.value.trim();
        const zone = this.zoneSelect.value;

        if (!domain) {
            this.showError('Введіть ім\'я домену');
            this.domainInput.focus();
            return;
        }

        if (!this.isValidDomainName(domain)) {
            this.showError('Недопустимі символи в імені домену');
            this.domainInput.focus();
            return;
        }

        // Show loading state
        this.setSearchLoading(true);

        try {
            if (this.bulkSearchActive) {
                await this.performBulkSearch(domain);
            } else {
                await this.performSingleSearch(domain, zone);
            }
        } catch (error) {
            this.showError('Помилка при перевірці домену. Спробуйте ще раз.');
            console.error('Search error:', error);
        } finally {
            this.setSearchLoading(false);
        }
    }

    /**
     * Perform single domain search
     */
    async performSingleSearch(domain, zone) {
        const cacheKey = `${domain}${zone}`;
        
        // Check cache first
        if (this.searchCache.has(cacheKey)) {
            this.displaySingleResult(this.searchCache.get(cacheKey));
            return;
        }

        const formData = new FormData();
        formData.append('action', 'check_domain');
        formData.append('domain', domain);
        formData.append('zone', zone);
        formData.append('csrf_token', this.csrfToken.value);

        const response = await fetch(window.domainConfig.searchUrl, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.error) {
            this.showError(result.error);
            return;
        }

        // Cache result
        this.searchCache.set(cacheKey, result);

        this.displaySingleResult(result);
    }

    /**
     * Perform bulk search across multiple zones
     */
    async performBulkSearch(domain) {
        const popularZones = ['.ua', '.com.ua', '.pp.ua', '.kiev.ua', '.net.ua', '.org.ua', '.com', '.net'];
        
        const formData = new FormData();
        formData.append('action', 'bulk_check');
        formData.append('domain', domain);
        formData.append('csrf_token', this.csrfToken.value);
        
        popularZones.forEach(zone => {
            formData.append('zones[]', zone);
        });

        const response = await fetch(window.domainConfig.searchUrl, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.error) {
            this.showError(result.error);
            return;
        }

        this.displayBulkResults(result.results);
    }

    /**
     * Perform quick check without full form submission
     */
    async performQuickCheck(domain) {
        if (!this.isValidDomainName(domain)) return;

        const zone = this.zoneSelect.value;
        const cacheKey = `${domain}${zone}`;

        if (this.searchCache.has(cacheKey)) {
            this.showQuickResult(this.searchCache.get(cacheKey));
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'check_domain');
            formData.append('domain', domain);
            formData.append('zone', zone);
            formData.append('csrf_token', this.csrfToken.value);

            const response = await fetch(window.domainConfig.searchUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.error) {
                this.searchCache.set(cacheKey, result);
                this.showQuickResult(result);
            }
        } catch (error) {
            console.error('Quick check error:', error);
        }
    }

    /**
     * Handle quick search button clicks
     */
    handleQuickSearch(event) {
        const zone = event.target.dataset.zone;
        
        if (zone) {
            this.zoneSelect.value = zone;
            
            const domain = this.domainInput.value.trim();
            if (domain) {
                this.performSingleSearch(domain, zone);
            } else {
                this.domainInput.focus();
                this.domainInput.placeholder = `назва-домену${zone}`;
            }
        }
    }

    /**
     * Toggle bulk search mode
     */
    toggleBulkSearch() {
        this.bulkSearchActive = !this.bulkSearchActive;
        
        const toggleText = this.bulkSearchActive ? 
            'Повернутися до звичайного пошуку' : 
            'Перевірити у всіх популярних зонах';
            
        this.bulkToggle.innerHTML = `
            <i class="bi bi-${this.bulkSearchActive ? 'arrow-left' : 'list-check'}"></i>
            ${toggleText}
        `;

        // Update form appearance
        this.zoneSelect.style.display = this.bulkSearchActive ? 'none' : 'block';
        
        // Clear previous results
        this.clearSearchResults();
    }

    /**
     * Display single search result
     */
    displaySingleResult(result) {
        const statusClass = result.available ? 'result-available' : 'result-unavailable';
        const statusIcon = result.available ? 'check-circle' : 'x-circle';
        const statusText = result.available ? 'Доступний' : 'Зайнятий';
        const statusColor = result.available ? 'success' : 'danger';

        const html = `
            <div class="search-result-card ${statusClass}">
                <div class="result-header">
                    <div class="result-domain">${result.domain}</div>
                    <div class="result-status ${result.available ? 'available' : 'unavailable'}">
                        <i class="bi bi-${statusIcon}"></i>
                        <span>${statusText}</span>
                    </div>
                </div>
                
                ${result.available ? `
                    <div class="result-details">
                        <div class="result-price">${this.formatPrice(result.price)} / рік</div>
                        <div class="result-renewal">Продовження: ${this.formatPrice(result.renewal_price)} / рік</div>
                    </div>
                    
                    <div class="result-actions">
                        <button class="btn-register" onclick="window.location.href='/cart/add-domain?domain=${encodeURIComponent(result.domain)}'">
                            <i class="bi bi-cart-plus"></i>
                            Додати до кошика
                        </button>
                        <button class="btn btn-outline-primary" onclick="this.parentElement.previousElementSibling.style.display='block'">
                            <i class="bi bi-info-circle"></i>
                            Детальніше
                        </button>
                    </div>
                    
                    <div class="result-extra-info" style="display: none;">
                        <h5>Що включено:</h5>
                        <ul>
                            <li>Безкоштовне керування DNS</li>
                            <li>Захист конфіденційності WHOIS</li>
                            <li>Автопродовження (опціонально)</li>
                            <li>Підтримка 24/7</li>
                        </ul>
                    </div>
                ` : `
                    <div class="result-message">
                        <p>Цей домен уже зареєстрований. Спробуйте інше ім'я або іншу доменну зону.</p>
                    </div>
                    
                    <div class="result-actions">
                        <button class="btn btn-outline-primary" onclick="window.open('/pages/domains/whois.php?domain=${encodeURIComponent(result.domain)}', '_blank')">
                            <i class="bi bi-search"></i>
                            WHOIS інформація
                        </button>
                        <button class="btn btn-outline-secondary" onclick="domainRegistration.suggestAlternatives('${result.domain}')">
                            <i class="bi bi-lightbulb"></i>
                            Альтернативи
                        </button>
                    </div>
                `}
            </div>
        `;

        this.searchResults.innerHTML = html;
        this.searchResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Display bulk search results
     */
    displayBulkResults(results) {
        const html = `
            <div class="bulk-results">
                ${results.map(result => `
                    <div class="bulk-result-item ${result.available ? 'available' : 'unavailable'}">
                        <div class="bulk-result-header">
                            <div class="bulk-domain">${result.domain}</div>
                            <div class="bulk-status ${result.available ? 'text-success' : 'text-danger'}">
                                <i class="bi bi-${result.available ? 'check-circle' : 'x-circle'}"></i>
                                ${result.available ? 'Доступний' : 'Зайнятий'}
                            </div>
                        </div>
                        
                        ${result.available ? `
                            <div class="bulk-price">${this.formatPrice(result.price)} / рік</div>
                            <button class="btn btn-sm btn-primary w-100 mt-2" onclick="window.location.href='/cart/add-domain?domain=${encodeURIComponent(result.domain)}'">
                                <i class="bi bi-cart-plus"></i>
                                Додати до кошика
                            </button>
                        ` : `
                            <div class="bulk-unavailable">
                                <small class="text-muted">Недоступний для реєстрації</small>
                            </div>
                        `}
                    </div>
                `).join('')}
            </div>
        `;

        this.searchResults.innerHTML = html;
        this.searchResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Show quick result hint
     */
    showQuickResult(result) {
        // Remove existing quick result
        const existing = document.querySelector('.quick-result-hint');
        if (existing) existing.remove();

        const hint = document.createElement('div');
        hint.className = `quick-result-hint ${result.available ? 'available' : 'unavailable'}`;
        hint.innerHTML = `
            <i class="bi bi-${result.available ? 'check-circle' : 'x-circle'}"></i>
            <span>${result.available ? 'Доступний' : 'Зайнятий'}</span>
            ${result.available ? `<span class="price">${this.formatPrice(result.price)}</span>` : ''}
        `;

        this.domainInput.parentNode.appendChild(hint);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (hint.parentNode) {
                hint.remove();
            }
        }, 3000);
    }

    /**
     * Suggest domain alternatives
     */
    suggestAlternatives(domain) {
        const baseName = domain.split('.')[0];
        const suggestions = [
            `${baseName}-ua`,
            `${baseName}2024`,
            `${baseName}-pro`,
            `my-${baseName}`,
            `${baseName}-site`,
            `get-${baseName}`
        ];

        const html = `
            <div class="domain-suggestions">
                <h5>Альтернативні варіанти:</h5>
                <div class="suggestions-grid">
                    ${suggestions.map(suggestion => `
                        <button class="suggestion-item" onclick="domainRegistration.domainInput.value='${suggestion}'; domainRegistration.handleSearch(event)">
                            ${suggestion}.ua
                        </button>
                    `).join('')}
                </div>
            </div>
        `;

        const existingSuggestions = document.querySelector('.domain-suggestions');
        if (existingSuggestions) {
            existingSuggestions.innerHTML = html;
        } else {
            this.searchResults.insertAdjacentHTML('beforeend', html);
        }
    }

    /**
     * Handle domain card hover effects
     */
    handleCardHover(event) {
        const card = event.currentTarget;
        card.style.transform = 'translateY(-8px) scale(1.02)';
    }

    /**
     * Handle domain card leave effects
     */
    handleCardLeave(event) {
        const card = event.currentTarget;
        card.style.transform = '';
    }

    /**
     * Set search loading state
     */
    setSearchLoading(loading) {
        const searchBtn = this.searchForm.querySelector('.search-btn');
        const icon = searchBtn.querySelector('i');
        const text = searchBtn.querySelector('span');

        if (loading) {
            searchBtn.disabled = true;
            searchBtn.classList.add('loading');
            icon.className = 'bi bi-hourglass-split';
            text.textContent = 'Перевіряємо...';
        } else {
            searchBtn.disabled = false;
            searchBtn.classList.remove('loading');
            icon.className = 'bi bi-search';
            text.textContent = 'Перевірити';
        }
    }

    /**
     * Update price display
     */
    updatePriceDisplay(price, renewal) {
        const priceElements = document.querySelectorAll('.dynamic-price');
        priceElements.forEach(element => {
            element.textContent = this.formatPrice(price);
        });
    }

    /**
     * Format price with currency
     */
    formatPrice(price) {
        return new Intl.NumberFormat('uk-UA', {
            style: 'currency',
            currency: 'UAH',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price).replace('₴', 'грн');
    }

    /**
     * Show error message
     */
    showError(message) {
        // Create or update error element
        let errorElement = document.querySelector('.search-error');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'search-error';
            this.searchResults.parentNode.insertBefore(errorElement, this.searchResults);
        }

        errorElement.innerHTML = `
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorElement.parentNode) {
                errorElement.remove();
            }
        }, 5000);
    }

    /**
     * Clear search results and cache
     */
    clearSearch() {
        this.searchResults.innerHTML = '';
        this.domainInput.value = '';
        this.domainInput.classList.remove('is-valid', 'is-invalid');
        
        // Clear validation feedback
        const feedback = document.querySelector('.domain-validation-feedback');
        if (feedback) feedback.remove();
        
        // Clear quick result hints
        const hints = document.querySelectorAll('.quick-result-hint');
        hints.forEach(hint => hint.remove());
        
        this.domainInput.focus();
    }

    /**
     * Clear search results only
     */
    clearSearchResults() {
        this.searchResults.innerHTML = '';
    }

    /**
     * Initialize animations and visual effects
     */
    initializeAnimations() {
        // Initialize AOS if available
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 600,
                easing: 'ease-out-cubic',
                once: true,
                offset: 100
            });
        }

        // Add scroll animations for stats
        this.initializeCounterAnimations();

        // Add floating animations to hero elements
        this.initializeFloatingAnimations();
    }

    /**
     * Initialize counter animations for statistics
     */
    initializeCounterAnimations() {
        const counters = document.querySelectorAll('.stat-number');
        
        const animateCounter = (counter) => {
            const target = parseInt(counter.textContent.replace(/\D/g, ''));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                const formattedValue = counter.textContent.includes('+') ? 
                    Math.floor(current) + '+' : 
                    counter.textContent.includes('від') ?
                    'від ' + Math.floor(current) + ' грн' :
                    counter.textContent.includes('/') ?
                    '24/7' :
                    Math.floor(current);
                    
                counter.textContent = formattedValue;
            }, 16);
        };

        // Intersection Observer for counters
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });

        counters.forEach(counter => observer.observe(counter));
    }

    /**
     * Initialize floating animations
     */
    initializeFloatingAnimations() {
        const floatingElements = document.querySelectorAll('.floating-element');
        
        floatingElements.forEach((element, index) => {
            element.style.animationDelay = `${index * 2}s`;
            element.style.animationDuration = `${6 + index}s`;
        });
    }

    /**
     * Setup form validation
     */
    setupValidation() {
        // Real-time validation for domain input
        if (this.domainInput) {
            this.domainInput.addEventListener('blur', (e) => {
                const value = e.target.value.trim();
                if (value && !this.isValidDomainName(value)) {
                    this.showError('Будь ласка, введіть правильне ім\'я домену');
                }
            });
        }

        // Prevent form submission with invalid data
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => {
                const domain = this.domainInput.value.trim();
                if (!domain || !this.isValidDomainName(domain)) {
                    e.preventDefault();
                    this.domainInput.focus();
                    return false;
                }
            });
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.domainRegistration = new DomainRegistration();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DomainRegistration;
}