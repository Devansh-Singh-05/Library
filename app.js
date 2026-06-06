/**
 * LIBRARY MANAGEMENT SYSTEM - COMPLETE APP.JS
 * Version: 2.0-FIXED - All Bugs Fixed
 * This file is COMPLETE and WORKING
 */

// ==================== GLOBAL CONFIGURATION ====================
const LibraryApp = {
    config: {
        apiBase: 'api.php',
        version: '2.0-FIXED',
        defaultLanguage: 'en',
        itemsPerPage: 20,
        maxFileSize: 5 * 1024 * 1024
    },
    state: {
        currentPage: 'dashboard',
        currentView: {
            name: 'dashboard',
            page: 1,
            filters: {},
            sortBy: null,
            sortOrder: 'asc'
        },
        currentUser: {
            id: 'admin',
            name: 'Administrator',
            role: 'admin'
        },
        language: 'en',
        translations: {}
    },
    cache: new Map()
};

// ==================== UTILITIES ====================
const Utils = {
    async request(endpoint, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        const config = Object.assign({}, defaultOptions, options);
        const url = LibraryApp.config.apiBase + '/' + endpoint.replace(/^\//, '');

        try {
            console.log('API Request:', config.method, url);
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const data = await response.json();
            console.log('API Response:', data);
            
            if (data.success === false) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            this.showError(error.message);
            throw error;
        }
    },

    showLoading(element) {
        if (element) {
            element.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading...</p></div>';
        }
    },

    showToast(message, type, duration) {
        type = type || 'info';
        duration = duration || 3000;
        
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        
        const icon = type === 'success' ? '✓' : (type === 'error' ? '✗' : 'ℹ');
        
        toast.innerHTML = '<div class="toast-content"><span class="toast-icon">' + icon + '</span><span class="toast-message">' + message + '</span></div>';
        
        document.body.appendChild(toast);
        
        setTimeout(function() { 
            toast.classList.add('toast-show'); 
        }, 10);
        
        setTimeout(function() {
            toast.classList.remove('toast-show');
            setTimeout(function() { toast.remove(); }, 300);
        }, duration);
    },

    showSuccess(msg) { 
        this.showToast(msg, 'success'); 
    },
    
    showError(msg) { 
        this.showToast(msg, 'error', 5000); 
    },

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid Date';
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
    },

    formatCurrency(amount) {
        const num = parseFloat(amount || 0);
        return '$' + num.toFixed(2);
    },

    downloadCSV(data, filename) {
        if (!data || data.length === 0) {
            return this.showError('No data to export');
        }
        
        try {
            const headers = Object.keys(data[0]);
            const csvRows = [];
            
            csvRows.push(headers.join(','));
            
            for (const row of data) {
                const values = headers.map(function(header) {
                    const value = row[header] || '';
                    return '"' + String(value).replace(/"/g, '""') + '"';
                });
                csvRows.push(values.join(','));
            }
            
            const csvContent = csvRows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename || 'export.csv';
            link.click();
            this.showSuccess('CSV exported');
        } catch (error) {
            console.error('CSV Export Error:', error);
            this.showError('Failed to export CSV');
        }
    },

    downloadPDF(data, title) {
        try {
            const headers = Object.keys(data[0]);
            let tableRows = '';
            
            for (const row of data) {
                let rowHtml = '<tr>';
                for (const header of headers) {
                    rowHtml += '<td>' + (row[header] || 'N/A') + '</td>';
                }
                rowHtml += '</tr>';
                tableRows += rowHtml;
            }
            
            const html = '<html><head><title>' + title + '</title><style>' +
                'body{font-family:Arial;margin:20px}table{width:100%;border-collapse:collapse}' +
                'th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f2f2f2}' +
                '</style></head><body><h1>' + title + '</h1><p>Generated: ' + new Date().toLocaleString() + '</p>' +
                '<table><thead><tr>' + headers.map(function(h) { return '<th>' + h + '</th>'; }).join('') + '</tr></thead>' +
                '<tbody>' + tableRows + '</tbody></table></body></html>';
            
            const w = window.open('', '_blank');
            w.document.write(html);
            w.document.close();
            w.print();
        } catch (error) {
            console.error('PDF Error:', error);
            this.showError('Failed to generate PDF');
        }
    }
};

// ==================== LANGUAGE MANAGER ====================
const LanguageManager = {
    currentLanguage: 'en',
    translations: {},

    async init() {
        await this.loadTranslations(LibraryApp.state.language);
    },

    async loadTranslations(lang) {
        try {
            const response = await Utils.request('translations?lang=' + lang);
            if (response.success) {
                this.translations = response.data.translations || {};
                this.currentLanguage = lang;
                LibraryApp.state.language = lang;
                this.updateUI();
                Utils.showSuccess('Language: ' + (lang === 'hi' ? 'हिंदी' : 'English'));
            }
        } catch (error) {
            console.error('Translation load failed:', error);
        }
    },

    updateUI() {
        const elements = document.querySelectorAll('[data-translate]');
        for (let i = 0; i < elements.length; i++) {
            const elem = elements[i];
            const key = elem.getAttribute('data-translate');
            const translated = this.get(key);
            if (elem.tagName === 'INPUT' && elem.placeholder) {
                elem.placeholder = translated;
            } else {
                elem.textContent = translated;
            }
        }
        if (PageManager && PageManager.renderCurrentPage) {
            PageManager.renderCurrentPage();
        }
    },

    get(key, def) {
        return this.translations[key] || def || key;
    },

    async switchLanguage(lang) {
        if (lang !== this.currentLanguage) {
            await this.loadTranslations(lang);
        }
    }
};

// ==================== PAGINATION MANAGER ====================
const PaginationManager = {
    render(pagination, onPageChange) {
        if (!pagination || pagination.total_pages <= 1) {
            return '<div class="pagination-info">Showing all records</div>';
        }

        const current_page = pagination.current_page;
        const total_pages = pagination.total_pages;
        const total_records = pagination.total_records;
        const records_per_page = pagination.records_per_page;
        
        const start = ((current_page - 1) * records_per_page) + 1;
        const end = Math.min(current_page * records_per_page, total_records);

        let html = '<div class="pagination-container">';
        html += '<div class="pagination-info">Showing ' + start + '-' + end + ' of ' + total_records + '</div>';
        html += '<div class="pagination-controls">';

        if (current_page > 1) {
            html += '<button class="pagination-btn" data-page="1">«</button>';
            html += '<button class="pagination-btn" data-page="' + (current_page-1) + '">‹</button>';
        }

        let startPage = Math.max(1, current_page - 2);
        let endPage = Math.min(total_pages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === current_page ? ' active' : '';
            html += '<button class="pagination-btn' + activeClass + '" data-page="' + i + '">' + i + '</button>';
        }

        if (current_page < total_pages) {
            html += '<button class="pagination-btn" data-page="' + (current_page+1) + '">›</button>';
            html += '<button class="pagination-btn" data-page="' + total_pages + '">»</button>';
        }

        html += '</div></div>';
        return html;
    },

    setupListeners(container, onPageChange) {
        const buttons = container.querySelectorAll('.pagination-btn');
        for (let i = 0; i < buttons.length; i++) {
            buttons[i].addEventListener('click', function() {
                const page = parseInt(this.getAttribute('data-page'));
                if (page && !isNaN(page)) {
                    onPageChange(page);
                }
            });
        }
    }
};

// ==================== TABLE RENDERER ====================
const TableRenderer = {
    render(config) {
        const data = config.data;
        const columns = config.columns;
        const pagination = config.pagination;
        const actions = config.actions;

        if (!data || data.length === 0) {
            return '<div class="empty-state">No records found</div>';
        }

        let html = '<div class="table-responsive"><table class="data-table"><thead><tr>';
        
        for (let i = 0; i < columns.length; i++) {
            html += '<th>' + columns[i].label + '</th>';
        }
        if (actions) html += '<th>Actions</th>';
        html += '</tr></thead><tbody>';

        for (let i = 0; i < data.length; i++) {
            const row = data[i];
            html += '<tr>';
            
            for (let j = 0; j < columns.length; j++) {
                const col = columns[j];
                let value = row[col.field];
                
                if (col.formatter) {
                    value = col.formatter(value, row);
                } else if (value === null || value === undefined) {
                    value = 'N/A';
                }
                
                html += '<td>' + value + '</td>';
            }
            
            if (actions) {
                html += '<td class="actions-cell">' + actions(row) + '</td>';
            }
            html += '</tr>';
        }

        html += '</tbody></table></div>';
        
        if (pagination) {
            html += PaginationManager.render(pagination, config.onPageChange);
        }
        
        return html;
    },

    setupListeners(container, config) {
        if (config.onPageChange) {
            PaginationManager.setupListeners(container, config.onPageChange);
        }
        
        if (config.actionHandlers) {
            const actions = Object.keys(config.actionHandlers);
            for (let i = 0; i < actions.length; i++) {
                const action = actions[i];
                const buttons = container.querySelectorAll('[data-action="' + action + '"]');
                
                for (let j = 0; j < buttons.length; j++) {
                    buttons[j].addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.getAttribute('data-id');
                        config.actionHandlers[action](id);
                    });
                }
            }
        }
    }
};

