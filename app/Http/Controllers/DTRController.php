<?php

namespace App\Http\Controllers;

use App\Models\DTRRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

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

    public function clockIn()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        
        $existingRecord = DTRRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingRecord) {
            return back()->with('error', 'You have already clocked in today.');
        }

        // Check if late (assuming work starts at 9 AM)
        $status = $now->hour >= 9 ? 'late' : 'present';

        DTRRecord::create([
            'user_id' => $user->id,
            'date' => $today,
            'time_in' => $now->toTimeString(),
            'status' => $status,
        ]);

        return back()->with('success', 'Successfully clocked in.');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        
        $record = DTRRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$record) {
            return back()->with('error', 'No clock-in record found for today.');
        }

        if ($record->time_out) {
            return back()->with('error', 'You have already clocked out today.');
        }

        $record->update([
            'time_out' => $now->toTimeString(),
        ]);

        return back()->with('success', 'Successfully clocked out.');
    }

    public function adminView()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $today = Carbon::today();
        $records = DTRRecord::with('user')
            ->whereDate('date', $today)
            ->get();

		$presentCount = $records->where('status', 'present')->count();
		$lateCount = $records->where('status', 'late')->count();
		$absentCount = $user && in_array($user->role, ['admin', 'hr']) ? (User::where('role', 'employee')->count() - $records->count()) : 0;

        return view('dtr.admin', compact('records', 'presentCount', 'lateCount', 'absentCount'));
    }
}