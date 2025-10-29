<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminNoBioRequestController extends Controller
{
    public function index()
    {
        // Logic to fetch all pending No Bio Requests
        $noBioRequests = []; // Placeholder
        return view('admin.attendance.no_bio_request.index', compact('noBioRequests'));
    }

    public function approve($id)
    {
        // Logic to approve the No Bio Request
        return redirect()->back()->with('success', 'No Bio Request approved.');
    }

    public function reject($id)
    {
        // Logic to reject the No Bio Request
        return redirect()->back()->with('error', 'No Bio Request rejected.');
    }
}
