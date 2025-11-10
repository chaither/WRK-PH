<?php

namespace App\Http\Controllers;

use App\Models\ChangeRestdayRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeRestdayController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentRestdays = $user->rest_days ? implode(', ', $user->rest_days) : 'N/A';
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        return view('attendance.change_restday.index', compact('currentRestdays', 'daysOfWeek'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'requested_restdays' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        ChangeRestdayRequest::create([
            'user_id' => $user->id,
            'current_restdays' => $user->rest_days,
            'requested_restdays' => [$request->requested_restdays], // Store as an array for consistency
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Change restday request submitted successfully and is awaiting approval!');
    }
}
