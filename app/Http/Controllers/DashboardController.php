<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payslip;
use App\Models\Department;

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

        return view('dashboard.employee', compact('payslips'));
    }
}