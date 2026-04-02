<?php

namespace App\Http\Controllers\Conductor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('conductor.dashboard', [
            'title' => 'Conductor Dashboard'
        ]);
    }
}
