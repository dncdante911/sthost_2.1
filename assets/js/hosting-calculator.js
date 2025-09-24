// hosting-calculator.js - Скрипти для калькулятора хостингу

// Ціни та конфігурації
const pricing = {
    shared: {
        plans: {
            start: { price: 99, name: 'Start', features: '1 сайт, 5 ГБ SSD' },
            basic: { price: 199, name: 'Basic', features: '5 сайтів, 15 ГБ SSD' },
            pro: { price: 399, name: 'Pro', features: 'Необмежено сайтів, 50 ГБ SSD' },
            business: { price: 699, name: 'Business', features: 'Необмежено сайтів, 100 ГБ SSD' }
        }
    },
    vps: {
        cpu: 100,      // за ядро
        ram: 50,       // за ГБ
        storage: 3     // за ГБ
    },
    dedicated: {
        servers: {
            entry: { price: 4999, name: 'Entry Server' },
            power: { price: 7999, name: 'Power Server' },
            elite: { price: 12999, name: 'Elite Server' }
        }
    },
    cloud: {
        cpu: 150,      // за ядро
        ram: 75,       // за ГБ
        storage: 5,    // за ГБ
        bandwidth: 2   // за ГБ
    }
};

// Поточна конфігурація
let currentConfig = {
    service: 'shared',
    plan: 'start',
    options: [],
    period: 'monthly',
    vps: {
        cpu: 2,
        ram: 4,
        storage: 50,
        os: 'ubuntu'
    },
    cloud: {
        cpu: 4,
        ram: 8,
        storage: 100,
        bandwidth: 1000
    },
    dedicated: {
        server: 'entry'
    }
};

// Ініціалізація
document.addEventListener('DOMContentLoaded', function() {
    initServiceSelector();
    initPlanSelectors();
    initSliders();
    initOptions();
    initPeriodSelector();
    loadSavedConfig();
    calculatePrice();
});

// Ініціалізація вибору сервісу
function initServiceSelector() {
    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('click', function() {
            // Оновлення активної картки
            document.querySelectorAll('.service-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            // Оновлення конфігурації
            currentConfig.service = this.dataset.service;
            
            // Показ відповідної конфігурації
            document.querySelectorAll('.service-config').forEach(config => {
                config.classList.remove('active');
            });
            document.getElementById(`${currentConfig.service}-config`).classList.add('active');
            
            // Перерахунок ціни
            calculatePrice();
        });
    });
}

// Ініціалізація вибору планів
function initPlanSelectors() {
    // Shared hosting плани
    document.querySelectorAll('.plan-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.plan-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            currentConfig.plan = this.dataset.plan;
            calculatePrice();
        });
    });
    
    // Dedicated сервери
    document.querySelectorAll('.server-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.server-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            currentConfig.dedicated.server = this.dataset.price ? 
                Object.keys(pricing.dedicated.servers).find(key => 
                    pricing.dedicated.servers[key].price == this.dataset.price
                ) : 'entry';
            calculatePrice();
        });
    });
}

// Ініціалізація слайдерів
function initSliders() {
    // VPS слайдери
    const vpsCpu = document.getElementById('vps-cpu');
    if (vpsCpu) {
        vpsCpu.addEventListener('input', function() {
            currentConfig.vps.cpu = parseInt(this.value);
            document.getElementById('vps-cpu-value').textContent = `${this.value} ядра`;
            calculatePrice();
        });
    }
    
    const vpsRam = document.getElementById('vps-ram');
    if (vpsRam) {
        vpsRam.addEventListener('input', function() {
            currentConfig.vps.ram = parseInt(this.value);
            document.getElementById('vps-ram-value').textContent = `${this.value} ГБ`;
            calculatePrice();
        });
    }
    
    const vpsStorage = document.getElementById('vps-storage');
    if (vpsStorage) {
        vpsStorage.addEventListener('input', function() {
            currentConfig.vps.storage = parseInt(this.value);
            document.getElementById('vps-storage-value').textContent = `${this.value} ГБ`;
            calculatePrice();
        });
    }
    
    // Cloud слайдери
    const cloudCpu = document.getElementById('cloud-cpu');
    if (cloudCpu) {
        cloudCpu.addEventListener('input', function() {
            currentConfig.cloud.cpu = parseInt(this.value);
            document.getElementById('cloud-cpu-value').textContent = `${this.value} ядра`;
            calculatePrice();
        });
    }
    
    const cloudRam = document.getElementById('cloud-ram');
    if (cloudRam) {
        cloudRam.addEventListener('input', function() {
            currentConfig.cloud.ram = parseInt(this.value);
            document.getElementById('cloud-ram-value').textContent = `${this.value} ГБ`;
            calculatePrice();
        });
    }
    
    const cloudStorage = document.getElementById('cloud-storage');
    if (cloudStorage) {
        cloudStorage.addEventListener('input', function() {
            currentConfig.cloud.storage = parseInt(this.value);
            document.getElementById('cloud-storage-value').textContent = `${this.value} ГБ`;
            calculatePrice();
        });
    }
    
    const cloudBandwidth = document.getElementById('cloud-bandwidth');
    if (cloudBandwidth) {
        cloudBandwidth.addEventListener('input', function() {
            currentConfig.cloud.bandwidth = parseInt(this.value);
            const value = parseInt(this.value);
            document.getElementById('cloud-bandwidth-value').textContent = 
                value >= 1000 ? `${(value/1000).toFixed(1)} ТБ` : `${value} ГБ`;
            calculatePrice();
        });
    }
}

