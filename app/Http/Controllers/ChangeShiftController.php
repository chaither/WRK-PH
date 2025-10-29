<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangeShiftController extends Controller
{
    public function index()
    {
        return view('attendance.change_shift.index');
    }

    public function store(Request $request)
    {
        // Logic to handle change shift request
        return redirect()->back()->with('success', 'Change shift request submitted successfully!');
    }
}
