<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayPeriod;
use Carbon\Carbon;

class PayrollHistoryController extends Controller
{
    public function getPayrollHistory(Request $request)
    {
        $type = $request->query('type');
        $query = PayPeriod::query();

        if ($type === 'semiMonthly') {
            $query->whereDay('end_date', '<=', 15);
        } elseif ($type === 'monthly') {
            // This condition checks if the end_date is the last day of its respective month.
            $query->whereRaw('DAY(end_date) = DAY(LAST_DAY(end_date))');
        } else {
            return response()->json([], 400); // Bad request if type is invalid
        }

        $payPeriods = $query->orderBy('end_date', 'desc')->get();

        $formattedHistory = $payPeriods->map(function ($period) {
            return [
                'id' => $period->id,
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
                'status' => $period->status,
                'total_net_pay' => $period->total_net_pay ?? 0,
            ];
        });

        return response()->json($formattedHistory);
    }
}
