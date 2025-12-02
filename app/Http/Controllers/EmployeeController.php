<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shift; // Add this line
use App\Models\Department; // Add this line
use App\Models\Notification; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function __construct()
    {
        // Removed middleware. Now handled by route groups only.
    }

    public function index(Request $request)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view employees.');
        }

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
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to create employees.');
        }
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
            'role' => 'required|in:employee,hr,admin', // Add role validation
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

        // The role is now directly taken from the validated input
        // $validated['role'] = 'employee'; // Removed hardcoded role
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
            'working_days' => $validated['working_days'],
            'rest_days' => $validated['rest_days'],
            'role' => $validated['role'],
        ]);

        return redirect()->route('department.index')->with('success', 'Employee created successfully');
    }

    public function show(User $employee)
    {
        $employee->load(['department', 'shift']);

        $data = $employee->toArray();
        $data['working_days'] = $this->decodeDaysArray($employee->working_days);
        $data['rest_days'] = $this->decodeDaysArray($employee->rest_days);
        $data['work_start_time'] = $employee->work_start
            ? Carbon::parse($employee->work_start)->format('H:i')
            : null;
        $data['work_end_time'] = $employee->work_end
            ? Carbon::parse($employee->work_end)->format('H:i')
            : null;

        return response()->json($data);
    }

    public function update(Request $request, User $employee)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to update employee information.');
        }
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

        // Check if the role has changed
        if ($employee->role !== $validated['role']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your role has been changed from ' . $employee->role . ' to ' . $validated['role'] . ' by an administrator.',
                'type' => 'role_change',
            ]);
        }

        // Check for changes in Personal Information
        if ($employee->first_name !== $validated['first_name'] ||
            $employee->last_name !== $validated['last_name'])
        {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your name has been updated to ' . $validated['first_name'] . ' ' . $validated['last_name'] . ' by an administrator.',
                'type' => 'personal_info_update',
            ]);
        }

        if ($employee->email !== $validated['email']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your email address has been updated from ' . $employee->email . ' to ' . $validated['email'] . ' by an administrator.',
                'type' => 'personal_info_update',
            ]);
        }

        if ($employee->position !== $validated['position']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your position has been changed from ' . $employee->position . ' to ' . $validated['position'] . ' by an administrator.',
                'type' => 'personal_info_update',
            ]);
        }

        // Department change check
        $newDepartmentId = $request->input('department_id');
        // Normalize newDepartmentId to be an integer or null
        $newDepartmentId = (empty($newDepartmentId) && $newDepartmentId !== 0) ? null : (int)$newDepartmentId;

        // Normalize oldDepartmentId to be an integer or null
        $oldDepartmentId = $employee->department_id;
        $oldDepartmentId = (empty($oldDepartmentId) && $oldDepartmentId !== 0) ? null : (int)$oldDepartmentId;

        // Log for debugging
        \Illuminate\Support\Facades\Log::info('Department Comparison', [
            'employee_id' => $employee->id,
            'oldDepartmentId' => $oldDepartmentId,
            'oldDepartmentIdType' => gettype($oldDepartmentId),
            'newDepartmentId' => $newDepartmentId,
            'newDepartmentIdType' => gettype($newDepartmentId),
        ]);

        if ($oldDepartmentId !== $newDepartmentId) {
            $oldDepartmentName = $employee->department->name ?? 'N/A';
            $newDepartment = ($newDepartmentId !== null) ? Department::find($newDepartmentId) : null;
            $newDepartmentName = $newDepartment->name ?? 'N/A';
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your department has been changed from ' . $oldDepartmentName . ' to ' . $newDepartmentName . ' by an administrator.',
                'type' => 'personal_info_update',
            ]);
        }

        // Check for changes in Work Schedule
        if (Carbon::parse($employee->start_date)->format('Y-m-d') !== Carbon::parse($validated['start_date'])->format('Y-m-d')) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your start date has been changed from ' . Carbon::parse($employee->start_date)->format('M d, Y') . ' to ' . Carbon::parse($validated['start_date'])->format('M d, Y') . ' by an administrator.',
                'type' => 'work_schedule_update',
            ]);
        }

        if ($employee->shift_id !== $validated['shift_id']) {
            $oldShiftName = $employee->shift->name ?? 'N/A';
            $newShift = Shift::find($validated['shift_id']);
            $newShiftName = $newShift->name ?? 'N/A';
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your shift has been changed from ' . $oldShiftName . ' to ' . $newShiftName . ' by an administrator.',
                'type' => 'work_schedule_update',
            ]);
        }

        if ($this->decodeDaysArray($employee->working_days) !== $validated['working_days']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your working days have been updated by an administrator.',
                'type' => 'work_schedule_update',
            ]);
        }

        if ($this->decodeDaysArray($employee->rest_days) !== $validated['rest_days']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your rest days have been updated by an administrator.',
                'type' => 'work_schedule_update',
            ]);
        }

        if (Carbon::parse($employee->work_start)->format('H:i') !== Carbon::parse($validated['work_start'])->format('H:i') ||
            Carbon::parse($employee->work_end)->format('H:i') !== Carbon::parse($validated['work_end'])->format('H:i'))
        {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your work start and/or end times have been updated by an administrator.',
                'type' => 'work_schedule_update',
            ]);
        }

        // Check for changes in Payroll Details
        if ($employee->pay_schedule !== $validated['pay_schedule']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your pay schedule has been changed from ' . $employee->pay_schedule . ' to ' . $validated['pay_schedule'] . ' by an administrator.',
                'type' => 'payroll_update',
            ]);
        }

        if ((float) $employee->basic_salary !== (float) $validated['basic_salary']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your basic salary has been changed from ₱' . number_format($employee->basic_salary, 2) . ' to ₱' . number_format($validated['basic_salary'], 2) . ' by an administrator.',
                'type' => 'payroll_update',
            ]);
        }

        if ((float) $employee->daily_rate !== (float) $validated['daily_rate']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your daily rate has been changed from ₱' . number_format($employee->daily_rate, 2) . ' to ₱' . number_format($validated['daily_rate'], 2) . ' by an administrator.',
                'type' => 'payroll_update',
            ]);
        }

        if ((float) $employee->hourly_rate !== (float) $validated['hourly_rate']) {
            Notification::create([
                'user_id' => $employee->id,
                'message' => 'Your hourly rate has been changed from ₱' . number_format($employee->hourly_rate, 2) . ' to ₱' . number_format($validated['hourly_rate'], 2) . ' by an administrator.',
                'type' => 'payroll_update',
            ]);
        }

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
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to delete employees.');
        }
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee account permanently deleted.');
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

    private function decodeDaysArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}