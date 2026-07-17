document.addEventListener('DOMContentLoaded', function() {
    // Change button text dynamically based on user selection
    const userSelect = document.getElementById('user-select');
    const btn = document.getElementById('employee-btn');
    
    if (userSelect && btn) {
        userSelect.addEventListener('change', function() {
            btn.textContent = this.value ? 'Update Employee Salary' : 'Add Employee';
        });
    }
});
