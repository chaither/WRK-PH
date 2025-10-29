<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminChangeRestdayController extends Controller
{
    public function index()
    {
        // Logic to fetch all pending change restday requests
        $changeRestdayRequests = []; // Placeholder
        return view('admin.attendance.change_restday.index', compact('changeRestdayRequests'));
    }

    public function approve($id)
    {
        // Logic to approve the change restday request
        return redirect()->back()->with('success', 'Change restday request approved.');
    }

    public function reject($id)
    {
        // Logic to reject the change restday request
        return redirect()->back()->with('error', 'Change restday request rejected.');
    }
}
