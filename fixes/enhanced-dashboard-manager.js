/**
 * ENHANCED DASHBOARD MANAGER - Replace your existing DashboardManager
 * Features: More stats, charts, recent activity, quick actions
 */

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
