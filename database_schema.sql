-- ============================================
-- LIBRARY MANAGEMENT SYSTEM - DATABASE SCHEMA
-- Complete and Production Ready
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `library_management_system` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `library_management_system`;

-- --------------------------------------------------------
-- Table: branches
-- --------------------------------------------------------
CREATE TABLE `branches` (
  `branch_id` varchar(10) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `branch_address` text NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `manager_id` varchar(10) DEFAULT NULL,
  `established_date` date DEFAULT (CURRENT_DATE),
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`branch_id`),
  KEY `idx_branch_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: books
-- --------------------------------------------------------
CREATE TABLE `books` (
  `isbn` varchar(20) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` year DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `language` varchar(50) DEFAULT 'English',
  `pages` int DEFAULT NULL,
  `total_copies` int NOT NULL DEFAULT 1,
  `available_copies` int NOT NULL DEFAULT 1,
  `price` decimal(10,2) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `description` text,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','damaged','lost') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`isbn`),
  KEY `idx_book_title` (`book_title`),
  KEY `idx_author` (`author`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: members
-- --------------------------------------------------------
CREATE TABLE `members` (
  `member_id` varchar(10) NOT NULL,
  `member_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(15) NOT NULL,
  `address` text,
  `membership_type` enum('student','faculty','public','premium') DEFAULT 'public',
  `reg_date` date DEFAULT (CURRENT_DATE),
  `expiry_date` date DEFAULT NULL,
  `max_books_allowed` int DEFAULT 3,
  `outstanding_fines` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','suspended','expired','blocked') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_member_name` (`member_name`),
  KEY `idx_membership_type` (`membership_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: issued_status
-- --------------------------------------------------------
CREATE TABLE `issued_status` (
  `issued_id` int NOT NULL AUTO_INCREMENT,
  `issued_member_id` varchar(10) NOT NULL,
  `issued_book_isbn` varchar(20) NOT NULL,
  `issued_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('issued','returned','overdue','lost') DEFAULT 'issued',
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `notes` text,
  `issued_by` varchar(50) DEFAULT 'admin',
  `returned_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`issued_id`),
  KEY `idx_member` (`issued_member_id`),
  KEY `idx_book` (`issued_book_isbn`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`issued_date`,`due_date`),
  CONSTRAINT `fk_issued_book` FOREIGN KEY (`issued_book_isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_issued_member` FOREIGN KEY (`issued_member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: fines
-- --------------------------------------------------------
CREATE TABLE `fines` (
  `fine_id` int NOT NULL AUTO_INCREMENT,
  `member_id` varchar(10) NOT NULL,
  `issued_id` int DEFAULT NULL,
  `fine_amount` decimal(10,2) NOT NULL,
  `fine_reason` varchar(255) DEFAULT 'Late Return',
  `fine_date` date DEFAULT (CURRENT_DATE),
  `payment_date` date DEFAULT NULL,
  `payment_status` enum('unpaid','paid','waived') DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fine_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_issued` (`issued_id`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `fk_fine_issued` FOREIGN KEY (`issued_id`) REFERENCES `issued_status` (`issued_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_fine_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: reservations
-- --------------------------------------------------------
CREATE TABLE `reservations` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `member_id` varchar(10) NOT NULL,
  `book_isbn` varchar(20) NOT NULL,
  `reservation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` date NOT NULL,
  `status` enum('active','fulfilled','expired','cancelled') DEFAULT 'active',
  `queue_position` int DEFAULT 1,
  `fulfilled_date` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`reservation_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_book` (`book_isbn`),
  KEY `idx_status` (`status`),
  KEY `idx_queue` (`book_isbn`,`queue_position`),
  CONSTRAINT `fk_reservation_book` FOREIGN KEY (`book_isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservation_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: activity_logs
-- --------------------------------------------------------
CREATE TABLE `activity_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `user_type` enum('admin','member','system') DEFAULT 'admin',
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` varchar(50) DEFAULT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_user` (`user_id`,`user_type`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: translations
-- --------------------------------------------------------
CREATE TABLE `translations` (
  `translation_id` int NOT NULL AUTO_INCREMENT,
  `language_code` varchar(5) NOT NULL,
  `translation_key` varchar(100) NOT NULL,
  `translation_value` text NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`translation_id`),
  UNIQUE KEY `unique_translation` (`language_code`,`translation_key`),
  KEY `idx_language` (`language_code`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert Default Translations (English and Hindi)
-- --------------------------------------------------------

-- English Translations
INSERT INTO `translations` (`language_code`, `translation_key`, `translation_value`, `category`) VALUES
('en', 'app_title', 'Library Management System', 'general'),
('en', 'dashboard', 'Dashboard', 'navigation'),
('en', 'books', 'Books', 'navigation'),
('en', 'members', 'Members', 'navigation'),
('en', 'circulation', 'Circulation', 'navigation'),
('en', 'fines', 'Fines', 'navigation'),
('en', 'reports', 'Reports', 'navigation'),
('en', 'reservations', 'Reservations', 'navigation'),
('en', 'settings', 'Settings', 'navigation'),
('en', 'search_placeholder', 'Search books, members, or transactions...', 'general'),
('en', 'quick_add', 'Quick Add', 'buttons'),
('en', 'total_books', 'Total Books', 'dashboard'),
('en', 'total_members', 'Total Members', 'dashboard'),
('en', 'books_issued', 'Books Issued', 'dashboard'),
('en', 'overdue_books', 'Overdue Books', 'dashboard'),
('en', 'add_book', 'Add Book', 'buttons'),
('en', 'edit_book', 'Edit Book', 'buttons'),
('en', 'delete_book', 'Delete Book', 'buttons'),
('en', 'issue_book', 'Issue Book', 'buttons'),
('en', 'return_book', 'Return Book', 'buttons'),
('en', 'add_member', 'Add Member', 'buttons'),
('en', 'circulation_report', 'Circulation Report', 'reports'),
('en', 'financial_report', 'Financial Report', 'reports'),
('en', 'membership_report', 'Membership Report', 'reports'),
('en', 'inventory_report', 'Inventory Report', 'reports'),
('en', 'generate_report', 'Generate Report', 'buttons'),
('en', 'export_csv', 'Export CSV', 'buttons'),
('en', 'export_pdf', 'Export PDF', 'buttons'),
('en', 'save', 'Save', 'buttons'),
('en', 'cancel', 'Cancel', 'buttons'),
('en', 'close', 'Close', 'buttons'),
('en', 'loading', 'Loading...', 'general'),
('en', 'no_records', 'No records found', 'general'),
('en', 'success', 'Success', 'general'),
('en', 'error', 'Error', 'general');

-- Hindi Translations
INSERT INTO `translations` (`language_code`, `translation_key`, `translation_value`, `category`) VALUES
('hi', 'app_title', 'पुस्तकालय प्रबंधन प्रणाली', 'general'),
('hi', 'dashboard', 'डैशबोर्ड', 'navigation'),
('hi', 'books', 'पुस्तकें', 'navigation'),
('hi', 'members', 'सदस्य', 'navigation'),
('hi', 'circulation', 'परिसंचरण', 'navigation'),
('hi', 'fines', 'जुर्माना', 'navigation'),
('hi', 'reports', 'रिपोर्ट', 'navigation'),
('hi', 'reservations', 'आरक्षण', 'navigation'),
('hi', 'settings', 'सेटिंग्स', 'navigation'),
('hi', 'search_placeholder', 'पुस्तकें, सदस्य या लेनदेन खोजें...', 'general'),
('hi', 'quick_add', 'त्वरित जोड़ें', 'buttons'),
('hi', 'total_books', 'कुल पुस्तकें', 'dashboard'),
('hi', 'total_members', 'कुल सदस्य', 'dashboard'),
('hi', 'books_issued', 'जारी की गई पुस्तकें', 'dashboard'),
('hi', 'overdue_books', 'अतिदेय पुस्तकें', 'dashboard'),
('hi', 'add_book', 'पुस्तक जोड़ें', 'buttons'),
('hi', 'edit_book', 'पुस्तक संपादित करें', 'buttons'),
('hi', 'delete_book', 'पुस्तक हटाएं', 'buttons'),
('hi', 'issue_book', 'पुस्तक जारी करें', 'buttons'),
('hi', 'return_book', 'पुस्तक वापस करें', 'buttons'),
('hi', 'add_member', 'सदस्य जोड़ें', 'buttons'),
('hi', 'circulation_report', 'परिसंचरण रिपोर्ट', 'reports'),
('hi', 'financial_report', 'वित्तीय रिपोर्ट', 'reports'),
('hi', 'membership_report', 'सदस्यता रिपोर्ट', 'reports'),
('hi', 'inventory_report', 'सूची रिपोर्ट', 'reports'),
('hi', 'generate_report', 'रिपोर्ट बनाएं', 'buttons'),
('hi', 'export_csv', 'CSV निर्यात करें', 'buttons'),
('hi', 'export_pdf', 'PDF निर्यात करें', 'buttons'),
('hi', 'save', 'सहेजें', 'buttons'),
('hi', 'cancel', 'रद्द करें', 'buttons'),
('hi', 'close', 'बंद करें', 'buttons'),
('hi', 'loading', 'लोड हो रहा है...', 'general'),
('hi', 'no_records', 'कोई रिकॉर्ड नहीं मिला', 'general'),
('hi', 'success', 'सफलता', 'general'),
('hi', 'error', 'त्रुटि', 'general');

-- --------------------------------------------------------
-- Create Views for Common Queries
-- --------------------------------------------------------

-- View: Current Issued Books
CREATE VIEW `view_current_issues` AS
SELECT 
    i.issued_id,
    i.issued_date,
    i.due_date,
    i.status,
    m.member_id,
    m.member_name,
    m.email,
    m.phone_number,
    b.isbn,
    b.book_title,
    b.author,
    b.category,
    DATEDIFF(CURRENT_DATE, i.due_date) as days_overdue,
    CASE 
        WHEN i.status = 'issued' AND i.due_date < CURRENT_DATE THEN 'overdue'
        WHEN i.status = 'issued' THEN 'active'
        ELSE i.status
    END as current_status
FROM issued_status i
JOIN members m ON i.issued_member_id = m.member_id
JOIN books b ON i.issued_book_isbn = b.isbn
WHERE i.status IN ('issued', 'overdue');

-- View: Member Statistics
CREATE VIEW `view_member_stats` AS
SELECT 
    m.member_id,
    m.member_name,
    m.membership_type,
    m.status,
    COUNT(DISTINCT i.issued_id) as total_borrowed,
    COUNT(DISTINCT CASE WHEN i.status = 'issued' THEN i.issued_id END) as currently_borrowed,
    COALESCE(SUM(f.fine_amount), 0) as total_fines,
    COALESCE(SUM(CASE WHEN f.payment_status = 'unpaid' THEN f.fine_amount ELSE 0 END), 0) as outstanding_fines
FROM members m
LEFT JOIN issued_status i ON m.member_id = i.issued_member_id
LEFT JOIN fines f ON m.member_id = f.member_id
GROUP BY m.member_id;

-- View: Book Availability
CREATE VIEW `view_book_availability` AS
SELECT 
    b.isbn,
    b.book_title,
    b.author,
    b.category,
    b.total_copies,
    b.available_copies,
    (b.total_copies - b.available_copies) as copies_issued,
    CASE 
        WHEN b.available_copies = 0 THEN 'Not Available'
        WHEN b.available_copies <= 2 THEN 'Limited'
        ELSE 'Available'
    END as availability_status,
    COUNT(DISTINCT r.reservation_id) as pending_reservations
FROM books b
LEFT JOIN reservations r ON b.isbn = r.book_isbn AND r.status = 'active'
WHERE b.status = 'active'
GROUP BY b.isbn;

-- --------------------------------------------------------
-- Create Indexes for Performance
-- --------------------------------------------------------

CREATE INDEX idx_issued_overdue ON issued_status(status, due_date);
CREATE INDEX idx_fines_unpaid ON fines(payment_status, member_id);
CREATE INDEX idx_reservations_active ON reservations(status, book_isbn);
CREATE INDEX idx_books_available ON books(available_copies, status);

COMMIT;

-- ============================================
-- Schema Creation Complete
-- ============================================
