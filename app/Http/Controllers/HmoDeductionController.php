<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HmoDeduction;
use Illuminate\Validation\Rule;

class HmoDeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(HmoDeduction::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_percentage' => 'required|boolean',
            'employee_share' => 'required|numeric|min:0',
            'target_type' => ['required_if:is_percentage,false', Rule::in(['all', 'employees', 'departments'])],
            'applies_to' => ['nullable', 'array',
                'required_if:target_type,employees',
                'required_if:target_type,departments'],
            'applies_to.*' => 'integer',
            'deduction_frequency' => ['required', 'string', Rule::in(['semi_monthly', 'first_half_monthly'])],
            'deduction_frequency_target_type' => ['required', 'string', Rule::in(['all', 'employees', 'departments'])],
            'deduction_frequency_applies_to' => ['nullable', 'array',
                'required_if:deduction_frequency_target_type,employees',
                'required_if:deduction_frequency_target_type,departments'],
            'deduction_frequency_applies_to.*' => 'integer',
        ]);

        // If is_percentage is true, or target_type is 'all', then applies_to should be null
        if ($validated['is_percentage'] || (isset($validated['target_type']) && $validated['target_type'] === 'all')) {
            $validated['applies_to'] = null;
            $validated['target_type'] = 'all'; // Reset to all if percentage based
        }

        // If deduction_frequency_target_type is 'all', then deduction_frequency_applies_to should be null
        if ($validated['deduction_frequency_target_type'] === 'all') {
            $validated['deduction_frequency_applies_to'] = null;
        }

        $deduction = HmoDeduction::create($validated);
        return response()->json($deduction, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $deduction = HmoDeduction::findOrFail($id);
        return response()->json($deduction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $deduction = HmoDeduction::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_percentage' => 'required|boolean',
            'employee_share' => 'required|numeric|min:0',
            'target_type' => ['required_if:is_percentage,false', Rule::in(['all', 'employees', 'departments'])],
            'applies_to' => ['nullable', 'array',
                'required_if:target_type,employees',
                'required_if:target_type,departments'],
            'applies_to.*' => 'integer',
            'deduction_frequency' => ['required', 'string', Rule::in(['semi_monthly', 'first_half_monthly'])],
            'deduction_frequency_target_type' => ['required', 'string', Rule::in(['all', 'employees', 'departments'])],
            'deduction_frequency_applies_to' => ['nullable', 'array',
                'required_if:deduction_frequency_target_type,employees',
                'required_if:deduction_frequency_target_type,departments'],
            'deduction_frequency_applies_to.*' => 'integer',
        ]);

        // If is_percentage is true, or target_type is 'all', then applies_to should be null
        if ($validated['is_percentage'] || (isset($validated['target_type']) && $validated['target_type'] === 'all')) {
            $validated['applies_to'] = null;
            $validated['target_type'] = 'all'; // Reset to all if percentage based
        }

        // If deduction_frequency_target_type is 'all', then deduction_frequency_applies_to should be null
        if ($validated['deduction_frequency_target_type'] === 'all') {
            $validated['deduction_frequency_applies_to'] = null;
        }

        $deduction->update($validated);
        return response()->json($deduction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deduction = HmoDeduction::findOrFail($id);
        $deduction->delete();
        return response()->json(null, 204);
    }
}
