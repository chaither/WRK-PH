<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoBioRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NoBioRequestController extends Controller
{
    public function index()
    {
        $noBioRequests = Auth::user()->noBioRequests()->latest()->get();
        return view('attendance.no_bio_request.index', compact('noBioRequests'));
    }

    public function store(Request $request)
    {
        // Load the user's shift to get their standard end time
        $user = Auth::user();
        $user->load('shift');
        $shiftEndTime = $user->shift ? Carbon::parse($user->shift->end_time)->format('H:i') : null;
        $shiftStartTime = $user->shift ? Carbon::parse($user->shift->start_time)->format('H:i') : null;

        $rules = [
            'date' => 'required|date',
            'type' => 'required|in:morning_in,morning_out,afternoon_in,afternoon_out,all_morning,all_afternoon,whole_day',
            'reason' => 'required|string|max:500',
            'requested_time_in' => 'nullable|date_format:H:i',
            'requested_time_out' => 'nullable|date_format:H:i',
        ];

        if ($request->type === 'morning_in') {
            $rules['requested_time_in'] = 'required|date_format:H:i';
            if ($shiftStartTime) {
                $rules['requested_time_in'] .= '|after_or_equal:' . $shiftStartTime;
            }
            $rules['requested_time_out'] = 'nullable';
        } elseif ($request->type === 'morning_out') {
            $rules['requested_time_out'] = 'required|date_format:H:i|before_or_equal:12:00';
            $rules['requested_time_in'] = 'nullable';
        } elseif ($request->type === 'afternoon_in') {
            $rules['requested_time_in'] = 'required|date_format:H:i|after_or_equal:13:00'; // Enforce after 1 PM for afternoon_in
            $rules['requested_time_out'] = 'nullable';
        } elseif ($request->type === 'afternoon_out') {
            $rules['requested_time_out'] = 'required|date_format:H:i';
            if ($shiftEndTime) {
                $rules['requested_time_out'] .= '|before_or_equal:' . $shiftEndTime;
            }
            $rules['requested_time_in'] = 'nullable';
        } elseif (in_array($request->type, ['all_morning', 'all_afternoon', 'whole_day'])) {
            // For these types, times are derived from shift, so they are not strictly required from the user
            // but should be allowed if provided (hence nullable above, no specific required rule here)
            $rules['requested_time_in'] = 'nullable|date_format:H:i';
            $rules['requested_time_out'] = 'nullable|date_format:H:i';
        }

        // New rules for 'all_morning', 'all_afternoon', and 'whole_day'
        if ($request->type === 'all_morning') {
            $rules['requested_time_in'] = 'required|date_format:H:i';
            if ($shiftStartTime) {
                $rules['requested_time_in'] .= '|after_or_equal:' . $shiftStartTime;
            }
            $rules['requested_time_out'] = 'required|date_format:H:i|before_or_equal:12:00';
        } elseif ($request->type === 'all_afternoon') {
            $rules['requested_time_in'] = 'required|date_format:H:i|after_or_equal:13:00';
            $rules['requested_time_out'] = 'required|date_format:H:i';
            if ($shiftEndTime) {
                $rules['requested_time_out'] .= '|before_or_equal:' . $shiftEndTime;
            }
        } elseif ($request->type === 'whole_day') {
            $rules['requested_time_in'] = 'required|date_format:H:i';
            if ($shiftStartTime) {
                $rules['requested_time_in'] .= '|after_or_equal:' . $shiftStartTime;
            }
            $rules['requested_time_out'] = 'required|date_format:H:i';
            if ($shiftEndTime) {
                $rules['requested_time_out'] .= '|before_or_equal:' . $shiftEndTime;
            }
        }

        $request->validate($rules);

        NoBioRequest::create([
            'user_id' => Auth::id(),
            'date' => $request->date,
            'type' => $request->type,
            'reason' => $request->reason,
            'requested_time_in' => $request->requested_time_in,
            'requested_time_out' => $request->requested_time_out,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'No Bio Request submitted successfully!');
    }
}
