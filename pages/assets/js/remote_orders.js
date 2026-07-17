document.addEventListener('DOMContentLoaded', function() {
    // Counter Animation
    document.querySelectorAll('.counter').forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 1000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        updateCounter();
    });
    
    // Auto-refresh every 30 seconds
    setInterval(() => {
        location.reload();
    }, 30000);
});

// Show Order Details
function showOrderDetails(orderId, items) {
    let content = '<div class="table-responsive"><table class="table"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>';
    
    items.forEach(item => {
        const subtotal = item.quantity * item.unit_price;
        content += `<tr>
            <td>${escapeHtml(item.product_name)}</td>
            <td>${item.quantity}</td>
            <td>UGX ${Number(item.unit_price).toLocaleString()}</td>
            <td>UGX ${Number(subtotal).toLocaleString()}</td>
        </tr>`;
    });
    
    content += '</tbody></table></div>';
    document.getElementById('orderDetailsContent').innerHTML = content;
    
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
}

// Cancel Order with confirmation
function cancelOrder(orderId) {
    // Show confirmation dialog
    if (!confirm('⚠️ Are you sure you want to cancel this order?\n\nThis action cannot be undone.')) {
        return;
    }
    
    fetch('remote_orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=cancel&order_id=${orderId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ Failed to cancel order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred while cancelling the order: ' + error.message);
    });
}

// Helper function for HTML escaping
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
