<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusRoute;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = BusRoute::where('status', 'active')->get();
        return response()->json([
            'status' => 'success',
            'data' => $routes
        ]);
    }
}
