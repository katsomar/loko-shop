// Role-based field visibility toggle
(function() {
    const roleSelect = document.getElementById('role');
    const businessFields = document.querySelector('.admin-business-fields');

    if (roleSelect && businessFields) {
        roleSelect.addEventListener('change', () => {
            businessFields.style.display = (roleSelect.value === 'admin') ? 'block' : 'none';
        });
    }
})();
