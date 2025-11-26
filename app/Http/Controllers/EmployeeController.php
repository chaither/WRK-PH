<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shift; // Add this line
use App\Models\Department; // Add this line
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
        // Removed redundant role check, now handled by route middleware.

        $query = User::query()->where('role', 'employee');

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        $employees = $query->get();
        $shifts = Shift::all(); // Fetch all shifts
        $departments = Department::all(); // Fetch all departments
        return view('department.index', compact('employees', 'shifts', 'departments')); // Pass shifts and departments to the view
    }

    public function store(Request $request)
    {
        // Removed redundant role check, now handled by route middleware.

        // Sanitize salary inputs to accept comma-separated or currency-formatted values
        if ($request->filled('monthly_salary')) {
            $clean = preg_replace('/[^0-9.\-]/', '', $request->input('monthly_salary'));
            $request->merge(['monthly_salary' => $clean]);
        }
        if ($request->filled('semi_monthly_salary')) {
            $clean = preg_replace('/[^0-9.\-]/', '', $request->input('semi_monthly_salary'));
            $request->merge(['semi_monthly_salary' => $clean]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'position' => 'required|string|max:255',
            'monthly_salary' => 'required_if:pay_period,monthly|nullable|numeric|min:0',
            'semi_monthly_salary' => 'required_if:pay_period,semi-monthly|nullable|numeric|min:0',
            'pay_period' => 'required|in:semi-monthly,monthly',
            'work_start' => 'required|date_format:H:i',
            'work_end' => 'required|date_format:H:i|after:work_start',
            'start_date' => 'required|date',
            'working_days' => 'nullable|array',
            'rest_days' => 'nullable|array', // Add rest_days validation
            'department_id' => 'nullable|exists:departments,id',
            'shift_id' => 'required|exists:shifts,id', // Add shift_id validation
        ]);

        // Handle password separately: only hash if provided
        $password = $request->input('password');
        if (!empty($password)) {
            $validated['password'] = Hash::make($password);
        } else {
            // If no password is provided, remove it from validated data to avoid hashing an empty string
            unset($validated['password']);
        }

        // Determine basic salary based on pay period
        $basicSalary = 0;
        if ($validated['pay_period'] === 'monthly') {
            $basicSalary = $validated['monthly_salary'];
        } elseif ($validated['pay_period'] === 'semi-monthly') {
            $basicSalary = $validated['semi_monthly_salary'];
        }

        // Convert semi-monthly to monthly equivalent for consistent rate calculation
        $effectiveMonthlySalary = $basicSalary;
        if ($validated['pay_period'] === 'semi-monthly') {
            $effectiveMonthlySalary = $basicSalary * 2;
        }

        // Calculate actual working days in month
        $actualWorkingDaysInMonth = $this->getActualWorkingDaysInMonth(
            $validated['working_days'], 
            $validated['rest_days']
        );

        $dailyRate = 0;
        $hourlyRate = 0;

        if ($effectiveMonthlySalary > 0 && $actualWorkingDaysInMonth > 0) {
            $dailyRate = $effectiveMonthlySalary / $actualWorkingDaysInMonth;
        $hourlyRate = $dailyRate / 8; // Assuming 8 working hours per day
        }

        $validated['basic_salary'] = round($effectiveMonthlySalary, 2); // Store effective monthly salary
        $validated['daily_rate'] = round($dailyRate, 2);
        $validated['hourly_rate'] = round($hourlyRate, 2);

        $validated['role'] = 'employee';
        $validated['working_days'] = $request->input('working_days', []);
        $validated['rest_days'] = $request->input('rest_days', []); // Save rest_days
        
        // Assign the pay_period input to the pay_schedule column
        $validated['pay_schedule'] = $validated['pay_period'];

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'position' => $validated['position'],
            'basic_salary' => $validated['basic_salary'],
            'daily_rate' => $validated['daily_rate'],
            'hourly_rate' => $validated['hourly_rate'],
            'pay_schedule' => $validated['pay_schedule'],
            'work_start' => $validated['work_start'],
            'work_end' => $validated['work_end'],
            'start_date' => $validated['start_date'],
            'department_id' => $validated['department_id'],
            'shift_id' => $validated['shift_id'],
            'role' => $validated['role'],
        ]);

        return redirect()->route('department.index')->with('success', 'Employee created successfully');
    }

    public function show(User $employee)
    {
        return response()->json($employee->load(['department', 'shift']));
    }

    public function update(Request $request, User $employee)
    {
        // Removed redundant role check, now handled by route middleware.

        // Sanitize salary inputs to accept comma-separated or currency-formatted values
        if ($request->filled('monthly_salary')) {
            $clean = preg_replace('/[^0-9.\-]/', '', $request->input('monthly_salary'));
            $request->merge(['monthly_salary' => $clean]);
        }
        if ($request->filled('semi_monthly_salary')) {
            $clean = preg_replace('/[^0-9.\-]/', '', $request->input('semi_monthly_salary'));
            $request->merge(['semi_monthly_salary' => $clean]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'position' => 'required|string|max:255',
            'pay_period' => 'required|in:semi-monthly,monthly',
            'monthly_salary' => 'required_if:pay_period,monthly|nullable|numeric|min:0',
            'semi_monthly_salary' => 'required_if:pay_period,semi-monthly|nullable|numeric|min:0',
            'work_start' => 'required|date_format:H:i',
            'work_end' => 'required|date_format:H:i|after:work_start',
            'role' => 'required|in:employee,hr,admin', // Include role in validation
            'start_date' => 'required|date',
            'working_days' => 'nullable|array',
            'rest_days' => 'nullable|array', // Add rest_days validation
            'shift_id' => 'required|exists:shifts,id', // Add shift_id validation
        ]);

        // Handle password separately: only hash if provided and not empty
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->input('password'));
        } else {
            unset($validated['password']); // Remove password from validated data if not provided
        }

        // Determine basic salary based on pay period
        $basicSalary = 0;
        if ($validated['pay_period'] === 'monthly') {
            $basicSalary = (float) $validated['monthly_salary'];
        } elseif ($validated['pay_period'] === 'semi-monthly') {
            $basicSalary = (float) $validated['semi_monthly_salary'];
        }

        // Convert semi-monthly to monthly equivalent for consistent rate calculation
        $effectiveMonthlySalary = $basicSalary;
        if ($validated['pay_period'] === 'semi-monthly') {
            $effectiveMonthlySalary = $basicSalary * 2;
        }

        // Calculate actual working days in month
        $actualWorkingDaysInMonth = $this->getActualWorkingDaysInMonth(
            $validated['working_days'], 
            $validated['rest_days']
        );

        $dailyRate = 0;
        $hourlyRate = 0;

        if ($effectiveMonthlySalary > 0 && $actualWorkingDaysInMonth > 0) {
            $dailyRate = $effectiveMonthlySalary / $actualWorkingDaysInMonth;
        $hourlyRate = $dailyRate / 8; // Assuming 8 working hours per day
        }

        $validated['basic_salary'] = round($effectiveMonthlySalary, 2); // Store effective monthly salary
        $validated['daily_rate'] = round($dailyRate, 2);
        $validated['hourly_rate'] = round($hourlyRate, 2);

        $validated['working_days'] = $request->input('working_days', []);
        $validated['rest_days'] = $request->input('rest_days', []); // Save rest_days

        // Assign the pay_period input to the pay_schedule column
        $validated['pay_schedule'] = $validated['pay_period'];

        $employee->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'position' => $validated['position'],
            'pay_schedule' => $validated['pay_schedule'],
            'basic_salary' => $validated['basic_salary'],
            'daily_rate' => $validated['daily_rate'],
            'hourly_rate' => $validated['hourly_rate'],
            'work_start' => $validated['work_start'],
            'work_end' => $validated['work_end'],
            'role' => $validated['role'],
            'start_date' => $validated['start_date'],
            'working_days' => $validated['working_days'],
            'rest_days' => $validated['rest_days'],
            'shift_id' => $validated['shift_id'],
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy(User $employee)
    {
        // Removed redundant role check, now handled by route middleware.

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }

    /**
     * Calculates the number of actual working days in the current month,
     * considering selected working days and rest days.
     *
     * @param array $selectedWorkingDaysArr
     * @param array $selectedRestDaysArr
     * @return int
     */
    private function getActualWorkingDaysInMonth(array $selectedWorkingDaysArr, array $selectedRestDaysArr): int
    {
        $today = now();
        $year = $today->year;
        $month = $today->month;
        $daysInMonth = $today->daysInMonth;
        $actualWorkingDays = 0;
 
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
 
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::createFromDate($year, $month, $day);
            $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $currentDayName = $dayNames[$dayOfWeek];
 
            if (in_array($currentDayName, $selectedWorkingDaysArr) && !in_array($currentDayName, $selectedRestDaysArr)) {
                $actualWorkingDays++;
            }
        }
 
        return $actualWorkingDays;
    }
}