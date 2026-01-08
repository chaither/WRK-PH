<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ChangeShiftRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChangeShiftController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $currentShift = 'N/A';
            
            if ($user->shift && $user->shift->start_time && $user->shift->end_time) {
                try {
                    $startTime = \Carbon\Carbon::parse($user->shift->start_time)->format('h:i A');
                    $endTime = \Carbon\Carbon::parse($user->shift->end_time)->format('h:i A');
                    $currentShift = $user->shift->name . ' (' . $startTime . ' - ' . $endTime . ')';
                } catch (\Exception $e) {
                    Log::warning('Invalid shift time format', ['user_id' => $user->id, 'shift_id' => $user->shift_id]);
                }
            }
            
            $shifts = Shift::all();
            $changeShiftRequests = $user->changeShiftRequests()->latest()->get();
            return view('attendance.change_shift.index', compact('currentShift', 'shifts', 'changeShiftRequests'));
        } catch (\Exception $e) {
            Log::error('Error loading change shift page: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard')->with('error', 'Failed to load change shift page. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'requested_shift' => 'required|exists:shifts,id',
                'reason' => 'required|string|max:255',
            ]);

            $user = Auth::user();

            $changeShiftRequest = ChangeShiftRequest::create([
                'user_id' => $user->id,
                'current_shift_id' => $user->shift_id,
                'requested_shift_id' => $request->requested_shift,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name ?? 'Employee';
            $requestedShiftName = $changeShiftRequest->requestedShift->name ?? 'Unknown Shift';

            // Create notification for admin/HR about new change shift request
            $adminUsers = \App\Models\User::whereIn('role', ['admin', 'hr'])->get();
            foreach ($adminUsers as $admin) {
                try {
                    \App\Models\Notification::create([
                        'user_id' => $admin->id,
                        'message' => "{$userName} submitted a change shift request for {$requestedShiftName}.",
                        'type' => 'change_shift_request_submitted',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create notification for change shift request', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Change shift request submitted successfully and is awaiting approval!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing change shift request: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to submit change shift request: ' . $e->getMessage())->withInput();
        }
    }

    public function showEmployeeShiftHistory(Request $request, \App\Models\User $employee)
    {
        try {
            // Ensure only admin/hr or the employee themselves can view shift change history
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

            $query = ChangeShiftRequest::where('user_id', $employee->id)->with(['currentShift', 'requestedShift']);

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
            }

            $records = $query->orderBy('created_at', 'desc')->get();

            return view('shift.employee_shift_history', compact('employee', 'records', 'startDate', 'endDate', 'isFiltered'));
        } catch (\Exception $e) {
            Log::error('Error showing employee shift history: ' . $e->getMessage(), [
                'employee_id' => $employee->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to load shift history: ' . $e->getMessage());
        }
    }
}
