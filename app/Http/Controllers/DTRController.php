<?php

namespace App\Http\Controllers;

use App\Models\DTRRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Helpers\TimeHelper;

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
        // If employee hasn't registered face, redirect them to one-time face registration
        if ($user->isEmployee() && empty($user->face_embedding)) {
            return redirect()->route('face.register')->with('info', 'Please register your face before accessing Daily Time Record.');
        }
        $today = Carbon::today();
        
        $dtrRecord = DTRRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $monthlyRecords = DTRRecord::where('user_id', $user->id)
            ->whereMonth('date', $today->month)
            ->whereYear('date', $today->year)
            ->orderBy('date', 'desc')
            ->get();

        return view('dtr.index', compact('dtrRecord', 'monthlyRecords'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        // Require face registration
        if (empty($user->face_embedding)) {
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

        $stored = json_decode($user->face_embedding, true);
        $threshold = config('face.threshold', 0.5);
        $distance = \App\Services\FaceRecognitionService::distance($stored, $probe);
        Log::info('Face match distance for user ' . $user->id . ': ' . $distance);
        if ($distance > $threshold) {
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
        // Require face registration
        if (empty($user->face_embedding)) {
            return back()->with('error', 'Face not registered. Please register your face before clocking out.')->with('show_face_register', true);
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

        $stored = json_decode($user->face_embedding, true);
        $threshold = config('face.threshold', 0.5);
        $distance = \App\Services\FaceRecognitionService::distance($stored, $probe);
        Log::info('Face match distance for user ' . $user->id . ': ' . $distance);
        if ($distance > $threshold) {
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

        $today = Carbon::today();

        // Calculate counts directly from DTR records for today
        $allDtrToday = DTRRecord::whereDate('date', $today)->get();

		$presentCount = $allDtrToday->where('status', 'present')->count();
		$lateCount = $allDtrToday->where('status', 'late')->count();
		$halfDayCount = $allDtrToday->where('status', 'half_day')->count();

		$totalEmployees = User::where('role', 'employee')->count();
		$employeesWithDTR = $allDtrToday->pluck('user_id')->unique()->count();
		$absentCount = $totalEmployees - $employeesWithDTR;

        $filterStatus = $request->input('status', 'present');

        $employees = User::where('role', 'employee')
                        ->orderBy('name')
                        ->with(['dtrRecords' => function ($query) use ($today) {
                            $query->whereDate('date', $today);
                        }]);

        if ($filterStatus == 'present' || $filterStatus == 'late' || $filterStatus == 'half_day') {
            $employees->whereHas('dtrRecords', function ($query) use ($today, $filterStatus) {
                $query->whereDate('date', $today)->where('status', $filterStatus);
            });
        } elseif ($filterStatus == 'absent') {
            $employees->whereDoesntHave('dtrRecords', function ($query) use ($today) {
                $query->whereDate('date', $today);
            });
        }

        $employees = $employees->get();

        $startDate = null;
        $endDate = null;

        return view('dtr.admin', compact('presentCount', 'lateCount', 'absentCount', 'halfDayCount', 'employees', 'today', 'startDate', 'endDate', 'filterStatus'));
    }

    public function employeesIndex()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $employees = User::where('role', 'employee')->orderBy('name')->get();

        return view('dtr.employees_list', compact('employees'));
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
}