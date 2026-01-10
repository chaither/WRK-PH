<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Shift;
use Illuminate\Http\Request;

class SyncDataController extends Controller
{
    /**
     * Get all data for biometric app to sync (Polling method)
     */
    public function getUpdates(Request $request)
    {
        // Simple polling: return all for now, or filter by updated_at if provided
        $since = $request->query('since');
        
        $usersQuery = User::whereNotNull('employee_id');
        $deptQuery = Department::query();
        $shiftQuery = Shift::query();

        if ($since) {
            $usersQuery->where('updated_at', '>', $since);
            $deptQuery->where('updated_at', '>', $since);
            $shiftQuery->where('updated_at', '>', $since);
        }

        return response()->json([
            'users' => $usersQuery->get(),
            'departments' => $deptQuery->get(),
            'shifts' => $shiftQuery->get(),
            'server_time' => now()->toDateTimeString()
        ]);
    }
}
