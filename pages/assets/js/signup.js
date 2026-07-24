// Role-based field visibility toggle
(function() {
    const roleSelect = document.getElementById('role');
    const businessFields = document.querySelector('.admin-business-fields');

    if (roleSelect && businessFields) {
        roleSelect.addEventListener('change', () => {
            businessFields.style.display = (roleSelect.value === 'admin') ? 'block' : 'none';
        });
    }

    // Toggle between New and Existing Business sections
    const bizOptNew = document.getElementById('biz_opt_new');
    const bizOptExisting = document.getElementById('biz_opt_existing');
    const newBizSection = document.getElementById('new-business-section');
    const existingBizSection = document.getElementById('existing-business-section');

    function toggleBizSections() {
        if (bizOptNew && bizOptNew.checked) {
            if (newBizSection) newBizSection.style.display = 'block';
            if (existingBizSection) existingBizSection.style.display = 'none';
        } else if (bizOptExisting && bizOptExisting.checked) {
            if (newBizSection) newBizSection.style.display = 'none';
            if (existingBizSection) existingBizSection.style.display = 'block';
        }
    }

    if (bizOptNew && bizOptExisting) {
        bizOptNew.addEventListener('change', toggleBizSections);
        bizOptExisting.addEventListener('change', toggleBizSections);
    }
})();
