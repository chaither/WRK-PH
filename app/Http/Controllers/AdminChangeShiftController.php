<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminChangeShiftController extends Controller
{
    public function index()
    {
        // Logic to fetch all pending change shift requests
        $changeShiftRequests = []; // Placeholder
        return view('admin.attendance.change_shift.index', compact('changeShiftRequests'));
    }

    public function approve($id)
    {
        // Logic to approve the change shift request
        return redirect()->back()->with('success', 'Change shift request approved.');
    }

    public function reject($id)
    {
        // Logic to reject the change shift request
        return redirect()->back()->with('error', 'Change shift request rejected.');
    }
}
