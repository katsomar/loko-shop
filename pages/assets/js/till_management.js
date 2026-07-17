document.addEventListener('DOMContentLoaded', function () {
  const branchSelect = document.getElementById('branch_id');
  const staffSelect = document.getElementById('staff_id');

  if (branchSelect && staffSelect) {
    branchSelect.addEventListener('change', function () {
      const branchId = this.value;
      Array.from(staffSelect.options).forEach(option => {
        const matches = option.dataset && option.dataset.branch == branchId;
        option.style.display = matches || option.value === '' ? '' : 'none';
      });
      staffSelect.value = '';
    });
  }

  // Tab state persistence using Bootstrap events
  var tillTabs = document.getElementById('tillTabs');
  if (tillTabs) {
    var tabButtons = tillTabs.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(function (btn) {
      btn.addEventListener('shown.bs.tab', function (e) {
        var tabId = e.target.getAttribute('data-bs-target').replace('#', '');
        var url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        history.replaceState(null, '', url);
      });
    });
  }
});

// Filter tills by branch in Till Safes removal form
(function(){
    const branchSelect = document.getElementById('safe_branch_id');
    const tillSelect = document.getElementById('safe_till_id');
    if (branchSelect && tillSelect) {
        function filterTills() {
            const bid = branchSelect.value;
            let firstVisible = null;
            Array.from(tillSelect.options).forEach(opt => {
                if (opt.value === '') return;
                const match = !bid || opt.getAttribute('data-branch') === bid;
                opt.style.display = match ? '' : 'none';
                if (match && !firstVisible) firstVisible = opt;
            });
            // Reset selection if current hidden
            if (tillSelect.selectedIndex > 0) {
                const selOpt = tillSelect.options[tillSelect.selectedIndex];
                if (selOpt.style.display === 'none') tillSelect.value = '';
            }
        }
        branchSelect.addEventListener('change', filterTills);
        filterTills();
    }
})();

// Undo Removal Modal Handler
(function(){
    const modalEl = document.getElementById('undoRemovalModal');
    const removalIdInput = document.getElementById('undo_removal_id');
    const tillIdInput = document.getElementById('undo_till_id');
    const branchIdInput = document.getElementById('undo_branch_id');
    const originalAmtSpan = document.getElementById('undo_original_amount');
    const returnAmtInput = document.getElementById('return_amount');
    
    document.querySelectorAll('.undo-removal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const rid = btn.dataset.removalId;
            const tid = btn.dataset.tillId;
            const bid = btn.dataset.branchId;
            const amt = parseFloat(btn.dataset.amount || '0');
            removalIdInput.value = rid;
            tillIdInput.value = tid;
            branchIdInput.value = bid;
            originalAmtSpan.textContent = 'UGX ' + amt.toFixed(2);
            returnAmtInput.value = '';
            returnAmtInput.max = amt;
            new bootstrap.Modal(modalEl).show();
        });
    });
    
    // Simple max validation
    returnAmtInput && returnAmtInput.addEventListener('input', () => {
        const max = parseFloat(returnAmtInput.max || '0');
        const val = parseFloat(returnAmtInput.value || '0');
        if(val > max){ 
            returnAmtInput.value = max.toFixed(2); 
        }
    });
})();
