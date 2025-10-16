<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [];
        
        if (auth()->user()->role === 'admin') {
            $data['employeeCount'] = User::where('role', 'employee')->count();
        }

        if (in_array(auth()->user()->role, ['admin', 'hr'])) {
            // Add attendance stats here when we implement the attendance system
            $data['presentToday'] = 0;
            $data['absentToday'] = 0;
        }

        return view('dashboard.index', $data);
    }
}