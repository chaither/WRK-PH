<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayPeriod;
use Carbon\Carbon;

class PayrollHistoryController extends Controller
{
    public function getPayrollHistory(Request $request)
    {
        try {
            $type = $request->query('type');
            $query = PayPeriod::query();

            if ($type === 'semiMonthly') {
                // Use pay_period_type instead of date-based logic for better reliability
                $query->where('pay_period_type', 'semi-monthly');
            } elseif ($type === 'monthly') {
                // Use pay_period_type instead of date-based logic for better reliability
                $query->where('pay_period_type', 'monthly');
            } else {
                return response()->json(['error' => 'Invalid type parameter'], 400); // Bad request if type is invalid
            }

            $payPeriods = $query->orderBy('end_date', 'desc')->get();

            $formattedHistory = $payPeriods->map(function ($period) {
                return [
                    'id' => $period->id,
                    'start_date' => $period->start_date ? $period->start_date->format('Y-m-d') : null,
                    'end_date' => $period->end_date ? $period->end_date->format('Y-m-d') : null,
                    'status' => $period->status ?? 'draft',
                    'total_net_pay' => $period->payslips->sum('net_pay') ?? 0,
                ];
            });

            return response()->json($formattedHistory);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getPayrollHistory: ' . $e->getMessage(), [
                'type' => $request->query('type'),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to retrieve payroll history'], 500);
        }
    }
}
