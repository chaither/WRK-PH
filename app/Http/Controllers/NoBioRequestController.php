<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoBioRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NoBioRequestController extends Controller
{
    public function index()
    {
        try {
            $noBioRequests = Auth::user()->noBioRequests()->latest()->get();
            return view('attendance.no_bio_request.index', compact('noBioRequests'));
        } catch (\Exception $e) {
            Log::error('Error loading no bio requests: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard')->with('error', 'Failed to load no bio requests. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            // Load the user's shift to get their standard end time
            $user = Auth::user();
            $user->load('shift');
            $shiftEndTime = null;
            $shiftStartTime = null;
            
            if ($user->shift && $user->shift->end_time) {
                try {
                    $shiftEndTime = Carbon::parse($user->shift->end_time)->format('H:i');
                } catch (\Exception $e) {
                    Log::warning('Invalid shift end_time format', ['user_id' => $user->id, 'end_time' => $user->shift->end_time]);
                }
            }
            
            if ($user->shift && $user->shift->start_time) {
                try {
                    $shiftStartTime = Carbon::parse($user->shift->start_time)->format('H:i');
                } catch (\Exception $e) {
                    Log::warning('Invalid shift start_time format', ['user_id' => $user->id, 'start_time' => $user->shift->start_time]);
                }
            }

        $rules = [
            'date' => 'required|date',
            'type' => 'required|in:morning_in,morning_out,afternoon_in,afternoon_out,all_morning,all_afternoon,whole_day',
            'reason' => 'required|string|max:500',
            'requested_time_in' => 'nullable|date_format:H:i',
            'requested_time_out' => 'nullable|date_format:H:i',
        ];

        if ($request->type === 'morning_in') {
            $rules['requested_time_in'] = 'required|date_format:H:i';
            if ($shiftStartTime) {
                $rules['requested_time_in'] .= '|after_or_equal:' . $shiftStartTime;
            }
            $rules['requested_time_out'] = 'nullable';
        } elseif ($request->type === 'morning_out') {
            $rules['requested_time_out'] = 'required|date_format:H:i|before_or_equal:12:00';
            $rules['requested_time_in'] = 'nullable';
        } elseif ($request->type === 'afternoon_in') {
            $rules['requested_time_in'] = 'required|date_format:H:i|after_or_equal:13:00'; // Enforce after 1 PM for afternoon_in
            $rules['requested_time_out'] = 'nullable';
        } elseif ($request->type === 'afternoon_out') {
            $rules['requested_time_out'] = 'required|date_format:H:i';
            if ($shiftEndTime) {
                $rules['requested_time_out'] .= '|before_or_equal:' . $shiftEndTime;
            }
            $rules['requested_time_in'] = 'nullable';
        } elseif (in_array($request->type, ['all_morning', 'all_afternoon', 'whole_day'])) {
            // For these types, times are derived from shift, so they are not strictly required from the user
            // but should be allowed if provided (hence nullable above, no specific required rule here)
            $rules['requested_time_in'] = 'nullable|date_format:H:i';
            $rules['requested_time_out'] = 'nullable|date_format:H:i';
        }

        // New rules for 'all_morning', 'all_afternoon', and 'whole_day'
        if ($request->type === 'all_morning') {
            $rules['requested_time_in'] = 'required|date_format:H:i';
            if ($shiftStartTime) {
                $rules['requested_time_in'] .= '|after_or_equal:' . $shiftStartTime;
            }
            $rules['requested_time_out'] = 'required|date_format:H:i|before_or_equal:12:00';
        } elseif ($request->type === 'all_afternoon') {
            $rules['requested_time_in'] = 'required|date_format:H:i|after_or_equal:13:00';
            $rules['requested_time_out'] = 'required|date_format:H:i';
            if ($shiftEndTime) {
                $rules['requested_time_out'] .= '|before_or_equal:' . $shiftEndTime;
            }
        } elseif ($request->type === 'whole_day') {
            $rules['requested_time_in'] = 'required|date_format:H:i';
            if ($shiftStartTime) {
                $rules['requested_time_in'] .= '|after_or_equal:' . $shiftStartTime;
            }
            $rules['requested_time_out'] = 'required|date_format:H:i';
            if ($shiftEndTime) {
                $rules['requested_time_out'] .= '|before_or_equal:' . $shiftEndTime;
            }
        }

        $request->validate($rules);

        NoBioRequest::create([
            'user_id' => Auth::id(),
            'date' => $request->date,
            'type' => $request->type,
            'reason' => $request->reason,
            'requested_time_in' => $request->requested_time_in,
            'requested_time_out' => $request->requested_time_out,
            'status' => 'pending',
        ]);

            // Create notification for admin/HR about new no bio request
            $adminUsers = \App\Models\User::whereIn('role', ['admin', 'hr'])->get();
            $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name ?? 'Employee';
            
            foreach ($adminUsers as $admin) {
                try {
                    \App\Models\Notification::create([
                        'user_id' => $admin->id,
                        'message' => "{$userName} submitted a no bio request for " . \Carbon\Carbon::parse($request->date)->format('M d, Y') . ". Type: " . str_replace('_', ' ', $request->type) . ".",
                        'type' => 'no_bio_request_submitted',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create notification', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return redirect()->back()->with('success', 'No Bio Request submitted successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing no bio request: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to submit no bio request: ' . $e->getMessage())->withInput();
        }
    }

    public function showEmployeeNoBioHistory(Request $request, \App\Models\User $employee)
    {
        try {
            // Ensure only admin/hr can view other employee's no-bio history
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
                try {
                    $startDate = \Carbon\Carbon::parse($specificDate);
                    $endDate = \Carbon\Carbon::parse($specificDate);
                    $isFiltered = true;
                } catch (\Exception $e) {
                    Log::warning('Invalid specific_date format', ['date' => $specificDate]);
                }
            } elseif ($requestStartDate && $requestEndDate) {
                try {
                    $startDate = \Carbon\Carbon::parse($requestStartDate);
                    $endDate = \Carbon\Carbon::parse($requestEndDate);
                    $isFiltered = true;
                } catch (\Exception $e) {
                    Log::warning('Invalid date range format', ['start' => $requestStartDate, 'end' => $requestEndDate]);
                }
            }
            
            if (!$startDate || !$endDate) {
                $startDate = \Carbon\Carbon::today()->startOfMonth();
                $endDate = \Carbon\Carbon::today()->endOfMonth();
            }

            $query = NoBioRequest::where('user_id', $employee->id);

            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }

            $records = $query->orderBy('date', 'desc')->get();

            return view('nobio.employee_nobio_history', compact('employee', 'records', 'startDate', 'endDate', 'isFiltered'));
        } catch (\Exception $e) {
            Log::error('Error showing employee no bio history: ' . $e->getMessage(), [
                'employee_id' => $employee->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to load no bio history: ' . $e->getMessage());
        }
    }
}