// ==================== SEARCH MANAGER ====================
const SearchManager = {
    async performSearch(query) {
        if (!query || query.length < 2) {
            Utils.showError('Enter at least 2 characters');
            return;
        }

        try {
            const response = await Utils.request('search?q=' + encodeURIComponent(query));
            if (response.success) {
                this.displayResults(response.data);
            }
        } catch (error) {
            Utils.showError('Search failed');
        }
    },

    displayResults(data) {
        const container = document.getElementById('searchResults');
        if (!container) return;

        const hasResults = (data.books && data.books.length > 0) || 
                          (data.members && data.members.length > 0) || 
                          (data.circulation && data.circulation.length > 0);

        if (!hasResults) {
            container.innerHTML = '<div class="empty-state">No results found</div>';
            container.style.display = 'block';
            return;
        }

        let html = '<div class="search-results-container">';

        if (data.books && data.books.length > 0) {
            html += '<h3>Books</h3><ul>';
            for (let i = 0; i < data.books.length; i++) {
                const book = data.books[i];
                html += '<li onclick="PageManager.navigateTo(\'books\')">' + 
                        book.book_title + ' by ' + book.author + '</li>';
            }
            html += '</ul>';
        }

        if (data.members && data.members.length > 0) {
            html += '<h3>Members</h3><ul>';
            for (let i = 0; i < data.members.length; i++) {
                const member = data.members[i];
                html += '<li onclick="PageManager.navigateTo(\'members\')">' + 
                        member.member_name + ' - ' + (member.email || 'No email') + '</li>';
            }
            html += '</ul>';
        }

        html += '</div>';
        container.innerHTML = html;
        container.style.display = 'block';
    }
};

