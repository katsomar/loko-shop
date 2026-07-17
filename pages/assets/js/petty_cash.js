document.addEventListener('DOMContentLoaded', function() {
    // Purpose select handler
    document.getElementById('purposeSelect')?.addEventListener('change', function() {
        const val = this.value;
        const companyReasonDiv = document.getElementById('companyReasonDiv');
        const reasonDiv = document.getElementById('reasonDiv');
        const reasonLabel = document.getElementById('reasonLabel');
        
        companyReasonDiv.classList.add('d-none');
        reasonDiv.classList.add('d-none');
        
        if (val === 'company') {
            companyReasonDiv.classList.remove('d-none');
        } else if (val === 'personal') {
            reasonDiv.classList.remove('d-none');
            reasonLabel.textContent = 'Reason';
        }
    });
    
    // Company reason select handler
    document.getElementById('companyReasonSelect')?.addEventListener('change', function() {
        const reasonDiv = document.getElementById('reasonDiv');
        const reasonLabel = document.getElementById('reasonLabel');
        
        if (this.value === 'other') {
            reasonDiv.classList.remove('d-none');
            reasonLabel.textContent = 'Other Reason';
        } else {
            reasonDiv.classList.add('d-none');
        }
    });
    
    // Pay button logic (works for both icon and regular button)
    document.querySelectorAll('.pay-petty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const balance = this.getAttribute('data-balance');
            
            document.getElementById('payPettyId').value = id;
            document.getElementById('payPettyAmount').value = balance;
            
            const payModal = new bootstrap.Modal(document.getElementById('payPettyModal'));
            payModal.show();
        });
    });
});
