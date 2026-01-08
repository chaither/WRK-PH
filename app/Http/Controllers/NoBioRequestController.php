<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoBioRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NoBioRequestController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to continue.');
            }
            
            $noBioRequests = $user->noBioRequests()->latest()->get();
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
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login to continue.');
            }
            
            if (!$user->relationLoaded('shift')) {
                $user->load('shift');
            }
            
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
                $rules['requested_time_out'] = 'nullable';
            } elseif ($request->type === 'morning_out') {
                $rules['requested_time_out'] = 'required|date_format:H:i';
                $rules['requested_time_in'] = 'nullable';
            } elseif ($request->type === 'afternoon_in') {
                $rules['requested_time_in'] = 'required|date_format:H:i';
                $rules['requested_time_out'] = 'nullable';
            } elseif ($request->type === 'afternoon_out') {
                $rules['requested_time_out'] = 'required|date_format:H:i';
                $rules['requested_time_in'] = 'nullable';
            } elseif (in_array($request->type, ['all_morning', 'all_afternoon', 'whole_day'])) {
                // For these types, times are derived from shift, so they are not strictly required from the user
                $rules['requested_time_in'] = 'nullable|date_format:H:i';
                $rules['requested_time_out'] = 'nullable|date_format:H:i';
            }

            // Validate request data
            Log::info('Validating no bio request', [
                'user_id' => Auth::id(),
                'type' => $request->type,
                'has_time_in' => !empty($request->requested_time_in),
                'has_time_out' => !empty($request->requested_time_out)
            ]);
            
            $validated = $request->validate($rules);
            
            // Custom validation for time ranges (after database format validation passes)
            if ($request->type === 'morning_in' && $shiftStartTime && $request->requested_time_in) {
                try {
                    $requestedTime = Carbon::parse($request->requested_time_in)->format('H:i');
                    if ($requestedTime < $shiftStartTime) {
                        return redirect()->back()->withErrors(['requested_time_in' => 'The requested time in must be after or equal to your shift start time (' . $shiftStartTime . ').'])->withInput();
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing requested_time_in for morning_in', ['time' => $request->requested_time_in]);
                }
            }
            
            if ($request->type === 'afternoon_in' && $request->requested_time_in) {
                try {
                    $requestedTime = Carbon::parse($request->requested_time_in)->format('H:i');
                    if ($requestedTime < '13:00') {
                        return redirect()->back()->withErrors(['requested_time_in' => 'The requested time in must be after or equal to 1:00 PM (13:00).'])->withInput();
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing requested_time_in for afternoon_in', ['time' => $request->requested_time_in]);
                }
            }
            
            if ($request->type === 'morning_out' && $request->requested_time_out) {
                try {
                    $requestedTime = Carbon::parse($request->requested_time_out)->format('H:i');
                    if ($requestedTime > '12:00') {
                        return redirect()->back()->withErrors(['requested_time_out' => 'The requested time out must be before or equal to 12:00 PM.'])->withInput();
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing requested_time_out for morning_out', ['time' => $request->requested_time_out]);
                }
            }
            
            if ($request->type === 'afternoon_out' && $shiftEndTime && $request->requested_time_out) {
                try {
                    $requestedTime = Carbon::parse($request->requested_time_out)->format('H:i');
                    if ($requestedTime > $shiftEndTime) {
                        return redirect()->back()->withErrors(['requested_time_out' => 'The requested time out must be before or equal to your shift end time (' . $shiftEndTime . ').'])->withInput();
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing requested_time_out for afternoon_out', ['time' => $request->requested_time_out]);
                }
            }

            // Use database transaction to ensure data integrity
            DB::beginTransaction();
            try {
                // Format date to ensure consistency
                $dateFormatted = Carbon::parse($request->date)->format('Y-m-d');
                
                Log::info('Creating no bio request', [
                    'user_id' => Auth::id(),
                    'date' => $dateFormatted,
                    'type' => $request->type
                ]);

                $noBioRequest = NoBioRequest::create([
                    'user_id' => Auth::id(),
                    'date' => $dateFormatted,
                    'type' => $request->type,
                    'reason' => $request->reason ?? '',
                    'requested_time_in' => $request->requested_time_in,
                    'requested_time_out' => $request->requested_time_out,
                    'status' => 'pending',
                ]);

                if (!$noBioRequest || !$noBioRequest->id) {
                    throw new \Exception('Failed to create no bio request - no ID returned');
                }

                Log::info('No bio request created successfully', ['id' => $noBioRequest->id]);

                // Create notification for admin/HR about new no bio request
                $adminUsers = \App\Models\User::whereIn('role', ['admin', 'hr'])->get();
                $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name ?? 'Employee';
                
                $notificationCount = 0;
                foreach ($adminUsers as $admin) {
                    try {
                        \App\Models\Notification::create([
                            'user_id' => $admin->id,
                            'message' => "{$userName} submitted a no bio request for " . Carbon::parse($dateFormatted)->format('M d, Y') . ". Type: " . str_replace('_', ' ', $request->type) . ".",
                            'type' => 'no_bio_request_submitted',
                        ]);
                        $notificationCount++;
                    } catch (\Exception $e) {
                        Log::warning('Failed to create notification for admin', [
                            'admin_id' => $admin->id,
                            'error' => $e->getMessage()
                        ]);
                        // Don't fail the whole request if notification fails
                    }
                }

                DB::commit();
                
                Log::info('No bio request submitted successfully', [
                    'no_bio_request_id' => $noBioRequest->id,
                    'notifications_created' => $notificationCount
                ]);

                return redirect()->back()->with('success', 'No Bio Request submitted successfully!');
            } catch (\Exception $dbException) {
                DB::rollBack();
                Log::error('Database error creating no bio request: ' . $dbException->getMessage(), [
                    'user_id' => Auth::id(),
                    'request_data' => $request->except(['_token']),
                    'trace' => $dbException->getTraceAsString()
                ]);
                throw $dbException; // Re-throw to be caught by outer catch block
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing no bio request: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token', 'password']),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to submit no bio request. Please check your input and try again.')->withInput();
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
