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

        $raw = json_decode($request->input('face_descriptor'), true);
        if (!$raw) {
            return back()->with('error', 'Invalid face descriptor. Please try again.');
        }

        // Accept either:
        // - flat descriptor: [0.12, ...]
        // - object with { samples: [[...],[...]], average: [...] }
        // Prefer storing `samples` (array of descriptors) for robustness; fall back to average or flat.
        $toStore = null;
        if (is_array($raw) && isset($raw['samples']) && is_array($raw['samples']) && count($raw['samples']) > 0) {
            $toStore = array_values($raw['samples']);
        } elseif (is_array($raw) && isset($raw['average']) && is_array($raw['average'])) {
            $toStore = $raw['average'];
        } elseif (is_array($raw)) {
            // Could be a flat descriptor or an array-of-arrays submitted directly
            // Detect array-of-arrays
            $first = reset($raw);
            if (is_array($first)) {
                $toStore = array_values($raw);
            } else {
                $toStore = array_values($raw);
            }
        }

        if (!$toStore || (is_array($toStore) && count($toStore) === 0)) {
            return back()->with('error', 'Invalid face descriptor. Please try again.');
        }

        // Store as JSON string (User model casts this field to array)
        $user->face_embedding = json_encode($toStore);
        $user->save();

        // After successful one-time registration, redirect the user to Daily Time Record
        return redirect()->route('dtr.index')->with('success', 'Face registration saved successfully. You can now use Face Verification to clock in/out.');
    }
}
