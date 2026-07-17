// Role-based field visibility toggle
(function() {
    const roleSelect = document.getElementById('role');
    const branchFields = document.querySelector('.staff-branch-fields');
    const managerFields = document.querySelector('.manager-branch-fields');
    const businessFields = document.querySelector('.admin-business-fields');

    if (roleSelect) {
        roleSelect.addEventListener('change', () => {
            branchFields.style.display = (roleSelect.value === 'staff') ? 'flex' : 'none';
            businessFields.style.display = (roleSelect.value === 'admin') ? 'block' : 'none';
            managerFields.style.display = (roleSelect.value === 'manager') ? 'flex' : 'none';
        });
    }
})();
