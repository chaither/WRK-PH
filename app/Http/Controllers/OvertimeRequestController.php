<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        return view('attendance.overtime_request.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:500',
        ]);

        OvertimeRequest::create([
            'user_id' => Auth::id(),
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Overtime Request submitted successfully!');
    }
}
