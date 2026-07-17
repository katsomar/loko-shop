document.addEventListener('DOMContentLoaded', () => {
    const uploadModal = new bootstrap.Modal(document.getElementById('imageUploadModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('imageDeleteModal'));

    const imageUploadForm = document.getElementById('imageUploadForm');
    const imageProductId = document.getElementById('imageProductId');
    const imageProductName = document.getElementById('imageProductName');
    const imageUploadMsg = document.getElementById('imageUploadMessage');
    const confirmUpload = document.getElementById('confirmImageUpload');
    const productImageInp = document.getElementById('productImageInput');
    const previewWrap = document.getElementById('imagePreviewWrapper');
    const previewImg = document.getElementById('imagePreview');

    const deleteProductId = document.getElementById('deleteImageProductId');
    const deleteMsg = document.getElementById('deleteImageMessage');
    const deleteFeedback = document.getElementById('deleteImageFeedback');
    const confirmDelete = document.getElementById('confirmImageDelete');

    // Open upload modal
    document.querySelectorAll('.btn-upload-image').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            imageProductId.value = id;
            imageProductName.textContent = 'Product: ' + name;
            productImageInp.value = '';
            previewWrap.style.display = 'none';
            imageUploadMsg.innerHTML = '';
            uploadModal.show();
        });
    });

    // Preview image before upload
    productImageInp?.addEventListener('change', () => {
        const file = productImageInp.files[0];
        if (!file) {
            previewWrap.style.display = 'none';
            return;
        }
        const url = URL.createObjectURL(file);
        previewImg.src = url;
        previewWrap.style.display = '';
    });

    // Upload handler
    confirmUpload?.addEventListener('click', async () => {
        imageUploadMsg.innerHTML = '';
        const file = productImageInp.files[0];
        if (!file) {
            imageUploadMsg.innerHTML = '<div class="alert alert-warning">Please select an image.</div>';
            return;
        }
        confirmUpload.disabled = true;
        confirmUpload.textContent = 'Uploading...';

        try {
            const fd = new FormData(imageUploadForm);
            fd.set('action', 'upload_image');

            const res = await fetch('product_images.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                imageUploadMsg.innerHTML = '<div class="alert alert-success">✅ ' + data.message + '</div>';
                setTimeout(() => location.reload(), 1000);
            } else {
                imageUploadMsg.innerHTML = '<div class="alert alert-danger">❌ ' + (data.message || 'Failed') + '</div>';
                confirmUpload.disabled = false;
                confirmUpload.textContent = 'Save Image';
            }
        } catch (e) {
            imageUploadMsg.innerHTML = '<div class="alert alert-danger">❌ Error: ' + e.message + '</div>';
            confirmUpload.disabled = false;
            confirmUpload.textContent = 'Save Image';
        }
    });

    // Open delete modal
    document.querySelectorAll('.btn-delete-image').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            deleteProductId.value = id;
            deleteMsg.textContent = 'Remove image for "' + name + '"?';
            deleteFeedback.innerHTML = '';
            confirmDelete.disabled = false;
            confirmDelete.textContent = 'Remove';
            deleteModal.show();
        });
    });

    // Confirm delete
    confirmDelete?.addEventListener('click', async () => {
        deleteFeedback.innerHTML = '';
        confirmDelete.disabled = true;
        confirmDelete.textContent = 'Removing...';

        try {
            const fd = new FormData();
            fd.append('action', 'delete_image');
            fd.append('product_id', deleteProductId.value);

            const res = await fetch('product_images.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                deleteFeedback.innerHTML = '<div class="alert alert-success">✅ ' + data.message + '</div>';
                setTimeout(() => location.reload(), 1000);
            } else {
                deleteFeedback.innerHTML = '<div class="alert alert-danger">❌ ' + (data.message || 'Failed') + '</div>';
                confirmDelete.disabled = false;
                confirmDelete.textContent = 'Remove';
            }
        } catch (e) {
            deleteFeedback.innerHTML = '<div class="alert alert-danger">❌ Error: ' + e.message + '</div>';
            confirmDelete.disabled = false;
            confirmDelete.textContent = 'Remove';
        }
    });
});
