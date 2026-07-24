(function(){
  // Run when DOM ready
  function onReady(cb) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', cb);
    } else cb();
  }

  onReady(function() {
    // --- ensureBootstrap helper (same as original) ---
    function ensureBootstrap(cb) {
      if (window.bootstrap) return cb();
      const src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
      if (document.querySelector('script[src="'+src+'"]')) {
        const t = setInterval(() => { if (window.bootstrap) { clearInterval(t); cb(); } }, 50);
        return;
      }
      const s = document.createElement('script');
      s.src = src;
      s.onload = cb;
      s.onerror = function() { console.error('Failed to load Bootstrap bundle.'); cb(); };
      document.head.appendChild(s);
    }

    // --- initPayModal ---
    function initPayModal() {
      const payModalEl = document.getElementById('payDebtorModal');
      if (!payModalEl) return;
      const payModal = new bootstrap.Modal(payModalEl);
      const pdDebtorLabel = document.getElementById('pdDebtorLabel');
      const pdBalanceText = document.getElementById('pdBalanceText');
      const pdDebtorId = document.getElementById('pdDebtorId');
      const pdAmount = document.getElementById('pdAmount');
      const pdMethod = document.getElementById('pdMethod');
      const pdMsg = document.getElementById('pdMsg');
      const pdConfirmBtn = document.getElementById('pdConfirmBtn');
      const pdSplitToggle = document.getElementById('pdSplitToggle');
      const pdSplitSection = document.getElementById('pdSplitSection');
      const pdMethodWrap = document.getElementById('pdMethodWrap');
      const pdAmountWrap = document.getElementById('pdAmountWrap');

      if (pdSplitToggle) {
        pdSplitToggle.addEventListener('change', function() {
          const isSplit = this.checked;
          if (pdSplitSection) pdSplitSection.style.display = isSplit ? 'block' : 'none';
          if (pdMethodWrap) pdMethodWrap.style.display = isSplit ? 'none' : '';
          if (pdAmountWrap) pdAmountWrap.style.display = isSplit ? 'none' : '';
        });
      }

      // Delegated click listener to open the modal for both shop & customer debtors
      document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-pay-debtor, .btn-pay-customer-debtor');
        if (!btn) return;

        const isCustomerDebtor = btn.classList.contains('btn-pay-customer-debtor');
        const id = btn.getAttribute('data-id');
        const balance = parseFloat(btn.getAttribute('data-balance') || 0);
        const name = btn.getAttribute('data-name') || (isCustomerDebtor ? 'Customer' : 'Debtor');

        pdDebtorId.value = id;
        pdAmount.value = '';
        if (pdSplitToggle) pdSplitToggle.checked = false;
        if (pdSplitSection) pdSplitSection.style.display = 'none';
        document.querySelectorAll('.pd-split-input').forEach(inp => inp.value = '');
        
        pdDebtorLabel.textContent = (isCustomerDebtor ? 'Customer: ' : 'Debtor: ') + name;
        pdBalanceText.textContent = 'UGX ' + balance.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        pdMsg.innerHTML = '';
        payModalEl.dataset.outstanding = String(balance);
        
        if (isCustomerDebtor) {
          payModalEl.dataset.isCustomerDebtor = '1';
        } else {
          delete payModalEl.dataset.isCustomerDebtor;
        }

        // Reset payment method to Cash and enable it
        if (pdMethod) pdMethod.value = 'Cash';
        if (pdMethodWrap) pdMethodWrap.style.display = '';
        if (pdAmountWrap) pdAmountWrap.style.display = '';

        payModal.show();
      });

      if (pdConfirmBtn) {
        pdConfirmBtn.addEventListener('click', async () => {
          const id = pdDebtorId.value;
          const outstanding = parseFloat(payModalEl.dataset.outstanding || 0);
          const isCustomerDebtor = payModalEl.dataset.isCustomerDebtor === '1';
          const isSplit = pdSplitToggle && pdSplitToggle.checked;

          let amount = 0;
          let pm = pdMethod?.value || 'Cash';
          let paymentsJsonArr = null;

          if (isSplit) {
            const splitArr = [];
            document.querySelectorAll('.pd-split-input').forEach(inp => {
              const val = parseFloat(inp.value || 0);
              const m = inp.getAttribute('data-method');
              if (!isNaN(val) && val > 0 && m) {
                amount += val;
                splitArr.push({ method: m, amount: val });
              }
            });
            if (splitArr.length === 0) {
              pdMsg.innerHTML = '<div class="alert alert-warning">Enter amount for at least one split payment method.</div>';
              return;
            }
            pm = 'Split Payment';
            paymentsJsonArr = splitArr;
          } else {
            amount = parseFloat(pdAmount.value || 0);
          }

          pdMsg.innerHTML = '';
          if (!id) { pdMsg.innerHTML = '<div class="alert alert-warning">Invalid selection.</div>'; return; }
          if (!amount || amount <= 0) { pdMsg.innerHTML = '<div class="alert alert-warning">Enter a valid amount.</div>'; return; }
          if (amount > outstanding + 0.01) { pdMsg.innerHTML = '<div class="alert alert-warning">Amount cannot exceed balance.</div>'; return; }

          pdConfirmBtn.disabled = true;
          pdConfirmBtn.textContent = 'Processing...';

          try {
            const endpoint = isCustomerDebtor ? 'pay_customer_debtor' : 'pay_debtor';
            let body = `${endpoint}=1&id=${encodeURIComponent(id)}&amount=${encodeURIComponent(amount)}&pm=${encodeURIComponent(pm)}`;
            if (paymentsJsonArr) {
              body += `&payments_json=${encodeURIComponent(JSON.stringify(paymentsJsonArr))}`;
            }

            const res = await fetch(location.pathname, {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: body
            });

            const text = await res.text();
            let data;
            try { data = JSON.parse(text); } catch (parseErr) {
              console.error('Invalid JSON response:', text);
              pdMsg.innerHTML = '<div class="alert alert-danger">Server error. See console.</div>';
              pdConfirmBtn.disabled = false;
              pdConfirmBtn.textContent = 'OK';
              return;
            }

            pdConfirmBtn.disabled = false;
            pdConfirmBtn.textContent = 'OK';

            if (data && data.reload) {
              payModal.hide();
              window.location.reload();
            } else {
              pdMsg.innerHTML = '<div class="alert alert-info">' + (data.message || 'Payment recorded') + '</div>';
            }
          } catch (err) {
            console.error('Request error:', err);
            pdConfirmBtn.disabled = false;
            pdConfirmBtn.textContent = 'OK';
            pdMsg.innerHTML = '<div class="alert alert-danger">Error processing payment.</div>';
          }
        });
      }
    }

    // ensure bootstrap then init pay modal
    ensureBootstrap(initPayModal);

    // Expose openReportGen globally (used by HTML buttons)
    window.openReportGen = function(type) {
      let title = 'Generate Report';
      const debtorTypeContainer = document.getElementById('report_debtor_type_container');
      const branchContainer = document.getElementById('report_branch_container');
      
      if (debtorTypeContainer) debtorTypeContainer.style.display = 'none';
      if (branchContainer) branchContainer.className = 'col-md-12';

      if (type === 'payment_analysis') {
        title = 'Generate Payment Analysis Report';
      } else if (type === 'debtors') {
        title = 'Generate Consolidated Debtors Report';
        if (debtorTypeContainer) debtorTypeContainer.style.display = 'block';
        if (branchContainer) branchContainer.className = 'col-md-6';
      } else if (type === 'product_summary') {
        title = 'Generate Product Summary Report';
      }
      
      const modalEl = document.getElementById('reportGenModal');
      if (!modalEl) return;
      document.getElementById('reportGenModalTitle').textContent = title;
      document.getElementById('reportGenForm').dataset.reportType = type;
      new bootstrap.Modal(modalEl).show();
    };

    // Report form submit
    const reportGenForm = document.getElementById('reportGenForm');
    if (reportGenForm) {
      reportGenForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const type = this.dataset.reportType || 'sales';
        const date_from = document.getElementById('report_date_from').value;
        const date_to = document.getElementById('report_date_to').value;
        const branch = document.getElementById('report_branch').value;
        const debtor_type_el = document.getElementById('report_debtor_type');
        const debtor_type = debtor_type_el ? debtor_type_el.value : 'all';
        const url = `reports_generator.php?type=${encodeURIComponent(type)}&date_from=${encodeURIComponent(date_from)}&date_to=${encodeURIComponent(date_to)}&branch=${encodeURIComponent(branch)}&debtor_type=${encodeURIComponent(debtor_type)}`;
        window.open(url, '_blank');
        const inst = bootstrap.Modal.getInstance(document.getElementById('reportGenModal'));
        if (inst) inst.hide();
      });
    }



    // Set Due Date button handler (delegated)
    document.body.addEventListener('click', function(e) {
      const btn = e.target.closest('.set-due-date-btn');
      if (!btn) return;

      const type = btn.getAttribute('data-type'); // 'shop' or 'customer'
      const id = btn.getAttribute('data-id');
      const name = btn.getAttribute('data-name');

      const dueDateModal = new bootstrap.Modal(document.getElementById('setDueDateModal'));
      document.getElementById('ddDebtorLabel').textContent = `Debtor: ${name}`;
      document.getElementById('ddDebtorId').value = id;
      document.getElementById('ddDebtorType').value = type;
      document.getElementById('ddDueDate').value = '';
      document.getElementById('ddMsg').innerHTML = '';

      dueDateModal.show();
    });

    // Invoice button handler — open invoice_preview.php with POST (delegated)
    document.body.addEventListener('click', function(e) {
      const btn = e.target.closest('.btn-view-invoice');
      if (!btn) return;

      // Read dataset (data- attributes)
      const type = btn.dataset.type || 'shop';
      const productsRaw = btn.dataset.products || '[]';
      const invoiceNo = btn.dataset.invoice || btn.dataset.receipt || '';
      const name = btn.dataset.name || '';
      const email = btn.dataset.email || '';
      const contact = btn.dataset.contact || '';
      const balance = parseFloat(btn.dataset.balance || 0);
      const paid = parseFloat(btn.dataset.paid || 0);
      const dueDate = btn.dataset.dueDate || btn.dataset.due_date || '';

      // Parse products JSON and compute total if possible
      let cart = [];
      try {
        cart = JSON.parse(productsRaw);
        if (!Array.isArray(cart)) cart = [];
      } catch (err) {
        cart = [];
      }
      let total = 0;
      if (cart.length > 0) {
        for (const it of cart) {
          const q = parseFloat(it.quantity || it.qty || 0) || 0;
          const p = parseFloat(it.price || 0) || 0;
          total += q * p;
        }
      } else {
        // Fallback to amount paid + balance (if products not available)
        total = (paid || 0) + (balance || 0);
      }

      // Prepare and submit POST form to invoice_preview.php in a new tab
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'invoice_preview.php';
      form.target = '_blank';
      form.style.display = 'none';

      const addField = (nameF, valueF) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = nameF;
        inp.value = (typeof valueF === 'string') ? valueF : JSON.stringify(valueF);
        form.appendChild(inp);
      };

      addField('cart', cart);
      addField('total', total);
      addField('payment_method', type === 'customer' ? 'Customer File' : (btn.dataset.paymentMethod || 'Debtor'));
      addField('amount_paid', paid);
      addField('balance', balance);
      addField('invoice_no', invoiceNo);
      addField('customer_name', name);
      addField('customer_email', email);
      addField('customer_contact', contact);
      addField('due_date', dueDate);

      document.body.appendChild(form);
      form.submit();
      setTimeout(() => document.body.removeChild(form), 600);
    });

    // Confirm due date setting
    const ddConfirmBtn = document.getElementById('ddConfirmBtn');
    if (ddConfirmBtn) {
      ddConfirmBtn.addEventListener('click', async function() {
        const id = document.getElementById('ddDebtorId').value;
        const type = document.getElementById('ddDebtorType').value;
        const dueDate = document.getElementById('ddDueDate').value;
        const ddMsg = document.getElementById('ddMsg');
        const ddConfirmBtnLocal = this;

        if (!dueDate) {
          ddMsg.innerHTML = '<div class="alert alert-warning">Please select a date.</div>';
          return;
        }

        ddConfirmBtnLocal.disabled = true;
        ddConfirmBtnLocal.textContent = 'Saving...';

        try {
          const formData = new FormData();
          let action = '';
          let id_field = '';

          if (type === 'shop') {
            action = 'set_due_date';
            id_field = 'debtor_id';
          } else { // customer
            action = 'set_customer_due_date';
            id_field = 'transaction_id';
          }

          formData.append(action, '1');
          formData.append(id_field, id);
          formData.append('due_date', dueDate);

          const res = await fetch(location.pathname, {
            method: 'POST',
            body: formData
          });

          const data = await res.json();

          ddConfirmBtnLocal.disabled = false;
          ddConfirmBtnLocal.textContent = 'OK';

          if (data.success) {
            ddMsg.innerHTML = '<div class="alert alert-success">Due date set successfully!</div>';
            setTimeout(() => {
              const inst = bootstrap.Modal.getInstance(document.getElementById('setDueDateModal'));
              if (inst) inst.hide();
              location.reload();
            }, 800);
          } else {
            ddMsg.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error setting due date') + '</div>';
          }
        } catch (err) {
          console.error('Error:', err);
          ddConfirmBtnLocal.disabled = false;
          ddConfirmBtnLocal.textContent = 'OK';
          ddMsg.innerHTML = '<div class="alert alert-danger">A client-side error occurred. Check console.</div>';
        }
      });
    }

    // --- Chart initialization (uses server-side data exposed on window.salesServerData) ---
    (function initCharts() {
      const dataObj = window.salesServerData || { chart_labels: [], series: {} };
      const rawLabels = dataObj.chart_labels || [];
      const chart_labels = rawLabels.map(m => {
        const parts = String(m).split('-');
        const y = parseInt(parts[0] || 0, 10);
        const mth = parseInt(parts[1] || 1, 10);
        const date = new Date(y, mth - 1, 1);
        return date.toLocaleString('en-US', { month: 'short', year: 'numeric' });
      });
      const dataByMethod = dataObj.series || {};
      const colors = {
        'Cash': '#1abc9c',
        'MTN MoMo': '#f1c40f',
        'Airtel Money': '#e74c3c',
        'Bank': '#3498db'
      };

      const getThemeColors = () => {
        const isDark = document.body.classList.contains('dark-mode');
        return {
          textColor: isDark ? '#ffffff' : '#000000',
          gridColor: isDark ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.1)'
        };
      };

      const makeOptions = (title) => {
        const { textColor, gridColor } = getThemeColors();
        return {
          type: 'bar',
          data: {
            labels: chart_labels,
            datasets: [{
              label: title,
              data: dataByMethod[title] || [],
              backgroundColor: (colors[title] || '#666') + '88',
              borderColor: colors[title] || '#666',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              x: { ticks: { color: textColor }, grid: { color: gridColor } },
              y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } }
            },
            plugins: {
              legend: { display: false, labels: { color: textColor } },
              tooltip: { titleColor: textColor, bodyColor: textColor }
            }
          }
        };
      };

      const charts = {};
      const makeChart = (id, title) => {
        const el = document.getElementById(id)?.getContext('2d');
        if (!el || typeof Chart === 'undefined') return;
        charts[id] = new Chart(el, makeOptions(title));
      };

      // Wait for Chart to be available if needed
      function tryInit() {
        if (typeof Chart === 'undefined') {
          setTimeout(tryInit, 120);
          return;
        }
        makeChart('chartCash', 'Cash');
        makeChart('chartMtn', 'MTN MoMo');
        makeChart('chartAirtel', 'Airtel Money');
        makeChart('chartBank', 'Bank');

        const applyThemeToCharts = () => {
          const { textColor, gridColor } = getThemeColors();
          Object.values(charts).forEach(ch => {
            ch.options.scales.x.ticks.color = textColor;
            ch.options.scales.y.ticks.color = textColor;
            ch.options.scales.x.grid.color = gridColor;
            ch.options.scales.y.grid.color = gridColor;
            if (ch.options.plugins && ch.options.plugins.legend && ch.options.plugins.legend.labels) {
              ch.options.plugins.legend.labels.color = textColor;
            }
            if (ch.options.plugins && ch.options.plugins.tooltip) {
              ch.options.plugins.tooltip.titleColor = textColor;
              ch.options.plugins.tooltip.bodyColor = textColor;
            }
            ch.update();
          });
        };

        const mo = new MutationObserver(applyThemeToCharts);
        mo.observe(document.body, { attributes: true, attributeFilter: ['class'] });
        window.addEventListener('storage', applyThemeToCharts);
      }

      tryInit();
    })();

    // --- initBankedModal ---
    function initBankedModal() {
      const modalEl = document.getElementById('inputBankedModal');
      if (!modalEl) return;
      const ibModal = new bootstrap.Modal(modalEl);
      const ibDate = document.getElementById('ibDate');
      const ibBranch = document.getElementById('ibBranch');
      const ibAmount = document.getElementById('ibAmount');
      const ibSaveBtn = document.getElementById('ibSaveBtn');
      const ibMsg = document.getElementById('ibMsg');

      // Click "Input Amount Banked" or "Edit Banked Amount"
      document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('#btnInputBanked, #btnInputBankedSmall, .btn-edit-banked');
        if (!btn) return;

        const isEdit = btn.classList.contains('btn-edit-banked');
        if (isEdit) {
          const dateVal = btn.getAttribute('data-date');
          const branchVal = btn.getAttribute('data-branch');
          const amountVal = btn.getAttribute('data-amount');
          
          if (ibDate) ibDate.value = dateVal || '';
          if (ibBranch) ibBranch.value = branchVal || '';
          if (ibAmount) ibAmount.value = amountVal || '';
        } else {
          if (ibAmount) ibAmount.value = '';
        }
        
        if (ibMsg) ibMsg.innerHTML = '';
        ibModal.show();
      });

      // Click Save
      if (ibSaveBtn) {
        ibSaveBtn.addEventListener('click', async () => {
          const dateVal = ibDate ? ibDate.value : '';
          const branchVal = ibBranch ? ibBranch.value : '';
          const amountVal = parseFloat(ibAmount ? ibAmount.value : 0);

          if (!dateVal) {
            ibMsg.innerHTML = '<div class="alert alert-warning">Please select a date.</div>';
            return;
          }
          if (!branchVal) {
            ibMsg.innerHTML = '<div class="alert alert-warning">Please select a branch.</div>';
            return;
          }
          if (isNaN(amountVal) || amountVal < 0) {
            ibMsg.innerHTML = '<div class="alert alert-warning">Please enter a valid amount.</div>';
            return;
          }

          const branchText = ibBranch.options[ibBranch.selectedIndex].text;
          const formattedAmount = amountVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
          
          const confirmMsg = `Are you sure you want to save UGX ${formattedAmount} as the banked amount for ${branchText} on ${dateVal}?`;
          if (!confirm(confirmMsg)) {
            return;
          }

          ibSaveBtn.disabled = true;
          ibSaveBtn.textContent = 'Saving...';
          if (ibMsg) ibMsg.innerHTML = '';

          try {
            const formData = new FormData();
            formData.append('save_banked_amount', '1');
            formData.append('date', dateVal);
            formData.append('branch_id', branchVal);
            formData.append('amount', amountVal);

            const res = await fetch(location.pathname, {
              method: 'POST',
              body: formData
            });

            const data = await res.json();
            ibSaveBtn.disabled = false;
            ibSaveBtn.textContent = 'Save';

            if (data.success) {
              ibMsg.innerHTML = '<div class="alert alert-success">Banked amount saved successfully!</div>';
              setTimeout(() => {
                ibModal.hide();
                location.reload();
              }, 800);
            } else {
              ibMsg.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error saving banked amount') + '</div>';
            }
          } catch (err) {
            console.error('Error:', err);
            ibSaveBtn.disabled = false;
            ibSaveBtn.textContent = 'Save';
            ibMsg.innerHTML = '<div class="alert alert-danger">A client-side error occurred.</div>';
          }
        });
      }

      // Handle Clear Button (delegated)
      document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-clear-banked');
        if (!btn) return;

        const dateVal = btn.getAttribute('data-date');
        const branchVal = btn.getAttribute('data-branch');
        if (!dateVal || !branchVal) {
          alert('Please select a specific branch to clear the banked amount.');
          return;
        }

        if (!confirm(`Are you sure you want to clear the banked amount for this branch on ${dateVal}?`)) {
          return;
        }

        try {
          const formData = new FormData();
          formData.append('clear_banked_amount', '1');
          formData.append('date', dateVal);
          formData.append('branch_id', branchVal);

          const res = await fetch(location.pathname, {
            method: 'POST',
            body: formData
          });

          const data = await res.json();
          if (data.success) {
            location.reload();
          } else {
            alert(data.message || 'Error clearing banked amount');
          }
        } catch (err) {
          console.error('Error:', err);
          alert('A client-side error occurred.');
        }
      });
    }

    ensureBootstrap(initBankedModal);

  }); // onReady
})();
