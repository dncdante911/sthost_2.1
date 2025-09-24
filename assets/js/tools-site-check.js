/**
 * Site Check Tool JavaScript
 * Инструмент проверки доступности сайтов для StormHosting UA
 */

class SiteChecker {
    constructor() {
        this.form = document.getElementById('siteCheckForm');
        this.urlInput = document.getElementById('siteUrl');
        this.submitBtn = document.querySelector('.btn-check');
        this.loadingDiv = document.querySelector('.loading');
        this.resultsSection = document.getElementById('resultsSection');
        this.resultsContainer = document.getElementById('resultsContainer');
        
        this.locations = [
            { code: 'kyiv', name: 'Київ, Україна', flag: '🇺🇦' },
            { code: 'frankfurt', name: 'Франкфурт, Німеччина', flag: '🇩🇪' },
            { code: 'london', name: 'Лондон, Великобританія', flag: '🇬🇧' },
            { code: 'nyc', name: 'Нью-Йорк, США', flag: '🇺🇸' },
            { code: 'singapore', name: 'Сінгапур', flag: '🇸🇬' },
            { code: 'tokyo', name: 'Токіо, Японія', flag: '🇯🇵' }
        ];
        
        this.init();
    }
    
    init() {
        this.createLocationSelector();
        this.bindEvents();
        this.setupFormValidation();
    }
    
    createLocationSelector() {
        const form = this.form;
        const locationSection = document.createElement('div');
        locationSection.className = 'location-select';
        locationSection.innerHTML = `
            <label class="form-label">Оберіть локації для перевірки:</label>
            <div class="location-grid" id="locationGrid"></div>
        `;
        
        // Вставляем после поля URL
        const urlGroup = form.querySelector('.input-group');
        urlGroup.parentNode.insertBefore(locationSection, urlGroup.nextSibling);
        
        const grid = document.getElementById('locationGrid');
        
        this.locations.forEach((location, index) => {
            const item = document.createElement('div');
            item.className = 'location-item';
            
            item.innerHTML = `
                <input type="checkbox" 
                       id="location_${location.code}" 
                       name="locations[]" 
                       value="${location.code}"
                       class="location-checkbox"
                       ${index < 3 ? 'checked' : ''}>
                <label for="location_${location.code}" class="location-label">
                    <span class="location-flag">${location.flag}</span>
                    <span class="location-name">${location.name}</span>
                </label>
            `;
            
            grid.appendChild(item);
        });
    }
    
    bindEvents() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        if (this.urlInput) {
            this.urlInput.addEventListener('input', () => this.validateUrl());
        }
        
