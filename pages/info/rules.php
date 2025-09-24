<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'rules';
$page_title = 'Правила надання послуг - StormHosting UA | Умови використання хостингу';
$meta_description = 'Правила надання послуг StormHosting UA: умови використання хостингу, VPS, доменів. Повний текст угоди та умов обслуговування.';
$meta_keywords = 'правила хостингу, умови користування, договір оферти, правила vps, правила доменів';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/info-rules.css'
];

$additional_js = [
    '/assets/js/info-rules.js'
];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Категорії документів
$document_categories = [
    'general' => [
        'title' => 'Загальні положення',
        'icon' => 'bi-file-text',
        'description' => 'Основні правила та умови надання послуг StormHosting UA',
        'documents' => [
            [
                'name' => 'Договір публічної оферти',
                'file' => '/documents/rules/contract-offer.pdf',
                'size' => '3.9 MB',
                'updated' => '2025-08-30'
            ],
            [
                'name' => 'Загальні умови обслуговування',
                'file' => '/documents/rules/general-terms.pdf',
                'size' => '0.2 MB',
                'updated' => '2025-08-25'
            ],
            [
                'name' => 'Політика конфіденційності',
                'file' => '/documents/rules/privacy-policy.pdf',
                'size' => '0.2 MB',
                'updated' => '2025-08-25'
            ]
        ]
    ],
    'hosting' => [
        'title' => 'Правила хостингу',
        'icon' => 'bi-server',
        'description' => 'Умови використання віртуального хостингу та хмарних послуг',
        'documents' => [
            [
                'name' => 'Правила використання хостингу',
                'file' => '/documents/rules/hosting-rules.pdf',
                'size' => '0.3 MB',
                'updated' => '2025-08-25'
            ],
            [
                'name' => 'Технічні вимоги та обмеження',
                'file' => '/documents/rules/hosting-limits.pdf',
                'size' => '0.4 MB',
                'updated' => '2025-08-25'
            ],
            [
                'name' => 'Політика використання ресурсів',
                'file' => '/documents/rules/resource-policy.pdf',
                'size' => '0.4 MB',
                'updated' => '2025-08-25'
            ]
        ]
    ],
    'vps' => [
        'title' => 'Правила VPS/VDS',
        'icon' => 'bi-hdd-stack',
        'description' => 'Умови використання віртуальних та виділених серверів',
        'documents' => [
            [
                'name' => 'Правила користування VPS',
                'file' => '/documents/rules/vps-rules.pdf',
                'size' => '0.45 MB',
                'updated' => '2025-08-25'
            ],
            [
                'name' => 'Технічні характеристики VDS',
                'file' => '/documents/rules/vds-specs.pdf',
                'size' => '4.2 MB',
                'updated' => '2025-08-25'
            ],
            [
                'name' => 'Політика безпеки серверів',
                'file' => '/documents/rules/server-security.pdf',
                'size' => '0.5 MB',
                'updated' => '2025-08-25'
            ]
        ]
    ],
    'domains' => [
        'title' => 'Правила доменів',
        'icon' => 'bi-globe',
        'description' => 'Умови реєстрації та використання доменних імен',
        'documents' => [
            [
                'name' => 'Правила реєстрації доменів',
                'file' => '/documents/rules/domain-registration.pdf',
                'size' => '0.4 MB',
                'updated' => '2025-08-25'
            ],
            [
                'name' => 'Політика трансферу доменів',
                'file' => '/documents/rules/domain-transfer.pdf',
                'size' => '0.4 MB',
                'updated' => '2025-81-25'
            ],
            [
                'name' => 'Правила використання DNS',
                'file' => '/documents/rules/dns-rules.pdf',
                'size' => '0.3 MB',
                'updated' => '2025-08-25'
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

<!-- Додати стилі для секції .UA доменів -->
<style>
.ua-info-card {
    background: white;
    border-radius: 12px;
    padding: 30px 25px;
    text-align: center;
    height: 100%;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.ua-info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.ua-info-card .card-icon {
    font-size: 3rem;
    margin-bottom: 20px;
}

.ua-info-card h5 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
}

.ua-info-card p {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 0;
}

.ua-regulations {
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.regulations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.regulation-item {
    display: flex;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: background-color 0.3s ease;
}

.regulation-item:hover {
    background: #e9ecef;
}

.reg-icon {
    font-size: 1.5rem;
    color: #667eea;
    margin-right: 15px;
    flex-shrink: 0;
}

.reg-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}

.reg-content a {
    color: #667eea;
    text-decoration: none;
    transition: color 0.3s ease;
}

.reg-content a:hover {
    color: #764ba2;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .ua-regulations {
        padding: 25px;
    }
    
    .regulations-grid {
        grid-template-columns: 1fr;
    }
    
    .regulation-item {
        flex-direction: column;
        text-align: center;
        padding: 15px;
    }
    
    .reg-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>

<!-- Rules Hero Section -->
<section class="rules-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <div class="rules-badge mb-3">
                        <i class="bi bi-shield-check"></i>
                        <span>Прозорі умови обслуговування</span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Правила надання послуг
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Ознайомтеся з повними правилами та умовами використання послуг StormHosting UA. 
                        Всі документи доступні для завантаження у форматі PDF.
                    </p>
                    
                    <!-- Швидкі посилання -->
                    <div class="quick-links">
                        <a href="#general" class="quick-link">
                            <i class="bi bi-file-text"></i>
                            <span>Загальні положення</span>
                        </a>
                        <a href="#hosting" class="quick-link">
                            <i class="bi bi-server"></i>
                            <span>Правила хостингу</span>
                        </a>
                        <a href="#vps" class="quick-link">
                            <i class="bi bi-hdd-stack"></i>
                            <span>VPS/VDS</span>
                        </a>
                        <a href="#domains" class="quick-link">
                            <i class="bi bi-globe"></i>
                            <span>Домени</span>
                        </a>
                        <a href="#ua-domains" class="quick-link">
                            <i class="bi bi-flag"></i>
                            <span>Домени .UA</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="documents-preview">
                        <div class="preview-header">
                            <h6>Документообіг</h6>
                            <div class="doc-status">
                                <span class="status-dot"></span>
                                Актуальна версія
                            </div>
                        </div>
                        
                        <div class="doc-stats">
                            <div class="stat-item">
                                <div class="stat-number">12</div>
                                <div class="stat-label">Документів</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">4</div>
                                <div class="stat-label">Категорій</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">серпень 2025</div>
                                <div class="stat-label">Останнє оновлення</div>
                            </div>
                        </div>
                        
                        <div class="recent-updates">
                            <div class="update-title">Останні оновлення</div>
                            <div class="update-item">
                                <span class="update-date">30.08.2025</span>
                                <span class="update-text">Оновлено договір публічної оферти</span>
                                <span class="update-type">PDF</span>
                            </div>
                            <div class="update-item">
                                <span class="update-date">25.08.2025</span>
                                <span class="update-text">Нові правила хостингу</span>
                                <span class="update-type">PDF</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Document Categories Section -->
<section class="categories-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Категорії документів</h2>
            <p class="lead text-muted">Оберіть категорію для перегляду відповідних правил та умов</p>
        </div>
        
        <!-- Category Navigation -->
        <div class="category-nav mb-4">
            <?php foreach ($document_categories as $key => $category): ?>
                <button class="category-btn <?php echo $key === 'general' ? 'active' : ''; ?>" 
                        data-category="<?php echo $key; ?>">
                    <i class="<?php echo $category['icon']; ?>"></i>
                    <span><?php echo $category['title']; ?></span>
                </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Document Sections -->
        <?php foreach ($document_categories as $key => $category): ?>
            <div class="category-section" id="<?php echo $key; ?>" 
                 style="<?php echo $key !== 'general' ? 'display: none;' : ''; ?>">
                <div class="category-header">
                    <div class="category-info">
                        <h3>
                            <i class="<?php echo $category['icon']; ?>"></i>
                            <?php echo $category['title']; ?>
                        </h3>
                        <p><?php echo $category['description']; ?></p>
                    </div>
                    <div class="category-actions">
                        <button class="btn btn-outline-primary" onclick="downloadAllCategory('<?php echo $key; ?>')">
                            <i class="bi bi-download"></i>
                            Завантажити всі
                        </button>
                    </div>
                </div>
                
                <div class="documents-grid">
                    <?php foreach ($category['documents'] as $document): ?>
                        <div class="document-card">
                            <div class="doc-icon">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <div class="doc-info">
                                <h5><?php echo $document['name']; ?></h5>
                                <div class="doc-meta">
                                    <span class="doc-size">
                                        <i class="bi bi-hdd"></i>
                                        <?php echo $document['size']; ?>
                                    </span>
                                    <span class="doc-date">
                                        <i class="bi bi-calendar"></i>
                                        <?php echo date('d.m.Y', strtotime($document['updated'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <button class="btn btn-primary btn-sm" onclick="viewDocument('<?php echo $document['file']; ?>')">
                                    <i class="bi bi-eye"></i>
                                    Переглянути
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="downloadDocument('<?php echo $document['file']; ?>')">
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
</section>

<!-- Секція для доменів .UA -->
<section class="ua-domains-info py-5 bg-light" id="ua-domains">
    <div class="container">
        <div class="text-center mb-5">
            <div class="category-header">
                <h3>
                    <i class="bi bi-flag text-primary me-2"></i>
                    Особливості роботи з доменами .UA
                </h3>
                <p class="lead text-muted">Важлива інформація для реєстрації та використання доменів .UA</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="ua-info-card">
                    <div class="card-icon">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                    </div>
                    <h5>Особливі умови</h5>
                    <p>Реєстрація доменів .UA здійснюється <strong>за наявності відповідної торговельної марки</strong> або згідно з вимогами Регламенту домену .UA.</p>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="ua-info-card">
                    <div class="card-icon">
                        <i class="bi bi-shield-slash text-info"></i>
                    </div>
                    <h5>Відповідальність</h5>
                    <p><strong>Адміністратор домену .UA та Оператор Реєстру не несуть відповідальності</strong> щодо наслідків використання доменних імен та порушення прав третіх осіб.</p>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="ua-info-card">
                    <div class="card-icon">
                        <i class="bi bi-balance-scale text-success"></i>
                    </div>
                    <h5>Вирішення спорів</h5>
                    <p>Спори щодо доменів .UA вирішуються згідно з <a href="https://hostmaster.ua/policy/ua-drp/" target="_blank">.UA Політикою вирішення спорів</a> та міжнародними процедурами WIPO.</p>
                </div>
            </div>
        </div>
        
        <div class="ua-regulations mt-5">
            <h4 class="mb-4">
                <i class="bi bi-file-text me-2"></i>
                Регламентні документи
            </h4>
            
            <div class="regulations-grid">
                <div class="regulation-item">
                    <div class="reg-icon">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <div class="reg-content">
                        <h6><a href="https://hostmaster.ua/policy/2ld.ua" target="_blank">Регламент публічних доменів</a></h6>
                        <small class="text-muted">Основні правила реєстрації публічних доменів</small>
                    </div>
                </div>
                
                <div class="regulation-item">
                    <div class="reg-icon">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <div class="reg-content">
                        <h6><a href="https://hostmaster.ua/policy/ua" target="_blank">Регламент домену .UA</a></h6>
                        <small class="text-muted">Загальні положення та вимоги для .UA</small>
                    </div>
                </div>
                
                <div class="regulation-item">
                    <div class="reg-icon">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <div class="reg-content">
                        <h6><a href="https://hostmaster.ua/policy/ua-drp/" target="_blank">.UA Політика вирішення спорів</a></h6>
                        <small class="text-muted">Процедури розгляду спорів щодо доменів</small>
                    </div>
                </div>
                
                <div class="regulation-item">
                    <div class="reg-icon">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <div class="reg-content">
                        <h6><a href="https://hostmaster.ua/services/" target="_blank">Порядок супроводу доменів</a></h6>
                        <small class="text-muted">WHOIS, RDAP сервіси та технічний супровід</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-warning mt-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Увага!</strong> При реєстрації домену .UA ви автоматично погоджуєтеся з усіма зазначеними регламентами та політиками. 
            Детальніше про вимоги до документів та процедуру реєстрації дізнавайтеся у нашої служби підтримки.
        </div>
    </div>
</section>

<!-- PDF Viewer Modal -->
<div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-labelledby="pdfViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfViewerModalLabel">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    Перегляд документа
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="pdf-viewer-container">
                    <div class="pdf-toolbar">
                        <div class="pdf-controls">
                            <button class="btn btn-sm btn-outline-secondary" id="pdfZoomOut">
                                <i class="bi bi-zoom-out"></i>
                            </button>
                            <span class="zoom-level">100%</span>
                            <button class="btn btn-sm btn-outline-secondary" id="pdfZoomIn">
                                <i class="bi bi-zoom-in"></i>
                            </button>
                        </div>
                        <div class="pdf-info">
                            <span id="pdfFileName">document.pdf</span>
                        </div>
                        <div class="pdf-actions">
                            <button class="btn btn-sm btn-outline-primary" id="pdfFullscreen">
                                <i class="bi bi-fullscreen"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" id="pdfDownload">
                                <i class="bi bi-download"></i>
                                Завантажити
                            </button>
                        </div>
                    </div>
                    <iframe id="pdfViewer" src="" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Management Section (Admin) -->
<section class="management-section py-5 bg-light" style="display: none;" id="adminSection">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h3>Управління документами</h3>
                <p class="text-muted">Завантажте нові документи або оновіть існуючі</p>
            </div>
            <div class="col-lg-4 text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-upload"></i>
                    Завантажити документ
                </button>
            </div>
        </div>
        
        <div class="admin-documents-table mt-4">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Документ</th>
                            <th>Категорія</th>
                            <th>Розмір</th>
                            <th>Останнє оновлення</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody id="adminDocumentsTable">
                        <!-- Динамічно заповнюється JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="bi bi-upload me-2"></i>
                    Завантажити документ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="documentName" class="form-label">Назва документа</label>
                        <input type="text" class="form-control" id="documentName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="documentCategory" class="form-label">Категорія</label>
                        <select class="form-select" id="documentCategory" required>
                            <option value="">Оберіть категорію</option>
                            <option value="general">Загальні положення</option>
                            <option value="hosting">Правила хостингу</option>
                            <option value="vps">Правила VPS/VDS</option>
                            <option value="domains">Правила доменів</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="documentFile" class="form-label">Файл документа</label>
                        <input type="file" class="form-control" id="documentFile" accept=".pdf" required>
                        <div class="form-text">Підтримуються лише PDF файли (макс. 10 МБ)</div>
                    </div>
                    
                    <div class="upload-progress" style="display: none;">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" id="uploadDocument">
                    <i class="bi bi-upload"></i>
                    Завантажити
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Contact CTA Section -->
<section class="contact-cta py-5 bg-primary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="text-white mb-3">Маєте питання щодо правил?</h3>
                <p class="text-white-50 mb-0">
                    Наша юридична служба готова надати роз'яснення щодо будь-яких аспектів 
                    наших правил та умов обслуговування.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="/pages/contacts.php" class="btn btn-light btn-lg">
                    <i class="bi bi-chat-dots me-2"></i>
                    Зв'язатися з юристом
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