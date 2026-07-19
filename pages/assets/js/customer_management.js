// Helper for HTML escaping
function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[c]));
}

// Helper for CSV escaping
function csvEscape(val) {
    if (val === null || val === undefined) return '';
    const str = String(val);
    if (str.includes(',') || str.includes('"') || str.includes('\n')) {
        return '"' + str.replace(/"/g, '""') + '"';
    }
    return str;
}

// Toggle "Other Payment Method" input visibility
(function(){
    const pmSelect = document.getElementById('pmSelect');
    const pmOtherWrap = document.getElementById('pmOtherWrap');
    const pmOtherInput = document.getElementById('pmOtherInput');
    if (pmSelect) {
        const toggle = () => {
            if (pmSelect.value === 'Other') {
                pmOtherWrap.style.display = '';
                pmOtherInput?.focus();
            } else {
                pmOtherWrap.style.display = 'none';
                if (pmOtherInput) pmOtherInput.value = '';
            }
        };
        pmSelect.addEventListener('change', toggle);
        toggle();
    }
})();

// Create customer via AJAX
document.getElementById('createCustomerForm')?.addEventListener('submit', async function(e){
    e.preventDefault();
    const pmSelect = document.getElementById('pmSelect');
    const pmOtherInput = document.getElementById('pmOtherInput');
    const msg = document.getElementById('createMsg');

    // Require custom text when 'Other' selected
    if (pmSelect && pmSelect.value === 'Other') {
        if (!pmOtherInput || !pmOtherInput.value.trim()) {
            msg.innerHTML = '<div class="alert alert-warning">Please enter the other payment method.</div>';
            return;
        }
    }

    const form = new FormData(this);
    form.append('action','create_customer');
    const res = await fetch(location.pathname, {method:'POST', body: form});
    const data = await res.json();
    if (data.success) {
        msg.innerHTML = '<div class="alert alert-success">Customer created. <a href="view_customer_file.php?id='+data.id+'">Open file</a></div>';
        setTimeout(()=>location.reload(),900);
    } else {
        msg.innerHTML = '<div class="alert alert-danger">'+(data.message||'Error')+'</div>';
    }
});

// Add Money button open modal
document.querySelectorAll('.add-money-btn').forEach(btn=>{
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        document.getElementById('amCustomerId').value = id;
        document.getElementById('amCustomerName').textContent = name;
        document.getElementById('amAmount').value = '';
        document.getElementById('amMsg').innerHTML = '';
        new bootstrap.Modal(document.getElementById('addMoneyModal')).show();
    });
});

// Confirm add money
document.getElementById('amConfirmBtn')?.addEventListener('click', async () => {
    const id = document.getElementById('amCustomerId').value;
    const amount = parseFloat(document.getElementById('amAmount').value || 0);
    const pm = document.getElementById('amPaymentMethod')?.value || 'Cash';
    if (!id || amount <= 0) { 
        document.getElementById('amMsg').innerHTML = '<div class="alert alert-warning">Enter valid amount.</div>'; 
        return; 
    }
    const form = new FormData();
    form.append('action','add_money');
    form.append('customer_id', id);
    form.append('amount', amount);
    form.append('payment_method', pm);
    const res = await fetch('customer_management.php', {method:'POST', body: form});
    const data = await res.json();
    if (data.success) {
        document.getElementById('amMsg').innerHTML = '<div class="alert alert-success">Amount added.</div>';
        setTimeout(()=>location.reload(),700);
    } else {
        document.getElementById('amMsg').innerHTML = '<div class="alert alert-danger">'+(data.message||'Error')+'</div>';
    }
});

// Delete customer
document.querySelectorAll('.delete-customer-btn').forEach(btn=>{
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this customer file? This will remove all transactions.')) return;
        const id = btn.dataset.id;
        const form = new FormData();
        form.append('action','delete_customer');
        form.append('customer_id', id);
        const res = await fetch('customer_management.php', {method:'POST', body: form});
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.message || 'Delete failed');
    });
});

