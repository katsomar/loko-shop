// Barcode scanning logic for Add Product
(function() {
    const scanBtn = document.getElementById('scanProductBarcodeBtn');
    const scanModal = document.getElementById('productBarcodeScanModal');
    const closeScanBtn = document.getElementById('closeProductBarcodeScan');
    const scanVideo = document.getElementById('productBarcodeScanVideo');
    const scanCanvas = document.getElementById('productBarcodeScanCanvas');
    const rotateBtn = document.getElementById('rotateProductBarcodeCameraBtn');
    const scanModeSel = document.getElementById('productBarcodeScanMode');
    const scanStatus = document.getElementById('productBarcodeScanStatus');
    let currentStream = null;
    let currentFacing = 'environment';
    let scanActive = false;

    scanBtn?.addEventListener('click', () => {
        scanModal.style.display = 'flex';
        scanStatus.textContent = '';
        startCameraScan();
    });

    closeScanBtn?.addEventListener('click', () => {
        scanModal.style.display = 'none';
        stopCameraScan();
    });

    rotateBtn?.addEventListener('click', () => {
        currentFacing = (currentFacing === 'environment') ? 'user' : 'environment';
        startCameraScan();
    });

    scanModeSel?.addEventListener('change', () => {
        if (scanModeSel.value === 'hardware') {
            stopCameraScan();
            scanVideo.style.display = 'none';
            scanCanvas.style.display = 'none';
            scanStatus.textContent = 'Focus barcode input field and scan using hardware scanner.';
            ensureHardwareInput();
        } else {
            scanVideo.style.display = '';
            scanStatus.textContent = '';
            startCameraScan();
        }
    });

    function startCameraScan() {
        stopCameraScan();
        scanActive = true;
        scanVideo.style.display = '';
        scanCanvas.style.display = 'none';
        scanStatus.textContent = 'Initializing camera...';
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: currentFacing }
            }).then(stream => {
                currentStream = stream;
                scanVideo.srcObject = stream;
                scanVideo.play();
                scanStatus.textContent = 'Point camera at barcode.';
                if ('BarcodeDetector' in window) {
                    const detector = new window.BarcodeDetector({ formats: ['ean_13', 'ean_8', 'code_128', 'upc_a', 'upc_e'] });
                    const scanFrame = () => {
                        if (!scanActive) return;
                        detector.detect(scanVideo).then(barcodes => {
                            if (barcodes.length > 0) {
                                handleBarcode(barcodes[0].rawValue);
                            } else {
                                requestAnimationFrame(scanFrame);
                            }
                        }).catch(() => requestAnimationFrame(scanFrame));
                    };
                    scanFrame();
                } else {
                    scanStatus.textContent = 'BarcodeDetector not supported. Please use Chrome/Edge or hardware scanner.';
                }
            }).catch(err => {
                scanStatus.textContent = 'Camera error: ' + err.message;
            });
        } else {
            scanStatus.textContent = 'Camera not supported.';
        }
    }

    function stopCameraScan() {
        scanActive = false;
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
        scanVideo.srcObject = null;
    }

    function ensureHardwareInput() {
        let hwInput = document.getElementById('hardwareProductBarcodeInput');
        if (!hwInput) {
            hwInput = document.createElement('input');
            hwInput.type = 'text';
            hwInput.id = 'hardwareProductBarcodeInput';
            hwInput.style.position = 'absolute';
            hwInput.style.opacity = 0;
            hwInput.style.pointerEvents = 'none';
            scanModal.appendChild(hwInput);
        }
        hwInput.value = '';
        hwInput.focus();
        hwInput.oninput = function() {
            if (hwInput.value.length >= 6) {
                handleBarcode(hwInput.value.trim());
                hwInput.value = '';
            }
        };
    }

    function handleBarcode(barcode) {
        scanStatus.textContent = 'Barcode detected: ' + barcode;
        document.getElementById('barcode').value = barcode;
        scanModal.style.display = 'none';
        stopCameraScan();
        document.getElementById('name').focus();
    }

    scanModal?.addEventListener('click', function(e) {
        if (e.target === scanModal) {
            scanModal.style.display = 'none';
            stopCameraScan();
        }
    });
})();

// DOM Ready Handlers
document.addEventListener('DOMContentLoaded', function() {
    // 1. Search products by Name & Barcode
    const searchInput = document.getElementById('productSearchInput');
    const table = document.getElementById('productsTable');
    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.trim().toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const nameIndex = window.productNameColumnIndex || 1;
                const nameCell = row.querySelectorAll('td')[nameIndex];
                const barcodeCell = row.querySelectorAll('td')[nameIndex + 1];
                
                if (nameCell) {
                    const name = nameCell.textContent.trim().toLowerCase();
                    const barcode = barcodeCell ? barcodeCell.textContent.trim().toLowerCase() : '';
                    if (name.includes(filter) || barcode.includes(filter) || filter === '') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    }

    // 2. Edit Product Modal opener
    const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
    document.querySelectorAll('.edit-product-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const productId = this.dataset.id;
            
            // Fetch product details via AJAX
            try {
                const formData = new FormData();
                formData.append('action', 'get_product_details');
                formData.append('product_id', productId);
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    const p = data.product;
                    document.getElementById('editProductId').value = p.id;
                    document.getElementById('editName').value = p.name;
                    document.getElementById('editBarcode').value = p.barcode;
                    document.getElementById('editCost').value = p['buying-price'];
                    document.getElementById('editPrice').value = p['selling-price'];
                    document.getElementById('editExpiryDate').value = p.expiry_date;
                    
                    // Clear adjustments fields
                    document.getElementById('editRestockQty').value = '';
                    document.getElementById('editDamagesQty').value = '';
                    
                    editModal.show();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Failed to load product details.');
            }
        });
    });

    // 3. Delete Product handler
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
    document.querySelectorAll('.delete-product-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            
            document.getElementById('deleteProductId').value = productId;
            document.getElementById('deleteProductMessage').textContent = 'Are you sure you want to delete "' + productName + '"? This will remove all its stock records permanently.';
            document.getElementById('deleteMessage').innerHTML = '';
            
            deleteModal.show();
        });
    });

    document.getElementById('confirmDeleteProduct')?.addEventListener('click', async function() {
        const productId = document.getElementById('deleteProductId').value;
        const msgDiv = document.getElementById('deleteMessage');
        
        this.disabled = true;
        this.textContent = 'Deleting...';
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete_product');
            formData.append('product_id', productId);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                msgDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                setTimeout(() => location.reload(), 800);
            } else {
                msgDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                this.disabled = false;
                this.textContent = 'Delete';
            }
        } catch (err) {
            msgDiv.innerHTML = '<div class="alert alert-danger">Error: ' + err.message + '</div>';
            this.disabled = false;
            this.textContent = 'Delete';
        }
    });
});
