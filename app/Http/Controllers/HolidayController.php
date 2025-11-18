<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $holidays = Holiday::all();
        return view('holidays.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This method is no longer used for returning a view directly.
        // It could be adapted to return default data as JSON if needed by a more complex AJAX setup,
        // but for now, the frontend modal directly uses config values for new entries.
        return response()->noContent();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:255',
            'type' => 'required|in:regular,special_non_working',
            'rate_multiplier' => 'required|numeric|min:0',
        ]);

        Holiday::create($validated);

        return redirect()->route('holidays.index')->with('success', 'Holiday created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        return response()->json($holiday);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        // This method is no longer used for returning a view directly.
        // The holiday data is fetched via AJAX by the frontend modal using the show method.
        return response()->noContent();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:regular,special_non_working',
            'rate_multiplier' => 'required|numeric|min:0',
        ]);

        $holiday->update($validated);

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