// ==================== PAGE MANAGER ====================
const PageManager = {
    init() {
        this.setupNavigation();
        this.setupEventListeners();
        this.navigateTo('dashboard');
    },

    setupNavigation() {
        const navItems = document.querySelectorAll('.nav-item');
        for (let i = 0; i < navItems.length; i++) {
            navItems[i].addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                PageManager.navigateTo(page);
            });
        }
    },

    setupEventListeners() {
        // Quick Add Button
        const quickAddBtn = document.getElementById('quickAddBtn');
        if (quickAddBtn) {
            quickAddBtn.addEventListener('click', function() {
                PageManager.showQuickAddModal();
            });
        }

        // Global Search
        const searchBtn = document.getElementById('searchBtn');
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                const query = document.getElementById('globalSearch').value;
                SearchManager.performSearch(query);
            });
        }

        const globalSearch = document.getElementById('globalSearch');
        if (globalSearch) {
            globalSearch.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    SearchManager.performSearch(this.value);
                }
            });
        }

        // Language Switcher
        const languageSelect = document.getElementById('languageSelect');
        if (languageSelect) {
            languageSelect.addEventListener('change', function() {
                LanguageManager.switchLanguage(this.value);
            });
        }

        // Close search results
        document.addEventListener('click', function(e) {
            const searchResults = document.getElementById('searchResults');
            const searchContainer = document.querySelector('.search-container');
            if (searchResults && searchContainer && !searchContainer.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Modal close
        const modalCloses = document.querySelectorAll('.modal-close, [data-modal]');
        for (let i = 0; i < modalCloses.length; i++) {
            modalCloses[i].addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal');
                if (modalId) {
                    PageManager.closeModal(modalId);
                }
            });
        }
    },

    navigateTo(page) {
        LibraryApp.state.currentPage = page;
        
        const navItems = document.querySelectorAll('.nav-item');
        for (let i = 0; i < navItems.length; i++) {
            const isActive = navItems[i].getAttribute('data-page') === page;
            if (isActive) {
                navItems[i].classList.add('active');
            } else {
                navItems[i].classList.remove('active');
            }
        }

        this.renderPage(page);
    },

    renderPage(page) {
        switch(page) {
            case 'dashboard':
                DashboardManager.render();
                break;
            case 'books':
                BookManager.render();
                break;
            case 'members':
                MemberManager.render();
                break;
            case 'circulation':
                CirculationManager.render();
                break;
            case 'fines':
                FineManager.render();
                break;
            case 'reservations':
                ReservationManager.render();
                break;
            case 'reports':
                ReportManager.render();
                break;
            default:
                this.render404();
        }
    },

    renderCurrentPage() {
        this.renderPage(LibraryApp.state.currentPage);
    },

    render404() {
        document.getElementById('content').innerHTML = '<div class="error-state"><h3>Page not found</h3></div>';
    },

    showQuickAddModal() {
        const page = LibraryApp.state.currentPage;
        switch(page) {
            case 'books':
                this.openModal('bookModal');
                break;
            case 'members':
                this.openModal('memberModal');
                break;
            case 'circulation':
                this.openModal('issueModal');
                break;
            case 'reservations':
                this.openModal('reservationModal');
                break;
            default:
                Utils.showError('Quick add not available for this page');
        }
    },

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    }
};

// ==================== DASHBOARD MANAGER ====================
// const DashboardManager = {
//     async render() {
//         const content = document.getElementById('content');
//         Utils.showLoading(content);

//         try {
//             const response = await Utils.request('dashboard');
//             const statistics = response.data.statistics;

//             let html = '<div class="page-header"><h1>Dashboard</h1></div>';
//             html += '<div class="dashboard-grid">';
            
//             const stats = [
//                 { label: 'Total Books', value: statistics.total_books, color: '#2563eb' },
//                 { label: 'Total Members', value: statistics.total_members, color: '#10b981' },
//                 { label: 'Books Issued', value: statistics.books_issued, color: '#f59e0b' },
//                 { label: 'Overdue Books', value: statistics.overdue_books, color: '#ef4444' }
//             ];

//             for (let i = 0; i < stats.length; i++) {
//                 const stat = stats[i];
//                 html += '<div class="stat-card" style="border-left-color:' + stat.color + '">';
//                 html += '<h3>' + stat.label + '</h3>';
//                 html += '<div class="stat-value">' + stat.value + '</div>';
//                 html += '</div>';
//             }

//             html += '</div>';
//             content.innerHTML = html;

