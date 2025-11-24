<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showRegistration()
    {
        $user = Auth::user();
        return view('face.register', compact('user'));
    }

    public function storeRegistration(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|string',
        ]);

        $user = Auth::user();

        // Prevent re-registration: employee can register only once unless data is removed
        if (!empty($user->face_embedding)) {
            return back()->with('error', 'Face already registered. If you need to re-register, please contact HR.');
        }

        $descriptor = json_decode($request->input('face_descriptor'), true);
        if (!is_array($descriptor) || count($descriptor) === 0) {
            return back()->with('error', 'Invalid face descriptor. Please try again.');
        }

        // Store as JSON string
        $user->face_embedding = json_encode($descriptor);
        $user->save();

        return back()->with('success', 'Face registration saved successfully.');
    }
}
