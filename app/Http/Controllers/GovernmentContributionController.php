<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GovernmentContribution;
use Illuminate\Validation\Rule;

class GovernmentContributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(GovernmentContribution::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['sss', 'philhealth', 'pagibig']), 'unique:government_contributions,type,NULL,id,min_salary,' . ($request->min_salary ?? 'NULL') . ',max_salary,' . ($request->max_salary ?? 'NULL')],
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gt:min_salary',
            'is_percentage' => 'required|boolean',
            'employee_share' => 'required|numeric|min:0',
            'employer_share' => 'nullable|numeric|min:0',
            'target_type' => ['required_if:is_percentage,false', Rule::in(['all', 'employees', 'departments'])],
            'applies_to' => ['nullable', 'array',
                'required_if:target_type,employees',
                'required_if:target_type,departments'],
            'applies_to.*' => 'integer',
        ]);

        // If is_percentage is true, or target_type is 'all', then applies_to should be null
        if ($validated['is_percentage'] || (isset($validated['target_type']) && $validated['target_type'] === 'all')) {
            $validated['applies_to'] = null;
            $validated['target_type'] = 'all'; // Reset to all if percentage based
        }

        $contribution = GovernmentContribution::create($validated);
        return response()->json($contribution, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contribution = GovernmentContribution::findOrFail($id);
        return response()->json($contribution);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $contribution = GovernmentContribution::findOrFail($id);

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['sss', 'philhealth', 'pagibig']), Rule::unique('government_contributions', 'type')->ignore($contribution->id, 'id')->where(function ($query) use ($request, $contribution) {
                $query->where('min_salary', $request->min_salary ?? null);
                $query->where('max_salary', $request->max_salary ?? null);
            })],
            'is_percentage' => 'required|boolean',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gt:min_salary',
            'employee_share' => 'required|numeric|min:0',
            'employer_share' => 'nullable|numeric|min:0',
            'target_type' => ['required_if:is_percentage,false', Rule::in(['all', 'employees', 'departments'])],
            'applies_to' => ['nullable', 'array',
                'required_if:target_type,employees',
                'required_if:target_type,departments'],
            'applies_to.*' => 'integer',
        ]);

        // If is_percentage is true, or target_type is 'all', then applies_to should be null
        if ($validated['is_percentage'] || (isset($validated['target_type']) && $validated['target_type'] === 'all')) {
            $validated['applies_to'] = null;
            $validated['target_type'] = 'all'; // Reset to all if percentage based
        }

        $contribution->update($validated);
        return response()->json($contribution);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $contribution = GovernmentContribution::findOrFail($id);
        $contribution->delete();
        return response()->json(null, 204);
    }
}