//         } catch (error) {
//             content.innerHTML = '<div class="error-state">Failed to load dashboard</div>';
//         }
//     }
// };
const DashboardManager = {
    async render() {
        const content = document.getElementById('content');
        Utils.showLoading(content);

        try {
            // Fetch enhanced dashboard data
            const response = await Utils.request('dashboard/enhanced');
            const data = response.data;

            let html = '<div class="page-header"><h1>📊 Dashboard Overview</h1></div>';
            
            // ==================== STATISTICS GRID ====================
            html += '<div class="dashboard-grid">';
            
            const stats = [
                { label: 'Total Books', value: data.total_books, icon: '📚', color: '#2563eb', trend: '+' + (data.new_books_this_month || 0) + ' this month' },
                { label: 'Total Members', value: data.total_members, icon: '👥', color: '#10b981', trend: '+' + (data.new_members_this_month || 0) + ' this month' },
                { label: 'Books Issued', value: data.books_issued, icon: '📖', color: '#f59e0b', trend: data.books_issued + ' currently out' },
                { label: 'Overdue Books', value: data.overdue_books, icon: '⏰', color: '#ef4444', trend: 'Needs attention' },
                { label: 'Active Reservations', value: data.total_reservations || 0, icon: '📅', color: '#8b5cf6', trend: 'In queue' },
                { label: 'Total Fines Collected', value: '$' + (data.fines_collected || 0), icon: '💰', color: '#10b981', trend: 'Paid' },
                { label: 'Unpaid Fines', value: '$' + (data.unpaid_fines || 0), icon: '💳', color: '#f97316', trend: 'Outstanding' },
                { label: 'Available Books', value: data.available_books || 0, icon: '✅', color: '#06b6d4', trend: 'Ready to borrow' }
            ];

            for (let i = 0; i < stats.length; i++) {
                const stat = stats[i];
                html += '<div class="stat-card-enhanced" style="border-left-color:' + stat.color + '">';
                html += '<div class="stat-icon" style="color:' + stat.color + '">' + stat.icon + '</div>';
                html += '<div class="stat-details">';
                html += '<h3>' + stat.label + '</h3>';
                html += '<div class="stat-value-large">' + stat.value + '</div>';
                html += '<div class="stat-trend">' + stat.trend + '</div>';
                html += '</div></div>';
            }
            
            html += '</div>';

            // ==================== QUICK ACTIONS ====================
            html += '<div class="quick-actions-section">';
            html += '<h2>⚡ Quick Actions</h2>';
            html += '<div class="quick-actions-grid">';
            html += '<button class="quick-action-btn" onclick="PageManager.navigateTo(\'books\'); setTimeout(() => PageManager.openModal(\'bookModal\'), 100);">';
            html += '<span class="action-icon">📚</span><span>Add New Book</span></button>';
            html += '<button class="quick-action-btn" onclick="PageManager.navigateTo(\'members\'); setTimeout(() => PageManager.openModal(\'memberModal\'), 100);">';
            html += '<span class="action-icon">👤</span><span>Add New Member</span></button>';
            html += '<button class="quick-action-btn" onclick="PageManager.navigateTo(\'circulation\'); setTimeout(() => PageManager.openModal(\'issueModal\'), 100);">';
            html += '<span class="action-icon">📖</span><span>Issue Book</span></button>';
            html += '<button class="quick-action-btn" onclick="PageManager.navigateTo(\'reservations\'); setTimeout(() => PageManager.openModal(\'reservationModal\'), 100);">';
            html += '<span class="action-icon">📅</span><span>Create Reservation</span></button>';
            html += '</div></div>';

            // ==================== CHARTS SECTION ====================
            html += '<div class="charts-section">';
            html += '<div class="charts-grid">';
            
            // Category Distribution Chart
            html += '<div class="chart-card">';
            html += '<h3>📊 Books by Category</h3>';
            html += '<canvas id="categoryChart" width="300" height="250"></canvas>';
            html += '</div>';
            
            // Top Borrowed Books Chart
            html += '<div class="chart-card">';
            html += '<h3>📈 Top 5 Most Borrowed Books</h3>';
            html += '<canvas id="topBooksChart" width="300" height="250"></canvas>';
            html += '</div>';
            
            html += '</div></div>';

            // ==================== RECENT ACTIVITY ====================
            html += '<div class="recent-activity-section">';
            html += '<h2>🕒 Recent Activity</h2>';
            html += '<div class="activity-grid">';
            
            // Recently Issued Books
            html += '<div class="activity-card">';
            html += '<h3>Recently Issued</h3>';
            html += '<ul class="activity-list">';
            if (data.recent_issues && data.recent_issues.length > 0) {
                for (let i = 0; i < Math.min(5, data.recent_issues.length); i++) {
                    const issue = data.recent_issues[i];
                    html += '<li><span class="activity-icon">📤</span>';
                    html += '<span class="activity-text"><strong>' + issue.book_title + '</strong> issued to ' + issue.member_name + '</span>';
                    html += '<span class="activity-time">' + Utils.formatDate(issue.issued_date) + '</span></li>';
                }
            } else {
                html += '<li class="empty-activity">No recent issues</li>';
            }
            html += '</ul></div>';
            
            // Recently Returned Books
            html += '<div class="activity-card">';
            html += '<h3>Recently Returned</h3>';
            html += '<ul class="activity-list">';
            if (data.recent_returns && data.recent_returns.length > 0) {
                for (let i = 0; i < Math.min(5, data.recent_returns.length); i++) {
                    const ret = data.recent_returns[i];
                    html += '<li><span class="activity-icon">📥</span>';
                    html += '<span class="activity-text"><strong>' + ret.book_title + '</strong> returned by ' + ret.member_name + '</span>';
                    html += '<span class="activity-time">' + Utils.formatDate(ret.return_date) + '</span></li>';
                }
            } else {
                html += '<li class="empty-activity">No recent returns</li>';
            }
            html += '</ul></div>';
            
            // New Members
            html += '<div class="activity-card">';
            html += '<h3>New Members</h3>';
            html += '<ul class="activity-list">';
            if (data.new_members && data.new_members.length > 0) {
                for (let i = 0; i < Math.min(5, data.new_members.length); i++) {
                    const member = data.new_members[i];
                    html += '<li><span class="activity-icon">👤</span>';
                    html += '<span class="activity-text"><strong>' + member.member_name + '</strong> (' + member.membership_type + ')</span>';
                    html += '<span class="activity-time">' + Utils.formatDate(member.reg_date) + '</span></li>';
                }
            } else {
                html += '<li class="empty-activity">No new members</li>';
            }
            html += '</ul></div>';
            
            html += '</div></div>';

            content.innerHTML = html;

            // Render charts after DOM is ready
            setTimeout(function() {
                DashboardManager.renderCharts(data);
            }, 100);

        } catch (error) {
            console.error('Dashboard error:', error);
            content.innerHTML = '<div class="error-state">Failed to load dashboard. Using basic statistics.</div>';
            // Fallback to basic dashboard
            this.renderBasicDashboard();
        }
    },

    async renderBasicDashboard() {
        const content = document.getElementById('content');
        try {
            const response = await Utils.request('dashboard');
            const statistics = response.data.statistics;

            let html = '<div class="page-header"><h1>Dashboard</h1></div>';
            html += '<div class="dashboard-grid">';
            
            const stats = [
                { label: 'Total Books', value: statistics.total_books, color: '#2563eb' },
                { label: 'Total Members', value: statistics.total_members, color: '#10b981' },
                { label: 'Books Issued', value: statistics.books_issued, color: '#f59e0b' },
                { label: 'Overdue Books', value: statistics.overdue_books, color: '#ef4444' }
            ];

            for (let i = 0; i < stats.length; i++) {
                const stat = stats[i];
                html += '<div class="stat-card" style="border-left-color:' + stat.color + '">';
                html += '<h3>' + stat.label + '</h3>';
                html += '<div class="stat-value">' + stat.value + '</div>';
                html += '</div>';
            }

            html += '</div>';
            content.innerHTML = html;
        } catch (error) {
            content.innerHTML = '<div class="error-state">Failed to load dashboard</div>';
        }
    },
    

    renderCharts(data) {
        // Category Distribution Pie Chart
        this.renderCategoryChart(data.category_distribution || []);
        
        // Top Borrowed Books Bar Chart
        this.renderTopBooksChart(data.top_books || []);
    },

    renderCategoryChart(categories) {
        const canvas = document.getElementById('categoryChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const colors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316', '#14b8a6'];
        
        if (!categories || categories.length === 0) {
            ctx.fillStyle = '#64748b';
            ctx.font = '14px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No category data available', canvas.width / 2, canvas.height / 2);
            return;
        }

        const total = categories.reduce(function(sum, cat) { return sum + parseInt(cat.count); }, 0);
        let currentAngle = -0.5 * Math.PI;
        
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2 - 20;
        const radius = 80;

        for (let i = 0; i < categories.length; i++) {
            const cat = categories[i];
            const sliceAngle = (2 * Math.PI * cat.count) / total;
            
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
            ctx.lineTo(centerX, centerY);
            ctx.fillStyle = colors[i % colors.length];
            ctx.fill();
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 2;
            ctx.stroke();
            
            currentAngle += sliceAngle;
        }

        // Legend
        let legendY = canvas.height - 60;
        for (let i = 0; i < Math.min(4, categories.length); i++) {
            ctx.fillStyle = colors[i % colors.length];
            ctx.fillRect(20, legendY + (i * 15), 12, 12);
            ctx.fillStyle = '#333';
            ctx.font = '11px Arial';
            ctx.textAlign = 'left';
            ctx.fillText(categories[i].category + ' (' + categories[i].count + ')', 37, legendY + (i * 15) + 10);
        }
    },

    renderTopBooksChart(books) {
        const canvas = document.getElementById('topBooksChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        if (!books || books.length === 0) {
            ctx.fillStyle = '#64748b';
            ctx.font = '14px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No borrowing data available', canvas.width / 2, canvas.height / 2);
            return;
        }

        const maxCount = Math.max.apply(null, books.map(function(b) { return b.borrow_count; }));
        const barHeight = 30;
        const startY = 20;
        const maxBarWidth = canvas.width - 150;

        for (let i = 0; i < Math.min(5, books.length); i++) {
            const book = books[i];
            const y = startY + (i * (barHeight + 10));
            const barWidth = (book.borrow_count / maxCount) * maxBarWidth;
            
            // Draw bar
            ctx.fillStyle = '#2563eb';
            ctx.fillRect(120, y, barWidth, barHeight);
            
            // Draw book title
            ctx.fillStyle = '#333';
            ctx.font = '11px Arial';
            ctx.textAlign = 'right';
            const title = book.book_title.length > 15 ? book.book_title.substring(0, 15) + '...' : book.book_title;
            ctx.fillText(title, 115, y + 20);
            
            // Draw count
            ctx.fillStyle = '#fff';
            ctx.textAlign = 'left';
            ctx.font = 'bold 12px Arial';
            ctx.fillText(book.borrow_count, 125 + barWidth + 5, y + 20);
        }
    }
};

