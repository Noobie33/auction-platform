<?php

namespace App\Http\Controllers\Bidder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BidderDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('bidder.dashboard');
    }
}