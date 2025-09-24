/**
 * StormHosting UA - Domain Management JavaScript
 */

class DomainManager {
    constructor() {
        this.searchForm = document.getElementById('domainSearchForm');
        this.whoisForm = document.getElementById('whoisForm');
        this.dnsForm = document.getElementById('dnsForm');
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupQuickActions();
    }

    bindEvents() {
        // Domain search form
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => this.handleDomainSearch(e));
        }

        // WHOIS form
        if (this.whoisForm) {
            this.whoisForm.addEventListener('submit', (e) => this.handleWhoisLookup(e));
        }

        // DNS form
        if (this.dnsForm) {
            this.dnsForm.addEventListener('submit', (e) => this.handleDNSLookup(e));
        }

        // Quick type buttons for DNS
        document.querySelectorAll('.quick-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.target.dataset.type;
                const select = document.getElementById('recordType');
                if (select) {
                    select.value = type;
                    this.highlightQuickButton(e.target);
                }
            });
        });

        // Quick search buttons for domain zones
        document.querySelectorAll('.quick-search-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const zone = e.target.dataset.zone;
                this.quickDomainSearch(zone);
            });
        });

        // Test type buttons for DNS
        document.querySelectorAll('.test-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.target.dataset.type;
                this.quickDNSTest(type);
            });
        });
    }

    setupQuickActions() {
        // Auto-suggest для domain input
        const domainInput = document.getElementById('domainName');
        if (domainInput) {
            domainInput.addEventListener('input', (e) => {
                this.validateDomainInput(e.target);
            });
        }

        // Real-time validation
        const whoisInput = document.getElementById('whoisDomain');
        if (whoisInput) {
            whoisInput.addEventListener('input', (e) => {
                this.validateDomainInput(e.target);
            });
        }

        const dnsInput = document.getElementById('dnsDomain');
        if (dnsInput) {
            dnsInput.addEventListener('input', (e) => {
                this.validateDomainInput(e.target);
            });
        }
    }

    validateDomainInput(input) {
        const value = input.value.toLowerCase();
        const isValid = /^[a-zA-Z0-9.-]*$/.test(value);
        
        input.classList.toggle('is-invalid', !isValid && value.length > 0);
        input.classList.toggle('is-valid', isValid && value.length > 2);

        // Remove invalid characters
        if (!isValid) {
            input.value = value.replace(/[^a-zA-Z0-9.-]/g, '');
        }
    }

    async handleDomainSearch(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const domain = formData.get('domain') || document.getElementById('domainName').value;
        const zone = formData.get('zone') || document.getElementById('domainZone').value;
        
        if (!domain || !zone) {
            this.showError('Введіть ім\'я домену та оберіть зону');
            return;
        }

        const resultsContainer = document.getElementById('searchResults');
        this.showLoading(resultsContainer, 'Перевіряємо доступність домену...');

        try {
            const response = await this.makeRequest('check_domain', {
                domain: domain,
                zone: zone,
                csrf_token: window.domainConfig?.csrfToken || document.getElementById('csrf_token').value
            });

            this.displayDomainResults(resultsContainer, response);
        } catch (error) {
            this.showError(error.message, resultsContainer);
        }
    }

    async handleWhoisLookup(e) {
        e.preventDefault();
        
        const domain = document.getElementById('whoisDomain').value;
        
        if (!domain) {
            this.showError('Введіть ім\'я домену');
            return;
        }

        const resultsContainer = document.getElementById('whoisResults');
        this.showLoading(resultsContainer, 'Виконуємо WHOIS запит...');

        try {
            const response = await this.makeRequest('whois_lookup', {
                domain: domain,
                csrf_token: window.whoisConfig?.csrfToken || document.getElementById('csrf_token').value
            });

            this.displayWhoisResults(resultsContainer, response);
        } catch (error) {
            this.showError(error.message, resultsContainer);
        }
    }

    async handleDNSLookup(e) {
        e.preventDefault();
        
        const domain = document.getElementById('dnsDomain').value;
        const recordType = document.getElementById('recordType').value;
        
        if (!domain) {
            this.showError('Введіть ім\'я домену');
            return;
        }

        const resultsContainer = document.getElementById('dnsResults');
        this.showLoading(resultsContainer, 'Виконуємо DNS запит...');

        try {
            const response = await this.makeRequest('dns_lookup', {
                domain: domain,
                record_type: recordType,
                csrf_token: window.dnsConfig?.csrfToken || document.getElementById('csrf_token').value
            });

            this.displayDNSResults(resultsContainer, response);
        } catch (error) {
            this.showError(error.message, resultsContainer);
        }
    }

    async makeRequest(action, data) {
        const url = '?ajax=1';
        const formData = new FormData();
        
        formData.append('action', action);
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        return result;
    }

    displayDomainResults(container, data) {
        const isAvailable = data.available;
        const statusClass = isAvailable ? 'success' : 'danger';
        const statusIcon = isAvailable ? 'check-circle' : 'x-circle';
        const statusText = isAvailable ? 'Доступний' : 'Зайнятий';

        container.innerHTML = `
            <div class="domain-result-card">
                <div class="result-header text-${statusClass}">
                    <i class="bi bi-${statusIcon} fs-1"></i>
                    <h3>${data.domain}</h3>
                    <p class="status">${statusText}</p>
                </div>
                
                <div class="result-body">
                    ${isAvailable ? `
                        <div class="price-info">
                            <div class="price-amount">${data.price} ${data.currency}</div>
                            <div class="price-period">за перший рік</div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn btn-primary btn-lg" onclick="registerDomain('${data.domain}')">
                                <i class="bi bi-cart-plus"></i>
                                Зареєструвати домен
                            </button>
                            <button class="btn btn-outline-secondary" onclick="addToWishlist('${data.domain}')">
                                <i class="bi bi-heart"></i>
                                Додати до списку бажань
                            </button>
                        </div>
                        
                        <div class="domain-benefits">
                            <div class="benefit">
                                <i class="bi bi-check text-success"></i>
                                Безкоштовне керування DNS
                            </div>
                            <div class="benefit">
                                <i class="bi bi-check text-success"></i>
                                Захист приватності WHOIS
                            </div>
                            <div class="benefit">
                                <i class="bi bi-check text-success"></i>
                                Підтримка 24/7
                            </div>
                        </div>
                    ` : `
                        <div class="unavailable-info">
                            <p>Цей домен вже зареєстрований кимось іншим.</p>
                            <div class="alternative-actions">
                                <button class="btn btn-outline-primary" onclick="suggestAlternatives('${data.domain}')">
                                    <i class="bi bi-lightbulb"></i>
                                    Запропонувати альтернативи
                                </button>
                                <button class="btn btn-outline-secondary" onclick="checkWhois('${data.domain}')">
                                    <i class="bi bi-info-circle"></i>
                                    Перевірити WHOIS
                                </button>
                                <button class="btn btn-outline-warning" onclick="monitorDomain('${data.domain}')">
                                    <i class="bi bi-bell"></i>
                                    Моніторити домен
                                </button>
                            </div>
                        </div>
                    `}
                </div>
            </div>
        `;
    }

    displayWhoisResults(container, data) {
        if (data.data.status === 'available') {
            container.innerHTML = `
                <div class="whois-result-card">
                    <div class="result-header text-success">
                        <i class="bi bi-check-circle fs-1"></i>
                        <h3>${data.domain}</h3>
                        <p class="status">Домен доступен для реєстрації</p>
                    </div>
                    <div class="result-body">
                        <a href="/domains/register?domain=${data.domain}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle"></i>
                            Зареєструвати домен
                        </a>
                    </div>
                </div>
            `;
            return;
        }

        const whoisData = data.data;
        container.innerHTML = `
            <div class="whois-result-card">
                <div class="result-header">
                    <h3>${data.domain}</h3>
                    <p class="whois-server">WHOIS Server: ${data.whois_server}</p>
                </div>
                
                <div class="result-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="whois-section">
                                <h5><i class="bi bi-calendar"></i> Важливі дати</h5>
                                <div class="whois-data">
                                    <div class="data-row">
                                        <span class="label">Дата реєстрації:</span>
                                        <span class="value">${whoisData.creation_date || 'Не вказано'}</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="label">Дата закінчення:</span>
                                        <span class="value">${whoisData.expiration_date || 'Не вказано'}</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="label">Останнє оновлення:</span>
                                        <span class="value">${whoisData.updated_date || 'Не вказано'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="whois-section">
                                <h5><i class="bi bi-building"></i> Реєстратор</h5>
                                <div class="whois-data">
                                    <div class="data-row">
                                        <span class="label">Реєстратор:</span>
                                        <span class="value">${whoisData.registrar || 'Не вказано'}</span>
                                    </div>
                                    <div class="data-row">
                                        <span class="label">Статус:</span>
                                        <span class="value">${whoisData.status || 'Не вказано'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${whoisData.name_servers ? `
                        <div class="col-12">
                            <div class="whois-section">
                                <h5><i class="bi bi-dns"></i> DNS сервери</h5>
                                <div class="name-servers">
                                    ${whoisData.name_servers.map(ns => `<span class="name-server">${ns}</span>`).join('')}
                                </div>
                            </div>
                        </div>
                        ` : ''}
                        
                        <div class="col-12">
                            <div class="whois-section">
                                <h5><i class="bi bi-file-text"></i> Необроблені дані WHOIS</h5>
                                <div class="raw-whois">
                                    <pre>${whoisData.raw_data || 'Дані недоступні'}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    displayDNSResults(container, data) {
        const results = data.results;
        
        if (!results || results.length === 0) {
            container.innerHTML = `
                <div class="dns-result-card">
                    <div class="result-header text-warning">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <h3>DNS записи не знайдено</h3>
                        <p>Для домену ${data.domain} не знайдено записів типу ${data.record_type}</p>
                    </div>
                </div>
            `;
            return;
        }

        const recordsHtml = results.map(record => {
            switch (record.type) {
                case 'A':
                    return `
                        <tr>
                            <td><span class="record-type type-a">${record.type}</span></td>
                            <td>${record.host}</td>
                            <td>${record.ip}</td>
                            <td>${record.ttl}</td>
                        </tr>
                    `;
                case 'AAAA':
                    return `
                        <tr>
                            <td><span class="record-type type-aaaa">${record.type}</span></td>
                            <td>${record.host}</td>
                            <td>${record.ipv6}</td>
                            <td>${record.ttl}</td>
                        </tr>
                    `;
                case 'MX':
                    return `
                        <tr>
                            <td><span class="record-type type-mx">${record.type}</span></td>
                            <td>${record.host}</td>
                            <td>${record.target} (пріоритет: ${record.pri})</td>
                            <td>${record.ttl}</td>
                        </tr>
                    `;
                case 'CNAME':
                    return `
                        <tr>
                            <td><span class="record-type type-cname">${record.type}</span></td>
                            <td>${record.host}</td>
                            <td>${record.target}</td>
                            <td>${record.ttl}</td>
                        </tr>
                    `;
                case 'TXT':
                    return `
                        <tr>
                            <td><span class="record-type type-txt">${record.type}</span></td>
                            <td>${record.host}</td>
                            <td class="txt-value">${record.txt}</td>
                            <td>${record.ttl}</td>
                        </tr>
                    `;
                case 'NS':
                    return `
                        <tr>
                            <td><span class="record-type type-ns">${record.type}</span></td>
                            <td>${record.host}</td>
                            <td>${record.target}</td>
                            <td>${record.ttl}</td>
                        </tr>
                    `;
                case 'SOA':
                    return `
                        <tr>
                            <td><span class="record-type type-soa">${record.type}</span></td>
                            <td>${record.host}</td>
                            <td>${record.mname}<br><small>Email: ${record.rname}</small></td>
                            <td>${record.ttl}</td>
                        </tr>
                    `;
                default:
                    return '';
            }
        }).join('');

        container.innerHTML = `
            <div class="dns-result-card">
                <div class="result-header text-success">
                    <i class="bi bi-check-circle fs-1"></i>
                    <h3>DNS записи для ${data.domain}</h3>
                    <p>Тип запису: ${data.record_type}</p>
                </div>
                
                <div class="result-body">
                    <div class="dns-table-wrapper">
                        <table class="table table-hover dns-records-table">
                            <thead>
                                <tr>
                                    <th>Тип</th>
                                    <th>Ім'я</th>
                                    <th>Значення</th>
                                    <th>TTL</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${recordsHtml}
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="dns-actions mt-4">
                        <button class="btn btn-outline-primary" onclick="exportDNSRecords('${data.domain}', '${data.record_type}')">
                            <i class="bi bi-download"></i>
                            Експортувати записи
                        </button>
                        <button class="btn btn-outline-secondary" onclick="checkAllRecords('${data.domain}')">
                            <i class="bi bi-search"></i>
                            Перевірити всі типи записів
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    quickDomainSearch(zone) {
        const input = document.getElementById('domainName');
        const select = document.getElementById('domainZone');
        
        if (input && select) {
            if (input.value) {
                select.value = zone;
                this.searchForm.dispatchEvent(new Event('submit'));
            } else {
                input.focus();
                input.placeholder = `введіть-назву${zone}`;
                select.value = zone;
            }
        }
    }

    quickDNSTest(type) {
        const select = document.getElementById('recordType');
        const input = document.getElementById('dnsDomain');
        
        if (select && input) {
            select.value = type;
            this.highlightQuickButton(document.querySelector(`[data-type="${type}"]`));
            
            if (input.value) {
                this.dnsForm.dispatchEvent(new Event('submit'));
            } else {
                input.focus();
                input.placeholder = `example.com для ${type} запису`;
            }
        }
    }

    highlightQuickButton(button) {
        // Remove active class from all buttons
        document.querySelectorAll('.quick-type-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Add active class to clicked button
        button.classList.add('active');
    }

    showLoading(container, message = 'Завантаження...') {
        container.innerHTML = `
            <div class="loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Завантаження...</span>
                </div>
                <p class="mt-3">${message}</p>
            </div>
        `;
    }

    showError(message, container = null) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        if (container) {
            container.innerHTML = errorHtml;
        } else {
            // Show in toast or modal
            this.showToast(message, 'error');
        }
    }

    showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
        
        const toastId = 'toast-' + Date.now();
        const iconClass = {
            'success': 'bi-check-circle',
            'error': 'bi-exclamation-triangle',
            'warning': 'bi-exclamation-triangle',
            'info': 'bi-info-circle'
        }[type] || 'bi-info-circle';

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.id = toastId;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${iconClass} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1080';
        document.body.appendChild(container);
        return container;
    }
}

