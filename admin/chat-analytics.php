<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналітика чата - StormHosting</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
            max-width: 1400px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }

        .stat-card.success {
            border-left-color: #22c55e;
        }

        .stat-card.warning {
            border-left-color: #f59e0b;
        }

        .stat-card.danger {
            border-left-color: #ef4444;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .stat-change {
            font-size: 0.8rem;
            font-weight: 500;
        }

        .stat-change.positive {
            color: #22c55e;
        }

        .stat-change.negative {
            color: #ef4444;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
        }

        .table-content {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .operator-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-online {
            background: #dcfce7;
            color: #166534;
        }

        .status-offline {
            background: #fee2e2;
            color: #991b1b;
        }

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .filter-select, .filter-input {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }

        .filter-btn:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a42a0);
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: #64748b;
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Аналітика чата</h1>
        <div class="breadcrumb">
            <a href="/admin/support-panel.php">← Назад до панелі операторів</a>
        </div>
    </div>

    <div class="main-container">
        <!-- Фильтры -->
        <div class="filters">
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">Період</label>
                    <select class="filter-select" id="periodFilter">
                        <option value="today">Сьогодні</option>
                        <option value="week" selected>Цей тиждень</option>
                        <option value="month">Цей місяць</option>
                        <option value="custom">Вибрати період</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Оператор</label>
                    <select class="filter-select" id="operatorFilter">
                        <option value="all">Всі оператори</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Статус</label>
                    <select class="filter-select" id="statusFilter">
                        <option value="all">Всі статуси</option>
                        <option value="active">Активні</option>
                        <option value="closed">Закриті</option>
                        <option value="waiting">В очікуванні</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button class="filter-btn" onclick="Analytics.loadData()">
                        🔄 Оновити дані
                    </button>
                </div>
            </div>
        </div>

        <!-- Статистические карточки -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="totalChats">-</div>
                <div class="stat-label">Всього чатів</div>
                <div class="stat-change positive" id="chatsChange">-</div>
            </div>
            <div class="stat-card success">
                <div class="stat-number" id="completedChats">-</div>
                <div class="stat-label">Завершені чати</div>
                <div class="stat-change positive" id="completedChange">-</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-number" id="avgResponseTime">-</div>
                <div class="stat-label">Середній час відповіді</div>
                <div class="stat-change" id="responseTimeChange">-</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-number" id="activeOperators">-</div>
                <div class="stat-label">Активні оператори</div>
                <div class="stat-change" id="operatorsChange">-</div>
            </div>
        </div>

        <!-- Графики -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">📈 Динаміка чатів</div>
                <canvas id="chatsChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">🥧 Розподіл по статусах</div>
                <canvas id="statusChart" width="300" height="300"></canvas>
            </div>
        </div>

        <!-- Таблица операторов -->
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">👥 Продуктивність операторів</div>
            </div>
            <div class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th>Оператор</th>
                            <th>Статус</th>
                            <th>Чати сьогодні</th>
                            <th>Середній час</th>
                            <th>Рейтинг</th>
                            <th>Остання активність</th>
                        </tr>
                    </thead>
                    <tbody id="operatorsTable">
                        <tr>
                            <td colspan="6" class="loading">
                                ⏳ Завантаження даних...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Таблица последних чатов -->
        <div class="table-card" style="margin-top: 2rem;">
            <div class="table-header">
                <div class="table-title">💬 Останні чати</div>
            </div>
            <div class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th>Клієнт</th>
                            <th>Тема</th>
                            <th>Оператор</th>
                            <th>Статус</th>
                            <th>Тривалість</th>
                            <th>Час створення</th>
                        </tr>
                    </thead>
                    <tbody id="recentChatsTable">
                        <tr>
                            <td colspan="6" class="loading">
                                ⏳ Завантаження даних...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        class ChatAnalytics {
            constructor() {
                this.chatsChart = null;
                this.statusChart = null;
                this.init();
            }
            
            init() {
                this.loadOperators();
                this.loadData();
                this.setupEventListeners();
            }
            
            setupEventListeners() {
                document.getElementById('periodFilter').addEventListener('change', () => {
                    this.loadData();
                });
                
                // Автообновление каждые 30 секунд
                setInterval(() => {
                    this.loadData();
                }, 30000);
            }
            
            async loadOperators() {
                try {
                    const response = await fetch('/api/chat/analytics.php?action=get_operators');
                    const result = await response.json();
                    
                    if (result.success) {
                        const select = document.getElementById('operatorFilter');
                        select.innerHTML = '<option value="all">Всі оператори</option>';
                        
                        result.data.forEach(operator => {
                            const option = document.createElement('option');
                            option.value = operator.id;
                            option.textContent = operator.name;
                            select.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Load operators error:', error);
                }
            }
            
            async loadData() {
                try {
                    const period = document.getElementById('periodFilter').value;
                    const operator = document.getElementById('operatorFilter').value;
                    const status = document.getElementById('statusFilter').value;
                    
                    const params = new URLSearchParams({
                        action: 'get_analytics',
                        period: period,
                        operator: operator,
                        status: status
                    });
                    
                    const response = await fetch(`/api/chat/analytics.php?${params}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.updateStats(result.data.stats);
                        this.updateCharts(result.data.charts);
                        this.updateOperatorsTable(result.data.operators);
                        this.updateRecentChats(result.data.recent_chats);
                    } else {
                        console.error('Analytics error:', result.message);
                    }
                } catch (error) {
                    console.error('Load data error:', error);
                }
            }
            
            updateStats(stats) {
                document.getElementById('totalChats').textContent = stats.total_chats || '0';
                document.getElementById('completedChats').textContent = stats.completed_chats || '0';
                document.getElementById('avgResponseTime').textContent = stats.avg_response_time || '0мин';
                document.getElementById('activeOperators').textContent = stats.active_operators || '0';
                
                // Обновляем изменения
                this.updateChange('chatsChange', stats.chats_change);
                this.updateChange('completedChange', stats.completed_change);
                this.updateChange('responseTimeChange', stats.response_time_change);
                this.updateChange('operatorsChange', stats.operators_change);
            }
            
            updateChange(elementId, change) {
                const element = document.getElementById(elementId);
                if (change > 0) {
                    element.textContent = `+${change}% від минулого періоду`;
                    element.className = 'stat-change positive';
                } else if (change < 0) {
                    element.textContent = `${change}% від минулого періоду`;
                    element.className = 'stat-change negative';
                } else {
                    element.textContent = 'Без змін';
                    element.className = 'stat-change';
                }
            }
            
            updateCharts(chartsData) {
                this.updateChatsChart(chartsData.chats_timeline);
                this.updateStatusChart(chartsData.status_distribution);
            }
            
            updateChatsChart(data) {
                const ctx = document.getElementById('chatsChart').getContext('2d');
                
                if (this.chatsChart) {
                    this.chatsChart.destroy();
                }
                
                this.chatsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            label: 'Чати',
                            data: data.values || [],
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            updateStatusChart(data) {
                const ctx = document.getElementById('statusChart').getContext('2d');
                
                if (this.statusChart) {
                    this.statusChart.destroy();
                }
                
                this.statusChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels || ['Активні', 'Завершені', 'В очікуванні'],
                        datasets: [{
                            data: data.values || [0, 0, 0],
                            backgroundColor: [
                                '#22c55e',
                                '#667eea',
                                '#f59e0b'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            updateOperatorsTable(operators) {
                const tbody = document.getElementById('operatorsTable');
                
                if (!operators || operators.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="no-data">Немає даних</td></tr>';
                    return;
                }
                
                tbody.innerHTML = operators.map(operator => `
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div class="operator-avatar">
                                    ${operator.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div style="font-weight: 500;">${operator.name}</div>
                                    <div style="font-size: 0.875rem; color: #64748b;">${operator.role}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge ${operator.is_online ? 'status-online' : 'status-offline'}">
                                ${operator.is_online ? 'Онлайн' : 'Офлайн'}
                            </span>
                        </td>
                        <td>${operator.chats_today || 0}</td>
                        <td>${operator.avg_time || '0мин'}</td>
                        <td>${'⭐'.repeat(operator.rating || 0)}</td>
                        <td>${this.formatTime(operator.last_activity)}</td>
                    </tr>
                `).join('');
            }
            
            updateRecentChats(chats) {
                const tbody = document.getElementById('recentChatsTable');
                
                if (!chats || chats.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="no-data">Немає даних</td></tr>';
                    return;
                }
                
                tbody.innerHTML = chats.map(chat => `
                    <tr>
                        <td>
                            <div>
                                <div style="font-weight: 500;">${chat.guest_name || 'Гість'}</div>
                                <div style="font-size: 0.875rem; color: #64748b;">${chat.guest_email || ''}</div>
                            </div>
                        </td>
                        <td>${chat.subject}</td>
                        <td>${chat.operator_name || 'Не призначено'}</td>
                        <td>
                            <span class="status-badge ${this.getStatusClass(chat.status)}">
                                ${this.getStatusText(chat.status)}
                            </span>
                        </td>
                        <td>${chat.duration || '0мин'}</td>
                        <td>${this.formatTime(chat.created_at)}</td>
                    </tr>
                `).join('');
            }
            
            getStatusClass(status) {
                const statusMap = {
                    'active': 'status-online',
                    'closed': 'status-offline',
                    'waiting': 'status-badge'
                };
                return statusMap[status] || 'status-badge';
            }
            
            getStatusText(status) {
                const statusMap = {
                    'active': 'Активний',
                    'closed': 'Закритий',
                    'waiting': 'Очікує'
                };
                return statusMap[status] || status;
            }
            
            formatTime(timeString) {
                if (!timeString) return '-';
                
                const date = new Date(timeString);
                const now = new Date();
                const diffInMinutes = Math.floor((now - date) / (1000 * 60));
                
                if (diffInMinutes < 1) return 'щойно';
                if (diffInMinutes < 60) return `${diffInMinutes}хв тому`;
                if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}г тому`;
                return date.toLocaleDateString('uk-UA');
            }
        }

        // Инициализация
        const Analytics = new ChatAnalytics();
    </script>
</body>
</html>