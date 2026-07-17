document.addEventListener('DOMContentLoaded', function() {
    // Handle supplier selection (show/hide fields based on "Other" selection)
    document.getElementById('supplier_id')?.addEventListener('change', function() {
        const supplierId = this.value;
        const isOther = (supplierId === 'other');
        
        // Show/hide business name field
        document.getElementById('business_name_wrapper').style.display = isOther ? '' : 'none';
        document.getElementById('business_name').required = isOther;
        
        // Show/hide product fields
        document.getElementById('product_wrapper').style.display = isOther ? 'none' : '';
        document.getElementById('product_manual_wrapper').style.display = isOther ? '' : 'none';
        document.getElementById('product').required = !isOther;
        document.getElementById('product_manual').required = isOther;
        
        // Reset fields
        const productSelect = document.getElementById('product');
        productSelect.innerHTML = '<option value="">Select product</option>';
        document.getElementById('product_manual').value = '';
        document.getElementById('business_name').value = '';
        document.getElementById('unit_price').value = '';
        document.getElementById('quantity').value = '';
        document.getElementById('amount').value = '';
        
        // For "Other", enable manual unit price entry
        if (isOther) {
            document.getElementById('unit_price').readOnly = false;
            document.getElementById('unit_price').required = true;
        } else {
            document.getElementById('unit_price').readOnly = true;
            document.getElementById('unit_price').required = false;
        }
        
        // For regular supplier, fetch products via AJAX
        if (!supplierId || isOther) return;
        fetch('expense.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'fetch_supplier_products=1&supplier_id=' + encodeURIComponent(supplierId)
        }).then(res => res.json()).then(data => {
            if (data.success && Array.isArray(data.products)) {
                data.products.forEach(p => {
                    productSelect.innerHTML += `<option value="${p.id}" data-unit_price="${p.unit_price}">${p.product_name}</option>`;
                });
            }
        });
    });

    // When product is selected, show unit price (for regular suppliers only)
    document.getElementById('product')?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const unitPrice = selected.getAttribute('data-unit_price') || '';
        document.getElementById('unit_price').value = unitPrice;
        document.getElementById('unit_price').readOnly = true;
        document.getElementById('quantity').value = '';
        document.getElementById('amount').value = '';
    });

    // Calculate amount when quantity or unit price changes
    function calculateAmount() {
        const qty = parseFloat(document.getElementById('quantity').value) || 0;
        const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
        document.getElementById('amount').value = (qty * unitPrice).toFixed(2);
    }
    document.getElementById('quantity')?.addEventListener('input', calculateAmount);
    document.getElementById('unit_price')?.addEventListener('input', calculateAmount);

    // Cart management
    let cart = [];

    function updateCartTable() {
        const cartSection = document.getElementById('cartSection');
        const cartItems = document.getElementById('cartItems');
        const cartTotal = document.getElementById('cartTotal');
        if (cart.length === 0) {
            cartSection.style.display = 'none';
            cartItems.innerHTML = '';
            cartTotal.textContent = '0';
            return;
        }
        cartSection.style.display = '';
        let total = 0;
        cartItems.innerHTML = '';
        cart.forEach((item, idx) => {
            total += parseFloat(item.amount || 0);
            cartItems.innerHTML += `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>UGX ${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>UGX ${parseFloat(item.amount).toFixed(2)}</td>
                    <td>UGX ${parseFloat(item.amount_paid).toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeCartItem(${idx})">Remove</button></td>
                </tr>
            `;
        });
        cartTotal.textContent = 'UGX ' + total.toFixed(2);
        
        if (cart.length > 0) {
            document.getElementById('category').removeAttribute('required');
            document.getElementById('branch_id').removeAttribute('required');
            document.getElementById('supplier_id').removeAttribute('required');
            document.getElementById('product').removeAttribute('required');
            document.getElementById('product_manual').removeAttribute('required');
            document.getElementById('business_name').removeAttribute('required');
            document.getElementById('date').removeAttribute('required');
            document.getElementById('spent_by').removeAttribute('required');
            document.getElementById('unit_price').removeAttribute('required');
        }
    }

    window.removeCartItem = function(idx) {
        cart.splice(idx, 1);
        updateCartTable();
        if (cart.length === 0) {
            document.getElementById('category').setAttribute('required', 'required');
            document.getElementById('branch_id').setAttribute('required', 'required');
            document.getElementById('supplier_id').setAttribute('required', 'required');
            document.getElementById('date').setAttribute('required', 'required');
            document.getElementById('spent_by').setAttribute('required', 'required');
        }
    };

    // Add to Cart handler
    document.getElementById('addToCartBtn')?.addEventListener('click', function() {
        const supplierId = document.getElementById('supplier_id').value;
        const isOther = (supplierId === 'other');
        
        let productId = '';
        let productName = '';
        
        if (isOther) {
            productName = document.getElementById('product_manual').value.trim();
            if (!productName) {
                alert('Please enter product name.');
                return;
            }
            productId = 'manual_' + Date.now();
        } else {
            const productSelect = document.getElementById('product');
            productId = productSelect.value;
            productName = productSelect.options[productSelect.selectedIndex]?.text || '';
            if (!productId) {
                alert('Please select a product.');
                return;
            }
        }
        
        const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
        const quantity = parseInt(document.getElementById('quantity').value) || 0;
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
        
        if (quantity <= 0 || unitPrice <= 0) {
            alert('Please enter valid quantity and unit price.');
            return;
        }
        
        cart.push({
            product: productId,
            product_name: productName,
            quantity: quantity,
            unit_price: unitPrice,
            amount: amount,
            amount_paid: amountPaid
        });
        
        updateCartTable();
        
        if (isOther) {
            document.getElementById('product_manual').value = '';
        } else {
            document.getElementById('product').selectedIndex = 0;
        }
        document.getElementById('unit_price').value = '';
        document.getElementById('quantity').value = '';
        document.getElementById('amount').value = '';
        document.getElementById('amount_paid').value = '';
    });

    // Form submit handler (bypasses HTML5 validation)
    document.getElementById('addExpenseForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (cart.length === 0) {
            alert('Please add at least one product to the cart.');
            return false;
        }
        
        const category = document.getElementById('category').value.trim();
        const branchId = document.getElementById('branch_id').value;
        const supplierId = document.getElementById('supplier_id').value;
        const date = document.getElementById('date').value;
        const spentBy = document.getElementById('spent_by').value;
        
        if (!category) {
            alert('Please enter a category.');
            document.getElementById('category').focus();
            return false;
        }
        if (!branchId) {
            alert('Please select a branch.');
            document.getElementById('branch_id').focus();
            return false;
        }
        if (!supplierId) {
            alert('Please select a supplier.');
            document.getElementById('supplier_id').focus();
            return false;
        }
        if (!date) {
            alert('Please select a date.');
            document.getElementById('date').focus();
            return false;
        }
        if (!spentBy) {
            alert('Please select who spent.');
            document.getElementById('spent_by').focus();
            return false;
        }
        
        if (supplierId === 'other') {
            const businessName = document.getElementById('business_name').value.trim();
            if (!businessName) {
                alert('Please enter business name for "Other" supplier.');
                document.getElementById('business_name').focus();
                return false;
            }
        }
        
        document.getElementById('cart_json').value = JSON.stringify(cart);
        
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = 'expense.php';
        tempForm.style.display = 'none';
        
        const fields = {
            'cart_json': JSON.stringify(cart),
            'category': category,
            'branch_id': branchId,
            'supplier_id': supplierId,
            'date': date,
            'spent_by': spentBy
        };
        
        if (supplierId === 'other') {
            fields['business_name'] = document.getElementById('business_name').value.trim();
        }
        
        for (const [name, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            tempForm.appendChild(input);
        }
        
        document.body.appendChild(tempForm);
        tempForm.submit();
        
        return false;
    });

    // Other Expense Form Cart Management
    let otherCart = [];

    function calculateOtherAmount() {
        const qty = parseFloat(document.getElementById('other_quantity').value) || 0;
        const unitPrice = parseFloat(document.getElementById('other_unit_price').value) || 0;
        document.getElementById('other_amount').value = (qty * unitPrice).toFixed(2);
    }
    document.getElementById('other_quantity')?.addEventListener('input', calculateOtherAmount);
    document.getElementById('other_unit_price')?.addEventListener('input', calculateOtherAmount);

    function updateOtherCartTable() {
        const cartSection = document.getElementById('otherCartSection');
        const cartItems = document.getElementById('otherCartItems');
        const cartTotal = document.getElementById('otherCartTotal');
        if (otherCart.length === 0) {
            cartSection.style.display = 'none';
            cartItems.innerHTML = '';
            cartTotal.textContent = '0';
            return;
        }
        cartSection.style.display = '';
        let total = 0;
        cartItems.innerHTML = '';
        otherCart.forEach((item, idx) => {
            total += parseFloat(item.amount || 0);
            cartItems.innerHTML += `
                <tr>
                    <td>${item.type}</td>
                    <td>${item.quantity}</td>
                    <td>UGX ${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>UGX ${parseFloat(item.amount).toFixed(2)}</td>
                    <td>UGX ${parseFloat(item.amount_paid).toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeOtherCartItem(${idx})">Remove</button></td>
                </tr>
            `;
        });
        cartTotal.textContent = 'UGX ' + total.toFixed(2);
        
        if (otherCart.length > 0) {
            document.getElementById('other_category').removeAttribute('required');
            document.getElementById('other_type').removeAttribute('required');
            document.getElementById('other_branch_id').removeAttribute('required');
            document.getElementById('other_date').removeAttribute('required');
            document.getElementById('other_spent_by').removeAttribute('required');
            document.getElementById('other_unit_price').removeAttribute('required');
            document.getElementById('other_quantity').removeAttribute('required');
        }
    }

    window.removeOtherCartItem = function(idx) {
        otherCart.splice(idx, 1);
        updateOtherCartTable();
        if (otherCart.length === 0) {
            document.getElementById('other_category').setAttribute('required', 'required');
            document.getElementById('other_type').setAttribute('required', 'required');
            document.getElementById('other_branch_id').setAttribute('required', 'required');
            document.getElementById('other_date').setAttribute('required', 'required');
            document.getElementById('other_spent_by').setAttribute('required', 'required');
            document.getElementById('other_unit_price').setAttribute('required', 'required');
            document.getElementById('other_quantity').setAttribute('required', 'required');
        }
    };

    document.getElementById('addToOtherCartBtn')?.addEventListener('click', function() {
        const type = document.getElementById('other_type').value.trim();
        const unitPrice = parseFloat(document.getElementById('other_unit_price').value) || 0;
        const quantity = parseInt(document.getElementById('other_quantity').value) || 0;
        const amount = parseFloat(document.getElementById('other_amount').value) || 0;
        const amountPaid = parseFloat(document.getElementById('other_amount_paid').value) || 0;
        
        if (!type || quantity <= 0 || unitPrice <= 0) {
            alert('Please fill in all required fields with valid values.');
            return;
        }
        
        otherCart.push({
            type: type,
            quantity: quantity,
            unit_price: unitPrice,
            amount: amount,
            amount_paid: amountPaid
        });
        
        updateOtherCartTable();
        
        document.getElementById('other_type').value = '';
        document.getElementById('other_unit_price').value = '';
        document.getElementById('other_quantity').value = '';
        document.getElementById('other_amount').value = '';
        document.getElementById('other_amount_paid').value = '';
    });

    document.getElementById('otherExpenseForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (otherCart.length === 0) {
            alert('Please add at least one item to the cart.');
            return false;
        }
        
        const category = document.getElementById('other_category').value.trim();
        const branchId = document.getElementById('other_branch_id').value;
        const date = document.getElementById('other_date').value;
        const spentBy = document.getElementById('other_spent_by').value;
        
        if (!category || !branchId || !date || !spentBy) {
            alert('Please fill in all required fields.');
            return false;
        }
        
        document.getElementById('other_cart_json').value = JSON.stringify(otherCart);
        
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = 'expense.php';
        tempForm.style.display = 'none';
        
        const fields = {
            'is_other_expense': '1',
            'cart_json': JSON.stringify(otherCart),
            'category': category,
            'branch_id': branchId,
            'date': date,
            'spent_by': spentBy
        };
        
        for (const [name, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            tempForm.appendChild(input);
        }
        
        document.body.appendChild(tempForm);
        tempForm.submit();
        
        return false;
    });

    // Report generation
    window.openReportGen = function(type) {
        document.getElementById('reportGenModalTitle').textContent =
            type === 'expenses' ? 'Generate Expenses Report' : 'Generate Total Expenses Report';
        document.getElementById('reportGenForm').dataset.reportType = type;
        new bootstrap.Modal(document.getElementById('reportGenModal')).show();
    };

    document.getElementById('reportGenForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const type = this.dataset.reportType || 'expenses';
        const date_from = document.getElementById('report_date_from').value;
        const date_to = document.getElementById('report_date_to').value;
        const branch = document.getElementById('report_branch').value;
        const url = `reports_generator.php?type=${encodeURIComponent(type)}&date_from=${encodeURIComponent(date_from)}&date_to=${encodeURIComponent(date_to)}&branch=${encodeURIComponent(branch)}`;
        window.open(url, '_blank');
        bootstrap.Modal.getInstance(document.getElementById('reportGenModal')).hide();
    });
});
