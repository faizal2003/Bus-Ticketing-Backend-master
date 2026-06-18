<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $query = Refund::with(['booking.schedule', 'user', 'processedBy']);

        // Search by booking code or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('booking', function($subQ) use ($search) {
                    $subQ->where('booking_code', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $refunds = $query->latest()->paginate(10);

        // Get status counts for filter tabs
        $statusCounts = [
            'all' => Refund::count(),
            'pending' => Refund::where('status', 'pending')->count(),
            'approved' => Refund::where('status', 'approved')->count(),
            'rejected' => Refund::where('status', 'rejected')->count(),
        ];

        return view('admin.refunds.index', compact('refunds', 'statusCounts'));
    }

    public function show(Refund $refund)
    {
        $refund->load(['booking.schedule.bus', 'booking.passengers', 'user', 'processedBy']);
        return view('admin.refunds.show', compact('refund'));
    }

    public function update(Request $request, Refund $refund)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($refund->status !== 'pending') {
            return redirect()->back()->with('error', 'Refund ini sudah diproses sebelumnya.');
        }

        $adminId = auth()->id();

        if ($request->status === 'approved') {
            $refund->approve($adminId, $request->admin_notes);
            $message = 'Refund berhasil disetujui. Dana akan dikembalikan ke pengguna.';
        } else {
            $refund->reject($adminId, $request->admin_notes);
            $message = 'Refund ditolak.';
        }

        return redirect()->route('admin.refunds.index')->with('success', $message);
    }

    public function updateWithNotes(Request $request, Refund $refund)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'required|string|max:500',
        ]);

        if ($refund->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Refund ini sudah diproses sebelumnya.'
            ], 400);
        }

        $adminId = auth()->id();

        if ($request->status === 'approved') {
            $refund->approve($adminId, $request->admin_notes);
            $message = 'Refund berhasil disetujui.';
        } else {
            $refund->reject($adminId, $request->admin_notes);
            $message = 'Refund ditolak.';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}
