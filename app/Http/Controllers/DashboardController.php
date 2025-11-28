<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payslip;
use App\Models\Department;
use App\Models\Absence;
use App\Models\Notification;
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

        if ($user && $user->role === 'admin') {
            $data['employeeCount'] = $employeeQuery->count();
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