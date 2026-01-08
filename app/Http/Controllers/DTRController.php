<?php

namespace App\Http\Controllers;

use App\Models\DTRRecord;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Helpers\TimeHelper;
use App\Models\LeaveRequest; // Add this import for LeaveRequest model

class DTRController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        // For employees: force face registration page first, then handle enable/disable
        if ($user->role === 'employee' && empty($user->face_embedding)) {
            // If employee hasn't registered face, redirect them to one-time face registration
            return redirect()->route('face.register')->with('info', 'Please register your face before accessing Daily Time Record.');
        }
        // If employee has registered face but face recognition is disabled, we just show a notice in the view
        $today = Carbon::today();

        $dtrRecord = DTRRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $monthlyRecords = DTRRecord::where('user_id', $user->id)
            ->whereMonth('date', $today->month)
            ->whereYear('date', $today->year)
            ->orderBy('date', 'desc')
            ->get();

        // Initialize $onLeave to false by default
        $onLeave = false;

        // Check if the employee is on an approved leave today
        $leaveRequest = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if ($leaveRequest) {
            $onLeave = true;
        }

        if ($onLeave) {
            return view('dtr.index', compact('dtrRecord', 'monthlyRecords', 'onLeave'))->with('error', 'You are on an approved leave today and cannot clock in or out.');
        }
        
        return view('dtr.index', compact('dtrRecord', 'monthlyRecords', 'onLeave'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();

        // Check if the employee is on an approved leave today before allowing clock-in
        $today = Carbon::today();
        $onLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if ($onLeave) {
            return back()->with('error', 'You are on an approved leave today and cannot clock in.');
        }

        // Check if face recognition is enabled for this employee
        if ($user->role === 'employee' && !$user->face_recognition_enabled) {
            return back()->with('error', 'Face recognition is not enabled for your account. Please contact HR or Admin to enable this feature.');
        }

        // Require face registration
        if ($user->role === 'employee' && empty($user->face_embedding)) {
            return back()->with('error', 'Face not registered. Please register your face before clocking in.')->with('show_face_register', true);
        }

        $probe = $request->input('face_descriptor');
        // Accept both JSON string or array for the face descriptor coming from the client
        if (is_string($probe)) {
            $decoded = json_decode($probe, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $probe = $decoded;
            }
        }
        if (!$probe || !is_array($probe)) {
            return back()->with('error', 'Face descriptor missing. Make sure your camera is enabled and try again.');
        }

        // Normalise probe: accept { samples: [...], average: [...] } or a flat descriptor array
        if (isset($probe['average']) && is_array($probe['average'])) {
            $probe = $probe['average'];
        } elseif (isset($probe['samples']) && is_array($probe['samples']) && count($probe['samples'])>0) {
            $samples = $probe['samples'];
            $count = count($samples);
            $len = count($samples[0]);
            $avg = array_fill(0, $len, 0.0);
            foreach ($samples as $s) {
                for ($i = 0; $i < $len; $i++) {
                    $avg[$i] += (float) ($s[$i] ?? 0);
                }
            }
            for ($i = 0; $i < $len; $i++) $avg[$i] = $avg[$i] / $count;
            $probe = $avg;
        }

        $stored = json_decode($user->face_embedding, true);
        $threshold = config('face.threshold', 0.5);
        $distance = \App\Services\FaceRecognitionService::minDistance($stored, $probe);
        Log::info('Face match distance for user ' . $user->id . ': ' . $distance);
        if (!\App\Services\FaceRecognitionService::matches($stored, $probe, $threshold)) {
            Log::warning("Face verification failed for user {$user->id}. Distance {$distance} > threshold {$threshold}");
            return back()->with('error', 'Face verification failed.');
        }
        $now = Carbon::now('Asia/Manila'); // Explicitly set timezone for clock-in
        $today = $now->toDateString();
        
        $existingRecord = DTRRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // Get expected work start time from user's schedule
        $workStartTime = Carbon::parse($user->work_start)->setTimezone('Asia/Manila');
        $lateMinutes = 0;
        $status = 'present';

        // Create Carbon instances with today's date but using only the time for comparison
        $clockInTimeOnly = Carbon::createFromTime($now->hour, $now->minute, $now->second, 'Asia/Manila');
        $workStartTimeOnly = Carbon::createFromTime($workStartTime->hour, $workStartTime->minute, $workStartTime->second, 'Asia/Manila');

        // Check if late
        if ($clockInTimeOnly->greaterThan($workStartTimeOnly)) {
            $lateMinutes = abs($clockInTimeOnly->diffInMinutes($workStartTimeOnly));
            $status = 'late';
        }

        $timeOfDay = TimeHelper::getTimeOfDay($now);

        // If no record exists for today, create the first clock-in
        if (!$existingRecord) {
            $recordData = [
                'user_id' => $user->id,
                'date' => $today,
                'status' => $status,
                'late_minutes' => $lateMinutes,
            ];

            if ($timeOfDay === 'Morning') {
                $recordData['time_in'] = $now;
                $message = 'Successfully clocked in for the morning.';
            } else { // Afternoon
                $recordData['time_in_2'] = $now;
                $message = 'Successfully clocked in for the afternoon.';
            }
            
            DTRRecord::create($recordData);
            return back()->with('success', $message);
        }

        // If a record exists, check if it's a second clock-in or a morning/afternoon switch
        if ($timeOfDay === 'Morning') {
            if (!$existingRecord->time_in) {
                $existingRecord->update([
                    'time_in' => $now,
                ]);
                return back()->with('success', 'Successfully clocked in for the morning.');
            } else if ($existingRecord->time_in && !$existingRecord->time_out && !$existingRecord->time_in_2) {
                // Allow re-clocking in for morning if they clocked out for lunch and haven't clocked in for afternoon
                return back()->with('error', 'You have already clocked in for the morning.');
            }
        } else { // Afternoon
            if (!$existingRecord->time_in_2) {
                $existingRecord->update([
                    'time_in_2' => $now,
                ]);
                return back()->with('success', 'Successfully clocked in for the afternoon.');
            } else if ($existingRecord->time_in_2 && !$existingRecord->time_out_2) {
                return back()->with('error', 'You have already clocked in for the afternoon.');
            }
        }

        return back()->with('error', 'You have already clocked in twice today.');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();

        // Check if the employee is on an approved leave today before allowing clock-out
        $today = Carbon::today();
        $onLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if ($onLeave) {
            return back()->with('error', 'You are on an approved leave today and cannot clock out.');
        }

        // Check if face recognition is enabled for this employee
        if ($user->role === 'employee' && !$user->face_recognition_enabled) {
            return back()->with('error', 'Face recognition is not enabled for your account. Please contact HR or Admin to enable this feature.');
        }

        // Require face registration
        if ($user->role === 'employee' && empty($user->face_embedding)) {
            return back()->with('error', 'Please register your face before clocking out.');
        }

        $probe = $request->input('face_descriptor');
        // Accept both JSON string or array for the face descriptor coming from the client
        if (is_string($probe)) {
            $decoded = json_decode($probe, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $probe = $decoded;
            }
        }
        if (!$probe || !is_array($probe)) {
            return back()->with('error', 'Face descriptor missing. Make sure your camera is enabled and try again.');
        }

        // Normalise probe: accept { samples: [...], average: [...] } or a flat descriptor array
        if (isset($probe['average']) && is_array($probe['average'])) {
            $probe = $probe['average'];
        } elseif (isset($probe['samples']) && is_array($probe['samples']) && count($probe['samples'])>0) {
            $samples = $probe['samples'];
            $count = count($samples);
            $len = count($samples[0]);
            $avg = array_fill(0, $len, 0.0);
            foreach ($samples as $s) {
                for ($i = 0; $i < $len; $i++) {
                    $avg[$i] += (float) ($s[$i] ?? 0);
                }
            }
            for ($i = 0; $i < $len; $i++) $avg[$i] = $avg[$i] / $count;
            $probe = $avg;
        }

        $stored = json_decode($user->face_embedding, true);
        $threshold = config('face.threshold', 0.5);
        $distance = \App\Services\FaceRecognitionService::minDistance($stored, $probe);
        Log::info('Face match distance for user ' . $user->id . ': ' . $distance);
        if (!\App\Services\FaceRecognitionService::matches($stored, $probe, $threshold)) {
            Log::warning("Face verification failed for user {$user->id}. Distance {$distance} > threshold {$threshold}");
            return back()->with('error', 'Face verification failed.');
        }
        $now = Carbon::now('Asia/Manila');
        $today = $now->toDateString();

        $record = DTRRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$record) {
            return back()->with('error', 'No clock-in record found for today.');
        }

        // Clock-out for the first time
        if ($record->time_in && !$record->time_out) {
            $record->update([
                'time_out' => $now,
            ]);
            // Recalculate all hours after first clock out
            $record->recalculateAllHours();
            return back()->with('success', 'Successfully clocked out for lunch.');
        }

        // Clock-out for the second time (after lunch)
        if ($record->time_in_2 && !$record->time_out_2) {
            $record->update([
                'time_out_2' => $now,
            ]);
            // Recalculate all hours after second clock out
            $record->recalculateAllHours();
            return back()->with('success', 'Successfully clocked out for the day.');
        }

        return back()->with('error', 'You have already clocked out twice today.');
    }

    public function adminView(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        // Get dates from request, default to today if not provided or empty
        $startDate = $request->filled('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::today();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::today();
        $selectedEmployeeId = $request->input('employee_id');
        $filterStatus = $request->input('status'); // Keep the status filter

        // Fetch all employees for the dropdown
        $allEmployees = User::where('role', 'employee')
                            ->orderBy('first_name')
                            ->orderBy('last_name')
                            ->get();

        $dtrRecordsQuery = DTRRecord::query();

        // Apply date range filter
        $dtrRecordsQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        // Apply employee filter
        if ($selectedEmployeeId) {
            $dtrRecordsQuery->where('user_id', $selectedEmployeeId);
        }

        $allDtrRecords = $dtrRecordsQuery->get();

        // Calculate counts based on the filtered records
        $presentCount = $allDtrRecords->where('status', 'present')->count();
        $lateCount = $allDtrRecords->where('status', 'late')->count();
        $halfDayCount = $allDtrRecords->where('status', 'half_day')->count();

        $totalEmployeesInFilter = User::where('role', 'employee');
        if ($selectedEmployeeId) {
            $totalEmployeesInFilter->where('id', $selectedEmployeeId);
        }
        $totalEmployeesInFilter = $totalEmployeesInFilter->count();

        // For absent count, we need to consider all employees if no specific employee is selected
        // and then subtract those who have a DTR record within the filtered date range.
        // If a specific employee is selected, then only check for that employee.
        $absentCount = 0;
        if ($selectedEmployeeId) {
            $hasDtr = DTRRecord::where('user_id', $selectedEmployeeId)
                                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                                ->exists();
            if (!$hasDtr) {
                $absentCount = 1;
            }
        } else {
            $employeesWithDTR = $allDtrRecords->pluck('user_id')->unique()->count();
            $absentCount = $totalEmployeesInFilter - $employeesWithDTR;
        }
        
        // Prepare the main employee list for the table, applying status filter if present
        $employees = User::where('role', 'employee')
                        ->orderBy('first_name')
                        ->orderBy('last_name');

        if ($selectedEmployeeId) {
            $employees->where('id', $selectedEmployeeId);
        }
        
        // This is the collection of employees and their DTR records that will be displayed in the table
        $employeesToDisplay = $employees->with(['dtrRecords' => function ($query) use ($startDate, $endDate) {
                                $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                            }]);

        if ($filterStatus == 'present' || $filterStatus == 'late' || $filterStatus == 'half_day') {
            $employeesToDisplay->whereHas('dtrRecords', function ($query) use ($startDate, $endDate, $filterStatus) {
                $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', $filterStatus);
            });
        } elseif ($filterStatus == 'absent') {
            $employeesToDisplay->whereDoesntHave('dtrRecords', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            });
        }

        $employeesToDisplay = $employeesToDisplay->get();

        return view('dtr.admin', compact(
            'presentCount', 'lateCount', 'absentCount', 'halfDayCount', 
            'employeesToDisplay', 'startDate', 'endDate', 'filterStatus',
            'allEmployees', 'selectedEmployeeId'
        ));
    }

    public function employeesIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $search = $request->input('search');

        $employees = User::where('role', 'employee')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%' . $search . '%')
                          ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('dtr.employees_list', compact('employees', 'search'));
    }

    public function showEmployeeDTR(Request $request, User $employee)
    {
        // Ensure only admin/hr can view other employee's DTR
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;
        $selectedDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $query = DTRRecord::where('user_id', $employee->id);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } else {
            $query->whereDate('date', $selectedDate);
        }

        $records = $query->orderBy('date', 'desc')->get();

        return view('dtr.employee_dtr', compact('employee', 'records', 'selectedDate', 'startDate', 'endDate'));
    }

    public function showDetailedEmployeeDTRHistory(Request $request, User $employee)
    {
        // Ensure only admin/hr or the employee themselves can view the DTR
        $user = Auth::user();
        if (!$user || (!in_array($user->role, ['admin', 'hr']) && $user->id !== $employee->id)) {
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
            $currentMonth = Carbon::today()->month;
            $currentYear = Carbon::today()->year;

            if ($period === 'first_half') {
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1);
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 15);
            } elseif ($period === 'second_half') {
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 16);
                $endDate = Carbon::createFromDate($currentYear, $currentMonth)->endOfMonth();
            } elseif ($period === 'whole_month') {
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1);
                $endDate = Carbon::createFromDate($currentYear, $currentMonth)->endOfMonth();
            }
            $isFiltered = true;
        } elseif ($specificDate) {
            $startDate = Carbon::parse($specificDate);
            $endDate = Carbon::parse($specificDate);
            $isFiltered = true;
        } elseif ($requestStartDate && $requestEndDate) {
            $startDate = Carbon::parse($requestStartDate);
            $endDate = Carbon::parse($requestEndDate);
            $isFiltered = true;
        } else {
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::today()->endOfMonth();
        }

        $dtrRecords = DTRRecord::where('user_id', $employee->id)
                                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                                ->orderBy('date', 'desc')
                                ->get();

        return view('dtr.employee_dtr_detailed_history', compact('employee', 'dtrRecords', 'startDate', 'endDate', 'isFiltered'));
    }

    public function faceRecognitionManagement(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $selectedDepartmentId = $request->input('department_id');
        $search = $request->input('search');

        // Get all departments
        $departments = Department::orderBy('name')->get();

        // Build employee query
        $employeesQuery = User::where('role', 'employee')
            ->with('department')
            ->when($selectedDepartmentId, function ($query) use ($selectedDepartmentId) {
                $query->where('department_id', $selectedDepartmentId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere('employee_id', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name');

        $employees = $employeesQuery->get();

        return view('dtr.face_recognition_management', compact('departments', 'employees', 'selectedDepartmentId', 'search'));
    }

    public function toggleFaceRecognition(Request $request, User $employee)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $employee->face_recognition_enabled = $request->input('enabled');
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Face recognition ' . ($request->input('enabled') ? 'enabled' : 'disabled') . ' successfully.',
            'enabled' => $employee->face_recognition_enabled
        ]);
    }
}