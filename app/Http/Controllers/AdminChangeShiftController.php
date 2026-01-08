<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeShiftRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminChangeShiftController extends Controller
{
    public function index(ChangeShiftRequest $changeShiftRequest = null)
    {
        try {
            if ($changeShiftRequest) {
                $changeShiftRequests = collect([$changeShiftRequest]); // Show only the specific change shift request
            } else {
                $changeShiftRequests = ChangeShiftRequest::with(['user', 'currentShift', 'requestedShift'])
                                                    ->where('status', 'pending')
                                                    ->latest()
                                                    ->get();
            }
            return view('admin.attendance.change_shift.index', compact('changeShiftRequests'));
        } catch (\Exception $e) {
            Log::error('Error loading change shift requests: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard')->with('error', 'Failed to load change shift requests. Please try again.');
        }
    }

    public function approve($id)
    {
        try {
            $changeShiftRequest = ChangeShiftRequest::with(['user', 'requestedShift'])->findOrFail($id);
            $changeShiftRequest->status = 'approved';
            $changeShiftRequest->save();

            $user = $changeShiftRequest->user;
            if (!$user) {
                throw new \Exception('User not found for change shift request');
            }

            $user->shift_id = $changeShiftRequest->requested_shift_id;
            $user->save();

            $requestedShiftName = $changeShiftRequest->requestedShift->name ?? 'the requested shift';

            // Create notification for employee
            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'message' => "Your change shift request for {$requestedShiftName} has been approved.",
                    'type' => 'change_shift_request_approved',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification for approved change shift request', [
                    'change_shift_request_id' => $changeShiftRequest->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->with('success', 'Change shift request approved and employee shift updated.');
        } catch (\Exception $e) {
            Log::error('Error approving change shift request: ' . $e->getMessage(), [
                'change_shift_request_id' => $id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to approve change shift request: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        try {
            $changeShiftRequest = ChangeShiftRequest::with(['user', 'requestedShift'])->findOrFail($id);
            $changeShiftRequest->status = 'rejected';
            $changeShiftRequest->save();

            $user = $changeShiftRequest->user;
            if (!$user) {
                throw new \Exception('User not found for change shift request');
            }

            $requestedShiftName = $changeShiftRequest->requestedShift->name ?? 'the requested shift';

            // Create notification for employee
            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'message' => "Your change shift request for {$requestedShiftName} has been rejected.",
                    'type' => 'change_shift_request_rejected',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification for rejected change shift request', [
                    'change_shift_request_id' => $changeShiftRequest->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'Change shift request rejected.');
        } catch (\Exception $e) {
            Log::error('Error rejecting change shift request: ' . $e->getMessage(), [
                'change_shift_request_id' => $id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to reject change shift request: ' . $e->getMessage());
        }
    }
}
