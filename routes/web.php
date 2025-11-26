<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DTRController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ChangeShiftController;
use App\Http\Controllers\ChangeRestdayController;
use App\Http\Controllers\AdminChangeShiftController;
use App\Http\Controllers\AdminChangeRestdayController;
use App\Http\Controllers\NoBioRequestController;
use App\Http\Controllers\AdminNoBioRequestController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\AdminOvertimeRequestController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\PayrollScheduleController;
use App\Http\Controllers\GovernmentContributionController;
use App\Http\Controllers\OrganizationChartController;
use App\Http\Controllers\EmployeeOrganizationChartController;

// Auth Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login'])->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// App Routes (Auth handled in controllers)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/employee-dashboard', [DashboardController::class, 'employeeDashboard'])->name('employee.dashboard');
    Route::get('/my-payslips', [PayrollController::class, 'employeePayslips'])->name('employee.payslips.index');
    Route::get('/employee/organization', [EmployeeOrganizationChartController::class, 'index'])->name('employee.organization.index');
});

// Employee Management Routes (Protected)
Route::middleware(['auth', \App\Http\Middleware\EnsureHrAdminRole::class])->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

Route::resource('department', DepartmentController::class);

// Holiday Management Routes (Protected)
Route::middleware(['auth', \App\Http\Middleware\EnsureHrAdminRole::class])->group(function () {
    Route::resource('holidays', HolidayController::class);
    Route::resource('payroll-schedules', PayrollScheduleController::class)->except(['create', 'edit']);
    Route::resource('government-contributions', GovernmentContributionController::class)->except(['create', 'edit']);
});

// Organization Chart Routes
Route::middleware(['auth', \App\Http\Middleware\EnsureHrAdminRole::class])->group(function () {
    Route::resource('organization', OrganizationChartController::class)->only(['index', 'store', 'update', 'destroy']);
});

// API for fetching employees and departments (for modals, etc.)
Route::middleware(['auth', \App\Http\Middleware\EnsureHrAdminRole::class])->group(function () {
    Route::get('/api/employees', function () {
        return response()->json(App\Models\User::where('role', 'employee')->select('id', 'name')->get());
    })->name('api.employees');
    Route::get('/api/departments', function () {
        return response()->json(App\Models\Department::select('id', 'name')->get());
    })->name('api.departments');
});

// Department Employee Routes
Route::get('/departments/{department}/employees', [DepartmentController::class, 'showEmployees'])->name('departments.show_employees');
Route::post('department/{department}/employees', [DepartmentController::class, 'addEmployee'])->name('department.employees.store');
Route::delete('department/{department}/employees/{employee}', [DepartmentController::class, 'removeEmployee'])->name('department.employees.remove');
Route::post('/departments/{department}/employees/add', [DepartmentController::class, 'addEmployeeToDepartment'])->name('departments.add_employee');
Route::delete('/departments/{department}/employees/{employee}/remove', [DepartmentController::class, 'removeEmployeeFromDepartment'])->name('departments.remove_employee');

// Payroll Routes
Route::prefix('payroll')->name('payroll.')->group(function() {
    Route::get('/', [PayrollController::class, 'index'])->name('index');
    Route::post('/pay-periods', [PayrollController::class, 'createPayPeriod'])->name('pay-periods.store');
    Route::post('/pay-periods/{payPeriod}/generate', [PayrollController::class, 'generatePayslips'])->name('generate');
    // Generate payroll for a selected date range (start_date, end_date)
    Route::post('/generate', [PayrollController::class, 'generateForRange'])->name('generate.range');
    Route::post('/pay-periods/{payPeriod}/complete', [PayrollController::class, 'completePayPeriod'])->name('pay-periods.complete');
    Route::get('/employees/{employee}/pay-periods/{payPeriod}/payslip', [PayrollController::class, 'showPayslip'])->name('show-payslip');
    Route::get('/download-pdf', [PayrollController::class, 'downloadPdf'])->name('download_pdf');
    Route::put('/payslip/{payslip}/deduction', [PayrollController::class, 'updateOtherDeduction'])->name('payroll.payslip.deduction');
    Route::put('/payslips/{payslip}/deductions', [PayrollController::class, 'updateDeductions'])->name('payslips.update-deductions');
});

// Leave Management Routes
Route::get('/leave-management', [LeaveController::class, 'index'])->name('leave.index');
Route::post('/leave-management/update-balance/{user}', [LeaveController::class, 'updateLeaveBalance'])->name('leave.updateBalance');