// Load transactions when accordion opened (Desktop)
document.querySelectorAll('#customersAccordion .accordion-button').forEach(btn=>{
    btn.addEventListener('click', async (e) => {
        const target = e.target.closest('.accordion-button');
        const collapseId = target.getAttribute('data-bs-target').substring(1);
        const customerId = collapseId.replace('collapse','');
        const container = document.getElementById('transContainer'+customerId);
        if (container.dataset.loaded) return;
        
        // Build filter form HTML
        const isNotStaff = window.customerMgmtConfig?.isNotStaff || false;
        const branchOptions = window.customerMgmtConfig?.branchOptions || '';
        
        const filterFormHtml = `
            <div class="transaction-filters mb-3 p-3 bg-light rounded">
                <form class="row g-2 trans-filter-form" data-customer-id="${customerId}">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small">Type</label>
                        <select name="trans_type" class="form-select form-select-sm">
                            <option value="all">All</option>
                            <option value="invoice">Invoice</option>
                            <option value="receipt">Receipt</option>
                        </select>
                    </div>
                    ${isNotStaff ? `
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small">Branch</label>
                        <select name="branch_filter" class="form-select form-select-sm">
                            <option value="">All Branches</option>
                            ${branchOptions}
                        </select>
                    </div>
                    ` : ''}
                    <div class="col-md-2 col-sm-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    </div>
                </form>
            </div>
            <div class="transactions-data-container"></div>
        `;
        
        container.innerHTML = filterFormHtml;
        
        // Function to load transactions with filters
        async function loadTransactions(filters = {}) {
            const dataContainer = container.querySelector('.transactions-data-container');
            dataContainer.innerHTML = '<div class="text-center text-muted p-3">Loading...</div>';
            
            const params = new URLSearchParams({
                fetch_transactions: '1',
                customer_id: customerId,
                ...filters
            });
            
            const res = await fetch('customer_management.php?' + params.toString());
            let data;
            try { 
                data = await res.json(); 
            } catch(err){
                dataContainer.innerHTML = '<div class="text-center text-danger p-3">Error loading transactions.</div>';
                return;
            }
            
            if (!data.success || !data.rows.length) { 
                dataContainer.innerHTML = '<div class="text-center text-muted p-3">No transactions found.</div>'; 
                return; 
            }

            // Build table HTML
            let html = '<div class="transactions-table"><table><thead><tr><th>Date & Time</th><th>Branch</th><th>Invoice/Receipt No.</th><th>Products</th><th class="text-center">Quantity</th><th class="text-end">Amount Paid</th><th class="text-end">Amount Credited</th><th>Payment Method</th><th>Status</th><th>Sold By</th></tr></thead><tbody>';
            
            data.rows.forEach(r=>{
                let prodDisplay = '';
                let totalQty = 0;
                try {
                    const pb = JSON.parse(r.products_bought || '[]');
                    if (Array.isArray(pb)) {
                        const parts = pb.map(p => {
                            const name = (p.name || p.product || '').toString();
                            const qty = parseInt(p.quantity || p.qty || 0) || 0;
                            totalQty += qty;
                            return `${escapeHtml(name)} x${qty}`;
                        });
                        prodDisplay = parts.join(', ');
                    } else {
                        prodDisplay = escapeHtml(String(r.products_bought || ''));
                    }
                } catch (err) {
                    prodDisplay = escapeHtml(String(r.products_bought || ''));
                }

                const paid = parseFloat(r.amount_paid || 0).toFixed(2);
                const credited = parseFloat(r.amount_credited || 0).toFixed(2);
                const soldBy = escapeHtml(r.sold_by || '');
                const invoiceReceiptNo = escapeHtml(r.invoice_receipt_no || '-');
                const branchName = escapeHtml(r.branch_name || 'Unknown');
                
                // Determine status badge
                let statusBadge = '';
                const status = r.status || '';
                const isRepayment = prodDisplay.toLowerCase().includes('repayment of invoice');
                
                if (isRepayment) {
                    const match = prodDisplay.match(/INV-\d{5}/i);
                    const originalInvoice = match ? match[0] : '';
                    statusBadge = `<button class="btn btn-sm view-original-invoice" data-invoice="${originalInvoice}" data-customer="${customerId}" title="View Original Invoice"><i class="fa fa-eye"></i></button>`;
                } else if (status === 'debtor') {
                    statusBadge = '<span class="badge bg-danger">Unpaid</span>';
                } else {
                    statusBadge = '<span class="badge bg-success">Paid</span>';
                }
                
                html += `<tr>
                           <td>${escapeHtml(r.date_time)}</td>
                           <td>${branchName}</td>
                           <td>${invoiceReceiptNo}</td>
                           <td>${prodDisplay || '-'}</td>
                           <td class="text-center">${totalQty}</td>
                           <td class="text-end">UGX ${paid}</td>
                           <td class="text-end">UGX ${credited}</td>
                           <td>${escapeHtml(r.payment_method || '')}</td>
                           <td>${statusBadge}</td>
                           <td>${soldBy}</td>
                         </tr>`;
            });
            html += '</tbody></table></div>';
            
            // Add pagination
            if (data.total_pages > 1) {
                html += '<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">';
                for (let p = 1; p <= data.total_pages; p++) {
                    const active = p === data.current_page ? 'active' : '';
                    html += `<li class="page-item ${active}"><a class="page-link page-link-trans" href="#" data-page="${p}">${p}</a></li>`;
                }
                html += '</ul></nav>';
                html += `<div class="text-center text-muted small">Showing page ${data.current_page} of ${data.total_pages} (${data.total_rows} total transactions)</div>`;
            }
            
            dataContainer.innerHTML = html;
            
            // Attach pagination click handlers
            dataContainer.querySelectorAll('.page-link-trans').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = link.getAttribute('data-page');
                    const currentFilters = Object.fromEntries(new FormData(container.querySelector('.trans-filter-form')));
                    loadTransactions({...currentFilters, page});
                });
            });
            
            // Attach click handlers for "View" buttons
            attachViewOriginalInvoiceHandlers(dataContainer);
        }
        
        // Attach filter form submit handler
        container.querySelector('.trans-filter-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const filters = Object.fromEntries(formData);
            loadTransactions(filters);
        });
        
        // Load initial data
        loadTransactions();
        container.dataset.loaded = '1';
    });
});

