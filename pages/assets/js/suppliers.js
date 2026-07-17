// Helper function for HTML escaping
function escapeHtml(s) {
    s = (s === null || s === undefined) ? '' : String(s);
    return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// Delete supplier
document.querySelectorAll('.delete-supplier-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Delete this supplier?')) return;
        const id = btn.getAttribute('data-id');
        fetch('suppliers.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'action=delete_supplier&id=' + encodeURIComponent(id)
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
        });
    });
});

// Edit supplier
document.querySelectorAll('.edit-supplier-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editSupplierId').value = btn.getAttribute('data-id');
        document.getElementById('editSupplierName').value = btn.getAttribute('data-name');
        document.getElementById('editSupplierLocation').value = btn.getAttribute('data-location');
        document.getElementById('editSupplierContact').value = btn.getAttribute('data-contact');
        document.getElementById('editSupplierEmail').value = btn.getAttribute('data-email');
        new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
    });
});

// Handle edit supplier form submit
document.getElementById('editSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = new FormData(this);
    form.append('action', 'edit_supplier');
    fetch('suppliers.php', {
        method: 'POST',
        body: form
    }).then(async res => {
        let data;
        try {
            data = await res.json();
        } catch (err) {
            alert('Error: Could not update supplier. Please try again.');
            return;
        }
        if (data.success) location.reload();
        else alert('Failed to update supplier.');
    });
});

/* =========================
   Supplier Transactions
   ========================= */

/* Helper: fetch transactions for a supplier */
async function fetchTransactionsForSupplier(supplierId) {
    const form = new FormData();
    form.append('action','fetch_supplier_transactions');
    form.append('supplier_id', supplierId);
    try {
        const res = await fetch('suppliers.php', { method: 'POST', body: form });
        return await res.json();
    } catch (err) {
        return { success: false, rows: [] };
    }
}

/* Render transactions into a container element */
function renderTransactionsInto(container, data) {
    let html = '<div class="transactions-table"><table><thead><tr><th>Date & Time</th><th>Branch</th><th>Products</th><th class="text-center">Quantity</th><th class="text-end">Unit Price</th><th class="text-end">Amount</th><th>Payment Method</th><th class="text-end">Amount Paid</th><th class="text-end">Balance</th><th>Actions</th></tr></thead><tbody>';

    if (!data.success || !Array.isArray(data.rows) || !data.rows.length) {
        html += '<tr><td colspan="10" class="text-center text-muted">No transactions found.</td></tr>';
        html += '</tbody></table></div>';
        container.innerHTML = html;
        return;
    }

    data.rows.forEach(r => {
        const paid = parseFloat(r.amount_paid || 0).toFixed(2);
        const balance = parseFloat(r.balance || 0).toFixed(2);
        const unitPrice = parseFloat(r.unit_price || 0).toFixed(2);
        const originalAmount = parseFloat(r.original_amount || r.amount || 0).toFixed(2);
        const amount = originalAmount;
        const products = escapeHtml(r.products_supplied || '');
        const qty = escapeHtml(r.quantity || '');
        const method = escapeHtml(r.payment_method || '');
        const date = escapeHtml(r.date_time || '');
        const branch = escapeHtml(r.branch || '');
        let actions = '';

        if (parseFloat(paid) > 0 && parseFloat(balance) === 0) {
            actions = `<span class="badge bg-success">Cleared</span>`;
        } else if (parseFloat(paid) > 0 && parseFloat(balance) > 0) {
            actions = `<span class="badge bg-warning text-dark">Partial</span>`;
        } else if (parseFloat(balance) > 0) {
            const supplierId = r.supplier_id || '';
            actions = `<button class="btn btn-success btn-sm pay-supplier-btn" data-id="${r.id}" data-balance="${parseFloat(balance)}" data-supplier="${supplierId}" title="Pay">Pay</button>`;
        } else {
            actions = `<span class="badge bg-success">Cleared</span>`;
        }

        html += `<tr>
            <td>${date}</td>
            <td>${branch}</td>
            <td>${products}</td>
            <td class="text-center">${qty}</td>
            <td class="text-end">UGX ${unitPrice}</td>
            <td class="text-end">UGX ${amount}</td>
            <td>${method}</td>
            <td class="text-end">UGX ${paid}</td>
            <td class="text-end">UGX ${balance}</td>
            <td>${actions}</td>
        </tr>`;
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;

    // Reattach pay button events
    container.querySelectorAll('.pay-supplier-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById('paySupplierModal');
            if (modal && !document.getElementById('paySupplierId')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.id = 'paySupplierId';
                modal.appendChild(input);
            }
            document.getElementById('payTransId').value = btn.getAttribute('data-id');
            document.getElementById('payAmount').value = btn.getAttribute('data-balance');
            if (document.getElementById('paySupplierId')) {
                document.getElementById('paySupplierId').value = btn.getAttribute('data-supplier') || '';
            }
            document.getElementById('payMsg').innerHTML = '';
            new bootstrap.Modal(document.getElementById('paySupplierModal')).show();
        });
    });
}

