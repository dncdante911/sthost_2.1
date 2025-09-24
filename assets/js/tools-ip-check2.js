/**
 * IP Check Tool JavaScript
 * Инструмент проверки IP адресов для StormHosting UA
 */

class IPChecker {
    constructor() {
        this.form = document.getElementById('ipCheckForm');
        this.ipInput = document.getElementById('ipAddress');
        this.submitBtn = document.querySelector('.btn-check');
        this.loadingDiv = document.querySelector('.loading');
        this.resultsSection = document.getElementById('resultsSection');
        this.resultsContainer = document.getElementById('resultsContainer');
        
        // Пользовательская локация для расчета расстояния
        this.userLocation = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupFormValidation();
        this.getUserLocation();
    }
    
    bindEvents() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        if (this.ipInput) {
            this.ipInput.addEventListener('input', () => this.validateIP());
            this.ipInput.addEventListener('paste', () => {
                setTimeout(() => this.validateIP(), 100);
            });
        }
        
        // Обработка кнопок быстрых действий
        window.checkSampleIP = (ip) => this.checkSampleIP(ip);
        window.checkCurrentIp = () => this.checkCurrentIP();
        window.pasteFromClipboard = () => this.pasteFromClipboard();
        
        // Обработка копирования
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('copy-button')) {
                this.copyToClipboard(e.target.dataset.copy);
            }
        });
    }
    
    setupFormValidation() {
        if (this.ipInput) {
            this.ipInput.addEventListener('blur', () => {
                this.validateIP();
            });
        }
    }
    
    validateIP() {
        const ip = this.ipInput.value.trim();
        
        this.ipInput.classList.remove('is-invalid', 'is-valid');
        
        if (ip) {
            if (this.isValidIP(ip)) {
                this.ipInput.classList.add('is-valid');
                return true;
            } else {
                this.ipInput.classList.add('is-invalid');
                this.showError('Будь ласка, введіть коректний IPv4 або IPv6 адрес');
                return false;
            }
        }
        return false;
    }
    
    isValidIP(ip) {
        // IPv4 regex
        const ipv4Regex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        
        // IPv6 regex (упрощенный)
        const ipv6Regex = /^(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$|^::1$|^::$/;
        
        return ipv4Regex.test(ip) || ipv6Regex.test(ip);
    }
    
    async getUserLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                },
                (error) => {
                    console.log('Geolocation error:', error);
                    // Fallback to IP-based location
                    this.getUserLocationByIP();
                }
            );
        } else {
            this.getUserLocationByIP();
        }
    }
    
    async getUserLocationByIP() {
        try {
            const response = await fetch('https://ipapi.co/json/');
            const data = await response.json();
            this.userLocation = {
                lat: data.latitude,
                lng: data.longitude
            };
        } catch (error) {
            console.log('IP location error:', error);
        }
    }
    
    checkSampleIP(ip) {
        this.ipInput.value = ip;
        this.validateIP();
        this.form.dispatchEvent(new Event('submit'));
    }
    
    checkCurrentIP() {
        const currentIpElement = document.querySelector('.ip-address');
        if (currentIpElement) {
            const currentIp = currentIpElement.textContent.trim();
            this.checkSampleIP(currentIp);
        }
    }
    
    async pasteFromClipboard() {
        try {
            const text = await navigator.clipboard.readText();
            if (this.isValidIP(text.trim())) {
                this.ipInput.value = text.trim();
                this.validateIP();
            } else {
                this.showWarning('Текст в буфері не є коректним IP адресом');
            }
        } catch (error) {
            this.showError('Не вдалося прочитати з буфера обміну');
        }
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateIP()) {
            return;
        }
        
        const ip = this.ipInput.value.trim();
        const options = this.getCheckOptions();
        
        try {
            this.showLoading();
            const results = await this.performIPCheck(ip, options);
            this.displayResults(results);
        } catch (error) {
            this.showError('Помилка під час перевірки: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    getCheckOptions() {
        return {
            checkBlacklists: document.getElementById('checkBlacklists')?.checked || false,
            checkThreatIntel: document.getElementById('checkThreatIntel')?.checked || false,
            checkDistance: document.getElementById('checkDistance')?.checked || false
        };
    }
    
    async performIPCheck(ip, options) {
        const formData = new FormData();
        formData.append('ip', ip);
        formData.append('options', JSON.stringify(options));
        
        // Добавляем CSRF токен если он есть
        if (window.csrfToken) {
            formData.append('csrf_token', window.csrfToken);
        }
        
        // Добавляем локацию пользователя для расчета расстояния
        if (this.userLocation) {
            formData.append('user_location', JSON.stringify(this.userLocation));
        }
        
        try {
            const response = await fetch('/api/tools/ip-check.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            // Получаем текст ответа для отладки
            const responseText = await response.text();
            
            // Пытаемся парсить JSON
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                console.error('Invalid JSON response:', responseText);
                throw new Error('Сервер повернув некоректну відповідь');
            }
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}: ${response.statusText}`);
            }
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            return data;
            
        } catch (fetchError) {
            console.error('Fetch error:', fetchError);
            throw fetchError;
        }
    }
    
    displayResults(data) {
        if (!this.resultsContainer) {
            this.createResultsContainer();
        }
        
        this.resultsContainer.innerHTML = '';
        
        // Основная информация об IP
        const generalCard = this.createGeneralInfoCard(data.general);
        this.resultsContainer.appendChild(generalCard);
        
        // Геолокация
        if (data.location) {
            const locationCard = this.createLocationCard(data.location);
            this.resultsContainer.appendChild(locationCard);
        }
        
        // Сетевая информация
        if (data.network) {
            const networkCard = this.createNetworkCard(data.network);
            this.resultsContainer.appendChild(networkCard);
        }
        
        // Результаты черных списков
        if (data.blacklists) {
            const blacklistCard = this.createBlacklistCard(data.blacklists);
            this.resultsContainer.appendChild(blacklistCard);
        }
        
        // Анализ угроз
        if (data.threats) {
            const threatsCard = this.createThreatsCard(data.threats);
            this.resultsContainer.appendChild(threatsCard);
        }
        
        // Информация о расстоянии
        if (data.distance) {
            const distanceCard = this.createDistanceCard(data.distance);
            this.resultsContainer.appendChild(distanceCard);
        }
        
        // Погода
        if (data.weather) {
            const weatherCard = this.createWeatherCard(data.weather);
            this.resultsContainer.appendChild(weatherCard);
        }
        
        // Показываем результаты
        this.showResults();
    }
    
    createGeneralInfoCard(general) {
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = '0ms';
        
        const ipType = this.isIPv6(general.ip) ? 'IPv6' : 'IPv4';
        const statusClass = general.is_valid ? 'status-safe' : 'status-danger';
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Загальна інформація
                </h3>
                <span class="result-status ${statusClass}">
                    ${ipType}
                </span>
            </div>
            <div class="ip-info-grid">
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-hdd-network"></i>
                        IP адреса:
                    </span>
                    <span class="info-value coordinate">
                        ${general.ip}
                        <button class="copy-button" data-copy="${general.ip}" title="Копіювати">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-shield-check"></i>
                        Тип адреси:
                    </span>
                    <span class="info-value">${general.ip_type || 'Публічна'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-clock"></i>
                        Час перевірки:
                    </span>
                    <span class="info-value">${this.formatDateTime(general.check_time)}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-globe"></i>
                        Протокол:
                    </span>
                    <span class="info-value">${ipType}</span>
                </div>
            </div>
        `;
        
        return card;
    }
    
    createLocationCard(location) {
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = '150ms';
        
        const flag = this.getCountryFlag(location.country_code);
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">
                    <i class="bi bi-geo-alt me-2"></i>
                    Геолокація
                </h3>
                <span class="result-status status-safe">
                    ${flag} ${location.country}
                </span>
            </div>
            <div class="ip-info-grid">
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-flag"></i>
                        Країна:
                    </span>
                    <span class="info-value">${flag} ${location.country}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-building"></i>
                        Регіон:
                    </span>
                    <span class="info-value">${location.region || 'Невідомо'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-pin-map"></i>
                        Місто:
                    </span>
                    <span class="info-value">${location.city || 'Невідомо'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-mailbox"></i>
                        Поштовий код:
                    </span>
                    <span class="info-value">${location.postal || 'Невідомо'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-compass"></i>
                        Координати:
                    </span>
                    <span class="info-value coordinate">
                        ${location.latitude}, ${location.longitude}
                        <button class="copy-button" data-copy="${location.latitude}, ${location.longitude}" title="Копіювати">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-clock"></i>
                        Часовий пояс:
                    </span>
                    <span class="info-value">${location.timezone || 'Невідомо'}</span>
                </div>
            </div>
        `;
        
        // Добавляем карту если есть координаты
        if (location.latitude && location.longitude) {
            const mapDiv = document.createElement('div');
            mapDiv.className = 'map-container';
            mapDiv.innerHTML = `
                <iframe 
                    src="https://www.openstreetmap.org/export/embed.html?bbox=${location.longitude-0.01},${location.latitude-0.01},${location.longitude+0.01},${location.latitude+0.01}&marker=${location.latitude},${location.longitude}"
                    width="100%" 
                    height="300"
                    frameborder="0">
                </iframe>
            `;
            card.appendChild(mapDiv);
        }
        
        return card;
    }
    
    createNetworkCard(network) {
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = '300ms';
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Мережева інформація
                </h3>
                <span class="result-status status-safe">
                    ASN ${network.asn || 'N/A'}
                </span>
            </div>
            <div class="ip-info-grid">
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-building"></i>
                        Провайдер:
                    </span>
                    <span class="info-value">${network.isp || 'Невідомо'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-diagram-2"></i>
                        Організація:
                    </span>
                    <span class="info-value">${network.org || 'Невідомо'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-hash"></i>
                        ASN:
                    </span>
                    <span class="info-value">${network.asn || 'Невідомо'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-router"></i>
                        Тип з'єднання:
                    </span>
                    <span class="info-value">${network.connection_type || 'Невідомо'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-speedometer"></i>
                        Швидкість:
                    </span>
                    <span class="info-value">${network.usage_type || 'Невідомо'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-shield"></i>
                        Проксі/VPN:
                    </span>
                    <span class="info-value ${network.is_proxy ? 'text-warning' : 'text-success'}">
                        ${network.is_proxy ? 'Так' : 'Ні'}
                    </span>
                </div>
            </div>
        `;
        
        return card;
    }
    
    createBlacklistCard(blacklists) {
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = '450ms';
        
        const totalLists = blacklists.length;
        const listedCount = blacklists.filter(bl => bl.listed).length;
        const statusClass = listedCount > 0 ? 'status-danger' : 'status-safe';
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">
                    <i class="bi bi-shield-exclamation me-2"></i>
                    Перевірка чорних списків
                </h3>
                <span class="result-status ${statusClass}">
                    ${listedCount}/${totalLists} списків
                </span>
            </div>
            <div class="blacklist-grid">
                ${blacklists.map(bl => `
                    <div class="blacklist-item ${bl.listed ? 'blacklist-listed' : (bl.checked ? 'blacklist-safe' : 'blacklist-unknown')}">
                        <span>${bl.name}</span>
                        <span>
                            ${bl.listed ? 
                                '<i class="bi bi-x-circle"></i> В списку' : 
                                (bl.checked ? '<i class="bi bi-check-circle"></i> Чистий' : '<i class="bi bi-question-circle"></i> Невідомо')
                            }
                        </span>
                    </div>
                `).join('')}
            </div>
            ${listedCount > 0 ? `
                <div class="error-message">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Увага! IP адреса знайдена в ${listedCount} чорному(их) списку(ах). 
                    Це може вказувати на підозрілу активність.</span>
                </div>
            ` : `
                <div class="success-message">
                    <i class="bi bi-check-circle"></i>
                    <span>IP адреса не знайдена в жодному з перевірених чорних списків.</span>
                </div>
            `}
        `;
        
        return card;
    }
    
    createThreatsCard(threats) {
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = '600ms';
        
        const threatCount = threats.categories ? threats.categories.length : 0;
        const statusClass = threatCount > 0 ? 'status-danger' : 'status-safe';
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">
                    <i class="bi bi-bug me-2"></i>
                    Аналіз загроз
                </h3>
                <span class="result-status ${statusClass}">
                    Ризик: ${threats.risk_level || 'Низький'}
                </span>
            </div>
            <div class="ip-info-grid">
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-speedometer"></i>
                        Рівень ризику:
                    </span>
                    <span class="info-value ${this.getRiskClass(threats.risk_level)}">
                        ${threats.risk_level || 'Низький'}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-percent"></i>
                        Оцінка загрози:
                    </span>
                    <span class="info-value">
                        ${threats.confidence || 0}%
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-clock-history"></i>
                        Остання активність:
                    </span>
                    <span class="info-value">
                        ${threats.last_seen || 'Невідомо'}
                    </span>
                </div>
            </div>
            
            ${threatCount > 0 ? `
                <div class="threat-badges">
                    ${threats.categories.map(category => `
                        <span class="threat-badge threat-${category.toLowerCase()}">
                            ${category}
                        </span>
                    `).join('')}
                </div>
                <div class="error-message">
                    <i class="bi bi-shield-x"></i>
                    <span>Виявлено загрози! IP може бути пов'язаний з шкідливою активністю.</span>
                </div>
            ` : `
                <div class="success-message">
                    <i class="bi bi-shield-check"></i>
                    <span>Загроз не виявлено. IP адреса виглядає безпечно.</span>
                </div>
            `}
        `;
        
        return card;
    }
    
    createDistanceCard(distance) {
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = '750ms';
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">
                    <i class="bi bi-compass me-2"></i>
                    Відстань від вас
                </h3>
            </div>
            <div class="distance-info">
                <div class="distance-value">${distance.km} км</div>
                <div class="distance-label">Приблизна відстань до IP адреси</div>
            </div>
            <div class="ip-info-grid">
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-geo"></i>
                        Відстань:
                    </span>
                    <span class="info-value">${distance.km} км</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-globe"></i>
                        В милях:
                    </span>
                    <span class="info-value">${distance.miles} миль</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-airplane"></i>
                        Час польоту:
                    </span>
                    <span class="info-value">${distance.flight_time || 'Н/Д'}</span>
                </div>
            </div>
        `;
        
        return card;
    }
    
    createWeatherCard(weather) {
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = '900ms';
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">
                    <i class="bi bi-cloud-sun me-2"></i>
                    Погода в регіоні
                </h3>
            </div>
            <div class="weather-widget">
                <div class="weather-current">
                    <div class="weather-icon">${this.getWeatherIcon(weather.condition)}</div>
                    <div>
                        <div class="weather-temp">${weather.temperature}°C</div>
                        <div class="weather-description">${weather.description}</div>
                    </div>
                </div>
            </div>
            <div class="ip-info-grid">
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-thermometer"></i>
                        Температура:
                    </span>
                    <span class="info-value">${weather.temperature}°C</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-droplet"></i>
                        Вологість:
                    </span>
                    <span class="info-value">${weather.humidity}%</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-wind"></i>
                        Вітер:
                    </span>
                    <span class="info-value">${weather.wind_speed} м/с</span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="bi bi-eye"></i>
                        Видимість:
                    </span>
                    <span class="info-value">${weather.visibility || 'Н/Д'} км</span>
                </div>
            </div>
        `;
        
        return card;
    }
    
    createResultsContainer() {
        if (this.resultsSection) {
            this.resultsSection.style.display = 'block';
            return;
        }
        
        const container = document.createElement('section');
        container.id = 'resultsSection';
        container.className = 'results-section';
        container.innerHTML = `
            <div class="container">
                <div class="text-center mb-4">
                    <h2 class="display-5 fw-bold">Результати перевірки IP</h2>
                </div>
                <div id="resultsContainer" class="results-grid"></div>
            </div>
        `;
        
        // Вставляем после формы
        const form = this.form.closest('section');
        form.parentNode.insertBefore(container, form.nextSibling);
        
        this.resultsSection = container;
        this.resultsContainer = document.getElementById('resultsContainer');
    }
    
    showLoading() {
        this.disableSubmit('Перевіряємо...');
        
        if (!this.loadingDiv) {
            this.loadingDiv = document.createElement('div');
            this.loadingDiv.className = 'loading';
            this.loadingDiv.innerHTML = `
                <div class="spinner"></div>
                <span>Виконується перевірка IP адреси...</span>
            `;
            this.form.appendChild(this.loadingDiv);
        }
        
        this.loadingDiv.style.display = 'flex';
    }
    
    hideLoading() {
        this.enableSubmit();
        if (this.loadingDiv) {
            this.loadingDiv.style.display = 'none';
        }
    }
    
    showResults() {
        if (this.resultsSection) {
            this.resultsSection.style.display = 'block';
            this.resultsSection.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    }
    
    disableSubmit(text = 'Перевірити IP') {
        if (this.submitBtn) {
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = `<i class="bi bi-hourglass-split me-1"></i>${text}`;
        }
    }
    
    enableSubmit() {
        if (this.submitBtn) {
            this.submitBtn.disabled = false;
            this.submitBtn.innerHTML = '<i class="bi bi-search me-1"></i>Перевірити IP';
        }
    }
    
    // Utility functions
    isIPv6(ip) {
        return ip.includes(':');
    }
    
    getCountryFlag(countryCode) {
        if (!countryCode) return '🌍';
        
        const flags = {
            'US': '🇺🇸', 'UA': '🇺🇦', 'RU': '🇷🇺', 'DE': '🇩🇪', 'FR': '🇫🇷', 
            'GB': '🇬🇧', 'CN': '🇨🇳', 'JP': '🇯🇵', 'KR': '🇰🇷', 'CA': '🇨🇦'
        };
        
        return flags[countryCode.toUpperCase()] || '🌍';
    }
    
    getRiskClass(riskLevel) {
        const level = (riskLevel || '').toLowerCase();
        if (level.includes('high') || level.includes('високий')) return 'text-danger';
        if (level.includes('medium') || level.includes('середній')) return 'text-warning';
        return 'text-success';
    }
    
    getWeatherIcon(condition) {
        const icons = {
            'clear': '☀️',
            'sunny': '☀️', 
            'cloudy': '☁️',
            'rain': '🌧️',
            'snow': '❄️',
            'storm': '⛈️'
        };
        
        return icons[condition?.toLowerCase()] || '🌤️';
    }
    
    formatDateTime(dateString) {
        if (!dateString) return new Date().toLocaleString('uk-UA');
        const date = new Date(dateString);
        return date.toLocaleString('uk-UA');
    }
    
    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.showSuccess('Скопійовано в буфер обміну');
            }).catch(() => {
                this.fallbackCopyTextToClipboard(text);
            });
        } else {
            this.fallbackCopyTextToClipboard(text);
        }
    }
    
    fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.position = 'fixed';
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            this.showSuccess('Скопійовано в буфер обміну');
        } catch (err) {
            this.showError('Помилка копіювання');
        }
        
        document.body.removeChild(textArea);
    }
    
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    showWarning(message) {
        this.showNotification(message, 'warning');
    }
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    showNotification(message, type = 'info') {
        // Создаем уведомление
        const notification = document.createElement('div');
        notification.className = `notification alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        notification.innerHTML = `
            <i class="bi bi-${this.getNotificationIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        
        // Добавляем в начало страницы
        document.body.insertBefore(notification, document.body.firstChild);
        
        // Автоматически скрываем через 5 секунд
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, что мы на странице ip-check
    if (document.getElementById('ipCheckForm')) {
        window.ipChecker = new IPChecker();
    }
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IPChecker;
}