// Ініціалізація опцій
function initOptions() {
    document.querySelectorAll('.form-check-input').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const price = parseInt(this.dataset.price) || 0;
            const optionName = this.nextElementSibling.textContent.trim();
            
            if (this.checked) {
                currentConfig.options.push({ name: optionName, price: price });
            } else {
                currentConfig.options = currentConfig.options.filter(opt => opt.name !== optionName);
            }
            
            calculatePrice();
        });
    });
    
    // VPS OS selector
    const vpsOs = document.getElementById('vps-os');
    if (vpsOs) {
        vpsOs.addEventListener('change', function() {
            currentConfig.vps.os = this.value;
            calculatePrice();
        });
    }
}

// Ініціалізація вибору періоду
function initPeriodSelector() {
    document.querySelectorAll('input[name="period"]').forEach(radio => {
        radio.addEventListener('change', function() {
            currentConfig.period = this.id;
            calculatePrice();
        });
    });
}

// Розрахунок ціни
function calculatePrice() {
    let basePrice = 0;
    let summaryContent = '';
    
    switch (currentConfig.service) {
        case 'shared':
            const sharedPlan = pricing.shared.plans[currentConfig.plan];
            basePrice = sharedPlan.price;
            summaryContent = `
                <div class="summary-item">
                    <strong>Тип:</strong> Віртуальний хостинг
                </div>
                <div class="summary-item">
                    <strong>План:</strong> ${sharedPlan.name}
                </div>
                <div class="summary-item">
                    <strong>Характеристики:</strong> ${sharedPlan.features}
                </div>
            `;
            break;
            
        case 'vps':
            basePrice = 
                currentConfig.vps.cpu * pricing.vps.cpu +
                currentConfig.vps.ram * pricing.vps.ram +
                currentConfig.vps.storage * pricing.vps.storage;
            
            // Додаткова вартість за Windows
            if (currentConfig.vps.os === 'windows') {
                basePrice += 500;
            }
            
            summaryContent = `
                <div class="summary-item">
                    <strong>Тип:</strong> VPS сервер
                </div>
                <div class="summary-item">
                    <strong>CPU:</strong> ${currentConfig.vps.cpu} vCPU
                </div>
                <div class="summary-item">
                    <strong>RAM:</strong> ${currentConfig.vps.ram} ГБ
                </div>
                <div class="summary-item">
                    <strong>SSD:</strong> ${currentConfig.vps.storage} ГБ
                </div>
                <div class="summary-item">
                    <strong>ОС:</strong> ${document.getElementById('vps-os')?.options[document.getElementById('vps-os').selectedIndex].text || 'Ubuntu'}
                </div>
            `;
            break;
            
        case 'dedicated':
            const server = pricing.dedicated.servers[currentConfig.dedicated.server];
            basePrice = server.price;
            summaryContent = `
                <div class="summary-item">
                    <strong>Тип:</strong> Виділений сервер
                </div>
                <div class="summary-item">
                    <strong>Конфігурація:</strong> ${server.name}
                </div>
            `;
            break;
            
        case 'cloud':
            basePrice = 
                currentConfig.cloud.cpu * pricing.cloud.cpu +
                currentConfig.cloud.ram * pricing.cloud.ram +
                currentConfig.cloud.storage * pricing.cloud.storage +
                currentConfig.cloud.bandwidth * pricing.cloud.bandwidth;
            
            summaryContent = `
                <div class="summary-item">
                    <strong>Тип:</strong> Хмарний хостинг
                </div>
                <div class="summary-item">
                    <strong>CPU:</strong> ${currentConfig.cloud.cpu} vCPU
                </div>
                <div class="summary-item">
                    <strong>RAM:</strong> ${currentConfig.cloud.ram} ГБ
                </div>
                <div class="summary-item">
                    <strong>Storage:</strong> ${currentConfig.cloud.storage} ГБ SSD
                </div>
                <div class="summary-item">
                    <strong>Трафік:</strong> ${currentConfig.cloud.bandwidth >= 1000 ? 
                        (currentConfig.cloud.bandwidth/1000).toFixed(1) + ' ТБ' : 
                        currentConfig.cloud.bandwidth + ' ГБ'}/міс
                </div>
            `;
            break;
    }
    
    // Додаткові опції
    let optionsPrice = currentConfig.options.reduce((sum, opt) => sum + opt.price, 0);
    
    // Розрахунок знижки
    let discount = 0;
    let discountPercent = 0;
    switch (currentConfig.period) {
        case 'quarterly':
            discountPercent = 5;
            break;
        case 'semiannual':
            discountPercent = 10;
            break;
        case 'annual':
            discountPercent = 15;
            break;
    }
    
    let totalPrice = basePrice + optionsPrice;
    discount = Math.round(totalPrice * discountPercent / 100);
    let finalPrice = totalPrice - discount;
    
    // Оновлення відображення
    document.getElementById('summary-content').innerHTML = summaryContent;
    document.getElementById('base-price').textContent = `${basePrice} ₴`;
    document.getElementById('options-price').textContent = `${optionsPrice} ₴`;
    
    const discountLine = document.querySelector('.discount-line');
    if (discount > 0) {
        discountLine.classList.remove('d-none');
        document.getElementById('discount-amount').textContent = `-${discount} ₴`;
    } else {
        discountLine.classList.add('d-none');
    }
    
    document.getElementById('total-price').textContent = finalPrice;
}

