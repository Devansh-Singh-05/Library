<?php
/**
 * LIBRARY MANAGEMENT SYSTEM - ENHANCED SAMPLE DATA
 * Insert 50+ books, 30+ members, and related transactions
 * Version: 2.0-FIXED
 */

require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Insert Enhanced Sample Data - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
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
        .progress { background: #e2e8f0; height: 8px; border-radius: 4px; margin: 10px 0; overflow: hidden; }
        .progress-bar { background: #2563eb; height: 100%; width: 0; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>📚 Enhanced Sample Data Insertion</h1>";
echo "<p class='info'>Inserting comprehensive test data: 50+ books, 30+ members, circulation, fines, and reservations.</p>";

try {
    $db = Database::getInstance();
    
    // ==================== BOOKS (50+) ====================
    echo "<h2>📖 Adding 50+ Books...</h2>";
    echo "<div class='progress'><div class='progress-bar' id='booksProgress'></div></div>";
    
    $books = array(
        // Fiction
        array('978-0-330-25864-8', 'The Lord of the Rings', 'J.R.R. Tolkien', 'HarperCollins', 1954, 'Fiction', 'English', 1178, 5, 3, 29.99, 'A-101', 'Epic fantasy novel'),
        array('978-0-06-112008-4', 'To Kill a Mockingbird', 'Harper Lee', 'J.B. Lippincott', 1960, 'Fiction', 'English', 324, 4, 2, 15.99, 'A-102', 'American classic'),
        array('978-0-14-028329-5', '1984', 'George Orwell', 'Secker & Warburg', 1949, 'Fiction', 'English', 328, 6, 4, 12.99, 'A-103', 'Dystopian novel'),
        array('978-0-06-093546-7', 'The Alchemist', 'Paulo Coelho', 'HarperTorch', 1988, 'Fiction', 'English', 208, 4, 3, 14.99, 'A-104', 'Philosophical novel'),
        array('978-0-452-28423-4', 'The Great Gatsby', 'F. Scott Fitzgerald', 'Scribner', 1925, 'Fiction', 'English', 180, 5, 3, 11.99, 'A-105', 'Jazz age classic'),
        array('978-0-316-76948-0', 'The Catcher in the Rye', 'J.D. Salinger', 'Little, Brown', 1951, 'Fiction', 'English', 277, 3, 2, 13.99, 'A-106', 'Coming of age'),
        array('978-0-06-112241-5', 'One Hundred Years of Solitude', 'Gabriel García Márquez', 'Harper & Row', 1967, 'Fiction', 'English', 417, 4, 3, 17.99, 'A-107', 'Magical realism'),
        array('978-0-06-112009-1', 'Brave New World', 'Aldous Huxley', 'Chatto & Windus', 1932, 'Fiction', 'English', 268, 4, 2, 14.99, 'A-108', 'Dystopian sci-fi'),
        array('978-0-14-118776-1', 'The Kite Runner', 'Khaled Hosseini', 'Riverhead Books', 2003, 'Fiction', 'English', 371, 5, 3, 16.99, 'A-109', 'Contemporary fiction'),
        array('978-0-06-112010-7', 'The Road', 'Cormac McCarthy', 'Alfred A. Knopf', 2006, 'Fiction', 'English', 287, 3, 2, 15.99, 'A-110', 'Post-apocalyptic'),
        
        // Fantasy
        array('978-0-7432-7356-5', 'The Da Vinci Code', 'Dan Brown', 'Doubleday', 2003, 'Thriller', 'English', 454, 3, 1, 19.99, 'B-201', 'Mystery thriller'),
        array('978-0-545-01022-1', 'Harry Potter and the Deathly Hallows', 'J.K. Rowling', 'Scholastic', 2007, 'Fantasy', 'English', 607, 8, 5, 24.99, 'C-301', 'Fantasy finale'),
        array('978-0-14-017739-8', 'The Hobbit', 'J.R.R. Tolkien', 'George Allen & Unwin', 1937, 'Fantasy', 'English', 310, 6, 4, 16.99, 'C-302', 'Fantasy adventure'),
        array('978-0-439-70936-2', 'Harry Potter and the Half-Blood Prince', 'J.K. Rowling', 'Scholastic', 2005, 'Fantasy', 'English', 652, 5, 4, 22.99, 'C-303', 'Fantasy series'),
        array('978-0-439-13959-1', 'Harry Potter and the Goblet of Fire', 'J.K. Rowling', 'Scholastic', 2000, 'Fantasy', 'English', 734, 4, 3, 23.99, 'C-304', 'Fantasy tournament'),
        array('978-0-439-06486-6', 'Harry Potter and the Chamber of Secrets', 'J.K. Rowling', 'Scholastic', 1998, 'Fantasy', 'English', 341, 6, 5, 18.99, 'C-305', 'Fantasy mystery'),
        array('978-0-06-124034-5', 'The Chronicles of Narnia', 'C.S. Lewis', 'HarperCollins', 1950, 'Fantasy', 'English', 767, 4, 3, 25.99, 'C-306', 'Fantasy classic'),
        array('978-0-345-33968-3', 'A Game of Thrones', 'George R.R. Martin', 'Bantam', 1996, 'Fantasy', 'English', 694, 5, 2, 26.99, 'C-307', 'Epic fantasy'),
        
        // Thriller & Mystery
        array('978-0-7432-7357-2', 'Angels & Demons', 'Dan Brown', 'Pocket Books', 2000, 'Thriller', 'English', 616, 4, 2, 18.99, 'B-202', 'Mystery thriller'),
        array('978-0-307-58837-1', 'Gone Girl', 'Gillian Flynn', 'Crown Publishing', 2012, 'Thriller', 'English', 415, 5, 3, 17.99, 'B-203', 'Psychological thriller'),
        array('978-0-385-34431-8', 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'Norstedts', 2005, 'Thriller', 'English', 465, 4, 2, 19.99, 'B-204', 'Crime thriller'),
        array('978-0-307-94561-1', 'The Girl on the Train', 'Paula Hawkins', 'Riverhead Books', 2015, 'Thriller', 'English', 336, 4, 3, 16.99, 'B-205', 'Psychological mystery'),
        
        // Science Fiction
        array('978-0-345-39180-3', 'Dune', 'Frank Herbert', 'Chilton Books', 1965, 'Science Fiction', 'English', 688, 4, 3, 21.99, 'D-401', 'Sci-fi epic'),
        array('978-0-553-57340-5', 'Foundation', 'Isaac Asimov', 'Gnome Press', 1951, 'Science Fiction', 'English', 255, 3, 2, 14.99, 'D-402', 'Space opera'),
        array('978-0-553-29337-0', 'The Hitchhiker\'s Guide to the Galaxy', 'Douglas Adams', 'Pan Books', 1979, 'Science Fiction', 'English', 216, 5, 4, 13.99, 'D-403', 'Sci-fi comedy'),
        array('978-0-441-00590-0', 'Ender\'s Game', 'Orson Scott Card', 'Tor Books', 1985, 'Science Fiction', 'English', 324, 4, 3, 15.99, 'D-404', 'Military sci-fi'),
        array('978-0-06-105417-7', 'Fahrenheit 451', 'Ray Bradbury', 'Ballantine Books', 1953, 'Science Fiction', 'English', 249, 3, 2, 12.99, 'D-405', 'Dystopian classic'),
        
        // Classic Literature
        array('978-0-14-143951-8', 'Pride and Prejudice', 'Jane Austen', 'T. Egerton', 1813, 'Classic', 'English', 432, 4, 3, 11.99, 'E-501', 'Romantic classic'),
        array('978-0-14-144926-5', 'Jane Eyre', 'Charlotte Brontë', 'Smith, Elder & Co.', 1847, 'Classic', 'English', 507, 3, 2, 12.99, 'E-502', 'Gothic romance'),
        array('978-0-14-303943-3', 'Wuthering Heights', 'Emily Brontë', 'Thomas Cautley Newby', 1847, 'Classic', 'English', 416, 3, 2, 11.99, 'E-503', 'Gothic novel'),
        array('978-0-14-062426-4', 'Crime and Punishment', 'Fyodor Dostoevsky', 'The Russian Messenger', 1866, 'Classic', 'English', 671, 3, 2, 18.99, 'E-504', 'Psychological fiction'),
        array('978-0-14-044792-2', 'War and Peace', 'Leo Tolstoy', 'The Russian Messenger', 1869, 'Classic', 'English', 1225, 2, 1, 24.99, 'E-505', 'Historical novel'),
        
        // Non-Fiction & Biography
        array('978-1-4516-7388-7', 'Steve Jobs', 'Walter Isaacson', 'Simon & Schuster', 2011, 'Biography', 'English', 656, 4, 3, 21.99, 'F-601', 'Tech biography'),
        array('978-0-385-49081-8', 'The Diary of a Young Girl', 'Anne Frank', 'Contact Publishing', 1947, 'Biography', 'English', 283, 5, 4, 13.99, 'F-602', 'Holocaust diary'),
        array('978-0-7432-7357-1', 'A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 1988, 'Science', 'English', 256, 3, 2, 16.99, 'G-701', 'Popular science'),
        array('978-0-06-251690-4', 'Sapiens', 'Yuval Noah Harari', 'Harper', 2011, 'History', 'English', 443, 5, 3, 19.99, 'G-702', 'Human history'),
        array('978-0-385-50420-1', 'Educated', 'Tara Westover', 'Random House', 2018, 'Memoir', 'English', 334, 4, 3, 17.99, 'F-603', 'Educational memoir'),
        
        // Self-Help & Psychology
        array('978-1-59184-283-9', 'The Power of Now', 'Eckhart Tolle', 'Namaste Publishing', 1997, 'Self-Help', 'English', 236, 4, 3, 14.99, 'H-801', 'Spiritual guide'),
        array('978-0-06-256254-4', 'Atomic Habits', 'James Clear', 'Avery', 2018, 'Self-Help', 'English', 320, 6, 4, 16.99, 'H-802', 'Habit formation'),
        array('978-0-7432-6968-1', 'The 7 Habits of Highly Effective People', 'Stephen Covey', 'Free Press', 1989, 'Self-Help', 'English', 381, 4, 3, 15.99, 'H-803', 'Personal development'),
        array('978-0-06-093264-0', 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus and Giroux', 2011, 'Psychology', 'English', 499, 3, 2, 18.99, 'H-804', 'Behavioral economics'),
        
        // Business & Economics
        array('978-0-06-206353-0', 'Zero to One', 'Peter Thiel', 'Crown Business', 2014, 'Business', 'English', 224, 4, 3, 17.99, 'I-901', 'Startup guide'),
        array('978-0-06-112008-5', 'The Lean Startup', 'Eric Ries', 'Crown Business', 2011, 'Business', 'English', 296, 4, 3, 16.99, 'I-902', 'Entrepreneurship'),
        array('978-0-307-88789-4', 'Good to Great', 'Jim Collins', 'HarperBusiness', 2001, 'Business', 'English', 300, 3, 2, 18.99, 'I-903', 'Business strategy'),
        
        // Children & Young Adult
        array('978-0-06-440055-8', 'Where the Wild Things Are', 'Maurice Sendak', 'Harper & Row', 1963, 'Children', 'English', 48, 6, 5, 8.99, 'J-1001', 'Picture book'),
        array('978-0-7432-5405-2', 'The Giving Tree', 'Shel Silverstein', 'Harper & Row', 1964, 'Children', 'English', 64, 5, 4, 9.99, 'J-1002', 'Children\'s classic'),
        array('978-0-06-440016-9', 'Charlotte\'s Web', 'E.B. White', 'Harper & Brothers', 1952, 'Children', 'English', 192, 5, 4, 10.99, 'J-1003', 'Children\'s novel'),
        array('978-0-14-241037-2', 'The Fault in Our Stars', 'John Green', 'Dutton Books', 2012, 'Young Adult', 'English', 313, 5, 3, 14.99, 'J-1004', 'YA romance'),
        array('978-0-439-02348-1', 'The Hunger Games', 'Suzanne Collins', 'Scholastic', 2008, 'Young Adult', 'English', 374, 6, 4, 15.99, 'J-1005', 'Dystopian YA')
    );
    
    $bookCount = 0;
    foreach ($books as $book) {
        $sql = "INSERT INTO books (isbn, book_title, author, publisher, publication_year, category, language, pages, total_copies, available_copies, price, location, description, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ON DUPLICATE KEY UPDATE book_title=VALUES(book_title)";
        $db->execute($sql, $book);
        $bookCount++;
    }
    echo "<p class='success'>✓ Added {$bookCount} books</p>";
    
    // ==================== MEMBERS (30+) ====================
    echo "<h2>👥 Adding 30+ Members...</h2>";
    
    $members = array(
        // Students
        array('M000001', 'Raj Kumar', 'raj.kumar@email.com', '9876543210', '123 MG Road, Delhi', 'student', 3),
        array('M000002', 'Priya Sharma', 'priya.sharma@email.com', '9876543211', '456 Park Street, Mumbai', 'student', 3),
        array('M000005', 'Vikram Singh', 'vikram.singh@email.com', '9876543214', '654 Marine Drive, Mumbai', 'student', 3),
        array('M000007', 'Rahul Verma', 'rahul.verma@email.com', '9876543216', '147 Residency Road, Bangalore', 'student', 3),
        array('M000011', 'Sanjay Patel', 'sanjay.patel@email.com', '9876543220', '852 Linking Road, Mumbai', 'student', 3),
        array('M000013', 'Neha Kapoor', 'neha.kapoor@email.com', '9876543222', '159 Connaught Place, Delhi', 'student', 3),
        array('M000015', 'Rohit Malhotra', 'rohit.malhotra@email.com', '9876543224', '753 Brigade Road, Bangalore', 'student', 3),
        array('M000017', 'Simran Kaur', 'simran.kaur@email.com', '9876543226', '951 Sector 17, Chandigarh', 'student', 3),
        array('M000019', 'Karan Mehta', 'karan.mehta@email.com', '9876543228', '357 MG Road, Pune', 'student', 3),
        array('M000021', 'Riya Sinha', 'riya.sinha@email.com', '9876543230', '159 Park Street, Kolkata', 'student', 3),
        
        // Faculty
        array('M000003', 'Amit Patel', 'amit.patel@email.com', '9876543212', '789 Brigade Road, Bangalore', 'faculty', 5),
        array('M000008', 'Meera Iyer', 'meera.iyer@email.com', '9876543217', '258 Anna Salai, Chennai', 'faculty', 5),
        array('M000014', 'Dr. Suresh Kumar', 'suresh.kumar@email.com', '9876543223', '264 Anna Nagar, Chennai', 'faculty', 5),
        array('M000016', 'Prof. Anjali Deshmukh', 'anjali.deshmukh@email.com', '9876543225', '468 Viman Nagar, Pune', 'faculty', 5),
        array('M000018', 'Dr. Rajesh Khanna', 'rajesh.khanna@email.com', '9876543227', '753 Model Town, Delhi', 'faculty', 5),
        array('M000020', 'Prof. Sunita Rao', 'sunita.rao@email.com', '9876543229', '852 Koramangala, Bangalore', 'faculty', 5),
        
        // Public
        array('M000006', 'Anita Desai', 'anita.desai@email.com', '9876543215', '987 MG Road, Pune', 'public', 3),
        array('M000009', 'Arjun Reddy', 'arjun.reddy@email.com', '9876543218', '369 Necklace Road, Hyderabad', 'public', 3),
        array('M000012', 'Deepa Nair', 'deepa.nair@email.com', '9876543221', '963 Anna Nagar, Chennai', 'public', 3),
        array('M000022', 'Manoj Tiwari', 'manoj.tiwari@email.com', '9876543231', '741 Gomti Nagar, Lucknow', 'public', 3),
        array('M000023', 'Pooja Reddy', 'pooja.reddy@email.com', '9876543232', '852 Banjara Hills, Hyderabad', 'public', 3),
        array('M000024', 'Arun Kumar', 'arun.kumar@email.com', '9876543233', '963 T Nagar, Chennai', 'public', 3),
        array('M000025', 'Lakshmi Menon', 'lakshmi.menon@email.com', '9876543234', '147 Indiranagar, Bangalore', 'public', 3),
        array('M000026', 'Vivek Oberoi', 'vivek.oberoi@email.com', '9876543235', '258 Juhu, Mumbai', 'public', 3),
        
        // Premium
        array('M000004', 'Sneha Gupta', 'sneha.gupta@email.com', '9876543213', '321 Connaught Place, Delhi', 'premium', 10),
        array('M000010', 'Kavita Menon', 'kavita.menon@email.com', '9876543219', '741 MG Road, Kochi', 'premium', 10),
        array('M000027', 'Rajiv Malhotra', 'rajiv.malhotra@email.com', '9876543236', '369 Defence Colony, Delhi', 'premium', 10),
        array('M000028', 'Shweta Agarwal', 'shweta.agarwal@email.com', '9876543237', '741 Bandra, Mumbai', 'premium', 10),
        array('M000029', 'Nikhil Joshi', 'nikhil.joshi@email.com', '9876543238', '852 Whitefield, Bangalore', 'premium', 10),
        array('M000030', 'Divya Krishnan', 'divya.krishnan@email.com', '9876543239', '963 Alwarpet, Chennai', 'premium', 10)
    );
    
    $memberCount = 0;
    $expiryDate = date('Y-m-d', strtotime('+1 year'));
    foreach ($members as $member) {
        $sql = "INSERT INTO members (member_id, member_name, email, phone_number, address, membership_type, max_books_allowed, expiry_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ON DUPLICATE KEY UPDATE member_name=VALUES(member_name)";
        $db->execute($sql, array_merge($member, array($expiryDate)));
        $memberCount++;
    }
    echo "<p class='success'>✓ Added {$memberCount} members</p>";
    
    // ==================== CIRCULATION (15+) ====================
    echo "<h2>🔄 Adding Circulation Records...</h2>";
    
    $circulations = array(
        // Currently issued
        array('M000001', '978-0-330-25864-8', 'DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 9 DAY)', 'issued'),
        array('M000002', '978-0-14-028329-5', 'DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 11 DAY)', 'issued'),
        array('M000003', '978-0-7432-7356-5', 'DATE_SUB(CURRENT_DATE, INTERVAL 20 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)', 'issued'),
        array('M000005', '978-0-545-01022-1', 'DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 12 DAY)', 'issued'),
        array('M000007', '978-0-439-70936-2', 'DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)', 'issued'),
        array('M000009', '978-0-14-118776-1', 'DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 13 DAY)', 'issued'),
        array('M000011', '978-0-06-093546-7', 'DATE_SUB(CURRENT_DATE, INTERVAL 4 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 10 DAY)', 'issued'),
        array('M000013', '978-0-307-58837-1', 'DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)', 'DATE_ADD(CURRENT_DATE, INTERVAL 8 DAY)', 'issued'),
        
        // Returned
        array('M000004', '978-0-06-112008-4', 'DATE_SUB(CURRENT_DATE, INTERVAL 15 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)', 'returned'),
        array('M000006', '978-0-452-28423-4', 'DATE_SUB(CURRENT_DATE, INTERVAL 25 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 11 DAY)', 'returned'),
        array('M000008', '978-0-14-143951-8', 'DATE_SUB(CURRENT_DATE, INTERVAL 18 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 4 DAY)', 'returned'),
        array('M000010', '978-1-4516-7388-7', 'DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 16 DAY)', 'returned'),
        array('M000012', '978-0-06-251690-4', 'DATE_SUB(CURRENT_DATE, INTERVAL 22 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 8 DAY)', 'returned'),
        array('M000014', '978-0-345-39180-3', 'DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY)', 'CURRENT_DATE', 'returned'),
        array('M000016', '978-0-439-13959-1', 'DATE_SUB(CURRENT_DATE, INTERVAL 28 DAY)', 'DATE_SUB(CURRENT_DATE, INTERVAL 14 DAY)', 'returned')
    );
    
    $circulationCount = 0;
    foreach ($circulations as $circ) {
        $sql = "INSERT INTO issued_status (issued_member_id, issued_book_isbn, issued_date, due_date, status, issued_by)
                VALUES ('{$circ[0]}', '{$circ[1]}', {$circ[2]}, {$circ[3]}, '{$circ[4]}', 'admin')
                ON DUPLICATE KEY UPDATE status=VALUES(status)";
        $db->execute($sql);
        $circulationCount++;
    }
    
    // Update available copies for issued books
    $issuedBooks = array('978-0-330-25864-8', '978-0-14-028329-5', '978-0-7432-7356-5', '978-0-545-01022-1', 
                         '978-0-439-70936-2', '978-0-14-118776-1', '978-0-06-093546-7', '978-0-307-58837-1');
    foreach ($issuedBooks as $isbn) {
        $db->execute("UPDATE books SET available_copies = available_copies - 1 WHERE isbn = ?", array($isbn));
    }
    
    echo "<p class='success'>✓ Added {$circulationCount} circulation records</p>";
    
    // ==================== FINES (8+) ====================
    echo "<h2>💰 Adding Fines...</h2>";
    
    $fines = array(
        array('M000003', 70.00, 'Late Return - 14 days overdue', 'unpaid'),
        array('M000005', 25.00, 'Late Return - 5 days overdue', 'paid'),
        array('M000006', 55.00, 'Late Return - 11 days overdue', 'paid'),
        array('M000007', 15.00, 'Late Return - 3 days overdue', 'unpaid'),
        array('M000009', 10.00, 'Lost Book Damage', 'unpaid'),
        array('M000010', 80.00, 'Late Return - 16 days overdue', 'paid'),
        array('M000012', 40.00, 'Late Return - 8 days overdue', 'unpaid'),
        array('M000016', 70.00, 'Late Return - 14 days overdue', 'paid')
    );
    
    $fineCount = 0;
    foreach ($fines as $fine) {
        $sql = "INSERT INTO fines (member_id, fine_amount, fine_reason, payment_status)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE fine_amount=VALUES(fine_amount)";
        $db->execute($sql, $fine);
        $fineCount++;
        
        if ($fine[3] === 'unpaid') {
            $db->execute("UPDATE members SET outstanding_fines = outstanding_fines + ? WHERE member_id = ?", array($fine[1], $fine[0]));
        }
    }
    echo "<p class='success'>✓ Added {$fineCount} fines</p>";
    
    // ==================== RESERVATIONS (10+) ====================
    echo "<h2>📅 Adding Reservations...</h2>";
    
    $reservations = array(
        array('M000015', '978-0-7432-7356-5', 1),
        array('M000017', '978-0-545-01022-1', 1),
        array('M000019', '978-0-7432-7356-5', 2),
        array('M000021', '978-0-439-70936-2', 1),
        array('M000023', '978-0-14-028329-5', 1),
        array('M000025', '978-0-330-25864-8', 1),
        array('M000027', '978-0-06-093546-7', 1),
        array('M000029', '978-0-307-58837-1', 1),
        array('M000022', '978-0-14-118776-1', 1),
        array('M000024', '978-0-439-13959-1', 1)
    );
    
    $reservationCount = 0;
    $expiryDate = date('Y-m-d', strtotime('+7 days'));
    foreach ($reservations as $res) {
        $sql = "INSERT INTO reservations (member_id, book_isbn, expiry_date, queue_position, status)
                VALUES (?, ?, ?, ?, 'active')
                ON DUPLICATE KEY UPDATE status=VALUES(status)";
        $db->execute($sql, array($res[0], $res[1], $expiryDate, $res[2]));
        $reservationCount++;
    }
    echo "<p class='success'>✓ Added {$reservationCount} reservations</p>";
    
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
    
    echo "<h2 class='success'>✅ Enhanced Sample Data Inserted Successfully!</h2>";
    echo "<p><strong>Total Records:</strong> " . ($bookCount + $memberCount + $circulationCount + $fineCount + $reservationCount) . " records inserted</p>";
    echo "<p>Your library system now has comprehensive test data with diverse categories and realistic scenarios.</p>";
    echo "<a href='index.html' class='btn'>Open Library System →</a>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Error Occurred</h2>";
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check:</p>";
    echo "<ul style='list-style:disc;margin-left:40px'>
            <li>Database connection in config.php</li>
            <li>Database 'library_management_system' exists</li>
            <li>All tables are created (run database_schema.sql)</li>
            <li>MySQL server is running</li>
          </ul>";
}

echo "</div></body></html>";
?>
