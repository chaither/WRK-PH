<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoBioRequest;
use App\Models\DTRRecord;
use Carbon\Carbon;

class AdminNoBioRequestController extends Controller
{
    public function index(NoBioRequest $noBioRequest = null)
    {
        if ($noBioRequest) {
            $noBioRequests = collect([$noBioRequest]); // Show only the specific no bio request
        } else {
            $noBioRequests = NoBioRequest::with('user')->where('status', 'pending')->get();
        }
        return view('admin.attendance.no_bio_request.index', compact('noBioRequests'));
    }

    public function approve($id)
    {
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

        if ($noBioRequest->type === 'morning_in') {
            $dtrRecord->time_in = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($noBioRequest->requested_time_in);
        } elseif ($noBioRequest->type === 'morning_out') {
            $dtrRecord->time_out = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($noBioRequest->requested_time_out);
        } elseif ($noBioRequest->type === 'afternoon_in') {
            $dtrRecord->time_in_2 = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($noBioRequest->requested_time_in);
        } elseif ($noBioRequest->type === 'afternoon_out') {
            $dtrRecord->time_out_2 = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($noBioRequest->requested_time_out);
        } elseif ($noBioRequest->type === 'all_morning' && $userShift) {
            $dtrRecord->time_in = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($userShift->start_time);
            $dtrRecord->time_out = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($defaultMorningOutTime);
        } elseif ($noBioRequest->type === 'all_afternoon' && $userShift) {
            $dtrRecord->time_in_2 = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($defaultAfternoonInTime);
            $dtrRecord->time_out_2 = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($userShift->end_time);
        } elseif ($noBioRequest->type === 'whole_day' && $userShift) {
            $dtrRecord->time_in = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($userShift->start_time);
            $dtrRecord->time_out = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($defaultMorningOutTime);
            $dtrRecord->time_in_2 = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($defaultAfternoonInTime);
            $dtrRecord->time_out_2 = Carbon::parse($noBioRequest->date)->setTimeFromTimeString($userShift->end_time);
        }

        // Set DTR record status to approved (if not already set by firstOrCreate)
        $dtrRecord->status = 'approved';
        $dtrRecord->recalculateAllHours();
        $dtrRecord->save();

        // Create notification for employee
        \App\Models\Notification::create([
            'user_id' => $noBioRequest->user_id,
            'message' => 'Your no bio request for ' . Carbon::parse($noBioRequest->date)->format('M d, Y') . ' (Type: ' . str_replace('_', ' ', $noBioRequest->type) . ') has been approved.',
            'type' => 'no_bio_request_approved',
        ]);

        return redirect()->back()->with('success', 'No Bio Request approved and DTR updated.');
    }

    public function reject($id)
    {
        $noBioRequest = NoBioRequest::findOrFail($id);
        $noBioRequest->status = 'rejected';
        $noBioRequest->save();

        // Create notification for employee
        \App\Models\Notification::create([
            'user_id' => $noBioRequest->user->id,
            'message' => 'Your no bio request for ' . Carbon::parse($noBioRequest->date)->format('M d, Y') . ' (Type: ' . str_replace('_', ' ', $noBioRequest->type) . ') has been rejected.',
            'type' => 'no_bio_request_rejected',
        ]);

        return redirect()->back()->with('error', 'No Bio Request rejected.');
    }
}