// Attach View Original Invoice handlers
function attachViewOriginalInvoiceHandlers(container) {
    container.querySelectorAll('.view-original-invoice').forEach(viewBtn => {
        viewBtn.addEventListener('click', async function() {
            const originalInvoice = this.getAttribute('data-invoice');
            const customerId = this.getAttribute('data-customer');
            if (!originalInvoice) {
                alert('Original invoice number not found');
                return;
            }
            
            try {
                const res = await fetch(`customer_management.php?fetch_transactions=1&customer_id=${customerId}`);
                const data = await res.json();
                
                if (data.success && data.rows) {
                    const originalTrans = data.rows.find(t => t.invoice_receipt_no === originalInvoice);
                    
                    if (originalTrans) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'invoice_preview.php';
                        form.target = '_blank';
                        form.style.display = 'none';
                        
                        const addField = (name, value) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = typeof value === 'string' ? value : JSON.stringify(value);
                            form.appendChild(input);
                        };
                        
                        let cart = [];
                        try {
                            cart = JSON.parse(originalTrans.products_bought || '[]');
                        } catch(e) {
                            cart = [];
                        }
                        
                        let total = 0;
                        if (Array.isArray(cart)) {
                            cart.forEach(item => {
                                const qty = parseFloat(item.quantity || item.qty || 0);
                                const price = parseFloat(item.price || 0);
                                total += qty * price;
                            });
                        }
                        
                        addField('cart', cart);
                        addField('total', total);
                        addField('payment_method', originalTrans.payment_method || 'Customer File');
                        addField('amount_paid', originalTrans.amount_paid || 0);
                        addField('balance', originalTrans.amount_credited || 0);
                        addField('invoice_no', originalInvoice);
                        addField('customer_name', 'Customer #' + customerId);
                        addField('customer_email', '');
                        addField('customer_contact', '');
                        addField('due_date', '');
                        
                        document.body.appendChild(form);
                        form.submit();
                        setTimeout(() => document.body.removeChild(form), 600);
                    } else {
                        alert(`Original invoice ${originalInvoice} not found in transaction history`);
                    }
                }
            } catch(err) {
                console.error('Error fetching original invoice:', err);
                alert('Error loading original invoice');
            }
        });
    });
}


// --- Per-customer report/export buttons on accordions ---
function attachCustomerActionHandlers() {
    document.querySelectorAll('.cust-report-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault(); e.stopPropagation();
            const id = btn.dataset.id, name = btn.dataset.name || 'Customer';
            generateCustomerReport(id, name);
        });
    });
    document.querySelectorAll('.cust-export-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault(); e.stopPropagation();
            const id = btn.dataset.id, name = btn.dataset.name || 'Customer';
            exportCustomerTransactions(id, name);
        });
    });
}

