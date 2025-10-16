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
			const basic = parseFloat(document.getElementById('basic_salary')?.value || 0);
			const d = parseFloat(document.getElementById('daily_rate')?.value || 0);
			const h = parseFloat(document.getElementById('hourly_rate')?.value || 0);
			if (basic <= 0 || d <= 0 || h <= 0) {
				e.preventDefault();
				alert('Please ensure all salary amounts are greater than zero.');
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

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initEmployeeModal);
else initEmployeeModal();
