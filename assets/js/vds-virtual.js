/**
 * VDS Virtual Page JavaScript
 * Handles VPS plan ordering, billing toggles, and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('VDS Virtual page JavaScript loading...');
    
    // Initialize all components
    try {
        initBillingToggle();
        initProgressBars();
        initVPSOrdering();
        initSmoothScrolling();
        initOSSelection();
        console.log('VDS Virtual page initialized successfully');
    } catch (error) {
        console.error('Error initializing VDS Virtual page:', error);
    }
});

/**
 * Initialize billing period toggle (monthly/yearly)
 */
function initBillingToggle() {
    const monthlyRadio = document.getElementById('vps-monthly');
    const yearlyRadio = document.getElementById('vps-yearly');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const yearlyPrices = document.querySelectorAll('.yearly-price');
    
    function togglePricing() {
        if (yearlyRadio && yearlyRadio.checked) {
            monthlyPrices.forEach(price => price.classList.add('d-none'));
            yearlyPrices.forEach(price => price.classList.remove('d-none'));
        } else {
            monthlyPrices.forEach(price => price.classList.remove('d-none'));
            yearlyPrices.forEach(price => price.classList.add('d-none'));
        }
    }
    
    if (monthlyRadio) monthlyRadio.addEventListener('change', togglePricing);
    if (yearlyRadio) yearlyRadio.addEventListener('change', togglePricing);
}