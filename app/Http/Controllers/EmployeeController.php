<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function __construct()
    {
        // Removed middleware. Now handled by route groups only.
    }

    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }
        $employees = User::where('role', 'employee')->get();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }
        return view('employees.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'position' => 'required|string|max:255',
            'basic_salary' => 'required|numeric|min:0',
            'pay_period' => 'required|in:semi-monthly,monthly',
            'work_start' => 'required|date_format:H:i',
            'work_end' => 'required|date_format:H:i|after:work_start',
        ]);

        // Calculate daily and hourly rates based on basic_salary
        $basicSalary = (float) $validated['basic_salary'];
        $dailyRate = $basicSalary / 22; // Assuming 22 working days per month
        $hourlyRate = $dailyRate / 8; // Assuming 8 working hours per day

        $validated['daily_rate'] = round($dailyRate, 2);
        $validated['hourly_rate'] = round($hourlyRate, 2);

        $validated['role'] = 'employee';
        $validated['password'] = Hash::make($validated['password']);
        
        User::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }
}