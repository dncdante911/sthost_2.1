/* ==========================================================================
   StormHosting UA - Calculators Script
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function() {
    // Cloud Calculator
    const cloudCalculator = document.getElementById('cloudCalculator');
    const cloudPriceDisplay = document.getElementById('cloudCalculatedPrice');
    const cpuSlider = document.getElementById('cpuSlider');
    const ramSlider = document.getElementById('ramSlider');
    const storageSlider = document.getElementById('storageSlider');
    const bandwidthSlider = document.getElementById('bandwidthSlider');
    const cpuValue = document.getElementById('cpuValue');
    const ramValue = document.getElementById('ramValue');
    const storageValue = document.getElementById('storageValue');
    const bandwidthValue = document.getElementById('bandwidthValue');
    const cdnOption = document.getElementById('cdnOption');
    const backupOption = document.getElementById('backupOption');
    const monitoringOption = document.getElementById('monitoringOption');
    const supportOption = document.getElementById('supportOption');

    if (cloudCalculator && cloudPriceDisplay) {
        // Ценообразование
        const pricing = {
            cpu: { base: 50, perUnit: 50 },
            ram: { base: 20, perUnit: 20 },
            storage: { base: 10, perUnit: 1 },
            bandwidth: { base: 50, perUnit: 0.5 },
            additional: {
                backup: 50,
                monitoring: 75,
                support: 100
            }
        };

        function updateValues() {
            cpuValue.textContent = cpuSlider.value;
            ramValue.textContent = ramSlider.value;
            storageValue.textContent = storageSlider.value;
            bandwidthValue.textContent = bandwidthSlider.value;
            calculateCloudPrice();
        }

        function calculateCloudPrice() {
            const cpu = parseInt(cpuSlider.value);
            const ram = parseInt(ramSlider.value);
            const storage = parseInt(storageSlider.value);
            const bandwidth = parseInt(bandwidthSlider.value);
            const backup = backupOption.checked ? pricing.additional.backup : 0;
            const monitoring = monitoringOption.checked ? pricing.additional.monitoring : 0;
            const support = supportOption.checked ? pricing.additional.support : 0;

            const cpuCost = pricing.cpu.base + (cpu - 1) * pricing.cpu.perUnit;
            const ramCost = pricing.ram.base + (ram - 1) * pricing.ram.perUnit;
            const storageCost = pricing.storage.base + (storage - pricing.storage.base) * pricing.storage.perUnit;
            const bandwidthCost = pricing.bandwidth.base + (bandwidth - pricing.bandwidth.base) * pricing.bandwidth.perUnit;

            const totalCost = cpuCost + ramCost + storageCost + bandwidthCost + backup + monitoring + support;
            const finalPrice = Math.round(totalCost);

            cloudPriceDisplay.textContent = finalPrice + ' <?php echo t('currency_month', ' грн/місяць'); ?>';

            // Сохранение конфигурации в localStorage
            localStorage.setItem('cloudConfig', JSON.stringify({
                cpu: cpu,
                ram: ram,
                storage: storage,
                bandwidth: bandwidth,
                backup: backupOption.checked,
                monitoring: monitoringOption.checked,
                support: supportOption.checked,
                price: finalPrice
            }));
        }

        // Обновление значений при изменении слайдеров
        [cpuSlider, ramSlider, storageSlider, bandwidthSlider].forEach(slider => {
            slider.addEventListener('input', updateValues);
        });

        // Обновление при изменении чекбоксов
        [backupOption, monitoringOption, supportOption].forEach(option => {
            option.addEventListener('change', calculateCloudPrice);
        });

        // Начальный расчет
        updateValues();

        // Восстановление сохраненной конфигурации
        const savedConfig = localStorage.getItem('cloudConfig');
        if (savedConfig) {
            const config = JSON.parse(savedConfig);
            cpuSlider.value = config.cpu;
            ramSlider.value = config.ram;
            storageSlider.value = config.storage;
            bandwidthSlider.value = config.bandwidth;
            backupOption.checked = config.backup;
            monitoringOption.checked = config.monitoring;
            supportOption.checked = config.support;
            updateValues();
        }
    }

    // Reseller Calculator (из предыдущего кода, оставляем для совместимости)
    const resellerCalculator = document.getElementById('resellerCalculator');
    const resellerPriceDisplay = document.getElementById('resellerCalculatedPrice');

    if (resellerCalculator && resellerPriceDisplay) {
        // Базовые цены
        const basePrice = 699;

        // Коэффициенты для ресурсов
        const diskMultiplier = { 20: 1, 50: 1.3, 100: 1.8, 200: 2.5 };
        const bandwidthMultiplier = { 200: 1, 500: 1.2, 1000: 1.5, 2000: 2 };
        const accountsMultiplier = { 10: 1, 20: 1.2, 50: 1.5, 100: 2 };
        const periodDiscount = { 1: 1, 3: 0.95, 6: 0.9, 12: 0.8 };

        function calculateResellerPrice() {
            const formData = new FormData(resellerCalculator);
            const diskSpace = formData.get('disk_space');
            const bandwidth = formData.get('bandwidth');
            const accounts = formData.get('accounts');
            const period = formData.get('payment_period');

            let diskCoeff = diskMultiplier[diskSpace] || 1;
            let bandwidthCoeff = bandwidthMultiplier[bandwidth] || 1;
            let accountsCoeff = accountsMultiplier[accounts] || 1;
            let periodCoeff = periodDiscount[period] || 1;

            let finalPrice = Math.round(basePrice * diskCoeff * bandwidthCoeff * accountsCoeff * periodCoeff);

            resellerPriceDisplay.textContent = finalPrice + ' <?php echo t('currency_month', 'грн/місяць'); ?>';
            
            // Сохранение конфигурации в localStorage
            localStorage.setItem('resellerConfig', JSON.stringify({
                disk_space: diskSpace,
                bandwidth: bandwidth,
                accounts: accounts,
                period: period,
                price: finalPrice
            }));
        }

        // Обновляем цену при изменении полей
        resellerCalculator.addEventListener('change', calculateResellerPrice);

        // Начальный расчет
        calculateResellerPrice();

        // Восстановление сохраненной конфигурации
        const savedResellerConfig = localStorage.getItem('resellerConfig');
        if (savedResellerConfig) {
            const config = JSON.parse(savedResellerConfig);
            resellerCalculator.querySelector('[name="disk_space"]').value = config.disk_space;
            resellerCalculator.querySelector('[name="bandwidth"]').value = config.bandwidth;
            resellerCalculator.querySelector('[name="accounts"]').value = config.accounts;
            resellerCalculator.querySelector('[name="payment_period"]').value = config.period;
            calculateResellerPrice();
        }
    }

    // Плавная прокрутка для якорных ссылок
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // AOS анимации
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
});