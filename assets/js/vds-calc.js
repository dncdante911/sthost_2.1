document.addEventListener('DOMContentLoaded', function () {
    const sliders = [
        { range: document.getElementById('cpuRange'), valueEl: document.getElementById('cpuValue'), summaryEl: document.getElementById('summaryCPU'), suffix: ' ядер' },
        { range: document.getElementById('ramRange'), valueEl: document.getElementById('ramValue'), summaryEl: document.getElementById('summaryRAM'), suffix: ' GB' },
        { range: document.getElementById('ssdRange'), valueEl: document.getElementById('ssdValue'), summaryEl: document.getElementById('summarySSD'), suffix: ' GB' },
        { range: document.getElementById('bwRange'), valueEl: document.getElementById('bwValue'), summaryEl: document.getElementById('summaryBW'), suffix: ' GB' }
    ];
    const options = [
        document.getElementById('backupOption'),
        document.getElementById('sslOption'),
        document.getElementById('panelOption')
    ];
    const billingPeriod = document.getElementById('billingPeriod');
    const summaryOptions = document.getElementById('summaryOptions');
    const totalPrice = document.getElementById('totalPrice');
    let mobileSummaryBar;

    // Создаём tooltip для каждого слайдера
    sliders.forEach(sl => {
        const tooltip = document.createElement('div');
        tooltip.className = 'slider-tooltip';
        sl.range.parentElement.style.position = 'relative';
        sl.range.parentElement.appendChild(tooltip);

        function updateTooltip() {
            const val = sl.range.value + sl.suffix;
            tooltip.textContent = val;
            const percent = (sl.range.value - sl.range.min) / (sl.range.max - sl.range.min);
            tooltip.style.left = `${percent * sl.range.offsetWidth}px`;
        }

        sl.range.addEventListener('input', () => {
            updateTooltip();
            calculatePrice();
        });
        sl.range.addEventListener('mousedown', () => sl.range.parentElement.classList.add('active'));
        sl.range.addEventListener('touchstart', () => sl.range.parentElement.classList.add('active'));
        sl.range.addEventListener('mouseup', () => sl.range.parentElement.classList.remove('active'));
        sl.range.addEventListener('touchend', () => sl.range.parentElement.classList.remove('active'));

        updateTooltip();
    });

    function animateValue(el, start, end, duration) {
        let startTime = null;
        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            let progress = Math.min((timestamp - startTime) / duration, 1);
            el.textContent = (start + (end - start) * progress).toFixed(2);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function calculatePrice() {
        let basePrice = (parseInt(sliders[0].range.value) * 5) +
                        (parseInt(sliders[1].range.value) * 2) +
                        (parseInt(sliders[2].range.value) * 0.5) +
                        (parseInt(sliders[3].range.value) * 0.05);

        let selectedOptions = [];
        options.forEach(opt => {
            if (opt.checked) {
                basePrice += parseFloat(opt.value);
                selectedOptions.push(opt.labels[0].innerText);
            }
        });

        let months = parseInt(billingPeriod.value);
        let discount = (months === 12) ? 0.10 : 0;
        let finalPrice = basePrice - (basePrice * discount);

        sliders.forEach(sl => {
            sl.valueEl.textContent = sl.range.value;
            sl.summaryEl.textContent = sl.range.value;
        });

        summaryOptions.textContent = selectedOptions.length ? `Опції: ${selectedOptions.join(', ')}` : 'Опції: -';
        animateValue(totalPrice, parseFloat(totalPrice.textContent), finalPrice, 300);

        if (mobileSummaryBar) {
            mobileSummaryBar.querySelector('.price').textContent = `$${finalPrice.toFixed(2)}/міс`;
        }

        localStorage.setItem('vdsConfig', JSON.stringify({
            cpu: sliders[0].range.value,
            ram: sliders[1].range.value,
            ssd: sliders[2].range.value,
            bw: sliders[3].range.value,
            options: selectedOptions,
            price: finalPrice.toFixed(2),
            period: months
        }));
    }

    // Восстановление конфигурации
    const saved = localStorage.getItem('vdsConfig');
    if (saved) {
        const cfg = JSON.parse(saved);
        sliders[0].range.value = cfg.cpu;
        sliders[1].range.value = cfg.ram;
        sliders[2].range.value = cfg.ssd;
        sliders[3].range.value = cfg.bw;
        billingPeriod.value = cfg.period;
        options.forEach(opt => { opt.checked = cfg.options.includes(opt.labels[0].innerText); });
    }

    // Мобильная панель
    if (window.innerWidth <= 767) {
        mobileSummaryBar = document.createElement('div');
        mobileSummaryBar.className = 'mobile-summary-bar';
        mobileSummaryBar.innerHTML = `
            <span class="price">$0.00/міс</span>
            <button id="mobileOrderBtn">Замовити</button>
        `;
        document.body.appendChild(mobileSummaryBar);
        document.getElementById('mobileOrderBtn').addEventListener('click', () => {
            document.querySelector('.calc-card').scrollIntoView({ behavior: 'smooth' });
        });
    }

    calculatePrice();
});
