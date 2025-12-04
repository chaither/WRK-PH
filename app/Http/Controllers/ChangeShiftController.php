<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ChangeShiftRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeShiftController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentShift = $user->shift ? $user->shift->name . ' (' . \Carbon\Carbon::parse($user->shift->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($user->shift->end_time)->format('h:i A') . ')' : 'N/A';
        $shifts = Shift::all();
        $changeShiftRequests = $user->changeShiftRequests()->latest()->get();
        return view('attendance.change_shift.index', compact('currentShift', 'shifts', 'changeShiftRequests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'requested_shift' => 'required|exists:shifts,id',
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        $changeShiftRequest = ChangeShiftRequest::create([
            'user_id' => $user->id,
            'current_shift_id' => $user->shift_id,
            'requested_shift_id' => $request->requested_shift,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Create notification for admin/HR about new change shift request
        $adminUsers = \App\Models\User::whereIn('role', ['admin', 'hr'])->get();
        foreach ($adminUsers as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'message' => "{$user->first_name} {$user->last_name} submitted a change shift request for " . $changeShiftRequest->requestedShift->name . ".",
                'type' => 'change_shift_request_submitted',
            ]);
        }

        return redirect()->back()->with('success', 'Change shift request submitted successfully and is awaiting approval!');
    }

    public function showEmployeeShiftHistory(Request $request, \App\Models\User $employee)
    {
        // Ensure only admin/hr can view other employee's shift change history
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

        $query = ChangeShiftRequest::where('user_id', $employee->id)->with(['currentShift', 'requestedShift']);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        return view('shift.employee_shift_history', compact('employee', 'records', 'startDate', 'endDate', 'isFiltered'));
    }
}
