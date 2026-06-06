<?php
/**
 * LIBRARY MANAGEMENT SYSTEM - SAMPLE DATA INSERTION
 * Run this file once to populate the database with test data
 * Version: 2.0-FIXED
 */

require_once 'config.php';

// HTML header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Insert Sample Data - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; margin-bottom: 20px; }
        h2 { color: #333; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-top: 30px; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .info { color: #64748b; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid #2563eb; }
        .stat-value { font-size: 24px; font-weight: bold; color: #2563eb; }
        .stat-label { font-size: 12px; color: #64748b; text-transform: uppercase; }
        .btn { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; font-weight: 600; }
        .btn:hover { background: #1e40af; }
        ul { list-style: none; padding: 0; }
        li { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        li:before { content: '✓ '; color: #10b981; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>📚 Library Management System - Sample Data Insertion</h1>";
echo "<p class='info'>This will populate your database with sample books, members, and transactions for testing.</p>";

try {
    $db = Database::getInstance();
    
    echo "<h2>Inserting Sample Data...</h2>";
    
    // ==================== BOOKS ====================
    echo "<h3>📖 Adding Books...</h3><ul>";
    
    $books = array(
        array('978-0-330-25864-8', 'The Lord of the Rings', 'J.R.R. Tolkien', 'HarperCollins', 1954, 'Fiction', 'English', 1178, 5, 3, 29.99, 'A-101', 'Epic fantasy novel'),
        array('978-0-06-112008-4', 'To Kill a Mockingbird', 'Harper Lee', 'J.B. Lippincott', 1960, 'Fiction', 'English', 324, 4, 2, 15.99, 'A-102', 'American classic'),
        array('978-0-14-028329-5', '1984', 'George Orwell', 'Secker & Warburg', 1949, 'Fiction', 'English', 328, 6, 4, 12.99, 'A-103', 'Dystopian novel'),
        array('978-0-7432-7356-5', 'The Da Vinci Code', 'Dan Brown', 'Doubleday', 2003, 'Thriller', 'English', 454, 3, 1, 19.99, 'B-201', 'Mystery thriller'),
        array('978-0-545-01022-1', 'Harry Potter and the Deathly Hallows', 'J.K. Rowling', 'Scholastic', 2007, 'Fantasy', 'English', 607, 8, 5, 24.99, 'C-301', 'Fantasy series finale'),
        array('978-0-06-093546-7', 'The Alchemist', 'Paulo Coelho', 'HarperTorch', 1988, 'Fiction', 'English', 208, 4, 3, 14.99, 'A-104', 'Philosophical novel'),
        array('978-0-452-28423-4', 'The Great Gatsby', 'F. Scott Fitzgerald', 'Scribner', 1925, 'Classic', 'English', 180, 5, 3, 11.99, 'A-105', 'Jazz age classic'),
        array('978-0-316-76948-0', 'The Catcher in the Rye', 'J.D. Salinger', 'Little, Brown', 1951, 'Fiction', 'English', 277, 3, 2, 13.99, 'A-106', 'Coming of age story'),
        array('978-0-06-112241-5', 'One Hundred Years of Solitude', 'Gabriel García Márquez', 'Harper & Row', 1967, 'Fiction', 'English', 417, 4, 3, 17.99, 'A-107', 'Magical realism'),
        array('978-0-14-017739-8', 'The Hobbit', 'J.R.R. Tolkien', 'George Allen & Unwin', 1937, 'Fantasy', 'English', 310, 6, 4, 16.99, 'C-302', 'Fantasy adventure'),
        array('978-0-06-112009-1', 'Brave New World', 'Aldous Huxley', 'Chatto & Windus', 1932, 'Science Fiction', 'English', 268, 4, 2, 14.99, 'A-108', 'Dystopian novel'),
        array('978-0-14-118776-1', 'The Kite Runner', 'Khaled Hosseini', 'Riverhead Books', 2003, 'Fiction', 'English', 371, 5, 3, 16.99, 'A-109', 'Contemporary fiction'),
        array('978-0-06-112010-7', 'The Road', 'Cormac McCarthy', 'Alfred A. Knopf', 2006, 'Fiction', 'English', 287, 3, 2, 15.99, 'A-110', 'Post-apocalyptic'),
        array('978-0-7432-7357-2', 'Angels & Demons', 'Dan Brown', 'Pocket Books', 2000, 'Thriller', 'English', 616, 4, 2, 18.99, 'B-202', 'Mystery thriller'),
        array('978-0-06-112011-4', 'Life of Pi', 'Yann Martel', 'Knopf Canada', 2001, 'Fiction', 'English', 319, 4, 3, 15.99, 'A-111', 'Adventure novel')
    );
    
    $bookCount = 0;
    foreach ($books as $book) {
        $sql = "INSERT INTO books (isbn, book_title, author, publisher, publication_year, category, language, pages, total_copies, available_copies, price, location, description, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ON DUPLICATE KEY UPDATE book_title=VALUES(book_title)";
        $db->execute($sql, $book);
        echo "<li>{$book[1]} by {$book[2]}</li>";
        $bookCount++;
    }
    echo "</ul><p class='success'>✓ Added {$bookCount} books</p>";
    
    // ==================== MEMBERS ====================
    echo "<h3>👥 Adding Members...</h3><ul>";
    
    $members = array(
        array('M000001', 'Raj Kumar', 'raj.kumar@email.com', '9876543210', '123 MG Road, Delhi', 'student', 3),
        array('M000002', 'Priya Sharma', 'priya.sharma@email.com', '9876543211', '456 Park Street, Mumbai', 'faculty', 5),
        array('M000003', 'Amit Patel', 'amit.patel@email.com', '9876543212', '789 Brigade Road, Bangalore', 'public', 3),
        array('M000004', 'Sneha Gupta', 'sneha.gupta@email.com', '9876543213', '321 Connaught Place, Delhi', 'premium', 10),
        array('M000005', 'Vikram Singh', 'vikram.singh@email.com', '9876543214', '654 Marine Drive, Mumbai', 'student', 3),
        array('M000006', 'Anita Desai', 'anita.desai@email.com', '9876543215', '987 MG Road, Pune', 'public', 3),
        array('M000007', 'Rahul Verma', 'rahul.verma@email.com', '9876543216', '147 Residency Road, Bangalore', 'student', 3),
        array('M000008', 'Meera Iyer', 'meera.iyer@email.com', '9876543217', '258 Anna Salai, Chennai', 'faculty', 5),
        array('M000009', 'Arjun Reddy', 'arjun.reddy@email.com', '9876543218', '369 Necklace Road, Hyderabad', 'public', 3),
        array('M000010', 'Kavita Menon', 'kavita.menon@email.com', '9876543219', '741 MG Road, Kochi', 'premium', 10),
        array('M000011', 'Sanjay Patel', 'sanjay.patel@email.com', '9876543220', '852 Linking Road, Mumbai', 'student', 3),
        array('M000012', 'Deepa Nair', 'deepa.nair@email.com', '9876543221', '963 Anna Nagar, Chennai', 'public', 3)
    );
    
    $memberCount = 0;
    $expiryDate = date('Y-m-d', strtotime('+1 year'));
    foreach ($members as $member) {
        $sql = "INSERT INTO members (member_id, member_name, email, phone_number, address, membership_type, max_books_allowed, expiry_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ON DUPLICATE KEY UPDATE member_name=VALUES(member_name)";
        $db->execute($sql, array_merge($member, array($expiryDate)));
        echo "<li>{$member[1]} - {$member[5]}</li>";
        $memberCount++;
    }
    echo "</ul><p class='success'>✓ Added {$memberCount} members</p>";
    
    // ==================== CIRCULATION ====================
    echo "<h3>🔄 Adding Circulation Records...</h3><ul>";
    
    $circulations = array(
        array('M000001', '978-0-330-25864-8', 'DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 9 DAY)', 'issued'),
        array('M000002', '978-0-14-028329-5', 'DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 11 DAY)', 'issued'),
        array('M000003', '978-0-7432-7356-5', 'DATE_SUB(CURRENT_DATE, INTERVAL 20 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)', 'issued'),
        array('M000004', '978-0-06-112008-4', 'DATE_SUB(CURRENT_DATE, INTERVAL 15 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)', 'returned')
    );
    
    $circulationCount = 0;
    foreach ($circulations as $circ) {
        $sql = "INSERT INTO issued_status (issued_member_id, issued_book_isbn, issued_date, due_date, status, issued_by)
                VALUES ('{$circ[0]}', '{$circ[1]}', {$circ[2]}, {$circ[3]}, '{$circ[4]}', 'admin')
                ON DUPLICATE KEY UPDATE status=VALUES(status)";
        $db->execute($sql);
        echo "<li>Book {$circ[1]} issued to {$circ[0]} - {$circ[4]}</li>";
        $circulationCount++;
    }
    
    // Update available copies
    $db->execute("UPDATE books SET available_copies = available_copies - 1 WHERE isbn = '978-0-330-25864-8'");
    $db->execute("UPDATE books SET available_copies = available_copies - 1 WHERE isbn = '978-0-14-028329-5'");
    $db->execute("UPDATE books SET available_copies = available_copies - 1 WHERE isbn = '978-0-7432-7356-5'");
    
    echo "</ul><p class='success'>✓ Added {$circulationCount} circulation records</p>";
    
    // ==================== FINES ====================
    echo "<h3>💰 Adding Fines...</h3><ul>";
    
    $fines = array(
        array('M000003', 50.00, 'Late Return', 'unpaid'),
        array('M000005', 25.00, 'Late Return', 'paid'),
        array('M000007', 15.00, 'Late Return', 'unpaid')
    );
    
    $fineCount = 0;
    foreach ($fines as $fine) {
        $sql = "INSERT INTO fines (member_id, fine_amount, fine_reason, payment_status)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE fine_amount=VALUES(fine_amount)";
        $db->execute($sql, $fine);
        echo "<li>{$fine[0]}: ${$fine[1]} - {$fine[3]}</li>";
        $fineCount++;
        
        if ($fine[3] === 'unpaid') {
            $db->execute("UPDATE members SET outstanding_fines = outstanding_fines + ? WHERE member_id = ?", array($fine[1], $fine[0]));
        }
    }
    echo "</ul><p class='success'>✓ Added {$fineCount} fines</p>";
    
    // ==================== RESERVATIONS ====================
    echo "<h3>📅 Adding Reservations...</h3><ul>";
    
    $reservations = array(
        array('M000006', '978-0-7432-7356-5', 1),
        array('M000008', '978-0-545-01022-1', 1),
        array('M000009', '978-0-7432-7356-5', 2)
    );
    
    $reservationCount = 0;
    $expiryDate = date('Y-m-d', strtotime('+7 days'));
    foreach ($reservations as $res) {
        $sql = "INSERT INTO reservations (member_id, book_isbn, expiry_date, queue_position, status)
                VALUES (?, ?, ?, ?, 'active')
                ON DUPLICATE KEY UPDATE status=VALUES(status)";
        $db->execute($sql, array($res[0], $res[1], $expiryDate, $res[2]));
        echo "<li>{$res[0]} reserved {$res[1]} (Queue: {$res[2]})</li>";
        $reservationCount++;
    }
    echo "</ul><p class='success'>✓ Added {$reservationCount} reservations</p>";
    
    // ==================== SUMMARY ====================
    echo "<h2>📊 Summary</h2>";
    echo "<div class='stats'>
            <div class='stat-card'>
                <div class='stat-value'>{$bookCount}</div>
                <div class='stat-label'>Books</div>
            </div>
            <div class='stat-card'>
                <div class='stat-value'>{$memberCount}</div>
                <div class='stat-label'>Members</div>
            </div>
            <div class='stat-card'>
                <div class='stat-value'>{$circulationCount}</div>
                <div class='stat-label'>Circulation</div>
            </div>
            <div class='stat-card'>
                <div class='stat-value'>{$fineCount}</div>
                <div class='stat-label'>Fines</div>
            </div>
            <div class='stat-card'>
                <div class='stat-value'>{$reservationCount}</div>
                <div class='stat-label'>Reservations</div>
            </div>
        </div>";
    
    echo "<h2 class='success'>✅ Sample Data Inserted Successfully!</h2>";
    echo "<p>Your library system is now populated with test data and ready to use.</p>";
    echo "<a href='index.html' class='btn'>Open Library System →</a>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Error Occurred</h2>";
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check:</p>";
    echo "<ul>
            <li>Database connection in config.php</li>
            <li>Database 'library_management_system' exists</li>
            <li>All tables are created (run database_schema.sql)</li>
            <li>MySQL server is running</li>
          </ul>";
}

echo "</div></body></html>";
?>