/* Setup accordion loader for a given selector prefix (desktop or mobile) */
function setupAccordionLoader(selectorPrefix, isMobile = false) {
    document.querySelectorAll(selectorPrefix + ' .accordion-button').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const target = e.target.closest('.accordion-button');
            const collapseId = target.getAttribute('data-bs-target').substring(1);
            let supplierId = collapseId.replace('collapseS','');
            if (isMobile) supplierId = supplierId.replace('m','');
            const containerId = 'transContainerS' + supplierId + (isMobile ? 'm' : '');
            const container = document.getElementById(containerId);
            if (!container) return;
            if (container.dataset.loaded) return;
            container.innerHTML = '<div class="text-muted">Loading...</div>';
            const data = await fetchTransactionsForSupplier(supplierId);
            renderTransactionsInto(container, data);
            container.dataset.loaded = '1';
        });
    });
}

// Initialize both desktop and mobile accordions
setupAccordionLoader('#suppliersAccordion', false);
setupAccordionLoader('#suppliersAccordionMobile', true);

/* Pay Supplier Confirm */
document.getElementById('payConfirmBtn')?.addEventListener('click', async () => {
    const transId = document.getElementById('payTransId').value;
    const amount = parseFloat(document.getElementById('payAmount').value || 0);
    const supplierIdElem = document.getElementById('paySupplierId');
    const supplierId = supplierIdElem ? supplierIdElem.value : '';

    if (!transId || amount <= 0) {
        document.getElementById('payMsg').innerHTML = '<div class="alert alert-warning">Enter valid amount.</div>';
        return;
    }

    const form = new FormData();
    form.append('action','pay_supplier_balance');
    form.append('trans_id', transId);
    form.append('amount_paid', amount);

    try {
        const res = await fetch('suppliers.php', {method:'POST', body: form});
        const data = await res.json();
        if (data.success) {
            document.getElementById('payMsg').innerHTML = '<div class="alert alert-success">Payment recorded.</div>';
            if (supplierId) {
                const fresh = await fetchTransactionsForSupplier(supplierId);
                const cDesktop = document.getElementById('transContainerS' + supplierId);
                const cMobile = document.getElementById('transContainerS' + supplierId + 'm');
                if (cDesktop) renderTransactionsInto(cDesktop, fresh);
                if (cMobile) renderTransactionsInto(cMobile, fresh);
            } else {
                setTimeout(() => location.reload(), 700);
            }
            setTimeout(() => { 
                const m = bootstrap.Modal.getInstance(document.getElementById('paySupplierModal')); 
                if (m) m.hide(); 
            }, 800);
        } else {
            document.getElementById('payMsg').innerHTML = '<div class="alert alert-danger">Error. Try again.</div>';
        }
    } catch (err) {
        document.getElementById('payMsg').innerHTML = '<div class="alert alert-danger">Error. Try again.</div>';
    }
});

/* =========================
   Supplier Products Accordion
   ========================= */
