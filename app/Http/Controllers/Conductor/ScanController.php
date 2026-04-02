<?php

namespace App\Http\Controllers\Conductor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        return view('conductor.scan', [
            'title' => 'Scan Ticket'
        ]);
    }

    public function scan(Request $request)
    {
        // Implementation for scanning ticket via web
    }
}
