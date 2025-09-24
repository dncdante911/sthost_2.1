/**
 * JavaScript для страницы правил
 * /assets/js/pages/info-rules.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех компонентов
    initCategoryNavigation();
    initDocumentActions();
    initPDFViewer();
    initUploadSystem();
    initScrollAnimations();
    initAdminSection();
});

// Навигация по категориям
function initCategoryNavigation() {
    const categoryButtons = document.querySelectorAll('.category-btn');
    const categorySections = document.querySelectorAll('.category-section');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetCategory = this.dataset.category;
            
            // Обновляем активные кнопки
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Показываем/скрываем секции
            categorySections.forEach(section => {
                if (section.id === targetCategory) {
                    section.style.display = 'block';
                    // Анимация появления
                    section.style.opacity = '0';
                    section.style.transform = 'translateY(20px)';
                    
                    setTimeout(() => {
                        section.style.transition = 'all 0.5s ease';
                        section.style.opacity = '1';
                        section.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    section.style.display = 'none';
                }
            });
            
            // Плавная прокрутка к секции
            setTimeout(() => {
                document.getElementById(targetCategory).scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        });
    });
    
    // Быстрые ссылки
    const quickLinks = document.querySelectorAll('.quick-link');
    quickLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetButton = document.querySelector(`[data-category="${targetId}"]`);
            if (targetButton) {
                targetButton.click();
            }
        });
    });
}

// Действия с документами
function initDocumentActions() {
    // Глобальные функции для кнопок
    window.viewDocument = function(filePath) {
        const modal = new bootstrap.Modal(document.getElementById('pdfViewerModal'));
        const iframe = document.getElementById('pdfViewer');
        const fileName = document.getElementById('pdfFileName');
        
        // Устанавливаем источник PDF
        iframe.src = filePath;
        fileName.textContent = filePath.split('/').pop();
        
        // Показываем модальное окно
        modal.show();
        
        // Логирование просмотра
        logDocumentAction('view', filePath);
    };
    
    window.downloadDocument = function(filePath) {
        // Создаем временную ссылку для скачивания
        const link = document.createElement('a');
        link.href = filePath;
        link.download = filePath.split('/').pop();
        link.target = '_blank';
        
        // Добавляем в DOM, кликаем и удаляем
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Показываем уведомление
        showNotification('Документ завантажується...', 'info');
        
        // Логирование скачивания
        logDocumentAction('download', filePath);
    };
    
    window.downloadAllCategory = function(category) {
        const categoryElement = document.getElementById(category);
        const documents = categoryElement.querySelectorAll('.document-card');
        
        if (documents.length === 0) {
            showNotification('У цій категорії немає документів', 'warning');
            return;
        }
        
        // Показываем прогресс
        showLoadingOverlay('Підготовка документів для завантаження...');
        
        // Скачиваем каждый документ с задержкой
        documents.forEach((doc, index) => {
            const downloadBtn = doc.querySelector('[onclick*="downloadDocument"]');
            if (downloadBtn) {
                setTimeout(() => {
                    const filePath = downloadBtn.getAttribute('onclick').match(/'([^']+)'/)[1];
                    downloadDocument(filePath);
                    
                    // Убираем загрузку после последнего файла
                    if (index === documents.length - 1) {
                        setTimeout(() => {
                            hideLoadingOverlay();
                            showNotification(`Завантаження ${documents.length} документів розпочато`, 'success');
                        }, 1000);
                    }
                }, index * 1000); // Задержка между скачиваниями
            }
        });
        
        // Логирование
        logDocumentAction('download_category', category);
    };
}

// PDF Viewer
function initPDFViewer() {
    const zoomInBtn = document.getElementById('pdfZoomIn');
    const zoomOutBtn = document.getElementById('pdfZoomOut');
    const fullscreenBtn = document.getElementById('pdfFullscreen');
    const downloadBtn = document.getElementById('pdfDownload');
    const zoomLevel = document.querySelector('.zoom-level');
    
    let currentZoom = 100;
    let currentDocument = '';
    
    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function() {
            if (currentZoom < 200) {
                currentZoom += 25;
                updateZoom();
            }
        });
    }
    
    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function() {
            if (currentZoom > 50) {
                currentZoom -= 25;
                updateZoom();
            }
        });
    }
    
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            const iframe = document.getElementById('pdfViewer');
            if (iframe.requestFullscreen) {
                iframe.requestFullscreen();
            } else if (iframe.webkitRequestFullscreen) {
                iframe.webkitRequestFullscreen();
            } else if (iframe.msRequestFullscreen) {
                iframe.msRequestFullscreen();
            }
        });
    }
    
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            if (currentDocument) {
                downloadDocument(currentDocument);
            }
        });
    }
    
    function updateZoom() {
        const iframe = document.getElementById('pdfViewer');
        if (iframe && iframe.contentWindow) {
            try {
                iframe.style.transform = `scale(${currentZoom / 100})`;
                iframe.style.transformOrigin = 'top left';
                if (zoomLevel) {
                    zoomLevel.textContent = currentZoom + '%';
                }
            } catch (e) {
                console.log('Cannot control PDF zoom');
            }
        }
    }
    
    // Обновляем текущий документ при открытии модального окна
    const modal = document.getElementById('pdfViewerModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            const iframe = document.getElementById('pdfViewer');
            currentDocument = iframe.src;
            currentZoom = 100;
            if (zoomLevel) {
                zoomLevel.textContent = '100%';
            }
        });
    }
}

// Система загрузки документов
function initUploadSystem() {
    const uploadBtn = document.getElementById('uploadDocument');
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('documentFile');
    const progressContainer = document.querySelector('.upload-progress');
    const progressBar = document.querySelector('.progress-bar');
    
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            const formData = new FormData();
            const name = document.getElementById('documentName').value;
            const category = document.getElementById('documentCategory').value;
            const file = fileInput.files[0];
            
            // Валидация
            if (!name || !category || !file) {
                showNotification('Будь ласка, заповніть всі поля', 'error');
                return;
            }
            
            if (file.type !== 'application/pdf') {
                showNotification('Підтримуються лише PDF файли', 'error');
                return;
            }
            
            if (file.size > 10 * 1024 * 1024) { // 10MB
                showNotification('Розмір файлу не повинен перевищувати 10 МБ', 'error');
                return;
            }
            
            // Подготавливаем данные
            formData.append('name', name);
            formData.append('category', category);
            formData.append('file', file);
            
            // Показываем прогресс
            progressContainer.style.display = 'block';
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Завантаження...';
            
            // Симуляция загрузки (в реальном проекте - AJAX запрос)
            simulateUpload(formData);
        });
    }
    
    function simulateUpload(formData) {
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 100) progress = 100;
            
            progressBar.style.width = progress + '%';
            
            if (progress >= 100) {
                clearInterval(interval);
                
                setTimeout(() => {
                    // Закрываем модальное окно
                    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                    modal.hide();
                    
                    // Сбрасываем форму
                    uploadForm.reset();
                    progressContainer.style.display = 'none';
                    progressBar.style.width = '0%';
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="bi bi-upload"></i> Завантажити';
                    
                    // Показываем успешное сообщение
                    showNotification('Документ успішно завантажено!', 'success');
                    
                    // Обновляем список документов
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }, 500);
            }
        }, 100);
    }
}

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
    
    // Наблюдаем за карточками документов
    const cards = document.querySelectorAll('.document-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });
}

// Админ секция
function initAdminSection() {
    // Проверяем права администратора (заглушка)
    const isAdmin = checkAdminRights();
    
    if (isAdmin) {
        const adminSection = document.getElementById('adminSection');
        if (adminSection) {
            adminSection.style.display = 'block';
            loadAdminDocuments();
        }
    }
}

function checkAdminRights() {
    // В реальном проекте здесь будет проверка сессии/токена
    return window.location.search.includes('admin=true');
}

function loadAdminDocuments() {
    const tableBody = document.getElementById('adminDocumentsTable');
    if (!tableBody) return;
    
    // Заглушка данных (в реальном проекте - AJAX запрос)
    const documents = [
        {
            name: 'Договір публічної оферти',
            category: 'Загальні положення',
            size: '2.4 MB',
            updated: '2024-01-15',
            status: 'active'
        },
        {
            name: 'Правила використання хостингу',
            category: 'Правила хостингу',
            size: '2.1 MB',
            updated: '2024-01-12',
            status: 'active'
        }
        // ... больше документов
    ];
    
    tableBody.innerHTML = documents.map(doc => `
        <tr>
            <td>
                <strong>${doc.name}</strong>
                <br>
                <small class="text-muted">${doc.category}</small>
            </td>
            <td>${doc.category}</td>
            <td>${doc.size}</td>
            <td>${formatDate(doc.updated)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editDocument('${doc.name}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteDocument('${doc.name}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Утилиты
function showNotification(message, type = 'info') {
    // Удаляем существующие уведомления
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
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    const iconMap = {
        success: 'bi-check-circle',
        error: 'bi-exclamation-triangle',
        warning: 'bi-exclamation-triangle',
        info: 'bi-info-circle'
    };
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${iconMap[type]} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Анимация появления
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Автоматическое удаление
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 4000);
}

function showLoadingOverlay(message = 'Завантаження...') {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="text-center text-white">
            <div class="loading-spinner mb-3"></div>
            <div>${message}</div>
        </div>
    `;
    document.body.appendChild(overlay);
}

function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

function logDocumentAction(action, document) {
    // В реальном проекте отправляем данные на сервер
    console.log(`Document ${action}:`, document);
    
    // Можно добавить аналитику
    if (typeof gtag !== 'undefined') {
        gtag('event', 'document_action', {
            action: action,
            document: document
        });
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

// Глобальные функции для админки
window.editDocument = function(docName) {
    showNotification('Функція редагування в розробці', 'info');
};

window.deleteDocument = function(docName) {
    if (confirm(`Ви впевнені, що хочете видалити документ "${docName}"?`)) {
        showNotification('Документ видалено', 'success');
        setTimeout(() => {
            loadAdminDocuments();
        }, 1000);
    }
};

// Обработка ошибок
window.addEventListener('error', (e) => {
    console.error('Rules page error:', e.error);
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+D для скачивания текущего документа
    if (e.ctrlKey && e.key === 'd') {
        e.preventDefault();
        const modal = document.getElementById('pdfViewerModal');
        if (modal.classList.contains('show')) {
            const downloadBtn = document.getElementById('pdfDownload');
            if (downloadBtn) {
                downloadBtn.click();
            }
        }
    }
    
    // Escape для закрытия модального окна
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    }
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        viewDocument: window.viewDocument,
        downloadDocument: window.downloadDocument,
        showNotification
    };
}