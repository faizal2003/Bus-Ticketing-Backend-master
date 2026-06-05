<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BusRoute;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = BusRoute::latest()->paginate(10);
        return view('superadmin.routes.index', compact('routes'));
    }

    public function create()
    {
        return view('superadmin.routes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'origin_city' => 'required|string|max:255',
            'destination_city' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        BusRoute::create($request->all());

        return redirect()->route('superadmin.routes.index')
            ->with('success', 'Rute berhasil ditambahkan.');
    }

    public function edit(BusRoute $route)
    {
        return view('superadmin.routes.edit', compact('route'));
    }

    public function update(Request $request, BusRoute $route)
    {
        $request->validate([
            'origin_city' => 'required|string|max:255',
            'destination_city' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $route->update($request->all());

        return redirect()->route('superadmin.routes.index')
            ->with('success', 'Rute berhasil diperbarui.');
    }

    public function destroy(BusRoute $route)
    {
        $route->delete();

        return redirect()->route('superadmin.routes.index')
            ->with('success', 'Rute berhasil dihapus.');
    }
}
