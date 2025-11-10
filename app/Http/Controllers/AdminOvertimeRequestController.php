<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use App\Models\DTRRecord;
use Carbon\Carbon;

class AdminOvertimeRequestController extends Controller
{
    public function index()
    {
        $overtimeRequests = OvertimeRequest::with('user')->where('status', 'pending')->get();
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

        $start = Carbon::parse($overtimeRequest->start_time);
        $end = Carbon::parse($overtimeRequest->end_time);
        $overtimeHours = $end->diffInMinutes($start) / 60;

        $dtrRecord->overtime_hours += $overtimeHours;
        $dtrRecord->recalculateAllHours(); // Recalculate to ensure all related fields are updated
        $dtrRecord->save();

        return redirect()->back()->with('success', 'Overtime Request approved and DTR updated.');
    }

    public function reject($id)
    {
        $overtimeRequest = OvertimeRequest::findOrFail($id);
        $overtimeRequest->status = 'rejected';
        $overtimeRequest->save();

        return redirect()->back()->with('error', 'Overtime Request rejected.');
    }
}


