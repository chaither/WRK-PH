<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayrollSchedule;
use Illuminate\Validation\Rule;

class PayrollScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(PayrollSchedule::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pay_period_type' => ['required', 'string', Rule::in(['semi-monthly', 'monthly']), 'unique:payroll_schedules,pay_period_type'],
            'generation_days' => ['required', 'array'],
            'generation_days.*' => ['required', Rule::in(['15', 'last_day'])],
        ]);

        // Ensure only one day for monthly type
        if ($validated['pay_period_type'] === 'monthly' && count($validated['generation_days']) !== 1) {
            return response()->json(['message' => 'Monthly pay period must have exactly one generation day.'], 422);
        }

        $schedule = PayrollSchedule::create($validated);
        return response()->json($schedule, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = PayrollSchedule::findOrFail($id);
        return response()->json($schedule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = PayrollSchedule::findOrFail($id);

        $validated = $request->validate([
            'pay_period_type' => ['required', 'string', Rule::in(['semi-monthly', 'monthly']), Rule::unique('payroll_schedules', 'pay_period_type')->ignore($schedule->id)],
            'generation_days' => ['required', 'array'],
            'generation_days.*' => ['required', Rule::in(['15', 'last_day'])],
        ]);

        // Ensure only one day for monthly type
        if ($validated['pay_period_type'] === 'monthly' && count($validated['generation_days']) !== 1) {
            return response()->json(['message' => 'Monthly pay period must have exactly one generation day.'], 422);
        }

        $schedule->update($validated);
        return response()->json($schedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = PayrollSchedule::findOrFail($id);
        $schedule->delete();
        return response()->json(null, 204);
    }
}
