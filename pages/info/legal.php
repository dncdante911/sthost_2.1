<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'legal';
$page_title = 'Юридична інформація - StormHosting UA | Реквізити та документи ФОП';
$meta_description = 'Юридична інформація StormHosting UA: реквізити ФОП, ліцензії, сертифікати, банківські реквізити. Повна правова інформація компанії.';
$meta_keywords = 'юридична інформація, реквізити фоп, ліцензії, сертифікати, банківські реквізити, правова інформація';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/info-legal.css'
];

$additional_js = [
    '/assets/js/info-legal.js'
];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Юридична інформація ФОП
$legal_info = [
    'company' => [
        'full_name' => 'Фізична особа-підприємець Діхтярь Ірина Олександрівна',
        'short_name' => 'ФОП Діхтярь І.О.',
        'registration_date' => '2025-08-12',
        'edrpou' => '3009915262',
        'ipn' => '3009915262',
        'license_number' => 'АЕ №012345',
        'license_date' => '2025-09-03',
        'address' => 'м. Дніпро, вул. Холодноярська 10/9',
        'postal_code' => '49047',
        'phone' => '+380 (97) 714-19-80',
        'email' => 'legal@sthost.pro',
        'website' => 'https://sthost.pro'
    ],
    'bank' => [
        'name' => 'АТ "ПУМБ"',
        'account' => 'UA273348510000000026000310998',
        'mfo' => '334851',
        'swift' => 'PBANUA2X',
        'address' => 'м. Дніпро'
    ],
    'tax' => [
        'office' => 'Головне управління ДПС у Дніпропетровській області',
        'address' => 'м. Дніпро, вул. ',
        'tax_number' => '1234567890',
        'vat_payer' => false,
        'tax_group' => '2 група (спрощена система)'
    ]
];

// Категорії документів
$document_categories = [
    'registration' => [
        'title' => 'Реєстраційні документи',
        'icon' => 'bi-file-earmark-text',
        'description' => 'Документи про державну реєстрацію ФОП та право ведення діяльності',
        'color' => 'primary',
        'documents' => [
            [
                'name' => 'Виписка з ЄДР ФОП',
                'file' => '/documents/legal/registration-extract.pdf',
                'size' => '1.2 MB',
                'updated' => '2025-07-23',
                'description' => 'Актуальна виписка з Єдиного державного реєстру'
            ],
            [
                'name' => 'Довідка про взяття на облік платника податків',
                'file' => '/documents/legal/tax-registration.pdf',
                'size' => '0.8 MB',
                'updated' => '2025-07-23',
                'description' => 'Документ про постановку на податковий облік'
            ],
            [
                'name' => 'Свідоцтво про державну реєстрацію',
                'file' => '/documents/legal/state-registration.pdf',
                'size' => '1.5 MB',
                'updated' => '2025-07-23',
                'description' => 'Первинний документ про реєстрацію ФОП'
            ]
        ]
    ],
    'licenses' => [
        'title' => 'Ліцензії та дозволи',
        'icon' => 'bi-award',
        'description' => 'Ліцензії на надання послуг електронних комунікацій та IT',
        'color' => 'success',
        'documents' => [
            [
                'name' => 'Ліцензія на надання послуг електронних комунікацій',
                'file' => '/documents/legal/telecom-license.pdf',
                'size' => '2.1 MB',
                'updated' => '2023-04-01',
                'description' => 'Ліцензія НКРЗІ на надання телекомунікаційних послуг'
            ],
            [
                'name' => 'Дозвіл на обробку персональних даних',
                'file' => '/documents/legal/data-processing-permit.pdf',
                'size' => '1.3 MB',
                'updated' => '2023-05-15',
                'description' => 'Дозвіл Уповноваженого з захисту персональних даних'
            ],
            [
                'name' => 'Сертифікат відповідності ISO 27001',
                'file' => '/documents/legal/iso27001-certificate.pdf',
                'size' => '1.8 MB',
                'updated' => '2023-12-01',
                'description' => 'Міжнародний сертифікат системи управління інформаційною безпекою'
            ]
        ]
    ],
    'financial' => [
        'title' => 'Фінансові документи',
        'icon' => 'bi-graph-up',
        'description' => 'Банківські реквізити, податкові звіти та фінансова звітність',
        'color' => 'warning',
        'documents' => [
            [
                'name' => 'Банківські реквізити',
                'file' => '/documents/legal/bank-details.pdf',
                'size' => '0.9 MB',
                'updated' => '2024-01-01',
                'description' => 'Повні банківські реквізити для розрахунків'
            ],
            [
                'name' => 'Довідка про відсутність заборгованості з податків',
                'file' => '/documents/legal/tax-clearance.pdf',
                'size' => '0.7 MB',
                'updated' => '2024-01-12',
                'description' => 'Актуальна довідка ДПС про відсутність податкових боргів'
            ],
            [
                'name' => 'Фінансовий звіт за 2025 рік',
                'file' => '/documents/legal/financial-report-2023.pdf',
                'size' => '3.2 MB',
                'updated' => '2024-01-31',
                'description' => 'Повний фінансовий звіт та аудиторський висновок'
            ]
        ]
    ],
    'insurance' => [
        'title' => 'Страхування та гарантії',
        'icon' => 'bi-shield-check',
        'description' => 'Страхові поліси та гарантійні зобов\'язання',
        'color' => 'info',
        'documents' => [
            [
                'name' => 'Поліс страхування професійної відповідальності',
                'file' => '/documents/legal/professional-insurance.pdf',
                'size' => '1.4 MB',
                'updated' => '2025-01-01',
                'description' => 'Страхування на суму 1 000 000 грн'
            ],
            [
                'name' => 'Поліс страхування даних клієнтів',
                'file' => '/documents/legal/data-insurance.pdf',
                'size' => '1.1 MB',
                'updated' => '2025-01-01',
                'description' => 'Кіберстрахування та захист персональних даних'
            ],
            [
                'name' => 'Гарантійний депозит',
                'file' => '/documents/legal/guarantee-deposit.pdf',
                'size' => '0.8 MB',
                'updated' => '2025-07-01',
                'description' => 'Документи про гарантійний депозит у банку'
            ]
        ]
    ]
];
?>

