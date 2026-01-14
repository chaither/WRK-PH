<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = Shift::orderBy('name')->get();
        return view('shift.index', compact('shifts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_night_shift' => 'nullable|boolean',
            'night_shift_multiplier' => 'nullable|numeric|min:1.00|max:3.00',
            'lunch_break_start' => 'nullable|date_format:H:i',
            'lunch_break_end' => 'nullable|date_format:H:i|after:lunch_break_start',
            'lunch_break_duration' => 'nullable|integer|min:0|max:180',
            'is_lunch_paid' => 'nullable|boolean',
        ]);

        // Convert time format
        $validated['start_time'] = $validated['start_time'] . ':00';
        $validated['end_time'] = $validated['end_time'] . ':00';
        
        // Handle lunch break times
        if (!empty($validated['lunch_break_start'])) {
            $validated['lunch_break_start'] = $validated['lunch_break_start'] . ':00';
        } else {
            $validated['lunch_break_start'] = null;
        }
        
        if (!empty($validated['lunch_break_end'])) {
            $validated['lunch_break_end'] = $validated['lunch_break_end'] . ':00';
        } else {
            $validated['lunch_break_end'] = null;
        }
        
        // Set defaults
        $validated['is_night_shift'] = $request->has('is_night_shift') ? 1 : 0;
        $validated['night_shift_multiplier'] = $validated['is_night_shift'] 
            ? ($validated['night_shift_multiplier'] ?? 1.10) 
            : 1.00;
            
        $validated['is_lunch_paid'] = $request->has('is_lunch_paid') ? 1 : 0;
        $validated['lunch_break_duration'] = $validated['lunch_break_duration'] ?? 60;

        Shift::create($validated);

        return redirect()->route('shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_night_shift' => 'nullable|boolean',
            'night_shift_multiplier' => 'nullable|numeric|min:1.00|max:3.00',
            'lunch_break_start' => 'nullable|date_format:H:i',
            'lunch_break_end' => 'nullable|date_format:H:i|after:lunch_break_start',
            'lunch_break_duration' => 'nullable|integer|min:0|max:180',
            'is_lunch_paid' => 'nullable|boolean',
        ]);

        // Convert time format
        $validated['start_time'] = $validated['start_time'] . ':00';
        $validated['end_time'] = $validated['end_time'] . ':00';
        
        // Handle lunch break times
        if (!empty($validated['lunch_break_start'])) {
            $validated['lunch_break_start'] = $validated['lunch_break_start'] . ':00';
        } else {
            $validated['lunch_break_start'] = null;
        }
        
        if (!empty($validated['lunch_break_end'])) {
            $validated['lunch_break_end'] = $validated['lunch_break_end'] . ':00';
        } else {
            $validated['lunch_break_end'] = null;
        }
        
        // Set defaults
        $validated['is_night_shift'] = $request->has('is_night_shift') ? 1 : 0;
        $validated['night_shift_multiplier'] = $validated['is_night_shift'] 
            ? ($validated['night_shift_multiplier'] ?? 1.10) 
            : 1.00;
            
        $validated['is_lunch_paid'] = $request->has('is_lunch_paid') ? 1 : 0;
        $validated['lunch_break_duration'] = $validated['lunch_break_duration'] ?? 60;

        $shift->update($validated);

        return redirect()->route('shifts.index')
            ->with('success', 'Shift updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        // Check if shift is assigned to any employees
        if ($shift->users()->count() > 0) {
            return redirect()->route('shifts.index')
                ->with('error', 'Cannot delete shift. It is assigned to ' . $shift->users()->count() . ' employee(s).');
        }

        $shift->delete();

        return redirect()->route('shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }
}