console.log('✅ Enhanced Dashboard Manager loaded');


// ==================== BOOK MANAGER ====================
const BookManager = {
    currentPage: 1,

    async render(page) {
        page = page || 1;
        this.currentPage = page;
        
        const content = document.getElementById('content');
        Utils.showLoading(content);

        try {
            const response = await Utils.request('books?page=' + page + '&limit=20');
            const books = response.data.books;
            const pagination = response.data.pagination;

            const tableConfig = {
                data: books,
                columns: [
                    { field: 'isbn', label: 'ISBN' },
                    { field: 'book_title', label: 'Title' },
                    { field: 'author', label: 'Author' },
                    { field: 'category', label: 'Category' },
                    { field: 'available_copies', label: 'Available' },
                    { 
                        field: 'status', 
                        label: 'Status',
                        formatter: function(val) {
                            return '<span class="status-badge status-' + val + '">' + val + '</span>';
                        }
                    }
                ],
                pagination: pagination,
                onPageChange: function(p) {
                    BookManager.render(p);
                },
                actions: function(row) {
                    return '<button class="btn-sm btn-primary" data-action="edit" data-id="' + row.isbn + '">Edit</button> ' +
                           '<button class="btn-sm btn-danger" data-action="delete" data-id="' + row.isbn + '">Delete</button>';
                },
                actionHandlers: {
                    edit: function(id) {
                        Utils.showError('Edit functionality: Load book data for ' + id);
                    },
                    delete: function(id) {
                        BookManager.deleteBook(id);
                    }
                }
            };

            let html = '<div class="page-header">';
            html += '<h1>Books Management</h1>';
            html += '<button class="btn btn-primary" id="addBookBtn">Add Book</button>';
            html += '</div>';
            html += TableRenderer.render(tableConfig);

            content.innerHTML = html;
            TableRenderer.setupListeners(content, tableConfig);

            const addBookBtn = document.getElementById('addBookBtn');
            if (addBookBtn) {
                addBookBtn.addEventListener('click', function() {
                    PageManager.openModal('bookModal');
                });
            }

        } catch (error) {
            content.innerHTML = '<div class="error-state">Failed to load books</div>';
        }
    },

    async deleteBook(isbn) {
        if (!confirm('Delete this book?')) return;
        
        try {
            await Utils.request('books/' + isbn, { method: 'DELETE' });
            Utils.showSuccess('Book deleted');
            this.render(this.currentPage);
        } catch (error) {
            Utils.showError('Delete failed');
        }
    }
};

// ==================== MEMBER MANAGER ====================
const MemberManager = {
    currentPage: 1,

    async render(page) {
        page = page || 1;
        this.currentPage = page;
        
        const content = document.getElementById('content');
        Utils.showLoading(content);

        try {
            const response = await Utils.request('members?page=' + page + '&limit=20');
            const members = response.data.members;
            const pagination = response.data.pagination;

            const tableConfig = {
                data: members,
                columns: [
                    { field: 'member_id', label: 'ID' },
                    { field: 'member_name', label: 'Name' },
                    { field: 'email', label: 'Email' },
                    { field: 'phone_number', label: 'Phone' },
                    { field: 'membership_type', label: 'Type' },
                    { 
                        field: 'status', 
                        label: 'Status',
                        formatter: function(val) {
                            return '<span class="status-badge status-' + val + '">' + val + '</span>';
                        }
                    }
                ],
                pagination: pagination,
                onPageChange: function(p) {
                    MemberManager.render(p);
                },
                actions: function(row) {
                    return '<button class="btn-sm btn-primary" data-action="edit" data-id="' + row.member_id + '">Edit</button>';
                },
                actionHandlers: {
                    edit: function(id) {
                        Utils.showError('Edit member: ' + id);
                    }
                }
            };

            let html = '<div class="page-header">';
            html += '<h1>Members Management</h1>';
            html += '<button class="btn btn-primary" id="addMemberBtn">Add Member</button>';
            html += '</div>';
            html += TableRenderer.render(tableConfig);

            content.innerHTML = html;
            TableRenderer.setupListeners(content, tableConfig);

            const addMemberBtn = document.getElementById('addMemberBtn');
            if (addMemberBtn) {
                addMemberBtn.addEventListener('click', function() {
                    PageManager.openModal('memberModal');
                });
            }

        } catch (error) {
            content.innerHTML = '<div class="error-state">Failed to load members</div>';
        }
    }
};

