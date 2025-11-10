<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminOvertimeRequestController extends Controller
{
    public function index()
    {
        // Logic to fetch all pending Overtime Requests
        $overtimeRequests = []; // Placeholder
        return view('admin.attendance.overtime_request.index', compact('overtimeRequests'));
    }

    public function approve($id)
    {
        // Logic to approve the Overtime Request
        return redirect()->back()->with('success', 'Overtime Request approved.');
    }

    public function reject($id)
    {
        // Logic to reject the Overtime Request
        return redirect()->back()->with('error', 'Overtime Request rejected.');
    }
}

