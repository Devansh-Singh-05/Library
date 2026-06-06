/**
 * FORM HANDLERS - Add this to your app.js file
 * This code handles all form submissions for books, members, circulation
 */

// Add these form handlers after the existing managers in app.js

// ==================== FORM SUBMISSION HANDLERS ====================

// Initialize form handlers when page loads
document.addEventListener('DOMContentLoaded', function() {
    
    // Book Form Handler
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
                const response = await Utils.request('books', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
                
                Utils.showSuccess('Book added successfully!');
                PageManager.closeModal('bookModal');
                bookForm.reset();
                
                // Refresh books page if currently on it
                if (LibraryApp.state.currentPage === 'books') {
                    BookManager.render();
                }
            } catch (error) {
                Utils.showError('Failed to add book: ' + error.message);
            }
        });
    }
    
    // Member Form Handler
    const memberForm = document.getElementById('memberForm');
    if (memberForm) {
        memberForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Generate member ID if not provided
            const memberId = 'M' + String(Date.now()).slice(-6);
            
            const formData = {
                member_id: memberId,
                member_name: document.getElementById('memberName').value,
                email: document.getElementById('memberEmail').value || null,
                phone_number: document.getElementById('memberPhone').value,
                address: document.getElementById('memberAddress').value || null,
                membership_type: document.getElementById('memberType').value,
                max_books_allowed: getMaxBooksForType(document.getElementById('memberType').value)
            };
            
            try {
                const response = await Utils.request('members', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
                
                Utils.showSuccess('Member added successfully! Member ID: ' + memberId);
                PageManager.closeModal('memberModal');
                memberForm.reset();
                
                // Refresh members page if currently on it
                if (LibraryApp.state.currentPage === 'members') {
                    MemberManager.render();
                }
            } catch (error) {
                Utils.showError('Failed to add member: ' + error.message);
            }
        });
    }
    
    // Issue Book Form Handler
    const issueForm = document.getElementById('issueForm');
    if (issueForm) {
        issueForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                member_id: document.getElementById('issueMemberId').value,
                book_isbn: document.getElementById('issueBookIsbn').value
            };
            
            try {
                const response = await Utils.request('circulation/issue', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
                
                Utils.showSuccess('Book issued successfully!');
                PageManager.closeModal('issueModal');
                issueForm.reset();
                
                // Refresh pages
                if (LibraryApp.state.currentPage === 'circulation') {
                    CirculationManager.render();
                } else if (LibraryApp.state.currentPage === 'books') {
                    BookManager.render();
                }
            } catch (error) {
                Utils.showError('Failed to issue book: ' + error.message);
            }
        });
    }
    
    // Reservation Form Handler
    const reservationForm = document.getElementById('reservationForm');
    if (reservationForm) {
        reservationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                member_id: document.getElementById('reserveMemberId').value,
                book_isbn: document.getElementById('reserveBookIsbn').value
            };
            
            try {
                const response = await Utils.request('reservations', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
                
                Utils.showSuccess('Reservation created successfully!');
                PageManager.closeModal('reservationModal');
                reservationForm.reset();
                
                // Refresh reservations page if currently on it
                if (LibraryApp.state.currentPage === 'reservations') {
                    ReservationManager.render();
                }
            } catch (error) {
                Utils.showError('Failed to create reservation: ' + error.message);
            }
        });
    }
});

// Helper function
function getMaxBooksForType(type) {
    switch(type) {
        case 'student': return 3;
        case 'faculty': return 5;
        case 'premium': return 10;
        case 'public': return 3;
        default: return 3;
    }
}

console.log('Form handlers loaded ✓');
