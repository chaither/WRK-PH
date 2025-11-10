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

    public function index(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $query = User::query()->where('role', 'employee');

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        $employees = $query->get();
        return view('employees.index', compact('employees'));
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
            'start_date' => 'required|date',
            'working_days' => 'nullable|array',
        ]);

        // Handle password separately: only hash if provided
        $password = $request->input('password');
        if (!empty($password)) {
            $validated['password'] = Hash::make($password);
        } else {
            // If no password is provided, remove it from validated data to avoid hashing an empty string
            unset($validated['password']);
        }

        // Calculate daily and hourly rates based on basic_salary
        $basicSalary = (float) $validated['basic_salary'];
        $dailyRate = $basicSalary / 22; // Assuming 22 working days per month
        $hourlyRate = $dailyRate / 8; // Assuming 8 working hours per day

        $validated['daily_rate'] = round($dailyRate, 2);
        $validated['hourly_rate'] = round($hourlyRate, 2);

        $validated['role'] = 'employee';
        $validated['working_days'] = $request->input('working_days', []);
        
        User::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }

    public function update(Request $request, User $employee)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'position' => 'required|string|max:255',
            'basic_salary' => 'required|numeric|min:0',
            'pay_period' => 'required|in:semi-monthly,monthly',
            'work_start' => 'required|date_format:H:i',
            'work_end' => 'required|date_format:H:i|after:work_start',
            'role' => 'required|in:employee,hr,admin', // Include role in validation
            'start_date' => 'required|date',
            'working_days' => 'nullable|array',
        ]);

        // Handle password separately: only hash if provided and not empty
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->input('password'));
        } else {
            unset($validated['password']); // Remove password from validated data if not provided
        }

        // Calculate daily and hourly rates based on basic_salary
        $basicSalary = (float) $validated['basic_salary'];
        $dailyRate = $basicSalary / 22; // Assuming 22 working days per month
        $hourlyRate = $dailyRate / 8; // Assuming 8 working hours per day

        $validated['daily_rate'] = round($dailyRate, 2);
        $validated['hourly_rate'] = round($hourlyRate, 2);

        $validated['working_days'] = $request->input('working_days', []);

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy(User $employee)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }
}