// Employee Leave Request Routes
Route::get('/my-leave-requests', [LeaveController::class, 'myLeaveRequests'])->name('employee.leave.index');
Route::get('/leave-request/create', [LeaveController::class, 'createLeaveRequest'])->name('employee.leave.create');
Route::post('/leave-request/store', [LeaveController::class, 'storeLeaveRequest'])->name('employee.leave.store');

// HR/Admin Leave Request Review Routes
Route::get('/leave-requests-review', [LeaveController::class, 'reviewLeaveRequests'])->name('leave.review');
Route::post('/leave-requests/{leaveRequest}/approve', [LeaveController::class, 'approveLeaveRequest'])->name('leave.approve');
Route::post('/leave-requests/{leaveRequest}/reject', [LeaveController::class, 'rejectLeaveRequest'])->name('leave.reject');
Route::get('/leave-requests/{leaveRequest}/reason-pdf', [LeaveController::class, 'generatePdfReason'])->name('leave.reason.pdf');

// Attendance Routes
Route::middleware(['auth'])->group(function () {
    // Attendance Routes
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/change-shift', [ChangeShiftController::class, 'index'])->name('change-shift.index');
        Route::post('/change-shift', [ChangeShiftController::class, 'store'])->name('change-shift.store');
        Route::get('/change-restday', [ChangeRestdayController::class, 'index'])->name('change-restday.index');
        Route::post('/change-restday', [ChangeRestdayController::class, 'store'])->name('change-restday.store');
        Route::get('/no-bio-request', [NoBioRequestController::class, 'index'])->name('no-bio-request.index');
        Route::post('/no-bio-request', [NoBioRequestController::class, 'store'])->name('no-bio-request.store');
        Route::get('/overtime-request', [OvertimeRequestController::class, 'index'])->name('overtime-request.index');
        Route::post('/overtime-request', [OvertimeRequestController::class, 'store'])->name('overtime-request.store');
    });
    // Face registration (employee)
    Route::get('/face/register', [\App\Http\Controllers\FaceController::class, 'showRegistration'])->name('face.register');
    Route::post('/face/register', [\App\Http\Controllers\FaceController::class, 'storeRegistration'])->name('face.register.store');
});

// Admin Attendance Routes
Route::prefix('admin/attendance')->name('admin.attendance.')->middleware(['auth', \App\Http\Middleware\EnsureHrAdminRole::class])->group(function () {
    // Change Shift Review
    Route::get('/change-shift-requests', [AdminChangeShiftController::class, 'index'])->name('change-shift.review');
    Route::post('/change-shift-requests/{id}/approve', [AdminChangeShiftController::class, 'approve'])->name('change-shift.approve');
    Route::post('/change-shift-requests/{id}/reject', [AdminChangeShiftController::class, 'reject'])->name('change-shift.reject');

    // Change Restday Review
    Route::get('/change-restday-requests', [AdminChangeRestdayController::class, 'index'])->name('change-restday.review');
    Route::post('/change-restday-requests/{id}/approve', [AdminChangeRestdayController::class, 'approve'])->name('change-restday.approve');
    Route::post('/change-restday-requests/{id}/reject', [AdminChangeRestdayController::class, 'reject'])->name('change-restday.reject');

    // No Bio Request Review
    Route::get('/no-bio-requests', [AdminNoBioRequestController::class, 'index'])->name('no-bio-request.review');
    Route::post('/no-bio-requests/{id}/approve', [AdminNoBioRequestController::class, 'approve'])->name('no-bio-request.approve');
    Route::post('/no-bio-requests/{id}/reject', [AdminNoBioRequestController::class, 'reject'])->name('no-bio-request.reject');

    // Overtime Request Review
    Route::get('/overtime-requests', [AdminOvertimeRequestController::class, 'index'])->name('overtime-request.review');
    Route::post('/overtime-requests/{id}/approve', [AdminOvertimeRequestController::class, 'approve'])->name('overtime-request.approve');
    Route::post('/overtime-requests/{id}/reject', [AdminOvertimeRequestController::class, 'reject'])->name('overtime-request.reject');
});

Route::get('dtr/admin', [DTRController::class, 'adminView'])->name('dtr.admin');
Route::get('dtr/employees/{employee}', [DTRController::class, 'showEmployeeDTR'])->name('dtr.employee.show');
Route::get('dtr/employees', [DTRController::class, 'employeesIndex'])->name('dtr.employees.index');

// DTR Routes
Route::prefix('dtr')->name('dtr.')->group(function() {
    Route::get('/', [DTRController::class, 'index'])->name('index');
    Route::post('/clock-in', [DTRController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [DTRController::class, 'clockOut'])->name('clock-out');
});

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update');
