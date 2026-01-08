<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoBioRequest;
use App\Models\DTRRecord;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminNoBioRequestController extends Controller
{
    public function index(NoBioRequest $noBioRequest = null)
    {
        try {
            if ($noBioRequest) {
                $noBioRequests = collect([$noBioRequest]); // Show only the specific no bio request
            } else {
                $noBioRequests = NoBioRequest::with('user')->where('status', 'pending')->get();
            }
            return view('admin.attendance.no_bio_request.index', compact('noBioRequests'));
        } catch (\Exception $e) {
            Log::error('Error loading no bio requests: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard')->with('error', 'Failed to load no bio requests. Please try again.');
        }
    }

    public function approve($id)
    {
        try {
            $noBioRequest = NoBioRequest::findOrFail($id);
            $noBioRequest->status = 'approved';
            $noBioRequest->save();

            // Find or create DTR record
            $dtrRecord = DTRRecord::firstOrCreate(
                ['user_id' => $noBioRequest->user_id, 'date' => $noBioRequest->date],
                ['status' => 'approved'] // Set a default status if new record is created
            );

            // Eager load the user's shift if it hasn't been loaded yet
            $noBioRequest->load('user.shift');
            $userShift = $noBioRequest->user->shift;

            // Define default lunch break times. These could be configured per shift in the future.
            $defaultMorningOutTime = '12:00'; // Example lunch start
            $defaultAfternoonInTime = '13:00'; // Example lunch end

            $requestDate = Carbon::parse($noBioRequest->date);

            if ($noBioRequest->type === 'morning_in' && $noBioRequest->requested_time_in) {
                $dtrRecord->time_in = $requestDate->copy()->setTimeFromTimeString($noBioRequest->requested_time_in);
            } elseif ($noBioRequest->type === 'morning_out' && $noBioRequest->requested_time_out) {
                $dtrRecord->time_out = $requestDate->copy()->setTimeFromTimeString($noBioRequest->requested_time_out);
            } elseif ($noBioRequest->type === 'afternoon_in' && $noBioRequest->requested_time_in) {
                $dtrRecord->time_in_2 = $requestDate->copy()->setTimeFromTimeString($noBioRequest->requested_time_in);
            } elseif ($noBioRequest->type === 'afternoon_out' && $noBioRequest->requested_time_out) {
                $dtrRecord->time_out_2 = $requestDate->copy()->setTimeFromTimeString($noBioRequest->requested_time_out);
            } elseif ($noBioRequest->type === 'all_morning' && $userShift && $userShift->start_time) {
                $dtrRecord->time_in = $requestDate->copy()->setTimeFromTimeString($userShift->start_time);
                $dtrRecord->time_out = $requestDate->copy()->setTimeFromTimeString($defaultMorningOutTime);
            } elseif ($noBioRequest->type === 'all_afternoon' && $userShift && $userShift->end_time) {
                $dtrRecord->time_in_2 = $requestDate->copy()->setTimeFromTimeString($defaultAfternoonInTime);
                $dtrRecord->time_out_2 = $requestDate->copy()->setTimeFromTimeString($userShift->end_time);
            } elseif ($noBioRequest->type === 'whole_day' && $userShift && $userShift->start_time && $userShift->end_time) {
                $dtrRecord->time_in = $requestDate->copy()->setTimeFromTimeString($userShift->start_time);
                $dtrRecord->time_out = $requestDate->copy()->setTimeFromTimeString($defaultMorningOutTime);
                $dtrRecord->time_in_2 = $requestDate->copy()->setTimeFromTimeString($defaultAfternoonInTime);
                $dtrRecord->time_out_2 = $requestDate->copy()->setTimeFromTimeString($userShift->end_time);
            }

            // Set DTR record status to approved (if not already set by firstOrCreate)
            $dtrRecord->status = 'approved';
            $dtrRecord->recalculateAllHours();
            $dtrRecord->save();

            // Create notification for employee
            try {
                \App\Models\Notification::create([
                    'user_id' => $noBioRequest->user_id,
                    'message' => 'Your no bio request for ' . $requestDate->format('M d, Y') . ' (Type: ' . str_replace('_', ' ', $noBioRequest->type) . ') has been approved.',
                    'type' => 'no_bio_request_approved',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification for approved no bio request', [
                    'no_bio_request_id' => $noBioRequest->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->with('success', 'No Bio Request approved and DTR updated.');
        } catch (\Exception $e) {
            Log::error('Error approving no bio request: ' . $e->getMessage(), [
                'no_bio_request_id' => $id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to approve no bio request: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        try {
            $noBioRequest = NoBioRequest::findOrFail($id);
            $noBioRequest->status = 'rejected';
            $noBioRequest->save();

            // Create notification for employee
            try {
                \App\Models\Notification::create([
                    'user_id' => $noBioRequest->user_id,
                    'message' => 'Your no bio request for ' . Carbon::parse($noBioRequest->date)->format('M d, Y') . ' (Type: ' . str_replace('_', ' ', $noBioRequest->type) . ') has been rejected.',
                    'type' => 'no_bio_request_rejected',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification for rejected no bio request', [
                    'no_bio_request_id' => $noBioRequest->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'No Bio Request rejected.');
        } catch (\Exception $e) {
            Log::error('Error rejecting no bio request: ' . $e->getMessage(), [
                'no_bio_request_id' => $id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to reject no bio request: ' . $e->getMessage());
        }
    }
}
