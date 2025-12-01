<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use App\Models\DTRRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Added Log facade

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
        $overtimeHours = $end->diffInMinutes($start, true) / 60; // Added true for absolute difference
        
        // Set overtime time in and time out
        $dtrRecord->overtime_time_in = $overtimeRequest->start_time;
        $dtrRecord->overtime_time_out = $overtimeRequest->end_time;

        // Log the calculated overtime hours for debugging
        Log::info('AdminOvertimeRequestController: Calculated Overtime Hours for request ' . $id . ': ' . $overtimeHours);

        $dtrRecord->overtime_hours += $overtimeHours;
        Log::info('AdminOvertimeRequestController: DTRRecord overtime_hours before recalculateAllHours: ' . $dtrRecord->overtime_hours);
        $dtrRecord->recalculateAllHours(); // Recalculate to ensure all related fields are updated
        Log::info('AdminOvertimeRequestController: DTRRecord overtime_hours after recalculateAllHours and before save: ' . $dtrRecord->overtime_hours);
        $dtrRecord->save();
        Log::info('AdminOvertimeRequestController: DTRRecord overtime_hours after save: ' . $dtrRecord->overtime_hours);

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


