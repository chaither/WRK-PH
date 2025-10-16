<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $data = [];
        
        $user = \Illuminate\Support\Facades\Auth::user();
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

        // If employee, show their payslips
        if ($user && $user->role === 'employee') {
            $data['payslips'] = \App\Models\Payslip::with('payPeriod')
                ->where('user_id', $user->id)
                ->orderByDesc('id')
                ->get();
        }
        return view('dashboard.index', $data);
    }
}