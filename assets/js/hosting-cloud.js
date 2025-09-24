// hosting-cloud.js - Скрипти для сторінки хмарного хостингу

// Ціни за ресурси
const pricing = {
    cpu: 150,      // ₴ за ядро
    ram: 75,       // ₴ за ГБ
    storage: 5,    // ₴ за ГБ
    bandwidth: 2   // ₴ за ГБ трафіку
};

// Готові конфігурації
const configs = {
    start: {
        name: 'Cloud Start',
        cpu: 1,
        ram: 2,
        storage: 25,
        bandwidth: 500,
        price: 399
    },
    business: {
        name: 'Cloud Business',
        cpu: 2,
        ram: 4,
        storage: 50,
        bandwidth: 1000,
        price: 799
    },
    pro: {
        name: 'Cloud Pro',
        cpu: 4,
        ram: 8,
        storage: 100,
        bandwidth: 2000,
        price: 1499
    },
    enterprise: {
        name: 'Cloud Enterprise',
        cpu: 8,
        ram: 16,
        storage: 200,
        bandwidth: 5000,
        price: 2999
    }
};

// Поточна конфігурація
let currentConfig = {
    cpu: 2,
    ram: 4,
    storage: 50,
    bandwidth: 1000,
    options: []
};

// Ініціалізація при завантаженні сторінки
document.addEventListener('DOMContentLoaded', function() {
    initCalculator();
    loadSavedConfiguration();
    initAnimations();
});

// Ініціалізація калькулятора
function initCalculator() {
    // CPU слайдер
    const cpuSlider = document.getElementById('cpu-slider');
    if (cpuSlider) {
        cpuSlider.addEventListener('input', function() {
            currentConfig.cpu = parseInt(this.value);
            document.getElementById('cpu-value').textContent = this.value;
            document.getElementById('summary-cpu').textContent = this.value;
            updatePrice();
        });
    }
    
    // RAM слайдер
    const ramSlider = document.getElementById('ram-slider');
    if (ramSlider) {
        ramSlider.addEventListener('input', function() {
            currentConfig.ram = parseInt(this.value);
            document.getElementById('ram-value').textContent = this.value;
            document.getElementById('summary-ram').textContent = this.value;
            updatePrice();
        });
    }
    
    // Storage слайдер
    const storageSlider = document.getElementById('storage-slider');
    if (storageSlider) {
        storageSlider.addEventListener('input', function() {
            currentConfig.storage = parseInt(this.value);
            document.getElementById('storage-value').textContent = this.value;
            document.getElementById('summary-storage').textContent = this.value;
            updatePrice();
        });
    }
    
    // Bandwidth слайдер
    const bandwidthSlider = document.getElementById('bandwidth-slider');
    if (bandwidthSlider) {
        bandwidthSlider.addEventListener('input', function() {
            currentConfig.bandwidth = parseInt(this.value);
            const value = parseInt(this.value);
            document.getElementById('bandwidth-value').textContent = value >= 1000 ? (value/1000).toFixed(1) : value;
            document.getElementById('summary-bandwidth').textContent = value >= 1000 ? (value/1000).toFixed(1) + ' ТБ' : value;
            updatePrice();
        });
    }
    
    // Додаткові опції
    document.querySelectorAll('.option-check input').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const price = parseInt(this.dataset.price);
            const name = this.nextElementSibling.querySelector('.option-title').textContent;
            
            if (this.checked) {
                currentConfig.options.push({ name, price });
            } else {
                currentConfig.options = currentConfig.options.filter(opt => opt.name !== name);
            }
            
            updateOptionsDisplay();
            updatePrice();
        });
    });
    
    // Перемикач періоду оплати
    document.querySelectorAll('input[name="period"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updatePrice();
            
            const yearlyInfo = document.getElementById('yearly-info');
            if (this.id === 'yearly') {
                yearlyInfo.classList.remove('d-none');
            } else {
                yearlyInfo.classList.add('d-none');
            }
        });
    });
}

