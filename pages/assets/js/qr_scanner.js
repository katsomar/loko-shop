let currentOrderId = null;
let currentOrderData = null;
let videoStream = null;
let recordSaleModal = null;

// Initialize QR scanner
async function initScanner() {
    const video = document.getElementById('qr-video');
    const statusDiv = document.getElementById('scan-status');
    
    try {
        // Get camera stream
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' }
        });
        
        video.srcObject = videoStream;
        
        // Check for BarcodeDetector API
        if ('BarcodeDetector' in window) {
            const barcodeDetector = new BarcodeDetector({
                formats: ['qr_code']
            });
            
            // Scan loop
            const scanFrame = async () => {
                if (!videoStream) return;
                
                try {
                    const barcodes = await barcodeDetector.detect(video);
                    
                    if (barcodes.length > 0) {
                        const qrCode = barcodes[0].rawValue;
                        console.log('QR Code detected:', qrCode);
                        await processQRCode(qrCode);
                    } else {
                        requestAnimationFrame(scanFrame);
                    }
                } catch (e) {
                    console.error('Scan error:', e);
                    requestAnimationFrame(scanFrame);
                }
            };
            
            scanFrame();
        } else {
            statusDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>BarcodeDetector not supported. Please use Chrome/Edge browser.</div>';
        }
    } catch (error) {
        console.error('Camera error:', error);
        statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times me-2"></i>Camera access denied. Please enable camera permissions.</div>';
    }
}

// Process QR code
async function processQRCode(qrCode) {
    const statusDiv = document.getElementById('scan-status');
    
    // Stop scanning
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    
    statusDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Validating QR code...</div>';
    
    try {
        const response = await fetch('../pos/ajax/validate_qr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qr_code: qrCode })
        });
        
        const data = await response.json();
        console.log('Validation response:', data);
        
        if (data.success) {
            displayOrderDetails(data.data);
            statusDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check me-2"></i>Valid QR code!</div>';
        } else {
            statusDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times me-2"></i>${data.message}</div>`;
            setTimeout(resetScanner, 3000);
        }
    } catch (error) {
        console.error('Validation error:', error);
        statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times me-2"></i>Failed to process QR code. Please try again.</div>';
        setTimeout(resetScanner, 3000);
    }
}

// Display order details
function displayOrderDetails(order) {
    currentOrderId = order.id;
    currentOrderData = order;
    
    document.getElementById('order-ref').textContent = order.order_reference;
    document.getElementById('customer-name').textContent = order.customer_name;
    document.getElementById('customer-phone').textContent = order.customer_phone;
    document.getElementById('branch-name').textContent = order.branch_name;
    document.getElementById('expected-amount').textContent = 'UGX ' + Number(order.expected_amount).toLocaleString();
    document.getElementById('order-date').textContent = new Date(order.created_at).toLocaleString();
    
    // Display order items
    const itemsBody = document.getElementById('order-items');
    itemsBody.innerHTML = '';
    
    order.items.forEach(item => {
        const row = `
            <tr>
                <td>${escapeHtml(item.product_name)}</td>
                <td>${item.quantity}</td>
                <td>UGX ${Number(item.unit_price).toLocaleString()}</td>
                <td>UGX ${Number(item.subtotal).toLocaleString()}</td>
            </tr>
        `;
        itemsBody.innerHTML += row;
    });
    
    // Show order details
    document.getElementById('order-details').classList.remove('d-none');
}

// Show record sale modal
function showRecordSaleModal() {
    if (!currentOrderData) {
        alert('No order data available');
        return;
    }
    
    // Populate modal fields
    document.getElementById('sale-customer-name').value = currentOrderData.customer_name;
    document.getElementById('sale-total-amount').value = 'UGX ' + Number(currentOrderData.expected_amount).toLocaleString();
    document.getElementById('sale-payment-method').value = 'Cash';
    document.getElementById('sale-message').innerHTML = '';
    
    // Show modal
    if (!recordSaleModal) {
        recordSaleModal = new bootstrap.Modal(document.getElementById('recordSaleModal'));
    }
    recordSaleModal.show();
}

// Record sale and complete order
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirm-record-sale')?.addEventListener('click', async function() {
        if (!currentOrderId) {
            alert('No order selected');
            return;
        }
        
        const paymentMethod = document.getElementById('sale-payment-method').value;
        const messageDiv = document.getElementById('sale-message');
        const confirmBtn = this;
        
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Recording...';
        messageDiv.innerHTML = '';
        
        try {
            const formData = new FormData();
            formData.append('action', 'record_sale');
            formData.append('order_id', currentOrderId);
            formData.append('payment_method', paymentMethod);
            
            const response = await fetch('', { // Submit to same page
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                messageDiv.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>${data.message}<br>Receipt No: <strong>${data.receipt_no}</strong></div>`;
                
                setTimeout(() => {
                    recordSaleModal.hide();
                    resetScanner();
                    alert('Sale recorded successfully!\nReceipt No: ' + data.receipt_no);
                }, 2000);
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>${data.message}</div>`;
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Record Sale';
            }
        } catch (error) {
            console.error('Error:', error);
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Failed to record sale. Please try again.</div>';
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Record Sale';
        }
    });
});

// Reset scanner
function resetScanner() {
    currentOrderId = null;
    currentOrderData = null;
    document.getElementById('order-details').classList.add('d-none');
    document.getElementById('scan-status').innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Point camera at QR code</div>';
    initScanner();
}

// Helper function
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Start scanner on page load
window.addEventListener('load', initScanner);

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }
});
