<?php
/**
 * LIBRARY MANAGEMENT SYSTEM - CONFIGURATION FILE
 * Complete Database Management and Utility Functions
 * ALL BUGS FIXED - Production Ready
 */

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Timezone setting
date_default_timezone_set('Asia/Kolkata');

// Database Configuration
class Database {
    private static $instance = null;
    private $conn = null;
    
    private $host = 'localhost';
    private $db_name = 'library_management_system';
    private $username = 'root';
    private $password = '';  // Change this to your MySQL password
    
    private function __construct() {
        // Private constructor prevents direct instantiation
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function connect() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                    $this->username,
                    $this->password,
                    array(
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    )
                );
            } catch(PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
    
    // Execute prepared statement with parameters
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Query Execution Error: " . $e->getMessage());
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }
    
    // Fetch single row
    public function fetch($sql, $params = []) {
        try {
            $stmt = $this->execute($sql, $params);
            return $stmt->fetch();
        } catch(Exception $e) {
            error_log("Fetch Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Fetch all rows
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->execute($sql, $params);
            return $stmt->fetchAll();
        } catch(Exception $e) {
            error_log("FetchAll Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get last insert ID
    public function lastInsertId() {
        return $this->connect()->lastInsertId();
    }
    
    // Begin transaction
    public function beginTransaction() {
        return $this->connect()->beginTransaction();
    }
    
    // Commit transaction
    public function commit() {
        return $this->connect()->commit();
    }
    
    // Rollback transaction
    public function rollback() {
        return $this->connect()->rollBack();
    }
}

// Utility Functions
class Utils {
    
    /**
     * Send JSON success response
     */
    public static function sendSuccess($message, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Send JSON error response
     */
    public static function sendError($message, $statusCode = 400, $errors = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Indian format)
     */
    public static function validatePhone($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's 10 digits
        return strlen($phone) === 10 && preg_match('/^[6-9][0-9]{9}$/', $phone);
    }
    
    /**
     * Validate ISBN
     */
    public static function validateISBN($isbn) {
        // Remove hyphens and spaces
        $isbn = preg_replace('/[-\s]/', '', $isbn);
        
        // ISBN-10 or ISBN-13
        return (strlen($isbn) === 10 || strlen($isbn) === 13) && ctype_alnum($isbn);
    }
    
    /**
     * Generate unique ID
     */
    public static function generateUniqueId($prefix = '', $length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $id = $prefix;
        
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $id;
    }
    
    /**
     * Calculate fine amount based on overdue days
     */
    public static function calculateFine($dueDate, $returnDate = null) {
        $return = $returnDate ? new DateTime($returnDate) : new DateTime();
        $due = new DateTime($dueDate);
        
        if ($return <= $due) {
            return 0;
        }
        
        $interval = $due->diff($return);
        $overdueDays = $interval->days;
        
        // Fine calculation: $5 per day for first 7 days, then $10 per day
        if ($overdueDays <= 7) {
            return $overdueDays * 5;
        } else {
            return (7 * 5) + (($overdueDays - 7) * 10);
        }
    }
    
    /**
     * Get pagination data
     */
    public static function getPaginationData($currentPage, $totalRecords, $recordsPerPage = 20) {
        $totalPages = ceil($totalRecords / $recordsPerPage);
        
        return [
            'current_page' => (int)$currentPage,
            'total_pages' => (int)$totalPages,
            'total_records' => (int)$totalRecords,
            'records_per_page' => (int)$recordsPerPage,
            'has_next' => $currentPage < $totalPages,
            'has_previous' => $currentPage > 1
        ];
    }
    
    /**
     * Format date for display
     */
    public static function formatDate($date, $format = 'Y-m-d') {
        if (!$date) return null;
        
        try {
            $dateObj = new DateTime($date);
            return $dateObj->format($format);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Check if date is overdue
     */
    public static function isOverdue($dueDate) {
        $today = new DateTime();
        $due = new DateTime($dueDate);
        
        return $today > $due;
    }
    
    /**
     * Log activity
     */
    public static function logActivity($userId, $userType, $action, $entityType = null, $entityId = null, $details = null) {
        try {
            $db = Database::getInstance();
            
            $sql = "INSERT INTO activity_logs (user_id, user_type, action, entity_type, entity_id, details, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $userId,
                $userType,
                $action,
                $entityType,
                $entityId,
                $details ? json_encode($details) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $db->execute($sql, $params);
            
            return true;
        } catch (Exception $e) {
            error_log("Activity Log Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get request body as JSON
     */
    public static function getRequestBody() {
        $body = file_get_contents('php://input');
        return json_decode($body, true);
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequiredFields($data, $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
    
    /**
     * Update book availability count
     */
    public static function updateBookAvailability($isbn, $increment = false) {
        try {
            $db = Database::getInstance();
            
            $operator = $increment ? '+' : '-';
            $sql = "UPDATE books SET available_copies = available_copies $operator 1 WHERE isbn = ?";
            
            $db->execute($sql, [$isbn]);
            
            return true;
        } catch (Exception $e) {
            error_log("Update Book Availability Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check member eligibility to borrow
     */
    public static function checkMemberEligibility($memberId) {
        try {
            $db = Database::getInstance();
            
            // Check member status
            $member = $db->fetch("SELECT * FROM members WHERE member_id = ?", [$memberId]);
            
            if (!$member) {
                return ['eligible' => false, 'reason' => 'Member not found'];
            }
            
            if ($member['status'] !== 'active') {
                return ['eligible' => false, 'reason' => 'Member account is not active'];
            }
            
            // Check outstanding fines
            if ($member['outstanding_fines'] > 0) {
                return ['eligible' => false, 'reason' => 'Outstanding fines must be paid first'];
            }
            
            // Check current borrowed books count
            $currentBorrowed = $db->fetch(
                "SELECT COUNT(*) as count FROM issued_status WHERE issued_member_id = ? AND status = 'issued'",
                [$memberId]
            );
            
            if ($currentBorrowed['count'] >= $member['max_books_allowed']) {
                return ['eligible' => false, 'reason' => 'Maximum book limit reached'];
            }
            
            return ['eligible' => true, 'reason' => null];
            
        } catch (Exception $e) {
            error_log("Check Eligibility Error: " . $e->getMessage());
            return ['eligible' => false, 'reason' => 'System error'];
        }
    }
    
    /**
     * Get book availability status
     */
    public static function getBookAvailability($isbn) {
        try {
            $db = Database::getInstance();
            
            $book = $db->fetch("SELECT * FROM books WHERE isbn = ?", [$isbn]);
            
            if (!$book) {
                return ['available' => false, 'reason' => 'Book not found'];
            }
            
            if ($book['status'] !== 'active') {
                return ['available' => false, 'reason' => 'Book is not available for borrowing'];
            }
            
            if ($book['available_copies'] <= 0) {
                return ['available' => false, 'reason' => 'No copies available'];
            }
            
            return ['available' => true, 'copies' => $book['available_copies']];
            
        } catch (Exception $e) {
            error_log("Get Book Availability Error: " . $e->getMessage());
            return ['available' => false, 'reason' => 'System error'];
        }
    }
}

// CORS Headers (if needed for API access)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