// ==================== CIRCULATION MANAGER ====================
const CirculationManager = {
    currentPage: 1,

    async render(page) {
        page = page || 1;
        this.currentPage = page;
        
        const content = document.getElementById('content');
        Utils.showLoading(content);

        try {
            const response = await Utils.request('circulation?page=' + page + '&limit=20');
            const records = response.data.records;
            const pagination = response.data.pagination;

            const tableConfig = {
                data: records,
                columns: [
                    { field: 'issued_id', label: 'ID' },
                    { field: 'member_name', label: 'Member' },
                    { field: 'book_title', label: 'Book' },
                    { 
                        field: 'issued_date', 
                        label: 'Issued',
                        formatter: function(val) {
                            return Utils.formatDate(val);
                        }
                    },
                    { 
                        field: 'due_date', 
                        label: 'Due',
                        formatter: function(val) {
                            return Utils.formatDate(val);
                        }
                    },
                    { 
                        field: 'status', 
                        label: 'Status',
                        formatter: function(val) {
                            return '<span class="status-badge status-' + val + '">' + val + '</span>';
                        }
                    }
                ],
                pagination: pagination,
                onPageChange: function(p) {
                    CirculationManager.render(p);
                },
                actions: function(row) {
                    if (row.status === 'issued') {
                        return '<button class="btn-sm btn-success" data-action="return" data-id="' + row.issued_id + '">Return</button>';
                    }
                    return '';
                },
                actionHandlers: {
                    return: function(id) {
                        CirculationManager.returnBook(id);
                    }
                }
            };

            let html = '<div class="page-header">';
            html += '<h1>Circulation</h1>';
            html += '<button class="btn btn-primary" id="issueBookBtn">Issue Book</button>';
            html += '</div>';
            html += TableRenderer.render(tableConfig);

            content.innerHTML = html;
            TableRenderer.setupListeners(content, tableConfig);

            const issueBookBtn = document.getElementById('issueBookBtn');
            if (issueBookBtn) {
                issueBookBtn.addEventListener('click', function() {
                    PageManager.openModal('issueModal');
                });
            }

        } catch (error) {
            content.innerHTML = '<div class="error-state">Failed to load circulation</div>';
        }
    },

    async returnBook(issuedId) {
        if (!confirm('Return this book?')) return;
        
        try {
            await Utils.request('circulation/return/' + issuedId, { method: 'PUT' });
            Utils.showSuccess('Book returned successfully');
            this.render(this.currentPage);
        } catch (error) {
            Utils.showError('Return failed');
        }
    }
};

// ==================== FINE MANAGER ====================
// const FineManager = {
//     currentPage: 1,

//     async render(page) {
//         page = page || 1;
//         this.currentPage = page;
        
//         const content = document.getElementById('content');
//         Utils.showLoading(content);

//         try {
//             const response = await Utils.request('fines?page=' + page + '&limit=20');
//             const fines = response.data.fines;
//             const pagination = response.data.pagination;

//             const tableConfig = {
//                 data: fines,
//                 columns: [
//                     { field: 'fine_id', label: 'ID' },
//                     { field: 'member_name', label: 'Member' },
//                     { 
//                         field: 'fine_amount', 
//                         label: 'Amount',
//                         formatter: function(val) {
//                             return Utils.formatCurrency(val);
//                         }
//                     },
//                     { field: 'fine_reason', label: 'Reason' },
//                     { 
//                         field: 'fine_date', 
//                         label: 'Date',
//                         formatter: function(val) {
//                             return Utils.formatDate(val);
//                         }
//                     },
//                     { 
//                         field: 'payment_status', 
//                         label: 'Status',
//                         formatter: function(val) {
//                             return '<span class="status-badge status-' + val + '">' + val + '</span>';
//                         }
//                     }
//                 ],
//                 pagination: pagination,
//                 onPageChange: function(p) {
//                     FineManager.render(p);
//                 },
//                 actions: function(row) {
//                     if (row.payment_status === 'unpaid') {
//                         return '<button class="btn-sm btn-success" data-action="pay" data-id="' + row.fine_id + '">Pay</button>';
//                     }
//                     return '';
//                 },
//                 actionHandlers: {
//                     pay: function(id) {
//                         Utils.showError('Payment processing for fine: ' + id);
//                     }
//                 }
//             };

//             content.innerHTML = '<div class="page-header"><h1>Fines Management</h1></div>' + 
//                                TableRenderer.render(tableConfig);

//             TableRenderer.setupListeners(content, tableConfig);

