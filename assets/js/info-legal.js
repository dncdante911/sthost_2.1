/**
 * JavaScript для страницы юридической информации
 * /assets/js/pages/info-legal.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех компонентов
    initDocumentViewer();
    initCopyFunctions();
    initQuickActions();
    initScrollAnimations();
    initStatCounters();
    initDocumentTracking();
});

// Просмотр документов
function initDocumentViewer() {
    let currentZoom = 100;
    let currentDocument = '';
    
    const modal = document.getElementById('documentModal');
    const zoomInBtn = document.getElementById('zoomIn');
    const zoomOutBtn = document.getElementById('zoomOut');
    const downloadBtn = document.getElementById('downloadCurrent');
    const zoomIndicator = document.querySelector('.zoom-indicator');
    
    // Функция для просмотра документа
    window.previewDocument = function(filePath, docName) {
        const modalInstance = new bootstrap.Modal(modal);
        const iframe = document.getElementById('documentFrame');
        const title = document.getElementById('documentTitle');
        
        // Устанавливаем данные документа
        currentDocument = filePath;
        currentZoom = 100;
        iframe.src = filePath;
        title.textContent = docName;
        
        // Показываем загрузку
        showDocumentLoading();
        
        // Показываем модальное окно
        modalInstance.show();
        
        // Обновляем зум индикатор
        updateZoomIndicator();
        
        // Логируем просмотр
        trackDocumentAction('preview', filePath, docName);
    };
    
    // Управление зумом
    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function() {
            if (currentZoom < 200) {
                currentZoom += 25;
                applyZoom();
            }
        });
    }
    
    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function() {
            if (currentZoom > 50) {
                currentZoom -= 25;
                applyZoom();
            }
        });
    }
    
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            if (currentDocument) {
                downloadDocument(currentDocument, document.getElementById('documentTitle').textContent);
            }
        });
    }
    
    function applyZoom() {
        const iframe = document.getElementById('documentFrame');
        if (iframe) {
            iframe.style.transform = `scale(${currentZoom / 100})`;
            iframe.style.transformOrigin = 'top left';
            updateZoomIndicator();
        }
    }
    
    function updateZoomIndicator() {
        if (zoomIndicator) {
            zoomIndicator.textContent = currentZoom + '%';
        }
    }
    
    function showDocumentLoading() {
        const iframe = document.getElementById('documentFrame');
        const loadingHtml = `
            <div class="document-loading">
                <div class="loading-spinner"></div>
                <span>Завантаження документа...</span>
            </div>
        `;
        
        // Создаем временный контейнер для отображения загрузки
        const loadingContainer = document.createElement('div');
        loadingContainer.innerHTML = loadingHtml;
        loadingContainer.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            z-index: 10;
        `;
        
        const viewer = document.querySelector('.document-viewer');
        viewer.appendChild(loadingContainer);
        
        // Убираем загрузку через 2 секунды
        setTimeout(() => {
            if (loadingContainer.parentNode) {
                loadingContainer.parentNode.removeChild(loadingContainer);
            }
        }, 2000);
    }
}

// Функции копирования
function initCopyFunctions() {
    // Глобальная функция копирования
    window.copyToClipboard = function(text, element) {
        navigator.clipboard.writeText(text).then(() => {
            // Показываем успешное копирование
            showCopySuccess(element);
            showNotification('Скопійовано в буфер обміну', 'success');
        }).catch(() => {
            // Fallback для старых браузеров
            fallbackCopyTextToClipboard(text);
            showCopySuccess(element);
            showNotification('Скопійовано в буфер обміну', 'success');
        });
    };
    
    // Копирование банковских реквизитов
    window.copyBankDetails = function() {
        const bankDetails = `
Банк: АТ "ПриватБанк"
IBAN: UA123456789012345678901234567
МФО: 305299
SWIFT: PBANUA2X
Адреса банку: м. Дніпро, вул. Набережна Перемоги, 50

Отримувач: ФОП Іванов І.І.
ЄДРПОУ: 1234567890
        `.trim();
        
        copyToClipboard(bankDetails);
        showNotification('Банківські реквізити скопійовано', 'success');
    };
    
    function showCopySuccess(element) {
        if (element && element.parentNode) {
            const button = element.parentNode.querySelector('.copy-btn') || element;
            button.classList.add('copy-success');
            
            setTimeout(() => {
                button.classList.remove('copy-success');
            }, 300);
        }
    }
    
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
        } catch (err) {
            console.error('Fallback: Could not copy text');
        }
        
        document.body.removeChild(textArea);
    }
}

// Быстрые действия
function initQuickActions() {
    // Скачивание реквизитов
    window.downloadRequisites = function() {
        const requisites = {
            company: "ФОП Іванов І.І.",
            edrpou: "1234567890",
            ipn: "1234567890",
            address: "м. Дніпро, вул. Центральна, 123, оф. 45",
            phone: "+380 (67) 123-45-67",
            email: "legal@stormhosting.ua",
            bank: "АТ \"ПриватБанк\"",
            iban: "UA123456789012345678901234567",
            mfo: "305299",
            swift: "PBANUA2X"
        };
        
        const content = `
РЕКВІЗИТИ ФОП
=============

Повна назва: ${requisites.company}
ЄДРПОУ: ${requisites.edrpou}
ІПН: ${requisites.ipn}
Адреса: ${requisites.address}
Телефон: ${requisites.phone}
Email: ${requisites.email}

БАНКІВСЬКІ РЕКВІЗИТИ
===================

Банк: ${requisites.bank}
IBAN: ${requisites.iban}
МФО: ${requisites.mfo}
SWIFT: ${requisites.swift}

Згенеровано: ${new Date().toLocaleString('uk-UA')}
        `;
        
        downloadTextFile('requisites_stormhosting.txt', content);
        trackDocumentAction('download', 'requisites', 'Реквізити компанії');
    };
    
    // Запрос верификации
    window.requestVerification = function() {
        showNotification('Запит на верифікацію відправлено', 'info');
        
        // Можно добавить модальное окно с формой
        const modal = createVerificationModal();
        modal.show();
    };
    
    // Связь с юристом
    window.contactLegal = function() {
        window.location.href = '/pages/contacts.php?department=legal';
    };
    
    // Скачивание всех документов
    window.downloadAllDocuments = function() {
        showLoadingOverlay('Підготовка архіву документів...');
        
        // Симуляция подготовки архива
        setTimeout(() => {
            hideLoadingOverlay();
            showNotification('Архів документів готовий до завантаження', 'success');
            
            // В реальном проекте здесь будет ссылка на ZIP файл
            const link = document.createElement('a');
            link.href = '/documents/legal/all_legal_documents.zip';
            link.download = 'stormhosting_legal_documents.zip';
            link.click();
            
            trackDocumentAction('download_all', 'archive', 'Всі юридичні документи');
        }, 3000);
    };
}

// Скачивание документов
window.downloadDocument = function(filePath, docName) {
    const link = document.createElement('a');
    link.href = filePath;
    link.download = docName + '.pdf';
    link.target = '_blank';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification(`Завантаження "${docName}" розпочато`, 'success');
    trackDocumentAction('download', filePath, docName);
};

// Скачивание документов категории
window.downloadCategoryDocs = function(category) {
    const categoryNames = {
        'registration': 'Реєстраційні документи',
        'licenses': 'Ліцензії та дозволи',
        'financial': 'Фінансові документи',
        'insurance': 'Страхування та гарантії'
    };
    
    showLoadingOverlay(`Підготовка документів категорії "${categoryNames[category]}"...`);
    
    setTimeout(() => {
        hideLoadingOverlay();
        showNotification(`Завантаження категорії "${categoryNames[category]}" розпочато`, 'success');
        
        // В реальном проекте здесь будет архив категории
        const link = document.createElement('a');
        link.href = `/documents/legal/${category}_documents.zip`;
        link.download = `${category}_documents.zip`;
        link.click();
        
        trackDocumentAction('download_category', category, categoryNames[category]);
    }, 2000);
};

// Анимации при скролле
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, observerOptions);
    
    // Наблюдаем за элементами
    const elements = document.querySelectorAll('.document-item, .category-block, .company-card, .banking-card, .tax-card');
    elements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'all 0.6s ease';
        observer.observe(element);
    });
}

// Анимация счетчиков
function initStatCounters() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    statNumbers.forEach(element => {
        observer.observe(element);
    });
    
    function animateCounter(element) {
        const text = element.textContent;
        const number = parseInt(text.replace(/\D/g, ''));
        
        if (isNaN(number)) return;
        
        const isPercentage = text.includes('%');
        const isPlus = text.includes('+');
        
        const duration = 2000;
        const start = performance.now();
        
        function animate(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.round(number * easeOutQuart);
            
            let displayText = current.toString();
            if (isPercentage) displayText += '%';
            if (isPlus) displayText += '+';
            
            element.textContent = displayText;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }
        
        requestAnimationFrame(animate);
    }
}

// Отслеживание действий с документами
function initDocumentTracking() {
    // Отслеживание времени на странице
    const startTime = Date.now();
    
    window.addEventListener('beforeunload', function() {
        const timeSpent = Math.round((Date.now() - startTime) / 1000);
        trackDocumentAction('page_time', 'legal_page', `${timeSpent} секунд`);
    });
}

function trackDocumentAction(action, document, name) {
    // Отправка данных в аналитику
    if (typeof gtag !== 'undefined') {
        gtag('event', 'legal_document_action', {
            action: action,
            document: document,
            name: name,
            timestamp: new Date().toISOString()
        });
    }
    
    // Логирование в консоль
    console.log(`Legal document action: ${action}`, { document, name });
    
    // Можно отправить на сервер для статистики
    if (action === 'download' || action === 'preview') {
        fetch('/api/track-document', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                document: document,
                name: name,
                timestamp: new Date().toISOString(),
                user_agent: navigator.userAgent
            })
        }).catch(err => console.log('Tracking failed:', err));
    }
}

// Утилиты
function downloadTextFile(filename, content) {
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
}

function createVerificationModal() {
    // Создаем модальное окно для верификации
    const modalHtml = `
        <div class="modal fade" id="verificationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-patch-check me-2"></i>
                            Запит на верифікацію
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="verificationForm">
                            <div class="mb-3">
                                <label class="form-label">Тип верифікації</label>
                                <select class="form-select" required>
                                    <option value="">Оберіть тип</option>
                                    <option value="company">Верифікація компанії</option>
                                    <option value="documents">Перевірка документів</option>
                                    <option value="financial">Фінансова перевірка</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Ваша компанія</label>
                                <input type="text" class="form-control" placeholder="Назва компанії" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email для зв'язку</label>
                                <input type="email" class="form-control" placeholder="your@email.com" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Коментар</label>
                                <textarea class="form-control" rows="3" placeholder="Додаткова інформація..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                        <button type="button" class="btn btn-primary" onclick="submitVerification()">
                            <i class="bi bi-send"></i>
                            Відправити запит
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Добавляем в DOM если еще нет
    if (!document.getElementById('verificationModal')) {
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    return new bootstrap.Modal(document.getElementById('verificationModal'));
}

function submitVerification() {
    const form = document.getElementById('verificationForm');
    const formData = new FormData(form);
    
    // Показываем загрузку
    showLoadingOverlay('Відправка запиту...');
    
    // Симуляция отправки
    setTimeout(() => {
        hideLoadingOverlay();
        
        // Закрываем модальное окно
        const modal = bootstrap.Modal.getInstance(document.getElementById('verificationModal'));
        modal.hide();
        
        // Показываем успех
        showNotification('Запит на верифікацію відправлено! Ми зв\'яжемося з вами протягом 24 годин.', 'success');
        
        // Сбрасываем форму
        form.reset();
        
        trackDocumentAction('verification_request', 'form_submit', 'Запрос верификации');
    }, 2000);
}

function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notif => notif.remove());
    
    const toast = document.createElement('div');
    toast.className = `notification-toast alert alert-${type === 'error' ? 'danger' : type} alert-dismissible`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 500px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    `;
    
    const iconMap = {
        success: 'bi-check-circle',
        error: 'bi-exclamation-triangle',
        warning: 'bi-exclamation-triangle',
        info: 'bi-info-circle'
    };
    
    toast.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="bi ${iconMap[type]} me-2 mt-1"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

function showLoadingOverlay(message = 'Завантаження...') {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    
    overlay.innerHTML = `
        <div class="text-center text-white">
            <div class="loading-spinner mb-3"></div>
            <div style="font-size: 16px; font-weight: 500;">${message}</div>
        </div>
    `;
    
    document.body.appendChild(overlay);
}

function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+D для скачивания текущего документа
    if (e.ctrlKey && e.key === 'd') {
        e.preventDefault();
        const modal = document.getElementById('documentModal');
        if (modal && modal.classList.contains('show')) {
            const downloadBtn = document.getElementById('downloadCurrent');
            if (downloadBtn) {
                downloadBtn.click();
            }
        }
    }
    
    // Ctrl+R для скачивания реквизитов
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        downloadRequisites();
    }
});

// Обработка ошибок
window.addEventListener('error', (e) => {
    console.error('Legal page error:', e.error);
    trackDocumentAction('error', 'javascript_error', e.error.message);
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        previewDocument: window.previewDocument,
        downloadDocument: window.downloadDocument,
        copyToClipboard: window.copyToClipboard,
        showNotification
    };
}