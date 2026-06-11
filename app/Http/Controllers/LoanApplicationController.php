<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    public function index(Request $request)
    {
        $loans = Loan::with(['member', 'reviewer', 'pemohon'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'pending'   => Loan::where('status', 'pending')->count(),
            'approved'  => Loan::where('status', 'approved')->whereMonth('reviewed_at', now()->month)->count(),
            'requested' => Loan::where('status', 'pending')->sum('amount'),
        ];

        $canApprove = $request->user()->hasAnyRole(['admin-koperasi', 'pengurus', 'super-user']);

        $meetings = \App\Models\Meeting::latest('tarikh')->get(['id', 'tajuk', 'tarikh']);
        $ahliList = \App\Models\Member::where('status', 'aktif')->orderBy('no_ahli')->get(['id', 'no_ahli', 'nama']);

        return view('pinjaman.index', compact('loans', 'stats', 'canApprove', 'meetings', 'ahliList'));
    }

    public function create(Request $request)
    {
        // Hanya ahli AKTIF layak dipohonkan pinjaman
        $members = Member::where('status', 'aktif')
            ->orderBy('no_ahli')
            ->get(['id', 'no_ahli', 'nama']);

        return view('pinjaman.create', compact('members'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'amount'    => ['required', 'numeric', 'min:100'],
            'tempoh'    => ['required', 'integer', 'min:1', 'max:120'],
            'tujuan'    => ['required', 'string', 'max:500'],
        ]);

        $member = Member::findOrFail($data['member_id']);

        // Kuatkuasa syarat kelayakan
        if (! $member->layakPinjam()) {
            return back()->withInput()
                ->with('error', "Ahli {$member->no_ahli} tidak layak memohon pinjaman (status tidak aktif).");
        }

        Loan::create([
            'member_id'    => $member->id,
            'dimohon_oleh' => $request->user()->id,   // staff yang key-in
            'amount'       => $data['amount'],
            'tempoh'       => $data['tempoh'],
            'tujuan'       => $data['tujuan'],
            'status'       => 'pending',
        ]);

        return redirect()->route('pinjaman.index')
            ->with('success', "Permohonan pinjaman untuk ahli {$member->no_ahli} berjaya direkod.");
    }

    public function decide(Request $request, Loan $loan)
    {
        if (! $request->user()->hasAnyRole(['admin-koperasi', 'pengurus', 'super-user'])) {
            abort(403, 'Tidak dibenarkan meluluskan.');
        }

        // Block self-approval: orang yang key-in tak boleh lulus sendiri
        if ($loan->dimohon_oleh === $request->user()->id) {
            return back()->with('error', 'Anda tidak boleh meluluskan permohonan yang anda sendiri rekod. Sila minta pegawai lain.');
        }

        $data = $request->validate([
            'status'       => ['required', 'in:approved,rejected'],
            'catatan'      => ['nullable', 'string', 'max:500'],
            // Maklumat mesyuarat wajib bila LULUS sahaja
            'meeting_id'   => ['required_if:status,approved', 'nullable', 'exists:meetings,id'],
            'pencadang_id' => ['required_if:status,approved', 'nullable', 'exists:members,id'],
            'penyokong_id' => ['required_if:status,approved', 'nullable', 'exists:members,id', 'different:pencadang_id'],
        ], [
            'meeting_id.required_if'   => 'Sila pilih mesyuarat kelulusan.',
            'pencadang_id.required_if' => 'Sila pilih pencadang.',
            'penyokong_id.required_if' => 'Sila pilih penyokong.',
            'penyokong_id.different'   => 'Penyokong mesti berbeza daripada pencadang.',
        ]);

        $loan->update([
            'status'       => $data['status'],
            'catatan'      => $data['catatan'] ?? null,
            'reviewed_by'  => $request->user()->id,
            'reviewed_at'  => now(),
            'meeting_id'   => $data['status'] === 'approved' ? $data['meeting_id'] : null,
            'pencadang_id' => $data['status'] === 'approved' ? $data['pencadang_id'] : null,
            'penyokong_id' => $data['status'] === 'approved' ? $data['penyokong_id'] : null,
        ]);

        $label = $data['status'] === 'approved' ? 'diluluskan' : 'ditolak';

        return redirect()->route('pinjaman.index')
            ->with('success', "Permohonan #{$loan->id} telah {$label}.");
    }
}