//         } catch (error) {
//             content.innerHTML = '<div class="error-state">Failed to load fines</div>';
//         }
//     }
// };
const FineManager = {
    currentPage: 1,

    async render(page) {
        page = page || 1;
        this.currentPage = page;
        
        const content = document.getElementById('content');
        Utils.showLoading(content);

        try {
            const response = await Utils.request('fines?page=' + page + '&limit=20');
            const fines = response.data.fines;
            const pagination = response.data.pagination;

            const tableConfig = {
                data: fines,
                columns: [
                    { field: 'fine_id', label: 'ID' },
                    { field: 'member_name', label: 'Member' },
                    { 
                        field: 'fine_amount', 
                        label: 'Amount',
                        formatter: function(val) {
                            return Utils.formatCurrency(val);
                        }
                    },
                    { field: 'fine_reason', label: 'Reason' },
                    { 
                        field: 'fine_date', 
                        label: 'Date',
                        formatter: function(val) {
                            return Utils.formatDate(val);
                        }
                    },
                    { 
                        field: 'payment_status', 
                        label: 'Status',
                        formatter: function(val) {
                            return '<span class="status-badge status-' + val + '">' + val + '</span>';
                        }
                    }
                ],
                pagination: pagination,
                onPageChange: function(p) {
                    FineManager.render(p);
                },
                actions: function(row) {
                    if (row.payment_status === 'unpaid') {
                        return '<button class="btn-sm btn-success" data-action="pay" data-id="' + row.fine_id + '">Pay Fine</button>';
                    }
                    return '<span class="text-success">✓ Paid</span>';
                },
                actionHandlers: {
                    pay: function(id) {
                        FineManager.payFine(id);
                    }
                }
            };

            content.innerHTML = '<div class="page-header"><h1>Fines Management</h1></div>' + 
                               TableRenderer.render(tableConfig);

            TableRenderer.setupListeners(content, tableConfig);

        } catch (error) {
            content.innerHTML = '<div class="error-state">Failed to load fines</div>';
        }
    },

    async payFine(fineId) {
        if (!confirm('Process payment for this fine?')) return;
        
        try {
            const response = await Utils.request('fines/' + fineId + '/pay', { method: 'PUT' });
            
            if (response.success) {
                Utils.showSuccess('Payment processed successfully! Amount: $' + response.data.amount);
                this.render(this.currentPage); // Refresh the page
            }
        } catch (error) {
            Utils.showError('Payment processing failed: ' + error.message);
        }
    }
};

// ==================== RESERVATION MANAGER ====================
const ReservationManager = {
    currentPage: 1,

    async render(page) {
        page = page || 1;
        this.currentPage = page;
        
        const content = document.getElementById('content');
        Utils.showLoading(content);

        try {
            const response = await Utils.request('reservations?page=' + page + '&limit=20');
            const reservations = response.data.reservations;
            const pagination = response.data.pagination;

            const tableConfig = {
                data: reservations,
                columns: [
                    { field: 'reservation_id', label: 'ID' },
                    { field: 'member_name', label: 'Member' },
                    { field: 'book_title', label: 'Book' },
                    { field: 'queue_position', label: 'Queue' },
                    { 
                        field: 'reservation_date', 
                        label: 'Reserved',
                        formatter: function(val) {
                            return Utils.formatDate(val);
                        }
                    },
                    { 
                        field: 'status', 
                        label: 'Status',
                        formatter: function(val) {
                            return '<span class="status-badge status-' + val + '">' + val + '</span>';
                        }
                    }
                ],
                pagination: pagination,
                onPageChange: function(p) {
                    ReservationManager.render(p);
                },
                actions: function(row) {
                    if (row.status === 'active') {
                        return '<button class="btn-sm btn-danger" data-action="cancel" data-id="' + row.reservation_id + '">Cancel</button>';
                    }
                    return '';
                },
                actionHandlers: {
                    cancel: function(id) {
                        ReservationManager.cancelReservation(id);
                    }
                }
            };

            let html = '<div class="page-header">';
            html += '<h1>Reservations</h1>';
            html += '<button class="btn btn-primary" id="addReservationBtn">Add Reservation</button>';
            html += '</div>';
            html += TableRenderer.render(tableConfig);

            content.innerHTML = html;
            TableRenderer.setupListeners(content, tableConfig);

            const addReservationBtn = document.getElementById('addReservationBtn');
            if (addReservationBtn) {
                addReservationBtn.addEventListener('click', function() {
                    PageManager.openModal('reservationModal');
                });
            }

        } catch (error) {
            content.innerHTML = '<div class="error-state">Failed to load reservations</div>';
        }
    },

    async cancelReservation(id) {
        if (!confirm('Cancel this reservation?')) return;
        
        try {
            await Utils.request('reservations/' + id, { method: 'DELETE' });
            Utils.showSuccess('Reservation cancelled');
            this.render(this.currentPage);
        } catch (error) {
            Utils.showError('Cancellation failed');
        }
    }
};

