document.addEventListener('DOMContentLoaded', function() {
    function openEditDeductionsModal(payslipId, totalDeductions) {
        document.getElementById('edit_deductions_payslip_id').value = payslipId;
        document.getElementById('deductions_amount').value = totalDeductions;

        const form = document.getElementById('editDeductionsForm');
        // Assuming route('payroll.payslips.update-deductions') is available globally or passed via data attribute
        form.action = `/admin/payroll/payslips/${payslipId}/update-deductions`;

        document.getElementById('editDeductionsModal').classList.remove('hidden');
        document.getElementById('editDeductionsModal').classList.add('flex');
    }

    function closeEditDeductionsModal() {
        document.getElementById('editDeductionsModal').classList.add('hidden');
        document.getElementById('editDeductionsModal').classList.remove('flex');
    }

    function downloadPayrollPdf(startDate, endDate) {
        const departmentCheckboxes = document.querySelectorAll('#generatePayrollModal .department-checkbox:checked');
        const selectedDepartmentIds = Array.from(departmentCheckboxes).map(cb => cb.value);

        let url = `/payroll/download-pdf?start_date=${startDate}&end_date=${endDate}`;

        if (selectedDepartmentIds.length > 0) {
            selectedDepartmentIds.forEach(id => {
                url += `&department_ids[]=${id}`;
            });
        }

        window.open(url, '_blank');
    }

    function openOvertimeMultiplierModal(currentMultiplier) {
        document.getElementById('currentOvertimeMultiplierDisplay').innerText = currentMultiplier;
        document.getElementById('overtime_multiplier_value').value = currentMultiplier || 1.5; // Default to 1.5 if not set

        const form = document.getElementById('overtimeMultiplierForm');
        form.action = '/admin/payroll/global-overtime-multiplier';

        document.getElementById('overtimeMultiplierModal').classList.remove('hidden');
        document.getElementById('overtimeMultiplierModal').classList.add('flex');
    }

    // Expose functions to the global scope if needed by Blade templates
    window.openEditDeductionsModal = openEditDeductionsModal;
    window.closeEditDeductionsModal = closeEditDeductionsModal;
    window.downloadPayrollPdf = downloadPayrollPdf;
    window.openOvertimeMultiplierModal = openOvertimeMultiplierModal;

    // Read global overtime multiplier from the hidden input
    window.globalOvertimeMultiplier = document.getElementById('globalOvertimeMultiplierData').value;

});
