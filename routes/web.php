<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DTRController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;

// Auth Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// App Routes (Auth handled in controllers)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('employees', EmployeeController::class);
// Payroll Routes
Route::prefix('payroll')->name('payroll.')->group(function() {
    Route::get('/', [PayrollController::class, 'index'])->name('index');
    Route::post('/pay-periods', [PayrollController::class, 'createPayPeriod'])->name('pay-periods.store');
    Route::post('/pay-periods/{payPeriod}/generate', [PayrollController::class, 'generatePayslips'])->name('generate');
    // Generate payroll for a selected date range (start_date, end_date)
    Route::post('/generate', [PayrollController::class, 'generateForRange'])->name('generate.range');
    Route::get('/employees/{employee}/pay-periods/{payPeriod}/payslip', [PayrollController::class, 'showPayslip'])->name('show-payslip');
});
Route::get('dtr/admin', [DTRController::class, 'adminView'])->name('dtr.admin');

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
