<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель техпідтримки - StormHosting</title>
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
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header .status {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        .main-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            height: calc(100vh - 80px);
        }

        .sidebar {
            background: white;
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
        }

        .login-section {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .login-section.logged-in {
            background: #f0fdf4;
            border-color: #22c55e;
        }

        .login-form input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .login-form button {
            width: 100%;
            padding: 0.75rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }

        .login-form button:hover {
            background: #5a6fd8;
        }

        .operator-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .operator-status {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
        }

        .sessions-list {
            flex: 1;
            overflow-y: auto;
        }

        .session-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .session-item:hover {
            background: #f8fafc;
        }

        .session-item.active {
            background: #eff6ff;
            border-right: 3px solid #3b82f6;
        }

        .session-item.urgent {
            border-left: 4px solid #ef4444;
        }

        .session-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .session-status {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .session-status.waiting {
            background: #fef3c7;
            color: #92400e;
        }

        .session-status.active {
            background: #dcfce7;
            color: #166534;
        }

        .chat-area {
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .chat-messages {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            background: #f8fafc;
        }

        .message {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .message.operator {
            flex-direction: row-reverse;
        }

        .message-content {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message.user .message-content {
            background: #eff6ff;
            color: #1e40af;
        }

        .message.operator .message-content {
            background: #667eea;
            color: white;
        }

        .message.system .message-content {
            background: #f3f4f6;
            color: #6b7280;
            font-style: italic;
            text-align: center;
            margin: 0 auto;
        }

        .message-time {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .chat-input {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: white;
        }

        .input-group {
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
        }

        .input-group textarea {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            resize: none;
            min-height: 44px;
            max-height: 120px;
        }

        .send-btn {
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
        }

        .send-btn:hover {
            background: #5a6fd8;
        }

        .send-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .no-session {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #6b7280;
        }

        .stats {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .logout-btn {
            width: 100%;
            padding: 0.5rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 1rem;
        }

        .filters {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .filter-btn {
            display: block;
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            background: transparent;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            text-align: left;
        }

        .filter-btn.active {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
        }

        @media (max-width: 768px) {
            .main-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
        }
        
        .header-buttons {
    position: absolute;
    top: 1rem;
    right: 2rem;
    display: flex;
    gap: 0.75rem;
}

.header-btn {
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.2);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
    border: 1px solid rgba(255,255,255,0.3);
}

.header-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .header-buttons {
        position: static;
        margin-top: 1rem;
        justify-content: center;
    }
}
    </style>
</head>
<body>
    <div class="header">
    <h1>Панель техпідтримки StormHosting</h1>
    <div class="status" id="connectionStatus">Підключення...</div>
    
    <div class="header-buttons">
        <a href="/admin/chat-analytics.php" target="_blank" class="header-btn">
            📊 Статистика
        </a>
        <a href="/admin/chat-files.php" target="_blank" class="header-btn">
            📁 Файли
        </a>
        <a href="#" onclick="toggleSettings()" class="header-btn">
            ⚙️ Налаштування
        </a>
    </div>
</div>

    <div class="main-container">
        <div class="sidebar">
            <!-- Секція авторизації -->
            <div class="login-section" id="loginSection">
                <div class="login-form" id="loginForm">
                    <h3 style="margin-bottom: 1rem;">Авторизація оператора</h3>
                    <input type="text" id="operatorName" placeholder="Ім'я оператора" />
                    <input type="password" id="operatorPassword" placeholder="Пароль" />
                    <button onclick="loginOperator()">Увійти</button>
                    <div style="margin-top: 1rem; font-size: 0.8rem; color: #6b7280;">
                        <strong>Тестові дані:</strong><br>
                        Ім'я: admin<br>
                        Пароль: stormoperator123
                    </div>
                </div>

                <div class="operator-info" id="operatorInfo" style="display: none;">
                    <div class="operator-status"></div>
                    <div>
                        <div style="font-weight: 500;" id="operatorNameDisplay"></div>
                        <div style="font-size: 0.8rem; color: #6b7280;">Онлайн</div>
                    </div>
                    <button class="logout-btn" onclick="logoutOperator()">Вийти</button>
                </div>
            </div>

            <!-- Статистика -->
            <div class="stats" id="statsSection" style="display: none;">
                <h4 style="margin-bottom: 0.75rem;">Статистика</h4>
                <div class="stats-grid">
                    <div>Активні чати: <span id="activeSessions">0</span></div>
                    <div>В очікуванні: <span id="waitingSessions">0</span></div>
                    <div>Сьогодні: <span id="todaySessions">0</span></div>
                    <div>Мої чати: <span id="mySessions">0</span></div>
                </div>
            </div>

            <!-- Фільтри -->
            <div class="filters" id="filtersSection" style="display: none;">
                <h4 style="margin-bottom: 0.75rem;">Фільтри</h4>
                <button class="filter-btn active" onclick="filterSessions('all')">Всі чати</button>
                <button class="filter-btn" onclick="filterSessions('waiting')">В очікуванні</button>
                <button class="filter-btn" onclick="filterSessions('active')">Активні</button>
                <button class="filter-btn" onclick="filterSessions('my')">Мої чати</button>
                <button class="filter-btn" onclick="filterSessions('urgent')">Термінові</button>
            </div>

            <!-- Список сессий -->
            <div class="sessions-list" id="sessionsList"></div>
        </div>

        <div class="chat-area">
            <div class="no-session" id="noSession">
                <h3>Оберіть чат для початку роботи</h3>
                <p>Виберіть сесію зі списку ліворуч для початку спілкування з клієнтом</p>
            </div>

            <div id="chatInterface" style="display: none; height: 100%; display: flex; flex-direction: column;">
                <div class="chat-header" id="chatHeader">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 id="chatTitle">Чат з клієнтом</h4>
                            <div style="font-size: 0.9rem; color: #6b7280;" id="chatInfo">Інформація про сесію</div>
                        </div>
                        <div>
                            <button onclick="takeSession()" id="takeSessionBtn" style="padding: 0.5rem 1rem; background: #22c55e; color: white; border: none; border-radius: 6px; margin-right: 0.5rem;">Взяти чат</button>
                            <button onclick="closeSession()" id="closeSessionBtn" style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 6px;">Закрити чат</button>
                        </div>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages"></div>

                <div class="chat-input">
                    <div class="input-group">
                        <textarea 
                            id="messageInput" 
                            placeholder="Напишіть повідомлення..."
                            onkeypress="handleKeyPress(event)"
                            oninput="autoResize(this)"></textarea>
                        <button class="send-btn" onclick="sendMessage()" id="sendButton">Відправити</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        class AdminPanel {
            constructor() {
                this.operatorId = null;
                this.currentSession = null;
                this.sessions = [];
                this.pollInterval = null;
                this.lastMessageId = 0;
                this.currentFilter = 'all';
                
                this.init();
            }
            
            init() {
                this.updateConnectionStatus(false);
                this.checkLogin();
                setInterval(() => this.updateStats(), 30000); // Обновляем статистику каждые 30 сек
            }
            
            async checkLogin() {
                const operatorId = localStorage.getItem('operator_id');
                const operatorName = localStorage.getItem('operator_name');
                
                if (operatorId && operatorName) {
                    this.operatorId = operatorId;
                    this.showLoggedIn(operatorName);
                    this.startPolling();
                }
            }
            
            async loginOperator() {
                const name = document.getElementById('operatorName').value.trim();
                const password = document.getElementById('operatorPassword').value.trim();
                
                if (!name || !password) {
                    alert('Введіть ім\'я та пароль');
                    return;
                }
                
                try {
                    const response = await fetch('/api/chat/operators.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'login',
                            name: name,
                            password: password
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.operatorId = result.data.operator_id;
                        localStorage.setItem('operator_id', this.operatorId);
                        localStorage.setItem('operator_name', name);
                        
                        this.showLoggedIn(name);
                        this.startPolling();
                        this.updateConnectionStatus(true);
                    } else {
                        alert('Помилка авторизації: ' + result.message);
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    alert('Помилка підключення до сервера');
                }
            }
            
            async logoutOperator() {
                try {
                    await fetch('/api/chat/operators.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'logout' })
                    });
                } catch (error) {
                    console.error('Logout error:', error);
                }
                
                this.operatorId = null;
                localStorage.removeItem('operator_id');
                localStorage.removeItem('operator_name');
                
                this.showLogin();
                this.stopPolling();
                this.updateConnectionStatus(false);
            }
            
            showLoggedIn(name) {
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('operatorInfo').style.display = 'flex';
                document.getElementById('operatorNameDisplay').textContent = name;
                document.getElementById('loginSection').classList.add('logged-in');
                
                document.getElementById('statsSection').style.display = 'block';
                document.getElementById('filtersSection').style.display = 'block';
                
                this.loadSessions();
            }
            
            showLogin() {
                document.getElementById('loginForm').style.display = 'block';
                document.getElementById('operatorInfo').style.display = 'none';
                document.getElementById('loginSection').classList.remove('logged-in');
                
                document.getElementById('statsSection').style.display = 'none';
                document.getElementById('filtersSection').style.display = 'none';
                document.getElementById('sessionsList').innerHTML = '';
                
                document.getElementById('operatorName').value = '';
                document.getElementById('operatorPassword').value = '';
            }
            
            async loadSessions() {
                if (!this.operatorId) return;
                
                try {
                    const response = await fetch(`/api/chat/operators.php?action=get_sessions&filter=${this.currentFilter}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.sessions = result.data.sessions;
                        this.updateSessionsList();
                        this.updateStats();
                    }
                } catch (error) {
                    console.error('Load sessions error:', error);
                }
            }
            
            updateSessionsList() {
                const container = document.getElementById('sessionsList');
                container.innerHTML = '';
                
                this.sessions.forEach(session => {
                    const item = document.createElement('div');
                    item.className = `session-item ${session.priority === 'urgent' ? 'urgent' : ''} ${this.currentSession && this.currentSession.id === session.id ? 'active' : ''}`;
                    item.onclick = () => this.selectSession(session);
                    
                    const timeAgo = this.getTimeAgo(session.created_at);
                    
                    item.innerHTML = `
                        <div class="session-meta">
                            <span class="session-status ${session.status}">${this.getStatusText(session.status)}</span>
                            <span style="font-size: 0.75rem; color: #6b7280;">${timeAgo}</span>
                        </div>
                        <div style="font-weight: 500; margin-bottom: 0.25rem;">${session.guest_name || 'Гість'}</div>
                        <div style="font-size: 0.9rem; color: #6b7280; line-height: 1.3;">${session.subject}</div>
                        ${session.operator_name ? `<div style="font-size: 0.8rem; color: #059669; margin-top: 0.25rem;">👤 ${session.operator_name}</div>` : ''}
                    `;
                    
                    container.appendChild(item);
                });
                
                if (this.sessions.length === 0) {
                    container.innerHTML = '<div style="padding: 2rem; text-align: center; color: #6b7280;">Немає сесій</div>';
                }
            }
            
            selectSession(session) {
                this.currentSession = session;
                this.lastMessageId = 0;
                
                document.getElementById('noSession').style.display = 'none';
                document.getElementById('chatInterface').style.display = 'flex';
                
                this.updateChatHeader();
                this.loadMessages();
                this.updateSessionsList(); // Обновляем список для выделения активной сессии
            }
            
            updateChatHeader() {
                if (!this.currentSession) return;
                
                document.getElementById('chatTitle').textContent = `Чат з ${this.currentSession.guest_name || 'Гість'}`;
                document.getElementById('chatInfo').textContent = `${this.currentSession.subject} • ${this.getStatusText(this.currentSession.status)}`;
                
                const takeBtn = document.getElementById('takeSessionBtn');
                takeBtn.style.display = this.currentSession.operator_id ? 'none' : 'inline-block';
            }
            
            async loadMessages() {
                if (!this.currentSession) return;
                
                try {
                    const response = await fetch(`/api/chat/messages.php?session_id=${this.currentSession.id}&last_message_id=${this.lastMessageId}`);
                    const result = await response.json();
                    
                    if (result.success && result.data.messages) {
                        result.data.messages.forEach(message => {
                            this.addMessageToChat(message);
                            this.lastMessageId = Math.max(this.lastMessageId, message.id);
                        });
                        
                        this.scrollToBottom();
                    }
                } catch (error) {
                    console.error('Load messages error:', error);
                }
            }
            
            addMessageToChat(message) {
                const container = document.getElementById('chatMessages');
                const messageDiv = document.createElement('div');
                
                let senderClass = message.sender_type;
                if (message.message_type === 'system') senderClass = 'system';
                
                messageDiv.className = `message ${senderClass}`;
                
                const time = new Date(message.created_at).toLocaleTimeString('uk-UA', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                let senderName = '';
                if (message.sender_type === 'operator' && message.sender_name) {
                    senderName = message.sender_name;
                } else if (message.sender_type === 'user') {
                    senderName = this.currentSession.guest_name || 'Клієнт';
                }
                
                messageDiv.innerHTML = `
                    <div class="message-content">
                        ${senderName && senderClass !== 'system' ? `<div style="font-size: 0.8rem; margin-bottom: 0.25rem; opacity: 0.8;">${senderName}</div>` : ''}
                        <div>${this.formatMessage(message.message)}</div>
                        <div class="message-time">${time}</div>
                    </div>
                `;
                
                container.appendChild(messageDiv);
            }
            
            async sendMessage() {
                if (!this.currentSession || !this.operatorId) return;
                
                const input = document.getElementById('messageInput');
                const message = input.value.trim();
                
                if (!message) return;
                
                try {
                    const sendBtn = document.getElementById('sendButton');
                    sendBtn.disabled = true;
                    sendBtn.textContent = 'Відправка...';
                    
                    const response = await fetch('/api/chat/messages.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            session_id: this.currentSession.id,
                            message: message,
                            sender_type: 'operator',
                            sender_id: this.operatorId
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        input.value = '';
                        this.autoResize(input);
                        this.addMessageToChat(result.data);
                        this.lastMessageId = Math.max(this.lastMessageId, result.data.id);
                        this.scrollToBottom();
                    } else {
                        alert('Помилка відправки: ' + result.message);
                    }
                } catch (error) {
                    console.error('Send message error:', error);
                    alert('Помилка відправки повідомлення');
                } finally {
                    const sendBtn = document.getElementById('sendButton');
                    sendBtn.disabled = false;
                    sendBtn.textContent = 'Відправити';
                }
            }
            
            async takeSession() {
                if (!this.currentSession || !this.operatorId) return;
                
                try {
                    const response = await fetch('/api/chat/session.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            session_id: this.currentSession.id,
                            action: 'assign_operator',
                            operator_id: this.operatorId
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.currentSession.operator_id = this.operatorId;
                        this.updateChatHeader();
                        this.loadMessages();
                        this.loadSessions();
                    } else {
                        alert('Помилка призначення: ' + result.message);
                    }
                } catch (error) {
                    console.error('Take session error:', error);
                    alert('Помилка призначення сесії');
                }
            }
            
            async closeSession() {
                if (!this.currentSession) return;
                
                if (!confirm('Ви впевнені що хочете закрити цей чат?')) return;
                
                try {
                    const response = await fetch('/api/chat/session.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            session_id: this.currentSession.id,
                            action: 'close'
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.currentSession = null;
                        document.getElementById('noSession').style.display = 'flex';
                        document.getElementById('chatInterface').style.display = 'none';
                        this.loadSessions();
                    } else {
                        alert('Помилка закриття: ' + result.message);
                    }
                } catch (error) {
                    console.error('Close session error:', error);
                    alert('Помилка закриття сесії');
                }
            }
            
            filterSessions(filter) {
                this.currentFilter = filter;
                
                // Обновляем кнопки фильтров
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                event.target.classList.add('active');
                
                this.loadSessions();
            }
            
            startPolling() {
                this.pollInterval = setInterval(() => {
                    this.loadSessions();
                    if (this.currentSession) {
                        this.loadMessages();
                    }
                }, 3000);
            }
            
            stopPolling() {
                if (this.pollInterval) {
                    clearInterval(this.pollInterval);
                    this.pollInterval = null;
                }
            }
            
            updateStats() {
                if (!this.sessions) return;
                
                const waiting = this.sessions.filter(s => s.status === 'waiting').length;
                const active = this.sessions.filter(s => s.status === 'active').length;
                const my = this.sessions.filter(s => s.operator_id == this.operatorId).length;
                const today = this.sessions.filter(s => {
                    const created = new Date(s.created_at);
                    const today = new Date();
                    return created.toDateString() === today.toDateString();
                }).length;
                
                document.getElementById('waitingSessions').textContent = waiting;
                document.getElementById('activeSessions').textContent = active;
                document.getElementById('mySessions').textContent = my;
                document.getElementById('todaySessions').textContent = today;
            }
            
            updateConnectionStatus(connected) {
                const status = document.getElementById('connectionStatus');
                if (connected) {
                    status.textContent = 'Підключено • Оператор онлайн';
                    status.style.color = '#22c55e';
                } else {
                    status.textContent = 'Відключено • Очікування підключення';
                    status.style.color = '#ef4444';
                }
            }
            
            // Utility methods
            
            formatMessage(message) {
                return message.replace(/\n/g, '<br>');
            }
            
            scrollToBottom() {
                const container = document.getElementById('chatMessages');
                container.scrollTop = container.scrollHeight;
            }
            
            autoResize(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            }
            
            handleKeyPress(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    this.sendMessage();
                }
            }
            
            getTimeAgo(dateString) {
                const now = new Date();
                const date = new Date(dateString);
                const diffInSeconds = Math.floor((now - date) / 1000);
                
                if (diffInSeconds < 60) return 'щойно';
                if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} хв тому`;
                if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} год тому`;
                return `${Math.floor(diffInSeconds / 86400)} дн тому`;
            }
            
            getStatusText(status) {
                const statusMap = {
                    'waiting': 'Очікує',
                    'active': 'Активний',
                    'closed': 'Закритий',
                    'transferred': 'Переданий'
                };
                return statusMap[status] || status;
            }
        }

        // Глобальные функции для вызова из HTML
        let adminPanel;

        document.addEventListener('DOMContentLoaded', function() {
            adminPanel = new AdminPanel();
        });

        function loginOperator() {
            adminPanel.loginOperator();
        }

        function logoutOperator() {
            adminPanel.logoutOperator();
        }

        function sendMessage() {
            adminPanel.sendMessage();
        }

        function takeSession() {
            adminPanel.takeSession();
        }

        function closeSession() {
            adminPanel.closeSession();
        }

        function filterSessions(filter) {
            adminPanel.filterSessions(filter);
        }

        function handleKeyPress(event) {
            adminPanel.handleKeyPress(event);
        }

        function autoResize(textarea) {
            adminPanel.autoResize(textarea);
        }
    </script>
    
    <script>
function toggleSettings() {
    alert('Налаштування:\n\n• Максимум сесій на оператора: 5\n• Час очікування: 30 хв\n• Автозакриття неактивних: 1 год\n• Звукові сповіщення: ✅\n• Статус набору: ✅\n\nДля зміни налаштувань зверніться до адміністратора.');
}
</script>
    
</body>
</html>