<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        try {
            $overtimeRequests = Auth::user()->overtimeRequests()->latest()->get();
            return view('attendance.overtime_request.index', compact('overtimeRequests'));
        } catch (\Exception $e) {
            Log::error('Error loading overtime requests: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard')->with('error', 'Failed to load overtime requests. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'reason' => 'required|string|max:500',
            ]);

            $overtimeRequest = OvertimeRequest::create([
                'user_id' => Auth::id(),
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            $user = Auth::user();
            $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name ?? 'Employee';
            $dateFormatted = Carbon::parse($request->date)->format('M d, Y');

            // Create notification for admin/HR about new overtime request
            $adminUsers = User::whereIn('role', ['admin', 'hr'])->get();
            foreach ($adminUsers as $admin) {
                try {
                    Notification::create([
                        'user_id' => $admin->id,
                        'message' => "{$userName} submitted an overtime request for {$dateFormatted}.",
                        'type' => 'overtime_request_submitted',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create notification for overtime request', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Create notification for employee
            try {
                Notification::create([
                    'user_id' => Auth::id(),
                    'message' => "Your overtime request for {$dateFormatted} has been submitted and is pending approval.",
                    'type' => 'overtime_request_pending',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create employee notification for overtime request', [
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->with('success', 'Overtime Request submitted successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing overtime request: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to submit overtime request: ' . $e->getMessage())->withInput();
        }
    }

    public function showEmployeeOvertimeHistory(Request $request, \App\Models\User $employee)
    {
        try {
            // Ensure only admin/hr can view other employee's overtime history
            // Ensure only admin/hr or the employee themselves can view overtime history
            $user = Auth::user();
            if (!$user || (!in_array($user->role, ['admin', 'hr']) && $user->id !== $employee->id)) {
                abort(403, 'Unauthorized access.');
            }

            $startDate = null;
            $endDate = null;
            $isFiltered = false;

            $period = $request->input('period');
            $specificDate = $request->input('specific_date');
            $requestStartDate = $request->input('start_date');
            $requestEndDate = $request->input('end_date');

            if ($period) {
                $currentMonth = \Carbon\Carbon::today()->month;
                $currentYear = \Carbon\Carbon::today()->year;

                if ($period === 'first_half') {
                    $startDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1);
                    $endDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 15);
                } elseif ($period === 'second_half') {
                    $startDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 16);
                    $endDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth)->endOfMonth();
                } elseif ($period === 'whole_month') {
                    $startDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1);
                    $endDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth)->endOfMonth();
                }
                $isFiltered = true;
            } elseif ($specificDate) {
                try {
                    $startDate = \Carbon\Carbon::parse($specificDate);
                    $endDate = \Carbon\Carbon::parse($specificDate);
                    $isFiltered = true;
                } catch (\Exception $e) {
                    Log::warning('Invalid specific_date format', ['date' => $specificDate]);
                }
            } elseif ($requestStartDate && $requestEndDate) {
                try {
                    $startDate = \Carbon\Carbon::parse($requestStartDate);
                    $endDate = \Carbon\Carbon::parse($requestEndDate);
                    $isFiltered = true;
                } catch (\Exception $e) {
                    Log::warning('Invalid date range format', ['start' => $requestStartDate, 'end' => $requestEndDate]);
                }
            }
            
            if (!$startDate || !$endDate) {
                $startDate = \Carbon\Carbon::today()->startOfMonth();
                $endDate = \Carbon\Carbon::today()->endOfMonth();
            }

            $query = OvertimeRequest::where('user_id', $employee->id);

            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }

            $records = $query->orderBy('date', 'desc')->get();

            return view('overtime.employee_overtime_history', compact('employee', 'records', 'startDate', 'endDate', 'isFiltered'));
        } catch (\Exception $e) {
            Log::error('Error showing employee overtime history: ' . $e->getMessage(), [
                'employee_id' => $employee->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to load overtime history: ' . $e->getMessage());
        }
    }
}