// Оновлення ціни
function updatePrice() {
    // Базова ціна
    let basePrice = 
        currentConfig.cpu * pricing.cpu +
        currentConfig.ram * pricing.ram +
        currentConfig.storage * pricing.storage +
        currentConfig.bandwidth * pricing.bandwidth;
    
    // Додаткові опції
    let optionsPrice = currentConfig.options.reduce((sum, opt) => sum + opt.price, 0);
    
    // Загальна ціна
    let totalPrice = basePrice + optionsPrice;
    
    // Застосування знижки для річної оплати
    const isYearly = document.getElementById('yearly').checked;
    if (isYearly) {
        const yearlyPrice = totalPrice * 12 * 0.85; // 15% знижка
        const savings = (totalPrice * 12) - yearlyPrice;
        document.getElementById('yearly-savings').textContent = Math.round(savings);
        totalPrice = Math.round(yearlyPrice / 12);
    }
    
    // Оновлення відображення ціни
    document.getElementById('monthly-price').textContent = totalPrice;
}

// Відображення обраних опцій
function updateOptionsDisplay() {
    const container = document.getElementById('selected-options');
    
    if (currentConfig.options.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="mb-2"><small class="text-white-50">Додаткові опції:</small></div>';
    currentConfig.options.forEach(opt => {
        html += `
            <div class="selected-option">
                <span>${opt.name}</span>
                <span>+${opt.price} ₴</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Вибір готової конфігурації
function selectConfig(configName) {
    const config = configs[configName];
    if (!config) return;
    
    // Встановлення значень слайдерів
    document.getElementById('cpu-slider').value = config.cpu;
    document.getElementById('cpu-value').textContent = config.cpu;
    document.getElementById('summary-cpu').textContent = config.cpu;
    
    document.getElementById('ram-slider').value = config.ram;
    document.getElementById('ram-value').textContent = config.ram;
    document.getElementById('summary-ram').textContent = config.ram;
    
    document.getElementById('storage-slider').value = config.storage;
    document.getElementById('storage-value').textContent = config.storage;
    document.getElementById('summary-storage').textContent = config.storage;
    
    document.getElementById('bandwidth-slider').value = config.bandwidth;
    document.getElementById('bandwidth-value').textContent = config.bandwidth;
    document.getElementById('summary-bandwidth').textContent = config.bandwidth >= 1000 ? 
        (config.bandwidth/1000).toFixed(1) + ' ТБ' : config.bandwidth;
    
    // Оновлення поточної конфігурації
    currentConfig.cpu = config.cpu;
    currentConfig.ram = config.ram;
    currentConfig.storage = config.storage;
    currentConfig.bandwidth = config.bandwidth;
    
    // Оновлення ціни
    updatePrice();
    
    // Прокрутка до калькулятора
    document.getElementById('calculator').scrollIntoView({ behavior: 'smooth' });
    
    // Показ повідомлення
    showNotification(`Конфігурація "${config.name}" застосована`);
}

// Збереження конфігурації
function saveConfiguration() {
    const configData = {
        cpu: currentConfig.cpu,
        ram: currentConfig.ram,
        storage: currentConfig.storage,
        bandwidth: currentConfig.bandwidth,
        options: currentConfig.options,
        savedAt: new Date().toISOString()
    };
    
    localStorage.setItem('cloudConfig', JSON.stringify(configData));
    showNotification('Конфігурація збережена успішно!');
}

// Завантаження збереженої конфігурації
function loadSavedConfiguration() {
    const saved = localStorage.getItem('cloudConfig');
    if (!saved) return;
    
    try {
        const config = JSON.parse(saved);
        
        // Встановлення збережених значень
        if (document.getElementById('cpu-slider')) {
            document.getElementById('cpu-slider').value = config.cpu;
            document.getElementById('cpu-value').textContent = config.cpu;
            document.getElementById('summary-cpu').textContent = config.cpu;
        }
        
        if (document.getElementById('ram-slider')) {
            document.getElementById('ram-slider').value = config.ram;
            document.getElementById('ram-value').textContent = config.ram;
            document.getElementById('summary-ram').textContent = config.ram;
        }
        
        if (document.getElementById('storage-slider')) {
            document.getElementById('storage-slider').value = config.storage;
            document.getElementById('storage-value').textContent = config.storage;
            document.getElementById('summary-storage').textContent = config.storage;
        }
        
        if (document.getElementById('bandwidth-slider')) {
            document.getElementById('bandwidth-slider').value = config.bandwidth;
            document.getElementById('bandwidth-value').textContent = config.bandwidth;
            document.getElementById('summary-bandwidth').textContent = config.bandwidth >= 1000 ? 
                (config.bandwidth/1000).toFixed(1) + ' ТБ' : config.bandwidth;
        }
        
        // Відновлення опцій
        if (config.options && config.options.length > 0) {
            config.options.forEach(opt => {
                const checkbox = Array.from(document.querySelectorAll('.option-check input')).find(
                    cb => cb.nextElementSibling.querySelector('.option-title').textContent === opt.name
                );
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
        
        currentConfig = config;
        updateOptionsDisplay();
        updatePrice();
        
        // Показ повідомлення про завантаження
        const savedDate = new Date(config.savedAt);
        const formattedDate = savedDate.toLocaleDateString('uk-UA');
        showNotification(`Завантажено конфігурацію від ${formattedDate}`);
        
    } catch (e) {
        console.error('Error loading saved configuration:', e);
    }
}

// Замовлення хмарного хостингу
function orderCloud() {
    // Підготовка даних конфігурації
    const configDetails = `
        <table class="table table-sm">
            <tr><td>Процесор:</td><td><strong>${currentConfig.cpu} vCPU</strong></td></tr>
            <tr><td>Пам'ять:</td><td><strong>${currentConfig.ram} ГБ RAM</strong></td></tr>
            <tr><td>Диск:</td><td><strong>${currentConfig.storage} ГБ SSD</strong></td></tr>
            <tr><td>Трафік:</td><td><strong>${currentConfig.bandwidth >= 1000 ? 
                (currentConfig.bandwidth/1000).toFixed(1) + ' ТБ' : currentConfig.bandwidth + ' ГБ'}/міс</strong></td></tr>
        </table>
    `;
    
    if (currentConfig.options.length > 0) {
        let optionsHtml = '<h6 class="mt-3">Додаткові опції:</h6><ul class="mb-0">';
        currentConfig.options.forEach(opt => {
            optionsHtml += `<li>${opt.name} (+${opt.price} ₴/міс)</li>`;
        });
        optionsHtml += '</ul>';
        document.getElementById('order-config-details').innerHTML = configDetails + optionsHtml;
    } else {
        document.getElementById('order-config-details').innerHTML = configDetails;
    }
    
    // Показ модального вікна
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
        type: 'cloud_hosting',
        config: currentConfig,
        customer: {
            name: formData.get('name'),
            surname: formData.get('surname'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            domain: formData.get('domain'),
            comment: formData.get('comment')
        },
        price: document.getElementById('monthly-price').textContent,
        period: document.getElementById('yearly').checked ? 'yearly' : 'monthly'
    };
    
    // Відправка даних через AJAX
    fetch('/api/orders/cloud.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Закриття модального вікна
            bootstrap.Modal.getInstance(document.getElementById('orderModal')).hide();
            
            // Показ повідомлення про успіх
            showNotification('Замовлення успішно відправлено! Ми зв\'яжемось з вами найближчим часом.', 'success');
            
            // Очищення форми
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

// Запит на міграцію
function requestMigration() {
    // Показ форми міграції
    const modal = new bootstrap.Modal(document.getElementById('migrationModal'));
    if (modal) {
        modal.show();
    } else {
        // Якщо модального вікна немає, перенаправлення на контакти
        window.location.href = '/pages/info/contacts.php?service=migration';
    }
}

// Ініціалізація анімацій
function initAnimations() {
    // Анімація серверних нод при скролі
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.server-node').forEach(node => {
        observer.observe(node);
    });
    
    // Паралакс ефект для hero секції
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.cloud-hero');
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });
}

// Показ сповіщень
function showNotification(message, type = 'info') {
    // Створення елемента сповіщення
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show notification`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Стилі для позиціонування
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Автоматичне закриття через 5 секунд
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Плавна прокрутка до якорів
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Анімація CSS для сповіщень
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .notification {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
`;
document.head.appendChild(style);