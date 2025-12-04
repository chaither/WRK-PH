<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeRestdayRequest;
use App\Models\User;

class AdminChangeRestdayController extends Controller
{
    public function index(ChangeRestdayRequest $changeRestdayRequest = null)
    {
        if ($changeRestdayRequest) {
            $changeRestdayRequests = collect([$changeRestdayRequest]); // Show only the specific change restday request
        } else {
            $changeRestdayRequests = ChangeRestdayRequest::with(['user'])->where('status', 'pending')->latest()->get();
        }
        return view('admin.attendance.change_restday.index', compact('changeRestdayRequests'));
    }

    public function approve($id)
    {
        $changeRestdayRequest = ChangeRestdayRequest::findOrFail($id);
        $changeRestdayRequest->status = 'approved';
        $changeRestdayRequest->save();

        $user = $changeRestdayRequest->user;
        $user->rest_days = $changeRestdayRequest->requested_restdays;
        $user->save();

        // Create notification for employee
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'message' => 'Your change restday request for ' . implode(', ', $changeRestdayRequest->requested_restdays) . ' has been approved.',
            'type' => 'change_restday_request_approved',
        ]);

        return redirect()->back()->with('success', 'Change restday request approved and employee rest days updated.');
    }

    public function reject($id)
    {
        $changeRestdayRequest = ChangeRestdayRequest::findOrFail($id);
        $changeRestdayRequest->status = 'rejected';
        $changeRestdayRequest->save();

        // Create notification for employee
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'message' => 'Your change restday request for ' . implode(', ', $changeRestdayRequest->requested_restdays) . ' has been rejected.',
            'type' => 'change_restday_request_rejected',
        ]);

        return redirect()->back()->with('error', 'Change restday request rejected.');
    }
}
