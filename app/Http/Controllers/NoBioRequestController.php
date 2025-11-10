<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoBioRequest;
use Illuminate\Support\Facades\Auth;

class NoBioRequestController extends Controller
{
    public function index()
    {
        $noBioRequests = Auth::user()->noBioRequests()->latest()->get();
        return view('attendance.no_bio_request.index', compact('noBioRequests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:time_in,time_out,both',
            'reason' => 'required|string|max:500',
        ]);

        NoBioRequest::create([
            'user_id' => Auth::id(),
            'date' => $request->date,
            'type' => $request->type,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'No Bio Request submitted successfully!');
    }
}