document.querySelectorAll('#supplierProductsAccordion .accordion-button').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const target = e.target.closest('.accordion-button');
        const collapseId = target.getAttribute('data-bs-target').substring(1);
        const supplierId = collapseId.replace('collapseP','');
        const container = document.getElementById('productsContainer' + supplierId);
        if (container.dataset.loaded) return;
        container.innerHTML = '<div class="text-muted">Loading...</div>';
        const form = new FormData();
        form.append('action','fetch_supplier_products');
        form.append('supplier_id', supplierId);
        let data;
        try {
            const res = await fetch('suppliers.php', {method:'POST', body: form});
            data = await res.json();
        } catch (err) {
            data = {success: false, rows: []};
        }
        let html = '<div class="transactions-table"><table><thead><tr><th>Product</th><th class="text-end">Unit Price</th><th>Actions</th></tr></thead><tbody>';
        if (!data.success || !Array.isArray(data.rows) || !data.rows.length) {
            html += '<tr><td colspan="3" class="text-center text-muted">No products found.</td></tr>';
            html += '</tbody></table></div>';
            container.innerHTML = html;
            container.dataset.loaded = '1';
            return;
        }
        data.rows.forEach(r => {
            html += `<tr>
                <td>${escapeHtml(r.product_name)}</td>
                <td class="text-end">UGX ${parseFloat(r.unit_price).toFixed(2)}</td>
                <td>
                    <button class="btn btn-warning btn-sm edit-supply-btn" data-id="${r.id}" data-supplier="${r.supplier_id}" data-name="${escapeHtml(r.product_name)}" data-price="${r.unit_price}">Edit</button>
                    <button class="btn btn-danger btn-sm delete-supply-btn" data-id="${r.id}">Delete</button>
                </td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        container.innerHTML = html;
        container.dataset.loaded = '1';

        // Attach edit/delete events
        container.querySelectorAll('.edit-supply-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('supplierProductModalTitle').textContent = 'Edit Supply';
                document.getElementById('spSupplierId').value = btn.getAttribute('data-supplier');
                document.getElementById('spProductId').value = btn.getAttribute('data-id');
                document.getElementById('spProductName').value = btn.getAttribute('data-name');
                document.getElementById('spUnitPrice').value = btn.getAttribute('data-price');
                document.getElementById('spMsg').innerHTML = '';
                document.getElementById('spSubmitBtn').textContent = 'Update';
                new bootstrap.Modal(document.getElementById('supplierProductModal')).show();
            });
        });
        container.querySelectorAll('.delete-supply-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Delete this product?')) return;
                const form = new FormData();
                form.append('action','delete_supplier_product');
                form.append('id', btn.getAttribute('data-id'));
                const res = await fetch('suppliers.php', {method:'POST', body: form});
                const data = await res.json();
                if (data.success) location.reload();
                else alert('Delete failed');
            });
        });
    });
});

// Add Supply button
document.querySelectorAll('.add-supply-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('supplierProductModalTitle').textContent = 'Add Supply';
        document.getElementById('spSupplierId').value = btn.getAttribute('data-supplier');
        document.getElementById('spProductId').value = '';
        document.getElementById('spProductName').value = '';
        document.getElementById('spUnitPrice').value = '';
        document.getElementById('spMsg').innerHTML = '';
        document.getElementById('spSubmitBtn').textContent = 'Add';
        new bootstrap.Modal(document.getElementById('supplierProductModal')).show();
    });
});

// Add/Edit Supplier Product form submit
document.getElementById('supplierProductForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const supplier_id = document.getElementById('spSupplierId').value;
    const id = document.getElementById('spProductId').value;
    const product_name = document.getElementById('spProductName').value;
    const unit_price = document.getElementById('spUnitPrice').value;
    const form = new FormData();
    if (id) {
        form.append('action','edit_supplier_product');
        form.append('id', id);
    } else {
        form.append('action','add_supplier_product');
        form.append('supplier_id', supplier_id);
    }
    form.append('product_name', product_name);
    form.append('unit_price', unit_price);
    const res = await fetch('suppliers.php', {method:'POST', body: form});
    const data = await res.json();
    if (data.success) {
        document.getElementById('spMsg').innerHTML = '<div class="alert alert-success">Saved.</div>';
        setTimeout(() => location.reload(), 700);
    } else {
        document.getElementById('spMsg').innerHTML = '<div class="alert alert-danger">Error. Please check your input.</div>';
    }
});

// Mobile Supplier Transactions Accordion
document.querySelectorAll('#suppliersAccordionMobile .accordion-button').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const target = e.target.closest('.accordion-button');
        const collapseId = target.getAttribute('data-bs-target').substring(1);
        const supplierId = collapseId.replace('collapseS','').replace('m','');
        const container = document.getElementById('transContainerS' + supplierId + 'm');
        if (container.dataset.loaded) return;
        container.innerHTML = '<div class="text-muted">Loading...</div>';
        const form = new FormData();
        form.append('action','fetch_supplier_transactions');
        form.append('supplier_id', supplierId);
        let data;
        try {
            const res = await fetch('suppliers.php', {method:'POST', body: form});
            data = await res.json();
        } catch (err) {
            data = {success: false, rows: []};
        }
        let html = '<table><thead><tr><th>Date & Time</th><th>Branch</th><th>Products</th><th class="text-center">Quantity</th><th class="text-end">Unit Price</th><th class="text-end">Amount</th><th>Payment Method</th><th class="text-end">Amount Paid</th><th class="text-end">Balance</th><th>Actions</th></tr></thead><tbody>';
        if (!data.success || !Array.isArray(data.rows) || !data.rows.length) {
            html += '<tr><td colspan="10" class="text-center text-muted">No transactions found.</td></tr>';
            html += '</tbody></table>';
            container.innerHTML = html;
            container.dataset.loaded = '1';
            return;
        }
        data.rows.forEach(r => {
            const paid = parseFloat(r.amount_paid || 0).toFixed(2);
            const balance = parseFloat(r.balance || 0).toFixed(2);
            const unitPrice = parseFloat(r.unit_price || 0).toFixed(2);
            const amount = parseFloat(r.amount || 0).toFixed(2);
            const products = escapeHtml(r.products_supplied || '');
            const qty = escapeHtml(r.quantity || '');
            const method = escapeHtml(r.payment_method || '');
            const date = escapeHtml(r.date_time || '');
            const branch = escapeHtml(r.branch || '');
            let actions = '';
            if (parseFloat(balance) > 0) {
                actions = `<button class="btn btn-success btn-sm pay-supplier-btn" data-id="${r.id}" data-balance="${balance}" title="Pay"><i class="fa fa-check"></i></button>`;
            } else {
                actions = `<span class="badge bg-success">Cleared</span>`;
            }
            html += `<tr>
                <td>${date}</td>
                <td>${branch}</td>
                <td>${products}</td>
                <td class="text-center">${qty}</td>
                <td class="text-end">UGX ${unitPrice}</td>
                <td class="text-end">UGX ${amount}</td>
                <td>${method}</td>
                <td class="text-end">UGX ${paid}</td>
                <td class="text-end">UGX ${balance}</td>
                <td>${actions}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
        container.dataset.loaded = '1';

        // Attach pay button events
        container.querySelectorAll('.pay-supplier-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('payTransId').value = btn.getAttribute('data-id');
                document.getElementById('payAmount').value = btn.getAttribute('data-balance');
                document.getElementById('payMsg').innerHTML = '';
                new bootstrap.Modal(document.getElementById('paySupplierModal')).show();
            });
        });
    });
});