<!-- Додаткові стилі для цієї сторінки -->
<?php if (isset($additional_css)): ?>
    <?php foreach ($additional_css as $css_file): ?>
        <link rel="stylesheet" href="<?php echo $css_file; ?>">
    <?php endforeach; ?>
<?php endif; ?>

<!-- Legal Hero Section -->
<section class="legal-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <div class="legal-badge mb-3">
                        <i class="bi bi-shield-fill-check"></i>
                        <span>Повна правова прозорість</span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Юридична інформація
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Вся необхідна юридична інформація про StormHosting UA: 
                        реквізити ФОП, ліцензії, сертифікати та фінансові документи.
                    </p>
                    
                    <!-- Статус компанії -->
                    <div class="company-status">
                        <div class="status-item">
                            <div class="status-icon verified">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                            <div class="status-info">
                                <h6>Верифіковано</h6>
                                <p>Офіційно зареєстрований ФОП</p>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon licensed">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <div class="status-info">
                                <h6>Ліцензовано</h6>
                                <p>Ліцензія на телекомунікації</p>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon insured">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                            <div class="status-info">
                                <h6>Застраховано</h6>
                                <p>Професійна відповідальність</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="legal-dashboard">
                        <div class="dashboard-header">
                            <h6>Правовий статус</h6>
                            <div class="verification-badge">
                                <span class="verify-dot"></span>
                                Верифіковано
                            </div>
                        </div>
                        
                        <div class="company-info">
                            <div class="company-logo">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="company-details">
                                <h5><?php echo $legal_info['company']['short_name']; ?></h5>
                                <p>ЄДРПОУ: <?php echo $legal_info['company']['edrpou']; ?></p>
                                <p>Ліцензія: <?php echo $legal_info['company']['license_number']; ?></p>
                            </div>
                        </div>
                        
                        <div class="legal-stats">
                            <div class="stat-grid">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo date('Y') - 2020; ?>+</div>
                                    <div class="stat-label">Років роботи</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">12</div>
                                    <div class="stat-label">Документів</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">4</div>
                                    <div class="stat-label">Ліцензії</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">100%</div>
                                    <div class="stat-label">Легально</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Details Section -->