        // Обработка изменения локаций
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('location-checkbox')) {
                this.updateLocationSelection();
            }
        });
        
        // Копирование кода API
        document.addEventListener('click', (e) => {
            if (e.target.closest('[onclick="copyCode()"]')) {
                this.copyApiCode();
            }
        });
    }
    
    setupFormValidation() {
        if (this.urlInput) {
            this.urlInput.addEventListener('blur', () => {
                this.validateUrl();
            });
        }
    }
    
    validateUrl() {
        const url = this.urlInput.value.trim();
        const urlPattern = /^https?:\/\/.+/i;
        
        this.urlInput.classList.remove('is-invalid', 'is-valid');
        
        if (url) {
            if (urlPattern.test(url)) {
                this.urlInput.classList.add('is-valid');
                return true;
            } else {
                this.urlInput.classList.add('is-invalid');
                this.showError('Будь ласка, введіть коректний URL (наприклад: https://example.com)');
                return false;
            }
        }
        return false;
    }
    
    updateLocationSelection() {
        const checkedBoxes = document.querySelectorAll('.location-checkbox:checked');
        const submitBtn = this.submitBtn;
        
        if (checkedBoxes.length === 0) {
            this.disableSubmit('Оберіть хоча б одну локацію');
        } else if (checkedBoxes.length > 4) {
            // Отключаем последний выбранный чекбокс
            const lastChecked = Array.from(checkedBoxes).pop();
            lastChecked.checked = false;
            this.showWarning('Максимум 4 локації одночасно');
        } else {
            this.enableSubmit();
        }
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateUrl()) {
            return;
        }
        
        const selectedLocations = Array.from(document.querySelectorAll('.location-checkbox:checked'))
            .map(cb => cb.value);
            
        if (selectedLocations.length === 0) {
            this.showError('Оберіть хоча б одну локацію для перевірки');
            return;
        }
        
        const url = this.urlInput.value.trim();
        
        try {
            this.showLoading();
            const results = await this.performSiteCheck(url, selectedLocations);
            this.displayResults(results);
        } catch (error) {
            this.showError('Помилка під час перевірки: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    async performSiteCheck(url, locations) {
        const formData = new FormData();
        formData.append('url', url);
        formData.append('locations', JSON.stringify(locations));
        
        // Добавляем CSRF токен если он есть
        if (window.csrfToken) {
            formData.append('csrf_token', window.csrfToken);
        }
        
        try {
            const response = await fetch('/api/tools/site-check.php', {
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
        
        // Общая информация о сайте
        const generalCard = this.createGeneralInfoCard(data.general);
        this.resultsContainer.appendChild(generalCard);
        
        // Результаты по локациям
        if (data.locations && data.locations.length > 0) {
            data.locations.forEach((locationData, index) => {
                const locationCard = this.createLocationCard(locationData, index);
                this.resultsContainer.appendChild(locationCard);
            });
        }
        
        // SSL информация
        if (data.ssl) {
            const sslCard = this.createSSLCard(data.ssl);
            this.resultsContainer.appendChild(sslCard);
        }
        
        // HTTP заголовки
        if (data.headers) {
            const headersCard = this.createHeadersCard(data.headers);
            this.resultsContainer.appendChild(headersCard);
        }
        
        // Показываем результаты
        this.showResults();
        
        // Добавляем график времени отклика
        this.createResponseTimeChart(data.locations);
    }
    
    createGeneralInfoCard(general) {
        const statusClass = this.getStatusClass(general.status_code);
        
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = '0ms';
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">Загальна інформація</h3>
                <span class="result-status status-${statusClass}">
                    ${general.status_code || 'N/A'}
                </span>
            </div>
            <div class="result-details">
                <div class="detail-item">
                    <span class="detail-label">URL:</span>
                    <span class="detail-value">${general.url}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">IP адреса:</span>
                    <span class="detail-value">${general.ip || 'Невідома'}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Сервер:</span>
                    <span class="detail-value">${general.server || 'Невідомий'}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Розмір контенту:</span>
                    <span class="detail-value">${this.formatBytes(general.content_length)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Тип контенту:</span>
                    <span class="detail-value">${general.content_type || 'text/html'}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Час перевірки:</span>
                    <span class="detail-value">${this.formatDateTime(general.check_time)}</span>
                </div>
            </div>
        `;
        
        return card;
    }
    
    createLocationCard(locationData, index) {
        const location = this.locations.find(l => l.code === locationData.location);
        const statusClass = this.getStatusClass(locationData.status_code);
        const responseClass = this.getResponseTimeClass(locationData.response_time);
        
        const card = document.createElement('div');
        card.className = 'result-card';
        card.style.animationDelay = `${(index + 1) * 150}ms`;
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">
                    ${location ? location.flag + ' ' + location.name : locationData.location}
                </h3>
                <span class="result-status status-${statusClass}">
                    ${locationData.status_code || 'Помилка'}
                </span>
            </div>
            <div class="result-details">
                <div class="detail-item">
                    <span class="detail-label">Час відповіді:</span>
                    <span class="detail-value ${responseClass}">
                        ${locationData.response_time ? locationData.response_time + ' мс' : 'Тайм-аут'}
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Статус:</span>
                    <span class="detail-value">${locationData.status_text || 'Невідомий'}</span>
                </div>
                ${locationData.error ? `
                <div class="detail-item">
                    <span class="detail-label">Помилка:</span>
                    <span class="detail-value error">${locationData.error}</span>
                </div>
                ` : ''}
                <div class="detail-item">
                    <span class="detail-label">DNS час:</span>
                    <span class="detail-value">${locationData.dns_time || 0} мс</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Час з'єднання:</span>
                    <span class="detail-value">${locationData.connect_time || 0} мс</span>
                </div>
            </div>
        `;
        
        return card;
    }
    
    createSSLCard(sslData) {
        const sslClass = sslData.valid ? 'ssl-info' : 'ssl-expired';
        const expiryClass = this.getSSLExpiryClass(sslData.days_until_expiry);
        
        const card = document.createElement('div');
        card.className = 'result-card';
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">SSL Сертифікат</h3>
                <span class="result-status ${sslData.valid ? 'status-success' : 'status-error'}">
                    ${sslData.valid ? 'Дійсний' : 'Недійсний'}
                </span>
            </div>
            <div class="${sslClass}">
                <div class="result-details">
                    <div class="detail-item">
                        <span class="detail-label">Емітент:</span>
                        <span class="detail-value">${sslData.issuer || 'Невідомий'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Виданий:</span>
                        <span class="detail-value">${this.formatDate(sslData.valid_from)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Діє до:</span>
                        <span class="detail-value ${expiryClass}">
                            ${this.formatDate(sslData.valid_to)}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Днів до закінчення:</span>
                        <span class="detail-value ${expiryClass}">
                            ${sslData.days_until_expiry} днів
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Альтернативні імена:</span>
                        <span class="detail-value">${sslData.alt_names ? sslData.alt_names.join(', ') : 'Немає'}</span>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }
    
    createHeadersCard(headers) {
        const card = document.createElement('div');
        card.className = 'result-card';
        
        const headersHtml = Object.entries(headers)
            .map(([key, value]) => `
                <tr>
                    <td>${this.escapeHtml(key)}</td>
                    <td>${this.escapeHtml(value)}</td>
                </tr>
            `).join('');
        
        card.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">HTTP Заголовки</h3>
                <span class="result-status status-success">
                    ${Object.keys(headers).length} заголовків
                </span>
            </div>
            <div class="table-responsive">
                <table class="headers-table">
                    <thead>
                        <tr>
                            <th>Заголовок</th>
                            <th>Значення</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${headersHtml}
                    </tbody>
                </table>
            </div>
        `;
        
        return card;
    }
    
    createResponseTimeChart(locations) {
        const chartContainer = document.createElement('div');
        chartContainer.className = 'result-card';
        chartContainer.innerHTML = `
            <div class="result-header">
                <h3 class="result-title">Графік часу відповіді</h3>
            </div>
            <div class="response-chart">
                <canvas id="responseChart" width="400" height="200"></canvas>
            </div>
        `;
        
        this.resultsContainer.appendChild(chartContainer);
        
        // Создаем график с Chart.js если доступен
        if (typeof Chart !== 'undefined') {
            this.renderChart(locations);
        } else {
            // Простой текстовый график
            this.renderSimpleChart(locations);
        }
    }
    
    renderChart(locations) {
        const ctx = document.getElementById('responseChart');
        if (!ctx) return;
        
        const labels = locations.map(loc => {
            const location = this.locations.find(l => l.code === loc.location);
            return location ? location.name : loc.location;
        });
        
        const data = locations.map(loc => loc.response_time || 0);
        const colors = data.map(time => {
            if (time < 500) return '#10B981'; // green
            if (time < 1000) return '#F59E0B'; // yellow
            return '#EF4444'; // red
        });
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Час відповіді (мс)',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Міллісекунди'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    renderSimpleChart(locations) {
        const chart = document.getElementById('responseChart');
        if (!chart) return;
        
        const maxTime = Math.max(...locations.map(l => l.response_time || 0));
        
        let html = '<div class="simple-chart">';
        locations.forEach(loc => {
            const location = this.locations.find(l => l.code === loc.location);
            const name = location ? location.name : loc.location;
            const time = loc.response_time || 0;
            const percentage = maxTime > 0 ? (time / maxTime) * 100 : 0;
            const color = time < 500 ? '#10B981' : time < 1000 ? '#F59E0B' : '#EF4444';
            
            html += `
                <div class="chart-bar" style="margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>${name}</span>
                        <span>${time} мс</span>
                    </div>
                    <div style="background: #e5e7eb; height: 20px; border-radius: 10px;">
                        <div style="background: ${color}; height: 100%; width: ${percentage}%; border-radius: 10px; transition: width 0.5s ease;"></div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        chart.outerHTML = html;
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
                    <h2 class="display-5 fw-bold">Результати перевірки</h2>
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
                <span>Виконується перевірка сайту...</span>
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
    
    disableSubmit(text = 'Перевірити') {
        if (this.submitBtn) {
            this.submitBtn.disabled = true;
            this.submitBtn.textContent = text;
        }
    }
    
    enableSubmit() {
        if (this.submitBtn) {
            this.submitBtn.disabled = false;
            this.submitBtn.textContent = 'Перевірити';
        }
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
    
    getStatusClass(statusCode) {
        if (!statusCode) return 'error';
        if (statusCode >= 200 && statusCode < 300) return 'success';
        if (statusCode >= 300 && statusCode < 400) return 'warning';
        return 'error';
    }
    
    getResponseTimeClass(responseTime) {
        if (!responseTime) return 'error';
        if (responseTime < 500) return 'success';
        if (responseTime < 1000) return 'warning';
        return 'error';
    }
    
    getSSLExpiryClass(daysUntilExpiry) {
        if (daysUntilExpiry < 0) return 'error';
        if (daysUntilExpiry < 30) return 'warning';
        return 'success';
    }
    
    formatBytes(bytes) {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    formatDate(dateString) {
        if (!dateString) return 'Невідомо';
        const date = new Date(dateString);
        return date.toLocaleDateString('uk-UA');
    }
    
    formatDateTime(dateString) {
        if (!dateString) return new Date().toLocaleString('uk-UA');
        const date = new Date(dateString);
        return date.toLocaleString('uk-UA');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    copyApiCode() {
        const codeElement = document.querySelector('.code-example pre code');
        if (!codeElement) return;
        
        const text = codeElement.textContent;
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.showSuccess('Код скопійовано в буфер обміну');
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
            this.showSuccess('Код скопійовано в буфер обміну');
        } catch (err) {
            this.showError('Помилка копіювання коду');
        }
        
        document.body.removeChild(textArea);
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, что мы на странице site-check
    if (document.getElementById('siteCheckForm')) {
        window.siteChecker = new SiteChecker();
    }
});

// Дополнительные функции для совместимости
function copyCode() {
    if (window.siteChecker) {
        window.siteChecker.copyApiCode();
    }
}

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SiteChecker;
}