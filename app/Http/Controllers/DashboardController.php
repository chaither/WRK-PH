<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payslip;
use App\Models\Department;
use App\Models\Absence;
use App\Models\Notification;
use App\Models\OvertimeRequest;
use App\Services\PayrollService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user && $user->role === 'employee') {
            return redirect()->route('employee.dashboard');
        }

        $data = [];
        $departments = Department::all(); // Fetch all departments
        $selectedDepartmentId = $request->input('department_id'); // Get selected department ID from request

        // Start building employee query
        $employeeQuery = User::where('role', 'employee');
        if ($selectedDepartmentId) {
            $employeeQuery->where('department_id', $selectedDepartmentId);
        }

        if ($user && $user->isHRManager()) {
            $data['employeeCount'] = $employeeQuery->count();
        }

        if ($user && $user->isHRManager()) {
            $data['pendingLeaveRequests'] = \App\Models\LeaveRequest::where('status', 'pending')->count();
            $data['pendingNotifications'] = Notification::whereNull('read_at')->count();
            $data['pendingOvertimeRequests'] = OvertimeRequest::where('status', 'pending')->count();
        }

        if ($user && in_array($user->role, ['admin', 'hr'])) {
            $today = now()->toDateString();

            // Start building DTRRecord query
            $dtrQuery = \App\Models\DTRRecord::whereDate('date', $today);
            if ($selectedDepartmentId) {
                $dtrQuery->whereHas('user', function ($query) use ($selectedDepartmentId) {
                    $query->where('department_id', $selectedDepartmentId);
                });
            }

            $data['presentToday'] = (clone $dtrQuery)->where('status', 'present')->count();
            $data['lateToday'] = (clone $dtrQuery)->where('status', 'late')->count();
            
            $totalEmployees = $employeeQuery->count(); // Use the filtered employee count
            $presentLateCount = $data['presentToday'] + $data['lateToday'];
            $data['absentToday'] = $totalEmployees - $presentLateCount;
        }

        // Fetch monthly payroll totals
        $monthlyPayroll = Payslip::selectRaw('DATE_FORMAT(pay_period_end, "%Y-%m") as month, SUM(net_pay) as total_net_pay')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->toArray();

        $payrollLabels = [];
        $payrollData = [];
        for ($i = 6; $i <= 12; $i++) { // June to December
            $month = '2025-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $payrollLabels[] = date('M', mktime(0, 0, 0, $i, 10));
            $payrollData[] = $monthlyPayroll[$month]['total_net_pay'] ?? 0;
        }
        for ($i = 1; $i <= 5; $i++) { // January to May
            $month = '2026-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $payrollLabels[] = date('M', mktime(0, 0, 0, $i, 10));
            $payrollData[] = $monthlyPayroll[$month]['total_net_pay'] ?? 0;
        }

        $data['payrollLabels'] = $payrollLabels;
        $data['payrollData'] = $payrollData;

        // Fetch monthly deduction totals
        $monthlyDeductions = Payslip::selectRaw('DATE_FORMAT(pay_period_end, "%Y-%m") as month, SUM(deductions) as total_deductions')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->toArray();

        $deductionLabels = [];
        $deductionData = [];
        for ($i = 6; $i <= 12; $i++) { // June to December
            $month = '2025-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $deductionLabels[] = date('M', mktime(0, 0, 0, $i, 10));
            $deductionData[] = $monthlyDeductions[$month]['total_deductions'] ?? 0;
        }
        for ($i = 1; $i <= 5; $i++) { // January to May
            $month = '2026-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $deductionLabels[] = date('M', mktime(0, 0, 0, $i, 10));
            $deductionData[] = $monthlyDeductions[$month]['total_deductions'] ?? 0;
        }

        $data['deductionLabels'] = $deductionLabels;
        $data['deductionData'] = $deductionData;

        // Combine payroll and deduction labels for consistency
        $combinedLabels = $payrollLabels; // Assuming payrollLabels and deductionLabels are identical
        
        $data['combinedLabels'] = $combinedLabels;
        $data['payrollData'] = $payrollData; // Keep for the combined chart
        $data['deductionData'] = $deductionData; // Keep for the combined chart

        // Fetch monthly attendance data
        $monthlyAttendance = \App\Models\DTRRecord::selectRaw('DATE_FORMAT(date, "%Y-%m") as month, 
                                                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                                                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_count,
                                                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->toArray();

        $attendanceLabels = [];
        $presentData = [];
        $lateData = [];
        $absentData = [];

        for ($i = 6; $i <= 12; $i++) { // June to December
            $month = '2025-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $attendanceLabels[] = date('M', mktime(0, 0, 0, $i, 10));
            $presentData[] = $monthlyAttendance[$month]['present_count'] ?? 0;
            $lateData[] = $monthlyAttendance[$month]['late_count'] ?? 0;
            $absentData[] = $monthlyAttendance[$month]['absent_count'] ?? 0;
        }
        for ($i = 1; $i <= 5; $i++) { // January to May
            $month = '2026-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $attendanceLabels[] = date('M', mktime(0, 0, 0, $i, 10));
            $presentData[] = $monthlyAttendance[$month]['present_count'] ?? 0;
            $lateData[] = $monthlyAttendance[$month]['late_count'] ?? 0;
            $absentData[] = $monthlyAttendance[$month]['absent_count'] ?? 0;
        }

        $data['attendanceLabels'] = $attendanceLabels;
        $data['presentData'] = $presentData;
        $data['lateData'] = $lateData;
        $data['absentData'] = $absentData;

        $data['departments'] = $departments; // Pass departments to the view
        $data['selectedDepartmentId'] = $selectedDepartmentId; // Pass selected department ID to the view

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('dashboard.index', $data);
    }

    public function employeeDashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user || $user->role !== 'employee') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $payslips = Payslip::with('payPeriod')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $absences = Absence::where('user_id', $user->id)
            ->orderByDesc('date')
            ->get();

        $generalNotifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $salaryNotification = null;
        $payrollService = new PayrollService();
        $today = Carbon::today();

        if ($user->pay_schedule) {
            $currentYear = $today->year;
            $currentMonth = $today->month;
            
            // Attempt to get pay periods for the current month and next month
            $payPeriodsThisMonth = $payrollService->getPayPeriodDates(
                $user->pay_schedule,
                Carbon::create($currentYear, $currentMonth, 1)->startOfMonth(),
                Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()
            );

            $payPeriodsNextMonth = [];
            if ($currentMonth === 12) {
                $payPeriodsNextMonth = $payrollService->getPayPeriodDates(
                    $user->pay_schedule,
                    Carbon::create($currentYear + 1, 1, 1)->startOfMonth(),
                    Carbon::create($currentYear + 1, 1, 1)->endOfMonth()
                );
            } else {
                $payPeriodsNextMonth = $payrollService->getPayPeriodDates(
                    $user->pay_schedule,
                    Carbon::create($currentYear, $currentMonth + 1, 1)->startOfMonth(),
                    Carbon::create($currentYear, $currentMonth + 1, 1)->endOfMonth()
                );
            }
            
            $allPayPeriods = array_merge($payPeriodsThisMonth, $payPeriodsNextMonth);
            $nextPayday = null;

            foreach ($allPayPeriods as $period) {
                $payDate = Carbon::parse($period['end']); // Assuming 'end' is the payday
                if ($payDate->greaterThanOrEqualTo($today)) {
                    if (!$nextPayday || $payDate->lessThan($nextPayday)) {
                        $nextPayday = $payDate;
                    }
                }
            }

            if ($nextPayday) {
                $diffInDays = $today->diffInDays($nextPayday, false);

                if ($diffInDays === 0) {
                    $salaryNotification = 'Today is payday! Your salary is ready for pickup.';
                } elseif ($diffInDays === 1) {
                    $salaryNotification = 'Tomorrow is payday! Prepare to pick up your salary.';
                }
            }
        }

        // Add salary notification to general notifications if it exists
        if ($salaryNotification) {
            $generalNotifications->prepend((object)['message' => $salaryNotification, 'type' => 'salary', 'created_at' => Carbon::now()]);
        }

        return view('dashboard.employee', compact('payslips', 'absences', 'generalNotifications'));
    }

    public function destroyNotification(Notification $notification)
    {
        $user = Auth::user();

        // Ensure the authenticated user owns the notification
        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully'], 200);
    }

    public function bulkDestroyNotifications(Request $request)
    {
        $user = Auth::user();
        $notificationIds = $request->input('ids');

        if (!is_array($notificationIds) || empty($notificationIds)) {
            return response()->json(['error' => 'No notifications selected for deletion'], 400);
        }

        // Delete only notifications that belong to the authenticated user
        Notification::where('user_id', $user->id)
            ->whereIn('id', $notificationIds)
            ->delete();

        return response()->json(['message' => 'Selected notifications deleted successfully'], 200);
    }
}