<section class="company-details-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="company-card">
                    <div class="company-header">
                        <h3>
                            <i class="bi bi-building me-2"></i>
                            Інформація про підприємство
                        </h3>
                        <div class="verification-status verified">
                            <i class="bi bi-patch-check-fill"></i>
                            Верифіковано
                        </div>
                    </div>
                    
                    <div class="company-info-grid">
                        <div class="info-row">
                            <div class="info-label">Повна назва:</div>
                            <div class="info-value"><?php echo $legal_info['company']['full_name']; ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Скорочена назва:</div>
                            <div class="info-value"><?php echo $legal_info['company']['short_name']; ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Дата реєстрації:</div>
                            <div class="info-value"><?php echo date('d.m.Y', strtotime($legal_info['company']['registration_date'])); ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">ЄДРПОУ:</div>
                            <div class="info-value">
                                <?php echo $legal_info['company']['edrpou']; ?>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?php echo $legal_info['company']['edrpou']; ?>')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">ІПН:</div>
                            <div class="info-value"><?php echo $legal_info['company']['ipn']; ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Юридична адреса:</div>
                            <div class="info-value"><?php echo $legal_info['company']['address']; ?>, <?php echo $legal_info['company']['postal_code']; ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Телефон:</div>
                            <div class="info-value">
                                <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', $legal_info['company']['phone']); ?>">
                                    <?php echo $legal_info['company']['phone']; ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value">
                                <a href="mailto:<?php echo $legal_info['company']['email']; ?>">
                                    <?php echo $legal_info['company']['email']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="quick-actions-card">
                    <h5>Швидкі дії</h5>
                    
                    <div class="action-buttons">
                        <button class="action-btn" onclick="downloadRequisites()">
                            <i class="bi bi-download"></i>
                            <span>Завантажити реквізити</span>
                        </button>
                        
                        <button class="action-btn" onclick="requestVerification()">
                            <i class="bi bi-patch-check"></i>
                            <span>Запросити верифікацію</span>
                        </button>
                        
                        <button class="action-btn" onclick="contactLegal()">
                            <i class="bi bi-chat-dots"></i>
                            <span>Зв'язатися з юристом</span>
                        </button>
                        
                        <button class="action-btn" onclick="downloadAllDocuments()">
                            <i class="bi bi-file-earmark-zip"></i>
                            <span>Скачати всі документи</span>
                        </button>
                    </div>
                    
                    <div class="trust-indicators">
                        <div class="trust-item">
                            <i class="bi bi-shield-check text-success"></i>
                            <span>SSL захищено</span>
                        </div>
                        <div class="trust-item">
                            <i class="bi bi-patch-check text-primary"></i>
                            <span>Державна реєстрація</span>
                        </div>
                        <div class="trust-item">
                            <i class="bi bi-award text-warning"></i>
                            <span>ISO 27001</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Banking Details Section -->
<section class="banking-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="banking-card">
                    <div class="banking-header">
                        <h4>
                            <i class="bi bi-bank me-2"></i>
                            Банківські реквізити
                        </h4>
                        <button class="btn btn-sm btn-outline-primary" onclick="copyBankDetails()">
                            <i class="bi bi-clipboard"></i>
                            Копіювати все
                        </button>
                    </div>
                    
                    <div class="bank-details">
                        <div class="bank-row">
                            <span class="bank-label">Банк:</span>
                            <span class="bank-value"><?php echo $legal_info['bank']['name']; ?></span>
                        </div>
                        
                        <div class="bank-row">
                            <span class="bank-label">IBAN:</span>
                            <span class="bank-value">
                                <?php echo $legal_info['bank']['account']; ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo $legal_info['bank']['account']; ?>')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </span>
                        </div>
                        
                        <div class="bank-row">
                            <span class="bank-label">МФО:</span>
                            <span class="bank-value">
                                <?php echo $legal_info['bank']['mfo']; ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo $legal_info['bank']['mfo']; ?>')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </span>
                        </div>
                        
                        <div class="bank-row">
                            <span class="bank-label">SWIFT:</span>
                            <span class="bank-value">
                                <?php echo $legal_info['bank']['swift']; ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo $legal_info['bank']['swift']; ?>')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </span>
                        </div>
                        
                        <div class="bank-row">
                            <span class="bank-label">Адреса банку:</span>
                            <span class="bank-value"><?php echo $legal_info['bank']['address']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="tax-card">
                    <div class="tax-header">
                        <h4>
                            <i class="bi bi-receipt me-2"></i>
                            Податкова інформація
                        </h4>
                        <div class="tax-status">
                            <span class="status-indicator good"></span>
                            Без заборгованості
                        </div>
                    </div>
                    
                    <div class="tax-details">
                        <div class="tax-row">
                            <span class="tax-label">Податкова:</span>
                            <span class="tax-value"><?php echo $legal_info['tax']['office']; ?></span>
                        </div>
                        
                        <div class="tax-row">
                            <span class="tax-label">Адреса:</span>
                            <span class="tax-value"><?php echo $legal_info['tax']['address']; ?></span>
                        </div>
                        
                        <div class="tax-row">
                            <span class="tax-label">Податковий номер:</span>
                            <span class="tax-value"><?php echo $legal_info['tax']['tax_number']; ?></span>
                        </div>
                        
                        <div class="tax-row">
                            <span class="tax-label">Платник ПДВ:</span>
                            <span class="tax-value">
                                <?php echo $legal_info['tax']['vat_payer'] ? 'Так' : 'Ні'; ?>
                                <span class="badge bg-<?php echo $legal_info['tax']['vat_payer'] ? 'success' : 'secondary'; ?> ms-2">
                                    <?php echo $legal_info['tax']['vat_payer'] ? 'ПДВ' : 'Без ПДВ'; ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="tax-row">
                            <span class="tax-label">Група платника:</span>
                            <span class="tax-value"><?php echo $legal_info['tax']['tax_group']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Documents Section -->
