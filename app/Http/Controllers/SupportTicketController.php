<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    /**
     * Tampilkan riwayat tiket bantuan milik pengguna login (Siswa / Guru / Ortu).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = SupportTicket::where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('support-tickets.index', compact('tickets'));
    }

    /**
     * Simpan pengajuan tiket bantuan baru dari pengguna.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'category' => 'required|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        SupportTicket::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'category' => $request->category,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return redirect()->route('support-tickets.index')
            ->with('success', 'Tiket bantuan kendala Anda berhasil dikirim ke Operator Sekolah.');
    }

    /**
     * Detail tiket bantuan untuk pengguna.
     */
    public function show($id)
    {
        $user = Auth::user();
        $ticket = SupportTicket::where('user_id', $user->id)
            ->findOrFail($id);

        return view('support-tickets.show', compact('ticket'));
    }

    /**
     * Halaman manajemen seluruh tiket masuk untuk Operator Sekolah.
     */
    public function operatorIndex(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $query = SupportTicket::where('tenant_id', $tenantId)
            ->with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $lowerSearch = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($lowerSearch) {
                $q->whereRaw("LOWER(subject) LIKE ?", [$lowerSearch])
                  ->orWhereRaw("LOWER(message) LIKE ?", [$lowerSearch])
                  ->orWhereHas('user', function ($uq) use ($lowerSearch) {
                      $uq->whereRaw("LOWER(name) LIKE ?", [$lowerSearch]);
                  });
            });
        }

        $tickets = $query->orderByRaw("FIELD(status, 'pending', 'processing', 'resolved') ASC")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = SupportTicket::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        $processingCount = SupportTicket::where('tenant_id', $tenantId)->where('status', 'processing')->count();
        $resolvedCount = SupportTicket::where('tenant_id', $tenantId)->where('status', 'resolved')->count();

        return view('operator.support-tickets.index', compact('tickets', 'pendingCount', 'processingCount', 'resolvedCount'));
    }

    /**
     * Detail tiket untuk diproses Operator Sekolah.
     */
    public function operatorShow($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $ticket = SupportTicket::where('tenant_id', $tenantId)
            ->with('user.schoolClass')
            ->findOrFail($id);

        return view('operator.support-tickets.show', compact('ticket'));
    }

    /**
     * Operator memperbarui status dan memberikan balasan tiket.
     */
    public function operatorUpdate(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $ticket = SupportTicket::where('tenant_id', $tenantId)
            ->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,resolved',
            'operator_response' => 'nullable|string|max:2000',
        ]);

        $ticket->update([
            'status' => $request->status,
            'operator_response' => $request->operator_response,
        ]);

        return redirect()->back()
            ->with('success', 'Status tiket bantuan dan catatan tanggapan berhasil diperbarui.');
    }
}