// Build printable report for a single customer's transactions
async function generateCustomerReport(customerId, customerName){
    const res = await fetch('customer_management.php?fetch_transactions=1&customer_id='+encodeURIComponent(customerId));
    const data = await res.json();
    if (!data.success || !data.rows.length) { alert('No transactions found for '+customerName); return; }

    let totalPaid = 0, totalCredited = 0;
    const rowsHtml = data.rows.map(r => {
        let prodDisplay = '', totalQty = 0;
        try {
            const pb = JSON.parse(r.products_bought || '[]');
            if (Array.isArray(pb)) {
                prodDisplay = pb.map(p => {
                    const qty = parseInt(p.quantity || p.qty || 0) || 0;
                    totalQty += qty;
                    return `${escapeHtml(p.name || p.product || '')} x${qty}`;
                }).join(', ');
            } else { prodDisplay = escapeHtml(String(r.products_bought || '')); }
        } catch { prodDisplay = escapeHtml(String(r.products_bought || '')); }
        totalPaid += parseFloat(r.amount_paid || 0);
        totalCredited += parseFloat(r.amount_credited || 0);
        return `<tr><td>${escapeHtml(r.date_time||'')}</td><td>${escapeHtml(r.branch_name || 'Unknown')}</td><td>${escapeHtml(r.invoice_receipt_no || '-')}</td><td>${prodDisplay||'-'}</td><td class="text-center">${totalQty}</td><td class="text-end">UGX ${parseFloat(r.amount_paid || 0).toFixed(2)}</td><td class="text-end">UGX ${parseFloat(r.amount_credited || 0).toFixed(2)}</td><td>${escapeHtml(r.payment_method||'')}</td><td>${escapeHtml(r.sold_by||'')}</td></tr>`;
    }).join('');

    const html = `
<!DOCTYPE html>
<html>
<head>
    <title>${escapeHtml(customerName)} - Transactions Report</title>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background:#f8f9fa; color:#222; margin:0; padding:0; }
        .report-container { max-width: 900px; margin: 2rem auto; background:#fff; border-radius:14px; box-shadow:0 4px 24px #0002; padding:2rem 2.5rem; }
        .report-header { text-align:center; margin-bottom:2rem; }
        .report-title { font-size:2rem; font-weight:bold; color:#1abc9c; margin-bottom:.4rem; }
        .report-meta { font-size:1.05rem; color:#555; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { padding:.7rem 1rem; border-bottom:1px solid #e0e0e0; font-size:1rem; }
        th { background:#1abc9c; color:#fff; font-weight:600; }
        tbody tr:nth-child(even) { background:#f4f6f9; }
        tbody tr:hover { background:#e0f7fa; }
        .print-btn { display:block; margin:1.5rem auto 0; padding:.7rem 2.5rem; font-size:1.1rem; background:#1abc9c; color:#fff; border:none; border-radius:8px; font-weight:bold; cursor:pointer; box-shadow:0 2px 8px #0002; }
        tfoot td { font-weight:bold; }
        @media print {
            .print-btn { display:none; }
            .report-container { box-shadow:none; border-radius:0; padding:.5rem; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <div class="report-title">Customer Transactions</div>
            <div class="report-meta">
                Customer: ${escapeHtml(customerName)}<br>
                Generated: ${new Date().toLocaleString()}
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Branch</th>
                    <th>Invoice/Receipt No.</th>
                    <th>Products</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Amount Paid</th>
                    <th class="text-end">Amount Credited</th>
                    <th>Payment Method</th>
                    <th>Sold By</th>
                </tr>
            </thead>
            <tbody>${rowsHtml}</tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end">Totals</td>
                    <td class="text-end">UGX ${totalPaid.toFixed(2)}</td>
                    <td class="text-end">UGX ${totalCredited.toFixed(2)}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        <button class="print-btn" onclick="window.print()">Print Report</button>
    </div>
</body>
</html>`;
    const w = window.open('', '_blank');
    w.document.write(html);
    w.document.close();
}

