<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest; // Added this import

class LeaveController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $employees = User::where('role', 'employee')->orderBy('name')->get();
        return view('leave.index', compact('employees'));
    }

    public function updateLeaveBalance(Request $request, User $user)
    {
        if (!Auth::user()->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
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
        $user = Auth::user();
        if (!$user->isEmployee()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'formal_letter' => 'required|file|mimes:pdf,docx|max:2048', // Max 2MB
        ]);

        $filePath = $request->file('formal_letter')->store('leave_letters', 'public');

        LeaveRequest::create([
            'user_id' => $user->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $filePath,
            'status' => 'pending',
        ]);

        return redirect()->route('employee.leave.index')->with('success', 'Leave request submitted successfully.');
    }

    // HR/Admin leave request review methods
    public function reviewLeaveRequests()
    {
        if (!Auth::user()->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $leaveRequests = LeaveRequest::with('user')->orderByDesc('created_at')->get();
        return view('leave.review', compact('leaveRequests'));
    }

    public function approveLeaveRequest(LeaveRequest $leaveRequest)
    {
        if (!Auth::user()->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
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
            return redirect()->route('leave.review')->with('success', 'Leave request approved and leave balance updated.');
        } else {
            return redirect()->route('leave.review')->with('error', 'Not enough leave balance for this request.');
        }
    }

    public function rejectLeaveRequest(LeaveRequest $leaveRequest)
    {
        if (!Auth::user()->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $leaveRequest->status = 'rejected';
        $leaveRequest->save();

        return redirect()->route('leave.review')->with('success', 'Leave request rejected.');
    }
}
