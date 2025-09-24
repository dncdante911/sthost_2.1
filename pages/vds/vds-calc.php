<?php
define('SECURE_ACCESS', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/pages/vds-calc.css">

<section class="vds-hero text-center text-white">
    <div class="container">
        <h1 class="display-4 fw-bold">Калькулятор VDS / VPS</h1>
        <p class="lead">Створіть ідеальний сервер під свої потреби</p>
        <div class="hero-visual mt-4">
            <img src="/assets/images/vds-server.svg" alt="VDS Servers" class="img-fluid animate-float">
        </div>
    </div>
</section>

<section class="vds-calculator section">
    <div class="container">
        <div class="row g-4">
            <!-- Настройки -->
            <div class="col-lg-8">
                <div class="calc-card p-4 shadow-sm rounded">
                    <h3 class="mb-4 fw-bold">Налаштування конфігурації</h3>

                    <!-- CPU -->
                    <div class="mb-4 slider-block">
                        <div class="d-flex justify-content-between">
                            <label for="cpuRange" class="form-label fw-semibold">CPU</label>
                            <span class="value-label"><span id="cpuValue">4</span> ядер</span>
                        </div>
                        <input type="range" min="1" max="32" step="1" value="4" id="cpuRange" class="form-range custom-range">
                    </div>

                    <!-- RAM -->
                    <div class="mb-4 slider-block">
                        <div class="d-flex justify-content-between">
                            <label for="ramRange" class="form-label fw-semibold">RAM</label>
                            <span class="value-label"><span id="ramValue">8</span> GB</span>
                        </div>
                        <input type="range" min="1" max="128" step="1" value="8" id="ramRange" class="form-range custom-range">
                    </div>

                    <!-- SSD -->
                    <div class="mb-4 slider-block">
                        <div class="d-flex justify-content-between">
                            <label for="ssdRange" class="form-label fw-semibold">SSD</label>
                            <span class="value-label"><span id="ssdValue">100</span> GB</span>
                        </div>
                        <input type="range" min="20" max="2000" step="10" value="100" id="ssdRange" class="form-range custom-range">
                    </div>

                    <!-- Bandwidth -->
                    <div class="mb-4 slider-block">
                        <div class="d-flex justify-content-between">
                            <label for="bwRange" class="form-label fw-semibold">Трафік</label>
                            <span class="value-label"><span id="bwValue">1000</span> GB</span>
                        </div>
                        <input type="range" min="100" max="10000" step="100" value="1000" id="bwRange" class="form-range custom-range">
                    </div>

                    <!-- Доп опции -->
                    <h5 class="mt-4 mb-3">Додаткові опції</h5>
                    <div class="options-grid">
                        <label class="option-card">
                            <input type="checkbox" value="5" id="backupOption">
                            <span>Резервне копіювання <small>(+$5/міс)</small></span>
                        </label>
                        <label class="option-card">
                            <input type="checkbox" value="3" id="sslOption">
                            <span>SSL Сертифікат <small>(+$3/міс)</small></span>
                        </label>
                        <label class="option-card">
                            <input type="checkbox" value="7" id="panelOption">
                            <span>Панель керування <small>(+$7/міс)</small></span>
                        </label>
                    </div>

                    <!-- Період -->
                    <h5 class="mt-4 mb-3">Період оплати</h5>
                    <select id="billingPeriod" class="form-select w-auto">
                        <option value="1">Щомісячно</option>
                        <option value="12">Річна оплата (-10%)</option>
                    </select>
                </div>
            </div>

            <!-- Итог -->
            <div class="col-lg-4">
                <div class="sticky-summary p-4 shadow-sm rounded">
                    <h4 class="mb-3">Ваша конфігурація</h4>
                    <ul class="list-unstyled mb-3">
                        <li>CPU: <span id="summaryCPU">4</span> ядер</li>
                        <li>RAM: <span id="summaryRAM">8</span> GB</li>
                        <li>SSD: <span id="summarySSD">100</span> GB</li>
                        <li>Трафік: <span id="summaryBW">1000</span> GB</li>
                        <li id="summaryOptions">Опції: -</li>
                    </ul>
                    <hr>
                    <h3 class="text-primary fw-bold">$<span id="totalPrice">0</span>/міс</h3>
                    <button class="btn btn-primary w-100 mt-3" id="orderBtn">Замовити</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="/assets/js/vds-calc.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
