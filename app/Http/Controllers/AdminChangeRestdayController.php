<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeRestdayRequest;
use App\Models\User;

class AdminChangeRestdayController extends Controller
{
    public function index()
    {
        $changeRestdayRequests = ChangeRestdayRequest::with(['user'])->where('status', 'pending')->latest()->get();
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

        return redirect()->back()->with('success', 'Change restday request approved and employee rest days updated.');
    }

    public function reject($id)
    {
        $changeRestdayRequest = ChangeRestdayRequest::findOrFail($id);
        $changeRestdayRequest->status = 'rejected';
        $changeRestdayRequest->save();

        return redirect()->back()->with('error', 'Change restday request rejected.');
    }
}
