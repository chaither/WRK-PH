<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest; // Added this import
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf; // Import the PDF facade

class LeaveController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to manage leave requests.');
        }

        $employees = User::where('role', 'employee')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        return view('leave.index', compact('employees'));
    }

    public function updateLeaveBalance(Request $request, User $user)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to manage leave requests.');
        }

        $request->validate([
            'leave_balance' => 'required|integer|min:0',
        ]);

        $user->leave_balance = $request->input('leave_balance');
        $user->save();

        return redirect()->route('leave.index')->with('success', 'Leave balance updated successfully.');
    }

    // Employee-specific leave request methods
    public function myLeaveRequests()
    {
        $user = Auth::user();
        if (!$user->isEmployee()) {
            abort(403, 'Unauthorized access.');
        }

        $leaveRequests = LeaveRequest::where('user_id', $user->id)->orderByDesc('created_at')->get();
        return view('employee.leave_requests', compact('leaveRequests'));
    }

    public function createLeaveRequest()
    {
        $user = Auth::user();
        if (!$user->isEmployee()) {
            abort(403, 'Unauthorized access.');
        }
        return view('employee.create_leave_request');
    }

    public function storeLeaveRequest(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user->isEmployee()) {
                abort(403, 'Unauthorized access.');
            }

            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:1000',
            ]);

            $leaveRequest = LeaveRequest::create([
                'user_id' => $user->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name ?? 'Employee';
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->format('M d, Y');
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->format('M d, Y');

            // Create notification for admin/HR about new leave request
            $adminUsers = User::whereIn('role', ['admin', 'hr'])->get();
            foreach ($adminUsers as $admin) {
                try {
                    Notification::create([
                        'user_id' => $admin->id,
                        'message' => "{$userName} submitted a leave request from {$startDateFormatted} to {$endDateFormatted}.",
                        'type' => 'leave_request_submitted',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create notification for leave request', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Create notification for employee
            try {
                Notification::create([
                    'user_id' => $user->id,
                    'message' => "Your leave request from {$startDateFormatted} to {$endDateFormatted} has been submitted and is pending approval.",
                    'type' => 'leave_request_pending',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create employee notification for leave request', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->with('success', 'Leave Request submitted successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing leave request: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to submit leave request: ' . $e->getMessage())->withInput();
        }
    }

    public function showEmployeeLeaveHistory(Request $request, User $employee)
    {
        // Ensure only admin/hr or the employee themselves can view leave history
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
            $startDate = \Carbon\Carbon::parse($specificDate);
            $endDate = \Carbon\Carbon::parse($specificDate);
            $isFiltered = true;
        } elseif ($requestStartDate && $requestEndDate) {
            $startDate = \Carbon\Carbon::parse($requestStartDate);
            $endDate = \Carbon\Carbon::parse($requestEndDate);
            $isFiltered = true;
        } else {
            $startDate = \Carbon\Carbon::today()->startOfMonth();
            $endDate = \Carbon\Carbon::today()->endOfMonth();
        }

        $query = LeaveRequest::where('user_id', $employee->id);

        if ($startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($query) use ($startDate, $endDate) {
                      $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                  });
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        return view('leave.employee_leave_history', compact('employee', 'records', 'startDate', 'endDate', 'isFiltered'));
    }

    public function generatePdfReason(LeaveRequest $leaveRequest)
    {
        // Ensure only admin/hr can view this or the employee themselves
        $user = Auth::user();
        if (!$user->isHRManager() && $user->id !== $leaveRequest->user_id) {
            return redirect()->route('leave.index')->with('error', 'You are not authorized to approve/reject this leave request.');
        }

        $pdf = Pdf::loadView('leave.reason_pdf', compact('leaveRequest'));
        $filename = $leaveRequest->user->name . ' - Leave Request.pdf';
        return $pdf->download($filename);
    }

    // HR/Admin leave request review methods
    public function reviewLeaveRequests(LeaveRequest $leaveRequest = null)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to manage leave requests.');
        }

        if ($leaveRequest) {
            $leaveRequests = collect([$leaveRequest]); // Show only the specific leave request
        } else {
            $leaveRequests = LeaveRequest::with('user')->orderByDesc('created_at')->get();
        }
        
        return view('leave.review', compact('leaveRequests'));
    }

    public function approveLeaveRequest(LeaveRequest $leaveRequest)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to manage leave requests.');
        }

        $leaveRequest->status = 'approved';
        $leaveRequest->save();

        // Deduct leave days from employee's leave balance
        $user = $leaveRequest->user;
        $startDate = \Carbon\Carbon::parse($leaveRequest->start_date);
        $endDate = \Carbon\Carbon::parse($leaveRequest->end_date);
        $leaveDays = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

        if ($user->leave_balance >= $leaveDays) {
            $user->leave_balance -= $leaveDays;
            $user->save();
            
            // Create notification for employee
            Notification::create([
                'user_id' => $user->id,
                'message' => 'Your leave request from ' . $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y') . ' has been approved.',
                'type' => 'leave_request_approved',
            ]);
            
            return redirect()->route('leave.review')->with('success', 'Leave request approved and leave balance updated.');
        } else {
            return redirect()->route('leave.review')->with('error', 'Not enough leave balance for this request.');
        }
    }

    public function rejectLeaveRequest(LeaveRequest $leaveRequest)
    {
        if (!Auth::user()->isHRManager()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to manage leave requests.');
        }

        $leaveRequest->status = 'rejected';
        $leaveRequest->save();

        // Create notification for employee
        $user = $leaveRequest->user;
        $startDate = \Carbon\Carbon::parse($leaveRequest->start_date);
        $endDate = \Carbon\Carbon::parse($leaveRequest->end_date);
        
        Notification::create([
            'user_id' => $user->id,
            'message' => 'Your leave request from ' . $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y') . ' has been rejected.',
            'type' => 'leave_request_rejected',
        ]);

        return redirect()->route('leave.review')->with('success', 'Leave request rejected.');
    }
}
