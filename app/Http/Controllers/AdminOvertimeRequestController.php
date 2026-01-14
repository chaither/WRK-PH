<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use App\Models\DTRRecord;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Added Log facade

class AdminOvertimeRequestController extends Controller
{
    public function index(OvertimeRequest $overtimeRequest = null)
    {
        if ($overtimeRequest) {
            $overtimeRequests = collect([$overtimeRequest]); // Show only the specific overtime request
        } else {
            $overtimeRequests = OvertimeRequest::with('user')->where('status', 'pending')->get();
        }
        
        return view('admin.attendance.overtime_request.index', compact('overtimeRequests'));
    }

    public function approve($id)
    {
        $overtimeRequest = OvertimeRequest::findOrFail($id);
        $overtimeRequest->status = 'approved';
        $overtimeRequest->save();

        $dtrRecord = DTRRecord::firstOrCreate(
            ['user_id' => $overtimeRequest->user_id, 'date' => $overtimeRequest->date]
        );

        // Set approved overtime window
        $dtrRecord->overtime_time_in = $overtimeRequest->start_time;
        $dtrRecord->overtime_time_out = $overtimeRequest->end_time;

        // Save the record. This triggers the 'saving' event in DTRRecord model,
        // which calls 'recalculateAllHours()'.
        // recalculateAllHours() will now use the newly set overtime_time_in/out
        // to strictly calculate the valid overtime hours based on actual time logs.
        $dtrRecord->save();
        
        Log::info('AdminOvertimeRequestController: Approved OT for User ' . $overtimeRequest->user_id . '. DTR recalculated. Overtime Hours: ' . $dtrRecord->overtime_hours);

        // Create notification for employee
        Notification::create([
            'user_id' => $overtimeRequest->user_id,
            'message' => 'Your overtime request for ' . Carbon::parse($overtimeRequest->date)->format('M d, Y') . ' has been approved.',
            'type' => 'overtime_request_approved',
        ]);

        return redirect()->back()->with('success', 'Overtime Request approved and DTR updated.');
    }

    public function reject($id)
    {
        $overtimeRequest = OvertimeRequest::findOrFail($id);
        $overtimeRequest->status = 'rejected';
        $overtimeRequest->save();

        // Create notification for employee
        Notification::create([
            'user_id' => $overtimeRequest->user_id,
            'message' => 'Your overtime request for ' . Carbon::parse($overtimeRequest->date)->format('M d, Y') . ' has been rejected.',
            'type' => 'overtime_request_rejected',
        ]);

        return redirect()->back()->with('error', 'Overtime Request rejected.');
    }
}


