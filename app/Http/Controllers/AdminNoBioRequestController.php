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

            // Define lunch break times based on shift if available
            $morningOutTime = ($userShift && $userShift->lunch_break_start) ? Carbon::parse($userShift->lunch_break_start)->format('H:i') : '12:00';
            $afternoonInTime = ($userShift && $userShift->lunch_break_end) ? Carbon::parse($userShift->lunch_break_end)->format('H:i') : '13:00';

            $requestDate = Carbon::parse($noBioRequest->date);
            
            // Helper to set time with Night Shift adjustment
            // If it's a Night Shift (Start > End), and the time we are setting is < Start, 
            // it means it's the Next Day (e.g. End time 04:00 vs Start 19:00).
            $setTime = function($baseDate, $timeStr, $shiftStartStr = null, $shiftEndStr = null) {
                if (!$timeStr) return null;
                
                $dt = $baseDate->copy()->setTimeFromTimeString($timeStr);
                
                // Check Night Shift Condition
                if ($shiftStartStr && $shiftEndStr) {
                    $sStart = Carbon::parse($shiftStartStr);
                    $sEnd = Carbon::parse($shiftEndStr);
                    
                    if ($sEnd->lessThan($sStart)) { // Is Night Shift
                         $t = Carbon::parse($timeStr);
                         // If time is less than Start (e.g. 01:00 < 19:00), it's next day
                         // We use a loose comparison, assuming times 00:00 to ShiftEnd belong to next day
                         if ($t->lessThan($sStart)) {
                             $dt->addDay();
                         }
                    }
                }
                return $dt;
            };
            
            $sStartStr = ($userShift && $userShift->start_time) ? $userShift->start_time : null;
            $sEndStr = ($userShift && $userShift->end_time) ? $userShift->end_time : null;

            if ($noBioRequest->type === 'morning_in' && $noBioRequest->requested_time_in) {
                $dtrRecord->time_in = $setTime($requestDate, $noBioRequest->requested_time_in, $sStartStr, $sEndStr);
            } elseif ($noBioRequest->type === 'morning_out' && $noBioRequest->requested_time_out) {
                $dtrRecord->time_out = $setTime($requestDate, $noBioRequest->requested_time_out, $sStartStr, $sEndStr);
            } elseif ($noBioRequest->type === 'afternoon_in' && $noBioRequest->requested_time_in) {
                $dtrRecord->time_in_2 = $setTime($requestDate, $noBioRequest->requested_time_in, $sStartStr, $sEndStr);
            } elseif ($noBioRequest->type === 'afternoon_out' && $noBioRequest->requested_time_out) {
                $dtrRecord->time_out_2 = $setTime($requestDate, $noBioRequest->requested_time_out, $sStartStr, $sEndStr);
            } elseif ($noBioRequest->type === 'all_morning') {
                $tIn = $noBioRequest->requested_time_in ?: ($userShift ? $userShift->start_time : null);
                $tOut = $noBioRequest->requested_time_out ?: ($morningOutTime); // Default to lunch start
                
                $dtrRecord->time_in = $setTime($requestDate, $tIn, $sStartStr, $sEndStr);
                $dtrRecord->time_out = $setTime($requestDate, $tOut, $sStartStr, $sEndStr);
            } elseif ($noBioRequest->type === 'all_afternoon') {
                $tIn = $noBioRequest->requested_time_in ?: ($afternoonInTime); // Default to lunch end
                $tOut = $noBioRequest->requested_time_out ?: ($userShift ? $userShift->end_time : null);
                
                $dtrRecord->time_in_2 = $setTime($requestDate, $tIn, $sStartStr, $sEndStr);
                $dtrRecord->time_out_2 = $setTime($requestDate, $tOut, $sStartStr, $sEndStr);
            } elseif ($noBioRequest->type === 'whole_day') {
                $tIn = $noBioRequest->requested_time_in ?: ($userShift ? $userShift->start_time : null);
                $tOut = $noBioRequest->requested_time_out ?: ($userShift ? $userShift->end_time : null);
                
                $dtrRecord->time_in = $setTime($requestDate, $tIn, $sStartStr, $sEndStr);
                $dtrRecord->time_out = $setTime($requestDate, $morningOutTime, $sStartStr, $sEndStr);
                $dtrRecord->time_in_2 = $setTime($requestDate, $afternoonInTime, $sStartStr, $sEndStr);
                $dtrRecord->time_out_2 = $setTime($requestDate, $tOut, $sStartStr, $sEndStr);
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
