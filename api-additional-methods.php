<?php
/**
 * ADD THESE METHODS TO YOUR api.php LibraryAPI class
 * These handle POST requests for creating books, members, and issuing books
 */

// ADD THESE METHODS INSIDE THE LibraryAPI class

// For MEMBERS - Add POST handling
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

private function createMember() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['member_name']) || empty($input['phone_number'])) {
            $this->sendError('Missing required fields: member_name, phone_number', 400);
        }
        
        // Generate member ID if not provided
        $memberId = isset($input['member_id']) ? $input['member_id'] : 'M' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        // Set expiry date (1 year from now)
        $expiryDate = date('Y-m-d', strtotime('+1 year'));
        
        // Set max books based on membership type
        $maxBooks = 3; // default
        if (isset($input['membership_type'])) {
            switch($input['membership_type']) {
                case 'faculty': $maxBooks = 5; break;
                case 'premium': $maxBooks = 10; break;
                case 'student': $maxBooks = 3; break;
                case 'public': $maxBooks = 3; break;
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
        
        $this->sendSuccess('Member created successfully', array('member_id' => $memberId), 201);
    } catch (Exception $e) {
        $this->sendError('Failed to create member: ' . $e->getMessage(), 500);
    }
}

// For CIRCULATION - Update to handle POST (issue book)
private function handleCirculation() {
    switch ($this->method) {
        case 'GET':
            return $this->getCirculation();
        case 'POST':
            // Check if it's issue or return
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

private function issueBook() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['member_id']) || empty($input['book_isbn'])) {
            $this->sendError('Missing required fields: member_id, book_isbn', 400);
        }
        
        $memberId = $input['member_id'];
        $bookIsbn = $input['book_isbn'];
        
        // Check if book exists and is available
        $book = $this->db->fetch("SELECT * FROM books WHERE isbn = ? AND status = 'active'", array($bookIsbn));
        if (!$book) {
            $this->sendError('Book not found', 404);
        }
        
        if ($book['available_copies'] <= 0) {
            $this->sendError('Book not available', 400);
        }
        
        // Check if member exists
        $member = $this->db->fetch("SELECT * FROM members WHERE member_id = ? AND status = 'active'", array($memberId));
        if (!$member) {
            $this->sendError('Member not found', 404);
        }
        
        // Check member's current issued books
        $issuedCount = $this->db->fetch("SELECT COUNT(*) as count FROM issued_status WHERE issued_member_id = ? AND status = 'issued'", array($memberId));
        if ($issuedCount['count'] >= $member['max_books_allowed']) {
            $this->sendError('Member has reached maximum books limit', 400);
        }
        
        // Issue the book
        $issuedDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+14 days'));
        
        $sql = "INSERT INTO issued_status (issued_member_id, issued_book_isbn, issued_date, due_date, status, issued_by) 
                VALUES (?, ?, ?, ?, 'issued', 'admin')";
        
        $this->db->execute($sql, array($memberId, $bookIsbn, $issuedDate, $dueDate));
        
        // Update available copies
        $this->db->execute("UPDATE books SET available_copies = available_copies - 1 WHERE isbn = ?", array($bookIsbn));
        
        $this->sendSuccess('Book issued successfully', array(
            'member_id' => $memberId,
            'book_isbn' => $bookIsbn,
            'due_date' => $dueDate
        ), 201);
    } catch (Exception $e) {
        $this->sendError('Failed to issue book: ' . $e->getMessage(), 500);
    }
}

private function returnBook($issuedId) {
    try {
        // Get the issued record
        $issued = $this->db->fetch("SELECT * FROM issued_status WHERE issued_id = ?", array($issuedId));
        if (!$issued) {
            $this->sendError('Issue record not found', 404);
        }
        
        if ($issued['status'] !== 'issued') {
            $this->sendError('Book already returned', 400);
        }
        
        $returnDate = date('Y-m-d');
        
        // Update issued status
        $sql = "UPDATE issued_status SET return_date = ?, status = 'returned' WHERE issued_id = ?";
        $this->db->execute($sql, array($returnDate, $issuedId));
        
        // Update available copies
        $this->db->execute("UPDATE books SET available_copies = available_copies + 1 WHERE isbn = ?", array($issued['issued_book_isbn']));
        
        // Calculate fine if overdue
        $dueDate = strtotime($issued['due_date']);
        $returnDateTime = strtotime($returnDate);
        
        if ($returnDateTime > $dueDate) {
            $daysOverdue = floor(($returnDateTime - $dueDate) / (60 * 60 * 24));
            $fineAmount = $daysOverdue * 5.00; // $5 per day
            
            // Create fine record
            $fineSql = "INSERT INTO fines (member_id, fine_amount, fine_reason, payment_status) 
                       VALUES (?, ?, ?, 'unpaid')";
            $this->db->execute($fineSql, array(
                $issued['issued_member_id'],
                $fineAmount,
                'Late Return - ' . $daysOverdue . ' days overdue'
            ));
            
            // Update member's outstanding fines
            $this->db->execute("UPDATE members SET outstanding_fines = outstanding_fines + ? WHERE member_id = ?", 
                             array($fineAmount, $issued['issued_member_id']));
        }
        
        $this->sendSuccess('Book returned successfully');
    } catch (Exception $e) {
        $this->sendError('Failed to return book: ' . $e->getMessage(), 500);
    }
}

// For RESERVATIONS - Add POST handling
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

private function createReservation() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['member_id']) || empty($input['book_isbn'])) {
            $this->sendError('Missing required fields: member_id, book_isbn', 400);
        }
        
        $memberId = $input['member_id'];
        $bookIsbn = $input['book_isbn'];
        
        // Check if book exists
        $book = $this->db->fetch("SELECT * FROM books WHERE isbn = ?", array($bookIsbn));
        if (!$book) {
            $this->sendError('Book not found', 404);
        }
        
        // Check if member exists
        $member = $this->db->fetch("SELECT * FROM members WHERE member_id = ?", array($memberId));
        if (!$member) {
            $this->sendError('Member not found', 404);
        }
        
        // Get queue position (count existing active reservations for this book + 1)
        $queuePos = $this->db->fetch("SELECT COUNT(*) as count FROM reservations WHERE book_isbn = ? AND status = 'active'", array($bookIsbn));
        $queuePosition = $queuePos['count'] + 1;
        
        $expiryDate = date('Y-m-d', strtotime('+7 days'));
        
        $sql = "INSERT INTO reservations (member_id, book_isbn, expiry_date, queue_position, status) 
                VALUES (?, ?, ?, ?, 'active')";
        
        $this->db->execute($sql, array($memberId, $bookIsbn, $expiryDate, $queuePosition));
        
        $this->sendSuccess('Reservation created successfully', array(
            'member_id' => $memberId,
            'book_isbn' => $bookIsbn,
            'queue_position' => $queuePosition
        ), 201);
    } catch (Exception $e) {
        $this->sendError('Failed to create reservation: ' . $e->getMessage(), 500);
    }
}

?>
