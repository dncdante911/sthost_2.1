<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Файловий менеджер чата - StormHosting</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .header .breadcrumb {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .header .breadcrumb a {
            color: white;
            text-decoration: none;
        }

        .main-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .toolbar {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .upload-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .upload-btn {
            position: relative;
            overflow: hidden;
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .upload-btn:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a42a0);
            transform: translateY(-1px);
        }

        .upload-input {
            position: absolute;
            left: -9999px;
        }

        .drag-drop-area {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #f9fafb;
            margin-bottom: 2rem;
            transition: all 0.3s;
        }

        .drag-drop-area.dragover {
            border-color: #667eea;
            background: #eff6ff;
        }

        .drag-drop-text {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .drag-drop-subtext {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .file-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
            position: relative;
        }

        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .file-preview {
            height: 150px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #64748b;
            position: relative;
        }

        .file-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-info {
            padding: 1rem;
        }

        .file-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1e293b;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .file-actions {
            display: flex;
            gap: 0.5rem;
        }

        .file-action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s;
            flex: 1;
        }

        .btn-view {
            background: #eff6ff;
            color: #2563eb;
        }

        .btn-view:hover {
            background: #dbeafe;
        }

        .btn-copy {
            background: #f0fdf4;
            color: #16a34a;
        }

        .btn-copy:hover {
            background: #dcfce7;
        }

        .btn-delete {
            background: #fef2f2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #fee2e2;
        }

        .search-filter {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-input {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            width: 250px;
        }

        .filter-select {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }

        .stats-bar {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #64748b;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: #64748b;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 90vw;
            max-height: 90vh;
            overflow: auto;
            position: relative;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: #f3f4f6;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s;
        }

        .upload-queue {
            margin-top: 1rem;
        }

        .upload-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .upload-item.success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
        }

        .upload-item.error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
        }

        @media (max-width: 768px) {
            .files-grid {
                grid-template-columns: 1fr;
            }
            
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-filter {
                flex-direction: column;
            }
            
            .search-input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📁 Файловий менеджер чата</h1>
        <div class="breadcrumb">
            <a href="/admin/support-panel.php">← Назад до панелі операторів</a>
        </div>
    </div>

    <div class="main-container">
        <!-- Панель инструментов -->
        <div class="toolbar">
            <div class="upload-area">
                <label class="upload-btn">
                    📤 Завантажити файли
                    <input type="file" class="upload-input" id="fileInput" multiple 
                           accept="image/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                </label>
                <span style="color: #6b7280; font-size: 0.9rem;">
                    Максимум 10MB на файл
                </span>
            </div>
            
            <div class="search-filter">
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="🔍 Пошук файлів...">
                <select class="filter-select" id="typeFilter">
                    <option value="all">Всі типи</option>
                    <option value="image">Зображення</option>
                    <option value="document">Документи</option>
                    <option value="archive">Архіви</option>
                </select>
            </div>
        </div>

        <!-- Drag & Drop область -->
        <div class="drag-drop-area" id="dragDropArea">
            <div class="drag-drop-text">📁 Перетягніть файли сюди для завантаження</div>
            <div class="drag-drop-subtext">або натисніть кнопку "Завантажити файли" вище</div>
        </div>

        <!-- Очередь загрузки -->
        <div class="upload-queue" id="uploadQueue" style="display: none;"></div>

        <!-- Сетка файлов -->
        <div class="files-grid" id="filesGrid">
            <div class="loading">
                ⏳ Завантаження файлів...
            </div>
        </div>

        <!-- Статистика -->
        <div class="stats-bar">
            <div>
                Всього файлів: <strong id="totalFiles">0</strong>
            </div>
            <div>
                Загальний розмір: <strong id="totalSize">0 MB</strong>
            </div>
            <div>
                Вільно місця: <strong id="freeSpace">∞</strong>
            </div>
        </div>
    </div>

    <!-- Модальное окно просмотра -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Перегляд файлу</h3>
                <button class="modal-close" onclick="FileManager.closeModal()">×</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Содержимое файла -->
            </div>
        </div>
    </div>

    <script>
        class ChatFileManager {
            constructor() {
                this.files = [];
                this.maxFileSize = 10 * 1024 * 1024; // 10MB
                this.allowedTypes = [
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'application/pdf', 'text/plain',
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/zip', 'application/x-rar-compressed'
                ];
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
                this.loadFiles();
            }
            
            setupEventListeners() {
                // Загрузка файлов
                document.getElementById('fileInput').addEventListener('change', (e) => {
                    this.handleFileSelect(e.target.files);
                });
                
                // Drag & Drop
                const dragArea = document.getElementById('dragDropArea');
                
                dragArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dragArea.classList.add('dragover');
                });
                
                dragArea.addEventListener('dragleave', () => {
                    dragArea.classList.remove('dragover');
                });
                
                dragArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dragArea.classList.remove('dragover');
                    this.handleFileSelect(e.dataTransfer.files);
                });
                
                // Поиск и фильтрация
                document.getElementById('searchInput').addEventListener('input', (e) => {
                    this.filterFiles();
                });
                
                document.getElementById('typeFilter').addEventListener('change', (e) => {
                    this.filterFiles();
                });
                
                // Клик по drag area
                dragArea.addEventListener('click', () => {
                    document.getElementById('fileInput').click();
                });
            }
            
            async loadFiles() {
                try {
                    const response = await fetch('/api/chat/files.php?action=list');
                    const result = await response.json();
                    
                    if (result.success) {
                        this.files = result.data.files;
                        this.updateFilesDisplay();
                        this.updateStats(result.data.stats);
                    } else {
                        console.error('Load files error:', result.message);
                        this.showError('Помилка завантаження файлів');
                    }
                } catch (error) {
                    console.error('Load files error:', error);
                    this.showError('Помилка підключення до сервера');
                }
            }
            
            handleFileSelect(files) {
                const validFiles = [];
                const errors = [];
                
                Array.from(files).forEach(file => {
                    if (file.size > this.maxFileSize) {
                        errors.push(`${file.name}: файл занадто великий (максимум 10MB)`);
                        return;
                    }
                    
                    if (!this.allowedTypes.includes(file.type)) {
                        errors.push(`${file.name}: непідтримуваний тип файлу`);
                        return;
                    }
                    
                    validFiles.push(file);
                });
                
                if (errors.length > 0) {
                    alert('Помилки:\n' + errors.join('\n'));
                }
                
                if (validFiles.length > 0) {
                    this.uploadFiles(validFiles);
                }
            }
            
            async uploadFiles(files) {
                const queueContainer = document.getElementById('uploadQueue');
                queueContainer.style.display = 'block';
                queueContainer.innerHTML = '';
                
                for (const file of files) {
                    await this.uploadSingleFile(file, queueContainer);
                }
                
                // Скрываем очередь через 3 секунды после завершения
                setTimeout(() => {
                    queueContainer.style.display = 'none';
                    this.loadFiles(); // Перезагружаем список файлов
                }, 3000);
            }
            
            async uploadSingleFile(file, container) {
                const uploadItem = document.createElement('div');
                uploadItem.className = 'upload-item';
                uploadItem.innerHTML = `
                    <div>
                        <div style="font-weight: 500;">${file.name}</div>
                        <div style="font-size: 0.8rem; color: #6b7280;">${this.formatFileSize(file.size)}</div>
                    </div>
                    <div style="text-align: right;">
                        <div class="upload-status">Завантаження...</div>
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                    </div>
                `;
                
                container.appendChild(uploadItem);
                
                const formData = new FormData();
                formData.append('file', file);
                formData.append('action', 'upload');
                
                try {
                    const xhr = new XMLHttpRequest();
                    
                    // Отслеживание прогресса
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const progress = (e.loaded / e.total) * 100;
                            const progressFill = uploadItem.querySelector('.progress-fill');
                            progressFill.style.width = progress + '%';
                        }
                    });
                    
                    // Обработка завершения
                    xhr.addEventListener('load', () => {
                        const result = JSON.parse(xhr.responseText);
                        const statusEl = uploadItem.querySelector('.upload-status');
                        
                        if (result.success) {
                            uploadItem.classList.add('success');
                            statusEl.textContent = '✅ Завантажено';
                        } else {
                            uploadItem.classList.add('error');
                            statusEl.textContent = '❌ ' + result.message;
                        }
                    });
                    
                    xhr.addEventListener('error', () => {
                        uploadItem.classList.add('error');
                        uploadItem.querySelector('.upload-status').textContent = '❌ Помилка';
                    });
                    
                    xhr.open('POST', '/api/chat/files.php');
                    xhr.send(formData);
                    
                    // Ждем завершения загрузки
                    await new Promise((resolve) => {
                        xhr.addEventListener('loadend', resolve);
                    });
                    
                } catch (error) {
                    console.error('Upload error:', error);
                    uploadItem.classList.add('error');
                    uploadItem.querySelector('.upload-status').textContent = '❌ Помилка';
                }
            }
            
            updateFilesDisplay() {
                const container = document.getElementById('filesGrid');
                
                if (this.files.length === 0) {
                    container.innerHTML = `
                        <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #6b7280;">
                            📁 Файли не знайдені<br>
                            <small>Завантажте перші файли для чату</small>
                        </div>
                    `;
                    return;
                }
                
                container.innerHTML = this.files.map(file => `
                    <div class="file-card" data-file-id="${file.id}">
                        <div class="file-preview">
                            ${this.getFilePreview(file)}
                        </div>
                        <div class="file-info">
                            <div class="file-name" title="${file.original_name}">
                                ${file.original_name}
                            </div>
                            <div class="file-details">
                                <span>${this.formatFileSize(file.file_size)}</span>
                                <span>${this.formatDate(file.created_at)}</span>
                            </div>
                            <div class="file-actions">
                                <button class="file-action-btn btn-view" onclick="FileManager.viewFile('${file.id}')">
                                    👁 Переглянути
                                </button>
                                <button class="file-action-btn btn-copy" onclick="FileManager.copyFileUrl('${file.file_url}')">
                                    📋 Копіювати URL
                                </button>
                                <button class="file-action-btn btn-delete" onclick="FileManager.deleteFile('${file.id}')">
                                    🗑 Видалити
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
            
            getFilePreview(file) {
                if (file.file_type.startsWith('image/')) {
                    return `<img src="${file.file_url}" alt="${file.original_name}">`;
                }
                
                const iconMap = {
                    'application/pdf': '📄',
                    'text/plain': '📝',
                    'application/msword': '📘',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '📘',
                    'application/zip': '📦',
                    'application/x-rar-compressed': '📦'
                };
                
                return iconMap[file.file_type] || '📄';
            }
            
            filterFiles() {
                const search = document.getElementById('searchInput').value.toLowerCase();
                const typeFilter = document.getElementById('typeFilter').value;
                
                let filteredFiles = this.files;
                
                // Фильтр по поиску
                if (search) {
                    filteredFiles = filteredFiles.filter(file => 
                        file.original_name.toLowerCase().includes(search)
                    );
                }
                
                // Фильтр по типу
                if (typeFilter !== 'all') {
                    filteredFiles = filteredFiles.filter(file => {
                        switch (typeFilter) {
                            case 'image':
                                return file.file_type.startsWith('image/');
                            case 'document':
                                return file.file_type.includes('pdf') || 
                                       file.file_type.includes('document') || 
                                       file.file_type.includes('text');
                            case 'archive':
                                return file.file_type.includes('zip') || 
                                       file.file_type.includes('rar');
                            default:
                                return true;
                        }
                    });
                }
                
                // Временно сохраняем отфильтрованные файлы
                const originalFiles = this.files;
                this.files = filteredFiles;
                this.updateFilesDisplay();
                this.files = originalFiles;
            }
            
            async viewFile(fileId) {
                try {
                    const file = this.files.find(f => f.id === fileId);
                    if (!file) return;
                    
                    const modal = document.getElementById('viewModal');
                    const modalTitle = document.getElementById('modalTitle');
                    const modalBody = document.getElementById('modalBody');
                    
                    modalTitle.textContent = file.original_name;
                    
                    if (file.file_type.startsWith('image/')) {
                        modalBody.innerHTML = `
                            <img src="${file.file_url}" alt="${file.original_name}" 
                                 style="max-width: 100%; height: auto; border-radius: 8px;">
                            <div style="margin-top: 1rem; padding: 1rem; background: #f9fafb; border-radius: 8px;">
                                <strong>Розмір:</strong> ${this.formatFileSize(file.file_size)}<br>
                                <strong>Тип:</strong> ${file.file_type}<br>
                                <strong>Завантажено:</strong> ${this.formatDate(file.created_at)}<br>
                                <strong>URL:</strong> <a href="${file.file_url}" target="_blank">${file.file_url}</a>
                            </div>
                        `;
                    } else if (file.file_type === 'text/plain') {
                        // Загружаем содержимое текстового файла
                        const response = await fetch(file.file_url);
                        const content = await response.text();
                        modalBody.innerHTML = `
                            <pre style="background: #f9fafb; padding: 1rem; border-radius: 8px; white-space: pre-wrap; max-height: 400px; overflow-y: auto;">${content}</pre>
                            <div style="margin-top: 1rem; padding: 1rem; background: #f9fafb; border-radius: 8px;">
                                <strong>Розмір:</strong> ${this.formatFileSize(file.file_size)}<br>
                                <strong>Завантажено:</strong> ${this.formatDate(file.created_at)}
                            </div>
                        `;
                    } else {
                        modalBody.innerHTML = `
                            <div style="text-align: center; padding: 2rem;">
                                <div style="font-size: 4rem; margin-bottom: 1rem;">${this.getFilePreview(file)}</div>
                                <h3>${file.original_name}</h3>
                                <div style="margin: 1rem 0; color: #6b7280;">
                                    ${this.formatFileSize(file.file_size)} • ${file.file_type}
                                </div>
                                <a href="${file.file_url}" target="_blank" 
                                   style="display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px;">
                                    📥 Завантажити файл
                                </a>
                            </div>
                        `;
                    }
                    
                    modal.classList.add('active');
                } catch (error) {
                    console.error('View file error:', error);
                    alert('Помилка перегляду файлу');
                }
            }
            
            async copyFileUrl(url) {
                try {
                    await navigator.clipboard.writeText(url);
                    this.showSuccess('URL скопійовано в буфер обміну');
                } catch (error) {
                    // Fallback для старых браузеров
                    const textArea = document.createElement('textarea');
                    textArea.value = url;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    this.showSuccess('URL скопійовано');
                }
            }
            
            async deleteFile(fileId) {
                if (!confirm('Ви впевнені, що хочете видалити цей файл?')) {
                    return;
                }
                
                try {
                    const response = await fetch('/api/chat/files.php', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', file_id: fileId })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showSuccess('Файл видалено');
                        this.loadFiles();
                    } else {
                        this.showError('Помилка видалення: ' + result.message);
                    }
                } catch (error) {
                    console.error('Delete file error:', error);
                    this.showError('Помилка видалення файлу');
                }
            }
            
            closeModal() {
                document.getElementById('viewModal').classList.remove('active');
            }
            
            updateStats(stats) {
                document.getElementById('totalFiles').textContent = stats.total_files || 0;
                document.getElementById('totalSize').textContent = this.formatFileSize(stats.total_size || 0);
                document.getElementById('freeSpace').textContent = '∞'; // Можно добавить реальную проверку
            }
            
            formatFileSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('uk-UA', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }
            
            showSuccess(message) {
                this.showNotification(message, 'success');
            }
            
            showError(message) {
                this.showNotification(message, 'error');
            }
            
            showNotification(message, type) {
                const notification = document.createElement('div');
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 1rem 1.5rem;
                    border-radius: 8px;
                    color: white;
                    font-weight: 500;
                    z-index: 10000;
                    animation: slideIn 0.3s ease;
                    background: ${type === 'success' ? '#22c55e' : '#ef4444'};
                `;
                
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        }

        // Глобальные функции
        const FileManager = new ChatFileManager();

        // Закрытие модалки по клику вне её
        document.getElementById('viewModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                FileManager.closeModal();
            }
        });

        // CSS анимации
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>