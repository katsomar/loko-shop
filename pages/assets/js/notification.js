document.addEventListener('DOMContentLoaded', function() {
    // Snooze notification (extend due date by 1 day)
    document.querySelectorAll('.snooze-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const type = this.getAttribute('data-type');
            const id = this.getAttribute('data-id');
            
            if (!confirm('Snooze this notification for 1 day?')) return;
            
            const formData = new FormData();
            
            // Tomorrow's date
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dueDate = tomorrow.toISOString().split('T')[0];
            
            if (type === 'shop') {
                formData.append('set_due_date', '1');
                formData.append('debtor_id', id);
                formData.append('due_date', dueDate);
            } else {
                formData.append('set_customer_due_date', '1');
                formData.append('transaction_id', id);
                formData.append('due_date', dueDate);
            }
            
            try {
                const res = await fetch('sales.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const data = await res.json();
                
                if (data.success) {
                    alert('Notification snoozed for 1 day.');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to snooze'));
                }
            } catch (err) {
                console.error(err);
                alert('Error snoozing notification. Check console.');
            }
        });
    });

    // Clear notification (mark as handled)
    document.querySelectorAll('.clear-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const type = this.getAttribute('data-type');
            const id = this.getAttribute('data-id');
            
            if (!confirm('Clear this notification? (This will mark it as handled)')) return;
            
            const formData = new FormData();
            if (type === 'shop') {
                formData.append('clear_shop_notification', '1');
                formData.append('debtor_id', id);
            } else {
                formData.append('clear_customer_notification', '1');
                formData.append('transaction_id', id);
            }
            
            try {
                const res = await fetch('clear_notification.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const data = await res.json();
                
                if (data.success) {
                    alert('Notification cleared.');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to clear'));
                }
            } catch (err) {
                console.error(err);
                alert('Error clearing notification. Check console.');
            }
        });
    });
});