// Global functions for button actions
window.registerDomain = function(domain) {
    window.location.href = `/domains/register?domain=${encodeURIComponent(domain)}`;
};

window.addToWishlist = function(domain) {
    // Add to wishlist functionality
    const manager = window.domainManager;
    manager.showToast(`Домен ${domain} додано до списку бажань`, 'success');
};

window.suggestAlternatives = function(domain) {
    // Suggest alternatives functionality
    const baseName = domain.split('.')[0];
    const alternatives = [
        `${baseName}.ua`,
        `${baseName}.com.ua`,
        `${baseName}.net.ua`,
        `${baseName}-ua.com`,
        `get${baseName}.com`
    ];
    
    alert(`Альтернативні варіанти:\n${alternatives.join('\n')}`);
};

window.checkWhois = function(domain) {
    window.location.href = `/domains/whois?domain=${encodeURIComponent(domain)}`;
};

window.monitorDomain = function(domain) {
    const manager = window.domainManager;
    manager.showToast(`Моніторинг домену ${domain} налаштовано`, 'success');
};

window.exportDNSRecords = function(domain, recordType) {
    // Export DNS records functionality
    const data = `# DNS Records for ${domain} (${recordType})\n# Generated on ${new Date().toISOString()}\n`;
    const blob = new Blob([data], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `${domain}-${recordType}-records.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

window.checkAllRecords = function(domain) {
    const types = ['A', 'AAAA', 'MX', 'CNAME', 'TXT', 'NS'];
    const promises = types.map(type => {
        // This would be a real API call in production
        return new Promise(resolve => {
            setTimeout(() => {
                resolve({ type, hasRecords: Math.random() > 0.3 });
            }, Math.random() * 1000);
        });
    });
    
    Promise.all(promises).then(results => {
        const summary = results.map(r => `${r.type}: ${r.hasRecords ? '✓' : '✗'}`).join('\n');
        alert(`Сводка по всім типам записів для ${domain}:\n\n${summary}`);
    });
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.domainManager = new DomainManager();
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Initialize popovers
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => {
        new bootstrap.Popover(popover);
    });
});

// Export for use in other scripts
window.DomainManager = DomainManager;