<section class="documents-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Юридичні документи</h2>
            <p class="lead text-muted">Всі необхідні ліцензії, сертифікати та правові документи</p>
        </div>
        
        <!-- Document Categories -->
        <div class="document-categories">
            <?php foreach ($document_categories as $key => $category): ?>
                <div class="category-block mb-5" data-category="<?php echo $key; ?>">
                    <div class="category-header">
                        <div class="category-title">
                            <i class="<?php echo $category['icon']; ?> text-<?php echo $category['color']; ?>"></i>
                            <h3><?php echo $category['title']; ?></h3>
                        </div>
                        <div class="category-actions">
                            <span class="document-count"><?php echo count($category['documents']); ?> документів</span>
                            <button class="btn btn-outline-<?php echo $category['color']; ?> btn-sm" onclick="downloadCategoryDocs('<?php echo $key; ?>')">
                                <i class="bi bi-download"></i>
                                Завантажити всі
                            </button>
                        </div>
                    </div>
                    
                    <p class="category-description"><?php echo $category['description']; ?></p>
                    
                    <div class="documents-list">
                        <?php foreach ($category['documents'] as $doc): ?>
                            <div class="document-item">
                                <div class="doc-icon">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                                </div>
                                
                                <div class="doc-content">
                                    <h5 class="doc-title"><?php echo $doc['name']; ?></h5>
                                    <p class="doc-description"><?php echo $doc['description']; ?></p>
                                    
                                    <div class="doc-meta">
                                        <span class="doc-size">
                                            <i class="bi bi-hdd"></i>
                                            <?php echo $doc['size']; ?>
                                        </span>
                                        <span class="doc-date">
                                            <i class="bi bi-calendar"></i>
                                            Оновлено: <?php echo date('d.m.Y', strtotime($doc['updated'])); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="doc-actions">
                                    <button class="btn btn-outline-primary btn-sm" onclick="previewDocument('<?php echo $doc['file']; ?>', '<?php echo $doc['name']; ?>')">
                                        <i class="bi bi-eye"></i>
                                        Переглянути
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="downloadDocument('<?php echo $doc['file']; ?>', '<?php echo $doc['name']; ?>')">
                                        <i class="bi bi-download"></i>
                                        Завантажити
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Document Viewer Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    <span id="documentTitle">Документ</span>
                </h5>
                <div class="modal-tools">
                    <button class="btn btn-sm btn-outline-secondary" id="zoomOut">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <span class="zoom-indicator">100%</span>
                    <button class="btn btn-sm btn-outline-secondary" id="zoomIn">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary" id="downloadCurrent">
                        <i class="bi bi-download"></i>
                        Завантажити
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="document-viewer">
                    <iframe id="documentFrame" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verification Section -->
<section class="verification-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-3">Перевірка юридичного статусу</h3>
                <p class="mb-0">
                    Ви можете самостійно перевірити наш правовий статус у державних реєстрах України 
                    або запросити додаткові документи для верифікації.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="https://usr.minjust.gov.ua/ua/freesearch" target="_blank" class="btn btn-light btn-lg me-2">
                    <i class="bi bi-search me-2"></i>
                    Перевірити в ЄДР
                </a>
                <a href="/pages/contacts.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-chat-dots me-2"></i>
                    Запросити документи
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Додаткові скрипти для цієї сторінки -->
<?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js_file): ?>
        <script src="<?php echo $js_file; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>