<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Services\PhilippineHolidayService;

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

    /**
     * Import Philippine holidays for a given year
     */
    public function importPhilippineHolidays(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = $validated['year'];
        $service = new PhilippineHolidayService();
        $holidays = $service->getHolidaysForYear($year);

        $created = 0;
        $skipped = 0;

        foreach ($holidays as $holidayData) {
            try {
                Holiday::firstOrCreate(
                    ['date' => $holidayData['date']],
                    [
                        'name' => $holidayData['name'],
                        'type' => $holidayData['type'],
                        'rate_multiplier' => $holidayData['rate_multiplier'],
                    ]
                );
                $created++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        $message = "Successfully imported Philippine holidays for {$year}. Created: {$created}, Skipped: {$skipped} (already exist).";
        return redirect()->route('holidays.index')->with('success', $message);
    }
}
