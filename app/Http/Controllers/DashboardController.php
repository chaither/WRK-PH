<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payslip;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user && $user->role === 'employee') {
            return redirect()->route('employee.dashboard');
        }

        $data = [];

        if ($user && $user->role === 'admin') {
            $data['employeeCount'] = User::where('role', 'employee')->count();
        }

        if ($user && in_array($user->role, ['admin', 'hr'])) {
            $today = now()->toDateString();
            $data['presentToday'] = \App\Models\DTRRecord::whereDate('date', $today)
                ->where('status', 'present')
                ->count();
            $data['lateToday'] = \App\Models\DTRRecord::whereDate('date', $today)
                ->where('status', 'late')
                ->count();
            $totalEmployees = \App\Models\User::where('role', 'employee')->count();
            $presentLateCount = $data['presentToday'] + $data['lateToday'];
            $data['absentToday'] = $totalEmployees - $presentLateCount;
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