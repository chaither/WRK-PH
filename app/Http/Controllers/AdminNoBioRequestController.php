<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoBioRequest;
use App\Models\DTRRecord;
use Carbon\Carbon;

class AdminNoBioRequestController extends Controller
{
    public function index()
    {
        $noBioRequests = NoBioRequest::with('user')->where('status', 'pending')->get();
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

        $timeToUpdate = Carbon::parse($noBioRequest->date)->setTime($noBioRequest->created_at->hour, $noBioRequest->created_at->minute, $noBioRequest->created_at->second);

        if ($noBioRequest->type === 'time_in') {
            $dtrRecord->time_in = $timeToUpdate;
        } elseif ($noBioRequest->type === 'time_out') {
            $dtrRecord->time_out = $timeToUpdate;
        } elseif ($noBioRequest->type === 'both') {
            // For 'both', we need to determine if it's for time_in or time_out based on the existing record or a reasonable assumption
            // For simplicity, let's assume if time_in is null, we set time_in, otherwise time_out. This might need refinement.
            if (is_null($dtrRecord->time_in)) {
                $dtrRecord->time_in = $timeToUpdate;
            } else {
                $dtrRecord->time_out = $timeToUpdate;
            }
        }

        $dtrRecord->status = 'approved'; // Set DTR record status to approved
        $dtrRecord->recalculateAllHours();
        $dtrRecord->save();

        return redirect()->back()->with('success', 'No Bio Request approved and DTR updated.');
    }

    public function reject($id)
    {
        $noBioRequest = NoBioRequest::findOrFail($id);
        $noBioRequest->status = 'rejected';
        $noBioRequest->save();

        return redirect()->back()->with('error', 'No Bio Request rejected.');
    }
}
