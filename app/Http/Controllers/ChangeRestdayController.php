<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangeRestdayController extends Controller
{
    public function index()
    {
        return view('attendance.change_restday.index');
    }

    public function store(Request $request)
    {
        // Logic to handle change restday request
        return redirect()->back()->with('success', 'Change restday request submitted successfully!');
    }
}
