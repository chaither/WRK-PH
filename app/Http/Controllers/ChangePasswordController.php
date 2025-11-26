<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ChangePasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showChangePasswordForm()
    {
        return view('auth.passwords.change');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match!']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('dashboard')->with('success', 'Password changed successfully!');
    }
}