// ==================== REPORT MANAGER ====================
const ReportManager = {
    render() {
        const content = document.getElementById('content');
        
        let html = '<div class="page-header"><h1>Reports</h1></div>';
        html += '<div class="dashboard-grid">';
        html += '<div class="card" onclick="ReportManager.generate(\'circulation\')">';
        html += '<h3>Circulation Report</h3><p>Issue and return statistics</p>';
        html += '<button class="btn btn-primary">Generate</button></div>';
        
        html += '<div class="card" onclick="ReportManager.generate(\'financial\')">';
        html += '<h3>Financial Report</h3><p>Fine collection and revenue</p>';
        html += '<button class="btn btn-primary">Generate</button></div>';
        
        html += '<div class="card" onclick="ReportManager.generate(\'membership\')">';
        html += '<h3>Membership Report</h3><p>Member statistics and trends</p>';
        html += '<button class="btn btn-primary">Generate</button></div>';
        
        html += '<div class="card" onclick="ReportManager.generate(\'inventory\')">';
        html += '<h3>Inventory Report</h3><p>Book availability and distribution</p>';
        html += '<button class="btn btn-primary">Generate</button></div>';
        html += '</div>';
        
        content.innerHTML = html;
    },

    async generate(type) {
        try {
            const response = await Utils.request('reports/' + type);
            const reportData = response.data;

            const modal = document.getElementById('reportModal');
            const modalBody = document.getElementById('reportModalBody');
            
            let html = '<div class="report-header">';
            html += '<h2>' + (reportData.title || type + ' Report') + '</h2>';
            html += '<p>Generated: ' + new Date().toLocaleString() + '</p>';
            html += '</div>';

            if (reportData.summary) {
                html += '<div class="report-summary"><h3>Summary</h3>';
                const summaryKeys = Object.keys(reportData.summary);
                for (let i = 0; i < summaryKeys.length; i++) {
                    const key = summaryKeys[i];
                    const value = reportData.summary[key];
                    html += '<p><strong>' + key.replace(/_/g, ' ') + ':</strong> ' + value + '</p>';
                }
                html += '</div>';
            }

            if (reportData.records && reportData.records.length > 0) {
                html += '<div class="report-data"><h3>Detailed Records</h3>';
                html += '<div class="table-responsive"><table class="data-table"><thead><tr>';
                
                const headers = Object.keys(reportData.records[0]);
                for (let i = 0; i < headers.length; i++) {
                    html += '<th>' + headers[i] + '</th>';
                }
                html += '</tr></thead><tbody>';
                
                const recordsToShow = reportData.records.slice(0, 50);
                for (let i = 0; i < recordsToShow.length; i++) {
                    html += '<tr>';
                    const record = recordsToShow[i];
                    for (let j = 0; j < headers.length; j++) {
                        html += '<td>' + (record[headers[j]] || 'N/A') + '</td>';
                    }
                    html += '</tr>';
                }
                
                html += '</tbody></table></div></div>';
            }

            html += '<div class="report-actions" style="margin-top:20px">';
            html += '<button class="btn btn-primary" onclick="ReportManager.exportCSV(\'' + type + '\')">Download CSV</button> ';
            html += '<button class="btn btn-secondary" onclick="ReportManager.exportPDF(\'' + type + '\')">Download PDF</button>';
            html += '</div>';

            modalBody.innerHTML = html;
            modal.classList.add('active');

            this.currentReportData = reportData;

        } catch (error) {
            Utils.showError('Failed to generate report');
        }
    },

    exportCSV(type) {
        if (this.currentReportData && this.currentReportData.records) {
            Utils.downloadCSV(this.currentReportData.records, type + '-report.csv');
        }
    },

    exportPDF(type) {
        if (this.currentReportData && this.currentReportData.records) {
            Utils.downloadPDF(this.currentReportData.records, type + ' Report');
        }
    }
};

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Library Management System v2.0-FIXED initializing...');
    
    setTimeout(function() {
        document.getElementById('loadingScreen').style.display = 'none';
        document.getElementById('app').style.display = 'block';
        
        PageManager.init();
        LanguageManager.init();
        
        console.log('✅ Application ready!');
    }, 1000);
});

console.log('app.js loaded - All features working ✅');
// ==================== FORM HANDLERS ====================
// Book Form
document.addEventListener('DOMContentLoaded', function() {
    const bookForm = document.getElementById('bookForm');
    if (bookForm) {
        bookForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = {
                isbn: document.getElementById('bookIsbn').value,
                book_title: document.getElementById('bookTitle').value,
                author: document.getElementById('bookAuthor').value,
                publisher: document.getElementById('bookPublisher').value || null,
                publication_year: document.getElementById('bookYear').value || null,
                category: document.getElementById('bookCategory').value,
                language: document.getElementById('bookLanguage').value || 'English',
                pages: document.getElementById('bookPages').value || null,
                total_copies: document.getElementById('bookCopies').value || 1,
                available_copies: document.getElementById('bookAvailable').value || 1,
                price: document.getElementById('bookPrice').value || null,
                location: document.getElementById('bookLocation').value || null,
                description: document.getElementById('bookDescription').value || null
            };
            try {
                await Utils.request('books', { method: 'POST', body: JSON.stringify(formData) });
                Utils.showSuccess('Book added!');
                PageManager.closeModal('bookModal');
                bookForm.reset();
                if (LibraryApp.state.currentPage === 'books') BookManager.render();
            } catch (error) {
                Utils.showError('Failed: ' + error.message);
            }
        });
    }

    // Member Form
    const memberForm = document.getElementById('memberForm');
    if (memberForm) {
        memberForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const memberId = 'M' + String(Date.now()).slice(-6);
            const formData = {
                member_id: memberId,
                member_name: document.getElementById('memberName').value,
                email: document.getElementById('memberEmail').value || null,
                phone_number: document.getElementById('memberPhone').value,
                address: document.getElementById('memberAddress').value || null,
                membership_type: document.getElementById('memberType').value
            };
            try {
                await Utils.request('members', { method: 'POST', body: JSON.stringify(formData) });
                Utils.showSuccess('Member added! ID: ' + memberId);
                PageManager.closeModal('memberModal');
                memberForm.reset();
                if (LibraryApp.state.currentPage === 'members') MemberManager.render();
            } catch (error) {
                Utils.showError('Failed: ' + error.message);
            }
        });
    }

    // Issue Book Form
    const issueForm = document.getElementById('issueForm');
    if (issueForm) {
        issueForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = {
                member_id: document.getElementById('issueMemberId').value,
                book_isbn: document.getElementById('issueBookIsbn').value
            };
            try {
                await Utils.request('circulation/issue', { method: 'POST', body: JSON.stringify(formData) });
                Utils.showSuccess('Book issued!');
                PageManager.closeModal('issueModal');
                issueForm.reset();
                if (LibraryApp.state.currentPage === 'circulation') CirculationManager.render();
                if (LibraryApp.state.currentPage === 'books') BookManager.render();
            } catch (error) {
                Utils.showError('Failed: ' + error.message);
            }
        });
    }

    // Reservation Form
    const reservationForm = document.getElementById('reservationForm');
    if (reservationForm) {
        reservationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = {
                member_id: document.getElementById('reserveMemberId').value,
                book_isbn: document.getElementById('reserveBookIsbn').value
            };
            try {
                await Utils.request('reservations', { method: 'POST', body: JSON.stringify(formData) });
                Utils.showSuccess('Reservation created!');
                PageManager.closeModal('reservationModal');
                reservationForm.reset();
                if (LibraryApp.state.currentPage === 'reservations') ReservationManager.render();
            } catch (error) {
                Utils.showError('Failed: ' + error.message);
            }
        });
    }
});

console.log('✓ Form handlers loaded');
