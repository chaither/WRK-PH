<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeRestdayRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminChangeRestdayController extends Controller
{
    public function index(ChangeRestdayRequest $changeRestdayRequest = null)
    {
        try {
            if ($changeRestdayRequest) {
                $changeRestdayRequests = collect([$changeRestdayRequest]); // Show only the specific change restday request
            } else {
                $changeRestdayRequests = ChangeRestdayRequest::with(['user'])->where('status', 'pending')->latest()->get();
            }
            return view('admin.attendance.change_restday.index', compact('changeRestdayRequests'));
        } catch (\Exception $e) {
            Log::error('Error loading change restday requests: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard')->with('error', 'Failed to load change restday requests. Please try again.');
        }
    }

    public function approve($id)
    {
        try {
            $changeRestdayRequest = ChangeRestdayRequest::with('user')->findOrFail($id);
            $changeRestdayRequest->status = 'approved';
            $changeRestdayRequest->save();

            $user = $changeRestdayRequest->user;
            if (!$user) {
                throw new \Exception('User not found for change restday request');
            }

            $user->rest_days = $changeRestdayRequest->requested_restdays;
            $user->save();

            $restdaysList = is_array($changeRestdayRequest->requested_restdays) 
                ? implode(', ', $changeRestdayRequest->requested_restdays) 
                : $changeRestdayRequest->requested_restdays;

            // Create notification for employee
            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'message' => "Your change restday request for {$restdaysList} has been approved.",
                    'type' => 'change_restday_request_approved',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification for approved change restday request', [
                    'change_restday_request_id' => $changeRestdayRequest->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->with('success', 'Change restday request approved and employee rest days updated.');
        } catch (\Exception $e) {
            Log::error('Error approving change restday request: ' . $e->getMessage(), [
                'change_restday_request_id' => $id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to approve change restday request: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        try {
            $changeRestdayRequest = ChangeRestdayRequest::with('user')->findOrFail($id);
            $changeRestdayRequest->status = 'rejected';
            $changeRestdayRequest->save();

            $user = $changeRestdayRequest->user;
            if (!$user) {
                throw new \Exception('User not found for change restday request');
            }

            $restdaysList = is_array($changeRestdayRequest->requested_restdays) 
                ? implode(', ', $changeRestdayRequest->requested_restdays) 
                : $changeRestdayRequest->requested_restdays;

            // Create notification for employee
            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'message' => "Your change restday request for {$restdaysList} has been rejected.",
                    'type' => 'change_restday_request_rejected',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification for rejected change restday request', [
                    'change_restday_request_id' => $changeRestdayRequest->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'Change restday request rejected.');
        } catch (\Exception $e) {
            Log::error('Error rejecting change restday request: ' . $e->getMessage(), [
                'change_restday_request_id' => $id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to reject change restday request: ' . $e->getMessage());
        }
    }
}
