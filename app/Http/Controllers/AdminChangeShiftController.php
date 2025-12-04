<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeShiftRequest;
use App\Models\User;

class AdminChangeShiftController extends Controller
{
    public function index(ChangeShiftRequest $changeShiftRequest = null)
    {
        if ($changeShiftRequest) {
            $changeShiftRequests = collect([$changeShiftRequest]); // Show only the specific change shift request
        } else {
            $changeShiftRequests = ChangeShiftRequest::with(['user', 'currentShift', 'requestedShift'])
                                                ->where('status', 'pending')
                                                ->latest()
                                                ->get();
        }
        return view('admin.attendance.change_shift.index', compact('changeShiftRequests'));
    }

    public function approve($id)
    {
        $changeShiftRequest = ChangeShiftRequest::findOrFail($id);
        $changeShiftRequest->status = 'approved';
        $changeShiftRequest->save();

        $user = $changeShiftRequest->user;
        $user->shift_id = $changeShiftRequest->requested_shift_id;
        $user->save();

        // Create notification for employee
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'message' => 'Your change shift request for ' . $changeShiftRequest->requestedShift->name . ' has been approved.',
            'type' => 'change_shift_request_approved',
        ]);

        return redirect()->back()->with('success', 'Change shift request approved and employee shift updated.');
    }

    public function reject($id)
    {
        $changeShiftRequest = ChangeShiftRequest::findOrFail($id);
        $changeShiftRequest->status = 'rejected';
        $changeShiftRequest->save();

        // Create notification for employee
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'message' => 'Your change shift request for ' . $changeShiftRequest->requestedShift->name . ' has been rejected.',
            'type' => 'change_shift_request_rejected',
        ]);

        return redirect()->back()->with('error', 'Change shift request rejected.');
    }
}