// Збереження конфігурації
function saveConfiguration() {
    const configData = {
        config: currentConfig,
        price: document.getElementById('total-price').textContent,
        savedAt: new Date().toISOString()
    };
    
    localStorage.setItem('hostingConfig', JSON.stringify(configData));
    showNotification('Конфігурація збережена успішно!', 'success');
}

// Завантаження збереженої конфігурації
function loadSavedConfig() {
    const saved = localStorage.getItem('hostingConfig');
    if (!saved) return;
    
    try {
        const data = JSON.parse(saved);
        currentConfig = data.config;
        
        // Відновлення UI стану
        // Тут можна додати код для відновлення стану інтерфейсу
        
        const savedDate = new Date(data.savedAt);
        const formattedDate = savedDate.toLocaleDateString('uk-UA');
        showNotification(`Завантажено конфігурацію від ${formattedDate}`, 'info');
        
        calculatePrice();
    } catch (e) {
        console.error('Error loading saved configuration:', e);
    }
}

// Перехід до замовлення
function proceedToOrder() {
    const orderSummary = document.getElementById('order-summary');
    const totalPrice = document.getElementById('total-price').textContent;
    
    orderSummary.innerHTML = `
        <h5>Деталі замовлення</h5>
        ${document.getElementById('summary-content').innerHTML}
        <div class="mt-3 pt-3 border-top">
            <strong>Всього до оплати: ${totalPrice} ₴/міс</strong>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('orderModal'));
    modal.show();
}

// Відправка замовлення
function submitOrder() {
    const form = document.getElementById('orderForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const orderData = {
        service: currentConfig.service,
        config: currentConfig,
        customer: {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            domain: formData.get('domain'),
            comment: formData.get('comment')
        },
        price: document.getElementById('total-price').textContent,
        period: currentConfig.period
    };
    
    // Відправка даних
    fetch('/api/orders/create.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('orderModal')).hide();
            showNotification('Замовлення успішно відправлено! Ми зв\'яжемось з вами найближчим часом.', 'success');
            form.reset();
        } else {
            showNotification('Помилка при відправці замовлення. Спробуйте ще раз.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Помилка з\'єднання. Перевірте інтернет-підключення.', 'error');
    });
}

// Показ сповіщень
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}