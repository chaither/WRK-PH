<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ChangeShiftRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeShiftController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentShift = $user->shift ? $user->shift->name . ' (' . \Carbon\Carbon::parse($user->shift->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($user->shift->end_time)->format('h:i A') . ')' : 'N/A';
        $shifts = Shift::all();
        return view('attendance.change_shift.index', compact('currentShift', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'requested_shift' => 'required|exists:shifts,id',
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        ChangeShiftRequest::create([
            'user_id' => $user->id,
            'current_shift_id' => $user->shift_id,
            'requested_shift_id' => $request->requested_shift,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Change shift request submitted successfully and is awaiting approval!');
    }
}