// Export single customer's transactions to CSV (Excel-compatible)
async function exportCustomerTransactions(customerId, customerName){
    const res = await fetch('customer_management.php?fetch_transactions=1&customer_id='+encodeURIComponent(customerId));
    const data = await res.json();
    if (!data.success || !data.rows.length) { alert('No transactions found for '+customerName); return; }

    const header = ['Date & Time','Branch','Invoice/Receipt No.','Products','Quantity','Amount Paid','Amount Credited','Payment Method','Sold By'];
    const csvRows = [header.join(',')];

    data.rows.forEach(r => {
        let prodDisplay = '', totalQty = 0;
        try {
            const pb = JSON.parse(r.products_bought || '[]');
            if (Array.isArray(pb)) {
                prodDisplay = pb.map(p => {
                    const name = (p.name || p.product || '').toString();
                    const qty = parseInt(p.quantity || p.qty || 0) || 0;
                    totalQty += qty;
                    return `${name} x${qty}`;
                }).join('; ');
            } else { prodDisplay = String(r.products_bought || ''); }
        } catch { prodDisplay = String(r.products_bought || ''); }

        const row = [
            csvEscape(r.date_time||''),
            csvEscape(r.branch_name||'Unknown'),
            csvEscape(r.invoice_receipt_no||''),
            csvEscape(prodDisplay||''),
            String(totalQty),
            String(parseFloat(r.amount_paid||0)),
            String(parseFloat(r.amount_credited||0)),
            csvEscape(r.payment_method||''),
            csvEscape(r.sold_by||'')
        ];
        csvRows.push(row.join(','));
    });

    const blob = new Blob([csvRows.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `customer_${customerId}_transactions.csv`;
    document.body.appendChild(a); a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// --- Generate ALL customers report (for Manage tab) ---
document.getElementById('btnGenerateReport')?.addEventListener('click', () => {
    const table = document.getElementById('manageCustomersTable');
    if (!table) { alert('No customer data to report'); return; }

    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const rowsHtml = rows.map(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length < 4) return '';
        const name = cells[0]?.textContent?.trim() || '';
        const contact = cells[1]?.textContent?.trim() || '';
        const credited = cells[2]?.textContent?.replace(/[^\d.-]/g, '') || '0';
        const balance = cells[3]?.textContent?.replace(/[^\d.-]/g, '') || '0';
        return `<tr>
            <td>${escapeHtml(name)}</td>
            <td>${escapeHtml(contact)}</td>
            <td class="text-end">UGX ${parseFloat(credited).toFixed(2)}</td>
            <td class="text-end">UGX ${parseFloat(balance).toFixed(2)}</td>
        </tr>`;
    }).join('');

    const html = `
<!DOCTYPE html>
<html>
<head>
    <title>All Customers Report</title>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background:#f8f9fa; color:#222; margin:0; padding:0; }
        .report-container { max-width: 800px; margin: 2rem auto; background:#fff; border-radius:14px; box-shadow:0 4px 24px #0002; padding:2rem 2.5rem; }
        .report-header { text-align:center; margin-bottom:2rem; }
        .report-title { font-size:2rem; font-weight:bold; color:#1abc9c; margin-bottom:.4rem; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { padding:.7rem 1rem; border-bottom:1px solid #e0e0e0; font-size:1rem; }
        th { background:#1abc9c; color:#fff; font-weight:600; }
        tbody tr:nth-child(even) { background:#f4f6f9; }
        tbody tr:hover { background:#e0f7fa; }
        .text-end { text-align:right; }
        .print-btn { display:block; margin:1.5rem auto 0; padding:.7rem 2.5rem; font-size:1.1rem; background:#1abc9c; color:#fff; border:none; border-radius:8px; font-weight:bold; cursor:pointer; box-shadow:0 2px 8px #0002; }
        @media print { .print-btn { display:none; } .report-container { box-shadow:none; border-radius:0; padding:.5rem; } }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <div class="report-title">All Customers Report</div>
            <div class="report-meta">Generated: ${new Date().toLocaleString()}</div>
        </div>
        <table>
            <thead>
                <tr><th>Name</th><th>Contact</th><th class="text-end">Amount Credited</th><th class="text-end">Account Balance</th></tr>
            </thead>
            <tbody>${rowsHtml}</tbody>
        </table>
        <button class="print-btn" onclick="window.print()">Print Report</button>
    </div>
</body>
</html>`;
    const w = window.open('', '_blank');
    w.document.write(html);
    w.document.close();
});

// --- Export ALL customers to CSV ---
document.getElementById('btnExportExcel')?.addEventListener('click', () => {
    const table = document.getElementById('manageCustomersTable');
    if (!table) { alert('No customer data to export'); return; }

    const header = ['Name', 'Contact', 'Amount Credited', 'Account Balance'];
    const csvRows = [header.join(',')];

    const rows = Array.from(table.querySelectorAll('tbody tr'));
    rows.forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length < 4) return;
        const name = cells[0]?.textContent?.trim() || '';
        const contact = cells[1]?.textContent?.trim() || '';
        const credited = cells[2]?.textContent?.replace(/[^\d.-]/g, '') || '0';
        const balance = cells[3]?.textContent?.replace(/[^\d.-]/g, '') || '0';
        const row = [
            csvEscape(name),
            csvEscape(contact),
            credited,
            balance
        ];
        csvRows.push(row.join(','));
    });
    const blob = new Blob([csvRows.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'all_customers.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
});

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    attachCustomerActionHandlers();

});
