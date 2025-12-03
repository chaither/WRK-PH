<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        $overtimeRequests = Auth::user()->overtimeRequests()->latest()->get();
        return view('attendance.overtime_request.index', compact('overtimeRequests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:500',
        ]);

        OvertimeRequest::create([
            'user_id' => Auth::id(),
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Overtime Request submitted successfully!');
    }

    public function showEmployeeOvertimeHistory(Request $request, \App\Models\User $employee)
    {
        // Ensure only admin/hr can view other employee's overtime history
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $startDate = null;
        $endDate = null;
        $isFiltered = false;

        $period = $request->input('period');
        $specificDate = $request->input('specific_date');
        $requestStartDate = $request->input('start_date');
        $requestEndDate = $request->input('end_date');

        if ($period) {
            $currentMonth = \Carbon\Carbon::today()->month;
            $currentYear = \Carbon\Carbon::today()->year;

            if ($period === 'first_half') {
                $startDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1);
                $endDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 15);
            } elseif ($period === 'second_half') {
                $startDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 16);
                $endDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth)->endOfMonth();
            } elseif ($period === 'whole_month') {
                $startDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1);
                $endDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth)->endOfMonth();
            }
            $isFiltered = true;
        } elseif ($specificDate) {
            $startDate = \Carbon\Carbon::parse($specificDate);
            $endDate = \Carbon\Carbon::parse($specificDate);
            $isFiltered = true;
        } elseif ($requestStartDate && $requestEndDate) {
            $startDate = \Carbon\Carbon::parse($requestStartDate);
            $endDate = \Carbon\Carbon::parse($requestEndDate);
            $isFiltered = true;
        } else {
            $startDate = \Carbon\Carbon::today()->startOfMonth();
            $endDate = \Carbon\Carbon::today()->endOfMonth();
        }

        $query = OvertimeRequest::where('user_id', $employee->id);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $records = $query->orderBy('date', 'desc')->get();

        return view('overtime.employee_overtime_history', compact('employee', 'records', 'startDate', 'endDate', 'isFiltered'));
    }
}

