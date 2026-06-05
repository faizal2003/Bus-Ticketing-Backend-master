<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index()
    {
        $refunds = Refund::with(['booking', 'user'])->latest()->paginate(10);
        return view('admin.refunds.index', compact('refunds'));
    }

    public function update(Request $request, Refund $refund)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $refund->update([
            'status' => $request->status
        ]);

        return redirect()->route('admin.refunds.index')->with('success', 'Status refund berhasil diubah.');
    }
}
