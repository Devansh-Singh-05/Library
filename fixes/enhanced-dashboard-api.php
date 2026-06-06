<?php
/**
 * ENHANCED DASHBOARD API ENDPOINT
 * Add this to your api.php file in the handleDashboard() method
 */

// Replace your existing handleDashboard() method with this:

private function handleDashboard() {
    if ($this->method !== 'GET') $this->sendError('Method not allowed', 405);
    
    // Check if enhanced dashboard is requested
    if (isset($this->params[0]) && $this->params[0] === 'enhanced') {
        return $this->getEnhancedDashboard();
    }
    
    // Basic dashboard (fallback)
    return $this->getBasicDashboard();
}

private function getBasicDashboard() {
    try {
        $stats = array(
            'total_books' => $this->db->fetch("SELECT COUNT(*) as count FROM books WHERE status = 'active'")['count'],
            'total_members' => $this->db->fetch("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'],
            'books_issued' => $this->db->fetch("SELECT COUNT(*) as count FROM issued_status WHERE status = 'issued'")['count'],
            'overdue_books' => $this->db->fetch("SELECT COUNT(*) as count FROM issued_status WHERE status = 'issued' AND due_date < CURRENT_DATE")['count']
        );
        
        $this->sendSuccess('Dashboard data retrieved', array('statistics' => $stats));
    } catch (Exception $e) {
        $this->sendError('Failed to load dashboard: ' . $e->getMessage(), 500);
    }
}

private function getEnhancedDashboard() {
    try {
        // Basic statistics
        $total_books = $this->db->fetch("SELECT COUNT(*) as count FROM books WHERE status = 'active'")['count'];
        $total_members = $this->db->fetch("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'];
        $books_issued = $this->db->fetch("SELECT COUNT(*) as count FROM issued_status WHERE status = 'issued'")['count'];
        $overdue_books = $this->db->fetch("SELECT COUNT(*) as count FROM issued_status WHERE status = 'issued' AND due_date < CURRENT_DATE")['count'];
        
        // Additional statistics
        $total_reservations = $this->db->fetch("SELECT COUNT(*) as count FROM reservations WHERE status = 'active'")['count'];
        $available_books = $this->db->fetch("SELECT SUM(available_copies) as count FROM books WHERE status = 'active'")['count'] ?? 0;
        
        // Fines statistics
        $fines_collected = $this->db->fetch("SELECT SUM(fine_amount) as total FROM fines WHERE payment_status = 'paid'")['total'] ?? 0;
        $unpaid_fines = $this->db->fetch("SELECT SUM(fine_amount) as total FROM fines WHERE payment_status = 'unpaid'")['total'] ?? 0;
        
        // New this month
        $new_books_this_month = $this->db->fetch("SELECT COUNT(*) as count FROM books WHERE DATE_FORMAT(reg_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')")['count'] ?? 0;
        $new_members_this_month = $this->db->fetch("SELECT COUNT(*) as count FROM members WHERE DATE_FORMAT(reg_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')")['count'];
        
        // Category distribution
        $category_distribution = $this->db->fetchAll("SELECT category, COUNT(*) as count FROM books WHERE status = 'active' GROUP BY category ORDER BY count DESC LIMIT 8");
        
        // Top 5 most borrowed books
        $top_books = $this->db->fetchAll("SELECT b.book_title, b.author, COUNT(i.issued_id) as borrow_count 
                                          FROM books b 
                                          JOIN issued_status i ON b.isbn = i.issued_book_isbn 
                                          GROUP BY b.isbn 
                                          ORDER BY borrow_count DESC 
                                          LIMIT 5");
        
        // Recent issues (last 5)
        $recent_issues = $this->db->fetchAll("SELECT i.issued_date, b.book_title, m.member_name 
                                               FROM issued_status i 
                                               JOIN books b ON i.issued_book_isbn = b.isbn 
                                               JOIN members m ON i.issued_member_id = m.member_id 
                                               ORDER BY i.issued_date DESC 
                                               LIMIT 5");
        
        // Recent returns (last 5)
        $recent_returns = $this->db->fetchAll("SELECT i.return_date, b.book_title, m.member_name 
                                                FROM issued_status i 
                                                JOIN books b ON i.issued_book_isbn = b.isbn 
                                                JOIN members m ON i.issued_member_id = m.member_id 
                                                WHERE i.status = 'returned' AND i.return_date IS NOT NULL 
                                                ORDER BY i.return_date DESC 
                                                LIMIT 5");
        
        // New members (last 5)
        $new_members = $this->db->fetchAll("SELECT member_name, membership_type, reg_date 
                                             FROM members 
                                             ORDER BY reg_date DESC 
                                             LIMIT 5");
        
        $data = array(
            'total_books' => $total_books,
            'total_members' => $total_members,
            'books_issued' => $books_issued,
            'overdue_books' => $overdue_books,
            'total_reservations' => $total_reservations,
            'available_books' => $available_books,
            'fines_collected' => number_format($fines_collected, 2),
            'unpaid_fines' => number_format($unpaid_fines, 2),
            'new_books_this_month' => $new_books_this_month,
            'new_members_this_month' => $new_members_this_month,
            'category_distribution' => $category_distribution,
            'top_books' => $top_books,
            'recent_issues' => $recent_issues,
            'recent_returns' => $recent_returns,
            'new_members' => $new_members
        );
        
        $this->sendSuccess('Enhanced dashboard data retrieved', $data);
    } catch (Exception $e) {
        $this->sendError('Failed to load enhanced dashboard: ' . $e->getMessage(), 500);
    }
}

?>
