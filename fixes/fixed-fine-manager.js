/**
 * FIX FINE PAYMENT BUTTON - Add this to your app.js
 * Update the FineManager to handle payment button clicks
 */

// REPLACE your existing FineManager with this updated version:

const FineManager = {
    currentPage: 1,

    async render(page) {
        page = page || 1;
        this.currentPage = page;
        
        const content = document.getElementById('content');
        Utils.showLoading(content);

        try {
            const response = await Utils.request('fines?page=' + page + '&limit=20');
            const fines = response.data.fines;
            const pagination = response.data.pagination;

            const tableConfig = {
                data: fines,
                columns: [
                    { field: 'fine_id', label: 'ID' },
                    { field: 'member_name', label: 'Member' },
                    { 
                        field: 'fine_amount', 
                        label: 'Amount',
                        formatter: function(val) {
                            return Utils.formatCurrency(val);
                        }
                    },
                    { field: 'fine_reason', label: 'Reason' },
                    { 
                        field: 'fine_date', 
                        label: 'Date',
                        formatter: function(val) {
                            return Utils.formatDate(val);
                        }
                    },
                    { 
                        field: 'payment_status', 
                        label: 'Status',
                        formatter: function(val) {
                            return '<span class="status-badge status-' + val + '">' + val + '</span>';
                        }
                    }
                ],
                pagination: pagination,
                onPageChange: function(p) {
                    FineManager.render(p);
                },
                actions: function(row) {
                    if (row.payment_status === 'unpaid') {
                        return '<button class="btn-sm btn-success" data-action="pay" data-id="' + row.fine_id + '">Pay Fine</button>';
                    }
                    return '<span class="text-success">✓ Paid</span>';
                },
                actionHandlers: {
                    pay: function(id) {
                        FineManager.payFine(id);
                    }
                }
            };

            content.innerHTML = '<div class="page-header"><h1>Fines Management</h1></div>' + 
                               TableRenderer.render(tableConfig);

            TableRenderer.setupListeners(content, tableConfig);

        } catch (error) {
            content.innerHTML = '<div class="error-state">Failed to load fines</div>';
        }
    },

    async payFine(fineId) {
        if (!confirm('Process payment for this fine?')) return;
        
        try {
            const response = await Utils.request('fines/' + fineId + '/pay', { method: 'PUT' });
            
            if (response.success) {
                Utils.showSuccess('Payment processed successfully! Amount: $' + response.data.amount);
                this.render(this.currentPage); // Refresh the page
            }
        } catch (error) {
            Utils.showError('Payment processing failed: ' + error.message);
        }
    }
};

console.log('✓ Fixed FineManager with payment processing');
