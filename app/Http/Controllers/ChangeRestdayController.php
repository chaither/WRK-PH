<?php

namespace App\Http\Controllers;

use App\Models\ChangeRestdayRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeRestdayController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentRestdays = $user->rest_days ? implode(', ', $user->rest_days) : 'N/A';
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $changeRestdayRequests = $user->changeRestdayRequests()->latest()->get();
        return view('attendance.change_restday.index', compact('currentRestdays', 'daysOfWeek', 'changeRestdayRequests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'requested_restdays' => 'required|array',
            'requested_restdays.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        ChangeRestdayRequest::create([
            'user_id' => $user->id,
            'current_restdays' => $user->rest_days,
            'requested_restdays' => $request->requested_restdays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Change restday request submitted successfully and is awaiting approval!');
    }

    public function showEmployeeRestdayHistory(Request $request, \App\Models\User $employee)
    {
        // Ensure only admin/hr can view other employee's rest day change history
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

        $query = ChangeRestdayRequest::where('user_id', $employee->id);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        return view('restday.employee_restday_history', compact('employee', 'records', 'startDate', 'endDate', 'isFiltered'));
    }
}
