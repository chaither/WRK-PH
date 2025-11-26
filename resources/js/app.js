import './bootstrap';

function initEmployeeModal() {
	const modal = document.getElementById('employeeModal');
	const form = document.getElementById('employeeForm');
	const dailyRate = document.getElementById('daily_rate');
	const hourlyRate = document.getElementById('hourly_rate');

	if (!modal) return;

	window.openEmployeeModal = function() {
		modal.classList.remove('hidden');
		modal.classList.add('flex');
	}

	window.closeEmployeeModal = function() {
		modal.classList.remove('flex');
		modal.classList.add('hidden');
		if (form) form.reset();
	}

	modal.addEventListener('click', function(e) {
		if (e.target === modal) window.closeEmployeeModal();
	});

	if (dailyRate && hourlyRate) {
		dailyRate.addEventListener('input', function() {
			const d = parseFloat(this.value) || 0;
			hourlyRate.value = (d / 8).toFixed(2);
		});
	}

	if (form) {
		form.addEventListener('submit', function(e) {
			// Detect salary inputs dynamically so hidden fields don't trigger validation
			const payPeriodSelect = document.getElementById('pay_period_modal') || document.getElementById('pay_period');
			const monthlyInput = document.getElementById('monthly_salary');
			const semiMonthlyInput = document.getElementById('semi_monthly_salary');

			let basicEl = document.getElementById('basic_salary');
			if (!basicEl) {
				if (payPeriodSelect) {
					if (payPeriodSelect.value === 'monthly' && monthlyInput) {
						basicEl = monthlyInput;
					} else if (payPeriodSelect.value === 'semi-monthly' && semiMonthlyInput) {
						basicEl = semiMonthlyInput;
					}
				}

				// Fallback for legacy forms where only one field exists
				if (!basicEl) {
					basicEl = monthlyInput || semiMonthlyInput;
				}
			}

			const dailyEl = document.getElementById('daily_rate') || document.getElementById('daily_rate_modal');
			const hourlyEl = document.getElementById('hourly_rate') || document.getElementById('hourly_rate_modal');

			// Only validate fields that are present on the current form
			const checks = [];
			if (basicEl) checks.push({ name: 'basic salary', value: parseFloat(basicEl.value || 0) });
			if (dailyEl) checks.push({ name: 'daily rate', value: parseFloat(dailyEl.value || 0) });
			if (hourlyEl) checks.push({ name: 'hourly rate', value: parseFloat(hourlyEl.value || 0) });

			if (checks.length > 0) {
				const invalid = checks.filter(c => isNaN(c.value) || c.value <= 0);
				if (invalid.length > 0) {
					e.preventDefault();
					const fields = invalid.map(i => i.name).join(', ');
					alert('Please ensure all salary amounts are greater than zero. Invalid: ' + fields);
				}
			}
		});
	}

	window.deleteEmployee = function(id) {
		if (!confirm('Are you sure you want to delete this employee?')) return;
		const f = document.createElement('form');
		f.method = 'POST';
		f.action = `/employees/${id}`;
		const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
		const csrfInput = document.createElement('input'); csrfInput.type='hidden'; csrfInput.name='_token'; csrfInput.value=csrf;
		const methodInput = document.createElement('input'); methodInput.type='hidden'; methodInput.name='_method'; methodInput.value='DELETE';
		f.appendChild(csrfInput); f.appendChild(methodInput); document.body.appendChild(f); f.submit();
	}
}

function initDeductionModal() {
  const modal = document.getElementById('deductionModal');
  const form = document.getElementById('deductionForm');
  const payslipIdInput = document.getElementById('deduction_payslip_id');
  const otherDeductionInput = document.getElementById('other_deductions');

  if (!modal) return;

  window.openDeductionModal = function(payslipId, otherDeduction = 0) {
    payslipIdInput.value = payslipId;
    otherDeductionInput.value = otherDeduction || 0;
    // Set the action dynamically (? route/payslip/update endpoint)
    form.action = '/payroll/payslip/' + payslipId + '/deduction';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  window.closeDeductionModal = function() {
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    if (form) form.reset();
  }

  modal.addEventListener('click', function(e) {
    if (e.target === modal) window.closeDeductionModal();
  });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initEmployeeModal);
else initEmployeeModal();

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initDeductionModal);
else initDeductionModal();