// Mobile Supplier Products Accordion
document.querySelectorAll('#supplierProductsAccordion .accordion-button').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const target = e.target.closest('.accordion-button');
        const collapseId = target.getAttribute('data-bs-target').substring(1);
        const supplierId = collapseId.replace('collapseP','');
        const containerMobile = document.getElementById('productsContainer' + supplierId + 'Mobile');
        if (containerMobile && !containerMobile.dataset.loaded) {
            containerMobile.innerHTML = '<div class="text-muted">Loading...</div>';
            const form = new FormData();
            form.append('action','fetch_supplier_products');
            form.append('supplier_id', supplierId);
            let data;
            try {
                const res = await fetch('suppliers.php', {method:'POST', body: form});
                data = await res.json();
            } catch (err) {
                data = {success: false, rows: []};
            }
            let html = '<table><thead><tr><th>Product</th><th class="text-end">Unit Price</th><th>Actions</th></tr></thead><tbody>';
            if (!data.success || !Array.isArray(data.rows) || !data.rows.length) {
                html += '<tr><td colspan="3" class="text-center text-muted">No products found.</td></tr>';
                html += '</tbody></table>';
                containerMobile.innerHTML = html;
                containerMobile.dataset.loaded = '1';
                return;
            }
            data.rows.forEach(r => {
                html += `<tr>
                    <td>${escapeHtml(r.product_name)}</td>
                    <td class="text-end">UGX ${parseFloat(r.unit_price).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-supply-btn" data-id="${r.id}" data-supplier="${r.supplier_id}" data-name="${escapeHtml(r.product_name)}" data-price="${r.unit_price}" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm delete-supply-btn" data-id="${r.id}" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            containerMobile.innerHTML = html;
            containerMobile.dataset.loaded = '1';

            // Attach edit/delete events
            containerMobile.querySelectorAll('.edit-supply-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('supplierProductModalTitle').textContent = 'Edit Supply';
                    document.getElementById('spSupplierId').value = btn.getAttribute('data-supplier');
                    document.getElementById('spProductId').value = btn.getAttribute('data-id');
                    document.getElementById('spProductName').value = btn.getAttribute('data-name');
                    document.getElementById('spUnitPrice').value = btn.getAttribute('data-price');
                    document.getElementById('spMsg').innerHTML = '';
                    document.getElementById('spSubmitBtn').textContent = 'Update';
                    new bootstrap.Modal(document.getElementById('supplierProductModal')).show();
                });
            });
            containerMobile.querySelectorAll('.delete-supply-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Delete this product?')) return;
                    const form = new FormData();
                    form.append('action','delete_supplier_product');
                    form.append('id', btn.getAttribute('data-id'));
                    const res = await fetch('suppliers.php', {method:'POST', body: form});
                    const data = await res.json();
                    if (data.success) location.reload();
                    else alert('Delete failed');
                });
            });
        }
    });
});
