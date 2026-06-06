<?php
/**
 * FIX ALL THREE ERRORS - Add/Replace these methods in your api.php
 * 1. Fine payment processing
 * 2. Book creation (POST)
 * 3. Reservation creation (POST)
 */

// ==================== FIX 1: FINES - Add payment processing ====================

// Update your handleFines() method to support PUT for payments:
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

// Add this NEW method after getFines():
private function payFine($fineId) {
    try {
        // Get fine details
        $fine = $this->db->fetch("SELECT * FROM fines WHERE fine_id = ?", array($fineId));
        if (!$fine) {
            $this->sendError('Fine not found', 404);
        }
        
        if ($fine['payment_status'] === 'paid') {
            $this->sendError('Fine already paid', 400);
        }
        
        // Update fine status to paid
        $paymentDate = date('Y-m-d');
        $sql = "UPDATE fines SET payment_status = 'paid', payment_date = ? WHERE fine_id = ?";
        $this->db->execute($sql, array($paymentDate, $fineId));
        
        // Update member's outstanding fines
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

// ==================== FIX 2: BOOKS - Ensure POST handler exists ====================

// Your handleBooks() should already have this, but here's the complete version:
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

// Make sure your createBook() method looks like this:
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

// ==================== FIX 3: RESERVATIONS - Add POST handler ====================

// Update your handleReservations() to support POST:
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

// Add this NEW method (if not already present):
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
        
        // Check if member already has an active reservation for this book
        $existing = $this->db->fetch("SELECT * FROM reservations WHERE member_id = ? AND book_isbn = ? AND status = 'active'", 
                                     array($memberId, $bookIsbn));
        if ($existing) {
            $this->sendError('Member already has an active reservation for this book', 400);
        }
        
        // Get queue position (count existing active reservations for this book + 1)
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

?>
