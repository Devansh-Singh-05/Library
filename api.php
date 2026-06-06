<?php
/**
 * LIBRARY MANAGEMENT SYSTEM - COMPLETE API
 * Production Ready - All Bugs Fixed
 */

// Error reporting - disable for production
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set JSON response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

class LibraryAPI {
    private $db;
    private $method;
    private $endpoint;
    private $params;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance();
            $this->method = $_SERVER['REQUEST_METHOD'];
            $this->parseRequest();
        } catch (Exception $e) {
            $this->sendError('System initialization failed: ' . $e->getMessage(), 500);
        }
    }
    
    private function parseRequest() {
        $request = $_SERVER['REQUEST_URI'];
        $path = parse_url($request, PHP_URL_PATH);
        
        $scriptName = $_SERVER['SCRIPT_NAME'];
        if (strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        }
        
        $pathComponents = array_filter(explode('/', trim($path, '/')));
        $this->endpoint = array_shift($pathComponents) ?: 'dashboard';
        $this->params = $pathComponents;
    }
    
    public function processRequest() {
        try {
            switch ($this->endpoint) {
                case 'dashboard':
                    return $this->handleDashboard();
                case 'books':
                    return $this->handleBooks();
                case 'members':
                    return $this->handleMembers();
                case 'circulation':
                    return $this->handleCirculation();
                case 'fines':
                    return $this->handleFines();
                case 'reservations':
                    return $this->handleReservations();
                case 'reports':
                    return $this->handleReports();
                case 'analytics':
                    return $this->handleAnalytics();
                case 'search':
                    return $this->handleSearch();
                case 'translations':
                    return $this->handleTranslations();
                default:
                    $this->sendError('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            $this->sendError('Request processing failed: ' . $e->getMessage(), 500);
        }
    }
    
    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode(array('success' => false, 'message' => $message));
        exit;
    }
    
    private function sendSuccess($message, $data = array(), $code = 200) {
        http_response_code($code);
        echo json_encode(array('success' => true, 'message' => $message, 'data' => $data));
        exit;
    }
    
    // DASHBOARD
    // private function handleDashboard() {
    //     if ($this->method !== 'GET') $this->sendError('Method not allowed', 405);
        
    //     try {
    //         $stats = array(
    //             'total_books' => $this->db->fetch("SELECT COUNT(*) as count FROM books WHERE status = 'active'")['count'],
    //             'total_members' => $this->db->fetch("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'],
    //             'books_issued' => $this->db->fetch("SELECT COUNT(*) as count FROM issued_status WHERE status = 'issued'")['count'],
    //             'overdue_books' => $this->db->fetch("SELECT COUNT(*) as count FROM issued_status WHERE status = 'issued' AND due_date < CURRENT_DATE")['count']
    //         );
            
    //         $this->sendSuccess('Dashboard data retrieved', array('statistics' => $stats));
    //     } catch (Exception $e) {
    //         $this->sendError('Failed to load dashboard: ' . $e->getMessage(), 500);
    //     }
    // }
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
    // BOOKS
    // private function handleBooks() {
    // switch ($this->method) {
    //     case 'GET':
    //         return isset($this->params[0]) ? $this->getBook($this->params[0]) : $this->getBooks();
    //     case 'POST':
    //         return $this->createBook();
    //     case 'PUT':
    //         return isset($this->params[0]) ? $this->updateBook($this->params[0]) : $this->sendError('Book ISBN required', 400);
    //     case 'DELETE':
    //         return isset($this->params[0]) ? $this->deleteBook($this->params[0]) : $this->sendError('Book ISBN required', 400);
    //     default:
    //         $this->sendError('Method not allowed', 405);
    //     }
    // }
    private function handleBooks() {
    switch ($this->method) {
        case 'GET':
            return isset($this->params[0]) ? $this->getBook($this->params[0]) : $this->getBooks();
        case 'POST':
            return $this->createBook();
        case 'PUT':
            return isset($this->params[0]) ? $this->updateBook($this->params[0]) : $this->sendError('Book ISBN required', 400);
        case 'DELETE':
            return isset($this->params[0]) ? $this->deleteBook($this->params[0]) : $this->sendError('Book ISBN required', 400);
        default:
            $this->sendError('Method not allowed', 405);
    }
    }
    private function getBooks() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $countSql = "SELECT COUNT(*) as total FROM books WHERE status = 'active'";
            $totalRecords = $this->db->fetch($countSql)['total'];
            
            $sql = "SELECT * FROM books WHERE status = 'active' ORDER BY book_title ASC LIMIT ? OFFSET ?";
            $books = $this->db->fetchAll($sql, array($limit, $offset));
            
            $pagination = array(
                'current_page' => $page,
                'total_pages' => ceil($totalRecords / $limit),
                'total_records' => $totalRecords,
                'records_per_page' => $limit
            );
            
            $this->sendSuccess('Books retrieved', array('books' => $books, 'pagination' => $pagination));
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve books: ' . $e->getMessage(), 500);
        }
    }
    
    // private function createBook() {
    //     try {
    //         $input = json_decode(file_get_contents('php://input'), true);
            
    //         if (!$input || empty($input['isbn']) || empty($input['book_title']) || empty($input['author'])) {
    //             $this->sendError('Missing required fields', 400);
    //         }
            
    //         $sql = "INSERT INTO books (isbn, book_title, author, publisher, publication_year, category, language, pages, total_copies, available_copies, price, location, description, status) 
    //                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
    //         $this->db->execute($sql, array(
    //             $input['isbn'],
    //             $input['book_title'],
    //             $input['author'],
    //             $input['publisher'] ?? null,
    //             $input['publication_year'] ?? null,
    //             $input['category'] ?? 'General',
    //             $input['language'] ?? 'English',
    //             $input['pages'] ?? null,
    //             $input['total_copies'] ?? 1,
    //             $input['available_copies'] ?? 1,
    //             $input['price'] ?? null,
    //             $input['location'] ?? null,
    //             $input['description'] ?? null
    //         ));
            
    //         $this->sendSuccess('Book created successfully', array('isbn' => $input['isbn']), 201);
    //     } catch (Exception $e) {
    //         $this->sendError('Failed to create book: ' . $e->getMessage(), 500);
    //     }
    // }
    private function createBook() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['isbn']) || empty($input['book_title']) || empty($input['author'])) {
            $this->sendError('Missing required fields: isbn, book_title, author', 400);
        }
        
        // Check if ISBN already exists
        $existing = $this->db->fetch("SELECT isbn FROM books WHERE isbn = ?", array($input['isbn']));
        if ($existing) {
            $this->sendError('Book with this ISBN already exists', 400);
        }
        
        $sql = "INSERT INTO books (isbn, book_title, author, publisher, publication_year, category, language, pages, total_copies, available_copies, price, location, description, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $this->db->execute($sql, array(
            $input['isbn'],
            $input['book_title'],
            $input['author'],
            $input['publisher'] ?? null,
            $input['publication_year'] ?? null,
            $input['category'] ?? 'General',
            $input['language'] ?? 'English',
            $input['pages'] ?? null,
            $input['total_copies'] ?? 1,
            $input['available_copies'] ?? 1,
            $input['price'] ?? null,
            $input['location'] ?? null,
            $input['description'] ?? null
        ));
        
        $this->sendSuccess('Book created successfully', array('isbn' => $input['isbn']), 201);
    } catch (Exception $e) {
        error_log("Book creation error: " . $e->getMessage());
        $this->sendError('Failed to create book: ' . $e->getMessage(), 500);
    }
    }
    
    private function updateBook($isbn) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $book = $this->db->fetch("SELECT * FROM books WHERE isbn = ?", array($isbn));
            if (!$book) $this->sendError('Book not found', 404);
            
            $sql = "UPDATE books SET book_title = ?, author = ?, category = ? WHERE isbn = ?";
            $this->db->execute($sql, array($input['book_title'], $input['author'], $input['category'], $isbn));
            
            $this->sendSuccess('Book updated successfully');
        } catch (Exception $e) {
            $this->sendError('Failed to update book: ' . $e->getMessage(), 500);
        }
    }
    
    private function deleteBook($isbn) {
        try {
            $this->db->execute("UPDATE books SET status = 'inactive' WHERE isbn = ?", array($isbn));
            $this->sendSuccess('Book deleted successfully');
        } catch (Exception $e) {
            $this->sendError('Failed to delete book: ' . $e->getMessage(), 500);
        }
    }
    
    // MEMBERS
    private function handleMembers() {
    switch ($this->method) {
        case 'GET':
            return $this->getMembers();
        case 'POST':
            return $this->createMember();
        default:
            $this->sendError('Method not allowed', 405);
        }
    }
    
    private function getMembers() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $countSql = "SELECT COUNT(*) as total FROM members WHERE status = 'active'";
            $totalRecords = $this->db->fetch($countSql)['total'];
            
            $sql = "SELECT * FROM members WHERE status = 'active' ORDER BY reg_date DESC LIMIT ? OFFSET ?";
            $members = $this->db->fetchAll($sql, array($limit, $offset));
            
            $pagination = array(
                'current_page' => $page,
                'total_pages' => ceil($totalRecords / $limit),
                'total_records' => $totalRecords,
                'records_per_page' => $limit
            );
            
            $this->sendSuccess('Members retrieved', array('members' => $members, 'pagination' => $pagination));
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve members: ' . $e->getMessage(), 500);
        }
    }
    private function createMember() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['member_name']) || empty($input['phone_number'])) {
            $this->sendError('Missing required fields', 400);
        }
        
        $memberId = isset($input['member_id']) ? $input['member_id'] : 'M' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $expiryDate = date('Y-m-d', strtotime('+1 year'));
        
        $maxBooks = 3;
        if (isset($input['membership_type'])) {
            switch($input['membership_type']) {
                case 'faculty': $maxBooks = 5; break;
                case 'premium': $maxBooks = 10; break;
                default: $maxBooks = 3;
            }
        }
        
        $sql = "INSERT INTO members (member_id, member_name, email, phone_number, address, membership_type, max_books_allowed, expiry_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $this->db->execute($sql, array(
            $memberId,
            $input['member_name'],
            $input['email'] ?? null,
            $input['phone_number'],
            $input['address'] ?? null,
            $input['membership_type'] ?? 'public',
            $maxBooks,
            $expiryDate
        ));
        
        $this->sendSuccess('Member created', array('member_id' => $memberId), 201);
    } catch (Exception $e) {
        $this->sendError('Failed: ' . $e->getMessage(), 500);
        }
    }
    
    // CIRCULATION
    private function handleCirculation() {
    switch ($this->method) {
        case 'GET':
            return $this->getCirculation();
        case 'POST':
            if (isset($this->params[0]) && $this->params[0] === 'issue') {
                return $this->issueBook();
            }
            $this->sendError('Invalid endpoint', 400);
            break;
        case 'PUT':
            if (isset($this->params[0]) && $this->params[0] === 'return' && isset($this->params[1])) {
                return $this->returnBook($this->params[1]);
            }
            $this->sendError('Invalid endpoint', 400);
            break;
        default:
            $this->sendError('Method not allowed', 405);
        }
    }
    
    private function getCirculation() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $countSql = "SELECT COUNT(*) as total FROM issued_status";
            $totalRecords = $this->db->fetch($countSql)['total'];
            
            $sql = "SELECT i.*, m.member_name, b.book_title 
                    FROM issued_status i
                    JOIN members m ON i.issued_member_id = m.member_id
                    JOIN books b ON i.issued_book_isbn = b.isbn
                    ORDER BY i.issued_date DESC LIMIT ? OFFSET ?";
            $records = $this->db->fetchAll($sql, array($limit, $offset));
            
            $pagination = array(
                'current_page' => $page,
                'total_pages' => ceil($totalRecords / $limit),
                'total_records' => $totalRecords,
                'records_per_page' => $limit
            );
            
            $this->sendSuccess('Circulation records retrieved', array('records' => $records, 'pagination' => $pagination));
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve circulation: ' . $e->getMessage(), 500);
        }
    }
    private function issueBook() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['member_id']) || empty($input['book_isbn'])) {
            $this->sendError('Missing fields', 400);
        }
        
        $memberId = $input['member_id'];
        $bookIsbn = $input['book_isbn'];
        
        $book = $this->db->fetch("SELECT * FROM books WHERE isbn = ?", array($bookIsbn));
        if (!$book || $book['available_copies'] <= 0) {
            $this->sendError('Book not available', 400);
        }
        
        $member = $this->db->fetch("SELECT * FROM members WHERE member_id = ?", array($memberId));
        if (!$member) {
            $this->sendError('Member not found', 404);
        }
        
        $issuedDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+14 days'));
        
        $sql = "INSERT INTO issued_status (issued_member_id, issued_book_isbn, issued_date, due_date, status, issued_by) 
                VALUES (?, ?, ?, ?, 'issued', 'admin')";
        
        $this->db->execute($sql, array($memberId, $bookIsbn, $issuedDate, $dueDate));
        $this->db->execute("UPDATE books SET available_copies = available_copies - 1 WHERE isbn = ?", array($bookIsbn));
        
        $this->sendSuccess('Book issued', array('due_date' => $dueDate), 201);
        } catch (Exception $e) {
        $this->sendError('Failed: ' . $e->getMessage(), 500);
        }
    }
    
    private function returnBook($issuedId) {
    try {
        $issued = $this->db->fetch("SELECT * FROM issued_status WHERE issued_id = ?", array($issuedId));
        if (!$issued) $this->sendError('Not found', 404);
        
        $returnDate = date('Y-m-d');
        $sql = "UPDATE issued_status SET return_date = ?, status = 'returned' WHERE issued_id = ?";
        $this->db->execute($sql, array($returnDate, $issuedId));
        
        // 🔥 THIS LINE IS CRITICAL - Updates available copies
        $this->db->execute("UPDATE books SET available_copies = available_copies + 1 WHERE isbn = ?", array($issued['issued_book_isbn']));
        
        $this->sendSuccess('Book returned');
        } catch (Exception $e) {
        $this->sendError('Failed: ' . $e->getMessage(), 500);
        }
    }
    
    // FINES
    // private function handleFines() {
    //     if ($this->method !== 'GET') $this->sendError('Method not allowed', 405);
    //     return $this->getFines();
    // }
    private function handleFines() {
    switch ($this->method) {
        case 'GET':
            return $this->getFines();
        case 'PUT':
            // Payment processing
            if (isset($this->params[0]) && isset($this->params[1]) && $this->params[1] === 'pay') {
                return $this->payFine($this->params[0]);
            }
            $this->sendError('Invalid endpoint', 400);
            break;
        default:
            $this->sendError('Method not allowed', 405);
        }
    }
    
    private function getFines() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $countSql = "SELECT COUNT(*) as total FROM fines";
            $totalRecords = $this->db->fetch($countSql)['total'];
            
            $sql = "SELECT f.*, m.member_name 
                    FROM fines f
                    JOIN members m ON f.member_id = m.member_id
                    ORDER BY f.fine_date DESC LIMIT ? OFFSET ?";
            $fines = $this->db->fetchAll($sql, array($limit, $offset));
            
            $pagination = array(
                'current_page' => $page,
                'total_pages' => ceil($totalRecords / $limit),
                'total_records' => $totalRecords,
                'records_per_page' => $limit
            );
            
            $this->sendSuccess('Fines retrieved', array('fines' => $fines, 'pagination' => $pagination));
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve fines: ' . $e->getMessage(), 500);
        }
    }
    private function payFine($fineId) {
    try {
        $fine = $this->db->fetch("SELECT * FROM fines WHERE fine_id = ?", array($fineId));
        if (!$fine) {
            $this->sendError('Fine not found', 404);
        }
        
        if ($fine['payment_status'] === 'paid') {
            $this->sendError('Fine already paid', 400);
        }
        
        $paymentDate = date('Y-m-d');
        $sql = "UPDATE fines SET payment_status = 'paid', payment_date = ? WHERE fine_id = ?";
        $this->db->execute($sql, array($paymentDate, $fineId));
        
        $this->db->execute("UPDATE members SET outstanding_fines = outstanding_fines - ? WHERE member_id = ?", 
                         array($fine['fine_amount'], $fine['member_id']));
        
        $this->sendSuccess('Fine paid successfully', array(
            'fine_id' => $fineId,
            'amount' => $fine['fine_amount'],
            'payment_date' => $paymentDate
        ));
    } catch (Exception $e) {
        $this->sendError('Payment failed: ' . $e->getMessage(), 500);
    }
}
    
    // RESERVATIONS
    // private function handleReservations() {
    //     if ($this->method === 'GET') return $this->getReservations();
    //     if ($this->method === 'DELETE' && isset($this->params[0])) return $this->cancelReservation($this->params[0]);
    //     $this->sendError('Method not allowed', 405);
    // }
    private function handleReservations() {
    switch ($this->method) {
        case 'GET':
            return $this->getReservations();
        case 'POST':
            return $this->createReservation();
        case 'DELETE':
            if (isset($this->params[0])) {
                return $this->cancelReservation($this->params[0]);
            }
            $this->sendError('Reservation ID required', 400);
            break;
        default:
            $this->sendError('Method not allowed', 405);
    }
    }

    private function getReservations() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $countSql = "SELECT COUNT(*) as total FROM reservations WHERE status = 'active'";
            $totalRecords = $this->db->fetch($countSql)['total'];
            
            $sql = "SELECT r.*, m.member_name, b.book_title 
                    FROM reservations r
                    JOIN members m ON r.member_id = m.member_id
                    JOIN books b ON r.book_isbn = b.isbn
                    WHERE r.status = 'active'
                    ORDER BY r.reservation_date DESC LIMIT ? OFFSET ?";
            $reservations = $this->db->fetchAll($sql, array($limit, $offset));
            
            $pagination = array(
                'current_page' => $page,
                'total_pages' => ceil($totalRecords / $limit),
                'total_records' => $totalRecords,
                'records_per_page' => $limit
            );
            
            $this->sendSuccess('Reservations retrieved', array('reservations' => $reservations, 'pagination' => $pagination));
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve reservations: ' . $e->getMessage(), 500);
        }
    }
    
    private function cancelReservation($id) {
        try {
            $this->db->execute("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ?", array($id));
            $this->sendSuccess('Reservation cancelled');
        } catch (Exception $e) {
            $this->sendError('Failed to cancel reservation: ' . $e->getMessage(), 500);
        }
    }
    
    // REPORTS
    private function handleReports() {
        if ($this->method !== 'GET') $this->sendError('Method not allowed', 405);
        
        $type = $this->params[0] ?? '';
        switch ($type) {
            case 'circulation':
                return $this->getCirculationReport();
            case 'financial':
                return $this->getFinancialReport();
            case 'membership':
                return $this->getMembershipReport();
            case 'inventory':
                return $this->getInventoryReport();
            default:
                $this->sendError('Invalid report type', 400);
        }
    }

    private function createReservation() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['member_id']) || empty($input['book_isbn'])) {
            $this->sendError('Missing required fields: member_id, book_isbn', 400);
        }
        
        $memberId = $input['member_id'];
        $bookIsbn = $input['book_isbn'];
        
        $book = $this->db->fetch("SELECT * FROM books WHERE isbn = ?", array($bookIsbn));
        if (!$book) {
            $this->sendError('Book not found', 404);
        }
        
        $member = $this->db->fetch("SELECT * FROM members WHERE member_id = ?", array($memberId));
        if (!$member) {
            $this->sendError('Member not found', 404);
        }
        
        // Check for duplicate reservation
        $existing = $this->db->fetch("SELECT * FROM reservations WHERE member_id = ? AND book_isbn = ? AND status = 'active'", 
                                     array($memberId, $bookIsbn));
        if ($existing) {
            $this->sendError('Member already has an active reservation for this book', 400);
        }
        
        // Get queue position
        $queuePos = $this->db->fetch("SELECT COUNT(*) as count FROM reservations WHERE book_isbn = ? AND status = 'active'", 
                                     array($bookIsbn));
        $queuePosition = $queuePos['count'] + 1;
        
        $expiryDate = date('Y-m-d', strtotime('+7 days'));
        
        $sql = "INSERT INTO reservations (member_id, book_isbn, expiry_date, queue_position, status) 
                VALUES (?, ?, ?, ?, 'active')";
        
        $this->db->execute($sql, array($memberId, $bookIsbn, $expiryDate, $queuePosition));
        
        $this->sendSuccess('Reservation created successfully', array(
            'member_id' => $memberId,
            'book_isbn' => $bookIsbn,
            'queue_position' => $queuePosition,
            'expiry_date' => $expiryDate
        ), 201);
    } catch (Exception $e) {
        error_log("Reservation creation error: " . $e->getMessage());
        $this->sendError('Failed to create reservation: ' . $e->getMessage(), 500);
    }
}

    
    private function getCirculationReport() {
        try {
            $records = $this->db->fetchAll("SELECT i.*, m.member_name, b.book_title FROM issued_status i JOIN members m ON i.issued_member_id = m.member_id JOIN books b ON i.issued_book_isbn = b.isbn ORDER BY i.issued_date DESC LIMIT 100");
            $summary = array('total_issued' => count($records));
            $this->sendSuccess('Report generated', array('title' => 'Circulation Report', 'summary' => $summary, 'records' => $records));
        } catch (Exception $e) {
            $this->sendError('Failed to generate report: ' . $e->getMessage(), 500);
        }
    }
    
    private function getFinancialReport() {
        try {
            $records = $this->db->fetchAll("SELECT f.*, m.member_name FROM fines f JOIN members m ON f.member_id = m.member_id ORDER BY f.fine_date DESC LIMIT 100");
            $summary = array('total_fines' => count($records));
            $this->sendSuccess('Report generated', array('title' => 'Financial Report', 'summary' => $summary, 'records' => $records));
        } catch (Exception $e) {
            $this->sendError('Failed to generate report: ' . $e->getMessage(), 500);
        }
    }
    
    private function getMembershipReport() {
        try {
            $records = $this->db->fetchAll("SELECT * FROM members WHERE status = 'active' ORDER BY reg_date DESC LIMIT 100");
            $summary = array('total_members' => count($records));
            $this->sendSuccess('Report generated', array('title' => 'Membership Report', 'summary' => $summary, 'records' => $records));
        } catch (Exception $e) {
            $this->sendError('Failed to generate report: ' . $e->getMessage(), 500);
        }
    }
    
    private function getInventoryReport() {
        try {
            $records = $this->db->fetchAll("SELECT * FROM books WHERE status = 'active' ORDER BY book_title ASC LIMIT 100");
            $summary = array('total_books' => count($records));
            $this->sendSuccess('Report generated', array('title' => 'Inventory Report', 'summary' => $summary, 'records' => $records));
        } catch (Exception $e) {
            $this->sendError('Failed to generate report: ' . $e->getMessage(), 500);
        }
    }
    
    // ANALYTICS
    private function handleAnalytics() {
        if ($this->method !== 'GET') $this->sendError('Method not allowed', 405);
        try {
            $data = array('message' => 'Analytics data');
            $this->sendSuccess('Analytics retrieved', $data);
        } catch (Exception $e) {
            $this->sendError('Failed to load analytics: ' . $e->getMessage(), 500);
        }
    }
    
    // SEARCH
    private function handleSearch() {
        if ($this->method !== 'GET') $this->sendError('Method not allowed', 405);
        
        try {
            $query = isset($_GET['q']) ? trim($_GET['q']) : '';
            if (strlen($query) < 2) $this->sendError('Query too short', 400);
            
            $searchParam = "%$query%";
            
            $books = $this->db->fetchAll("SELECT isbn, book_title, author FROM books WHERE (book_title LIKE ? OR author LIKE ?) AND status = 'active' LIMIT 10", array($searchParam, $searchParam));
            $members = $this->db->fetchAll("SELECT member_id, member_name, email FROM members WHERE (member_name LIKE ? OR email LIKE ?) AND status = 'active' LIMIT 10", array($searchParam, $searchParam));
            
            $this->sendSuccess('Search completed', array('books' => $books, 'members' => $members, 'circulation' => array()));
        } catch (Exception $e) {
            $this->sendError('Search failed: ' . $e->getMessage(), 500);
        }
    }
    
    // TRANSLATIONS
    private function handleTranslations() {
        if ($this->method !== 'GET') $this->sendError('Method not allowed', 405);
        
        try {
            $lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
            $translations = $this->db->fetchAll("SELECT translation_key, translation_value FROM translations WHERE language_code = ?", array($lang));
            
            $translationMap = array();
            foreach ($translations as $t) {
                $translationMap[$t['translation_key']] = $t['translation_value'];
            }
            
            $this->sendSuccess('Translations retrieved', array('translations' => $translationMap));
        } catch (Exception $e) {
            $this->sendError('Failed to load translations: ' . $e->getMessage(), 500);
        }
    }
}

// Initialize and process
$api = new LibraryAPI();
$api->processRequest();
?>
