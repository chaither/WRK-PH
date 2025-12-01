<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Department;
use App\Models\User;
use App\Models\Shift; // Add this line
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Add this line

class DepartmentController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view departments.');
        }
        $departments = Department::all();
        $shifts = Shift::all(); // Fetch all shifts
        return view('department.index', compact('departments', 'shifts')); // Pass shifts to the view
    }

    public function create()
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to create departments.');
        }
        return view('departments.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to create departments.');
        }
        $request->validate([
            'name' => 'required|unique:departments|max:255',
        ]);

        $department = Department::create([
            'name' => $request->name,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Department created successfully.', 'department' => $department]);
        }

        return redirect()->route('department.index')->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view departments.');
        }
        return view('departments.show', compact('department'));
    }

    public function showEmployees(Department $department)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view employees in departments.');
        }
        $employees = $department->users()->where('role', 'employee')->get();
        $availableEmployees = User::whereDoesntHave('department')
                                  ->orWhereNull('department_id')
                                  ->where('role', 'employee')
                                  ->get();
                                  
        $departments = Department::all(); // Fetch all departments
        $shifts = Shift::all(); // Fetch all shifts
        return view('department.show_employees', compact('department', 'employees', 'availableEmployees', 'departments', 'shifts'));
    }

    public function addEmployeeToDepartment(Request $request, Department $department)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to add employees to departments.');
        }
        $request->validate([
            'employee_id' => 'required|exists:users,id',
        ]);

        $employee = User::find($request->employee_id);

        if ($employee->department_id === null) {
            $employee->department_id = $department->id;
            $employee->save();
            return redirect()->route('department.show_employees', $department->id)->with('success', 'Employee added to department successfully.');
        }

        return redirect()->route('department.show_employees', $department->id)->with('error', 'Employee is already assigned to a department.');
    }

    public function addEmployee(Request $request)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to add employees.');
        }
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,hr,employee',
            'position' => 'nullable|string|max:255',
            'basic_salary' => 'required|numeric|min:0',
            'pay_period' => 'required|string|in:semi-monthly,monthly',
            'work_start' => 'required|date_format:H:i',
            'work_end' => 'required|date_format:H:i|after:work_start',
            'department_id' => 'nullable|exists:departments,id', // Make department_id nullable and validate existence
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'position' => $request->position,
            'basic_salary' => $request->basic_salary,
            'pay_period' => $request->pay_period,
            'pay_schedule' => $request->pay_period, // Ensure pay_schedule is set
            'daily_rate' => $request->basic_salary / 22, // Example calculation, adjust as needed
            'hourly_rate' => ($request->basic_salary / 22) / 8, // Example calculation
            'work_start' => $request->work_start,
            'work_end' => $request->work_end,
            'department_id' => $request->department_id, // Assign to the selected department from the request
        ]);

        return redirect()->route('department.index')->with('success', 'Employee created and assigned to department successfully.');
    }

    public function removeEmployeeFromDepartment(Department $department, User $employee)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to remove employees from departments.');
        }
        if ($employee->department_id === $department->id) {
            $employee->delete(); // Changed to delete the employee
            return redirect()->route('employees.index')->with('success', 'Employee account permanently deleted.');
        }

        return redirect()->route('departments.show_employees', $department->id)->with('error', 'Employee not found in this department.');
    }

    public function removeEmployee(Department $department, User $employee)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to remove employees.');
        }
        $employee->department_id = null;
        $employee->save();
        return redirect()->route('department.show_employees', $department->id)->with('success', 'Employee removed from department successfully.');
    }

    public function edit(Department $department)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to edit departments.');
        }
        // This method is typically used to show an edit form.
        // With modals, we're populating the form via JavaScript,
        // so this method might not be directly called for the modal itself,
        // but it's good practice to have for API consistency or if a dedicated edit page were used.
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to update departments.');
        }
        $request->validate([
            'name' => 'required|unique:departments,name,' . $department->id . '|max:255',
        ]);

        $department->update(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully.',
                'department' => $department,
            ]);
        }

        return redirect()->route('department.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to delete departments.');
        }
        $department->delete();

        return redirect()->route('department.index')->with('success', 'Department deleted successfully.');
    }
}
