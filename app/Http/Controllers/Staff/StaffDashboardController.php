<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('staff.dashboard');
    }
}