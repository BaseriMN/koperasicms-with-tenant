<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::with(['user', 'nextOfKin'])
            ->when($request->search, fn ($q, $s) =>
                $q->where(function ($qq) use ($s) {
                    $cari = '%' . strtolower($s) . '%';
                    $qq->whereRaw('LOWER(nama) LIKE ?', [$cari])
                       ->orWhereRaw('LOWER(no_ahli) LIKE ?', [$cari])
                       ->orWhereRaw('LOWER(no_kp) LIKE ?', [$cari]);
                }))
            ->when($request->status, fn ($q, $st) => $q->where('status', $st))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        
        return view('members.index', compact('members'));
    }

    /**
     * Export ahli ke CSV. Menghormati penapis (search/status) jika ada,
     * jika tiada penapis ia export SEMUA ahli.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $namaFail = 'senarai-ahli-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$namaFail}\"",
        ];

        // Query sama logik dengan index (penapis search + status)
        $query = Member::with('nextOfKin')
            ->when($request->search, fn ($q, $s) =>
                $q->where(function ($qq) use ($s) {
                    $cari = '%' . strtolower($s) . '%';
                    $qq->whereRaw('LOWER(nama) LIKE ?', [$cari])
                       ->orWhereRaw('LOWER(no_ahli) LIKE ?', [$cari])
                       ->orWhereRaw('LOWER(no_kp) LIKE ?', [$cari]);
                }))
            ->when($request->status, fn ($q, $st) => $q->where('status', $st))
            ->orderBy('no_ahli');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 untuk Excel

            fputcsv($out, [
                'No. Ahli', 'Nama', 'No. KP', 'Telefon', 'Status', 'Tarikh Sertai',
                'Alamat', 'Baki Saham (RM)', 'Baki Simpanan (RM)',
                'Waris: Nama', 'Waris: Hubungan', 'Waris: No. KP', 'Waris: Telefon', 'Waris: Alamat',
            ]);

            $query->chunk(300, function ($rows) use ($out) {
                foreach ($rows as $m) {
                    $w = $m->nextOfKin;
                    fputcsv($out, [
                        $m->no_ahli,
                        $m->nama,
                        $m->no_kp ?? '',
                        $m->telefon ?? '',
                        ucfirst(str_replace('_', ' ', $m->status)),
                        optional($m->tarikh_sertai)->format('Y-m-d'),
                        $m->alamat ?? '',
                        number_format($m->bakiSaham(), 2, '.', ''),
                        number_format($m->bakiSimpanan(), 2, '.', ''),
                        $w->nama ?? '',
                        $w->hubungan ?? '',
                        $w->no_kp ?? '',
                        $w->telefon ?? '',
                        $w->alamat ?? '',
                    ]);
                }
            });

            fclose($out);
        }, $namaFail, $headers);
    }

    public function create()
    {
        $users      = User::orderBy('name')->get(['id', 'name']);
        $nextNoAhli = Member::nextNoAhli();   // paparan sahaja (preview)

        return view('members.create', compact('users', 'nextNoAhli'));
    }

    public function store(Request $request)
    {
        $data = $this->validateMember($request);

        $member = Member::create([
            'user_id'       => $data['user_id'] ?? null,
            'nama'          => $data['nama'],
            'no_kp'         => $data['no_kp'] ?? null,
            'telefon'       => $data['telefon'] ?? null,
            'alamat'        => $data['alamat'] ?? null,
            'tarikh_sertai' => $data['tarikh_sertai'] ?? now()->toDateString(),
            'status'        => $data['status'] ?? 'aktif',
            'foto_path'     => $request->hasFile('foto')
                ? $request->file('foto')->store('members', 'public')
                : null,
            // no_ahli auto-jana oleh model
        ]);

        // Simpan waris jika ada
        if ($request->filled('waris_nama')) {
            $member->nextOfKin()->create($this->validateWaris($request));
        }

        return redirect()->route('members.show', $member)
            ->with('success', "Ahli {$member->no_ahli} berjaya didaftar.");
    }

    public function show(Member $member)
    {
        $member->load(['user', 'nextOfKin', 'ownershipTransfers' => fn ($q) => $q->latest()]);

        $recent = $member->transactions()->with('recorder')->latest()->limit(10)->get();

        $summary = [
            'saham'    => $member->bakiSaham(),
            'simpanan' => $member->bakiSimpanan(),
        ];

        return view('members.show', compact('member', 'recent', 'summary'));
    }

    public function edit(Member $member)
    {
        $member->load('nextOfKin');
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('members.edit', compact('member', 'users'));
    }

    public function update(Request $request, Member $member)
    {
        $data = $this->validateMember($request, $member);

        // Handle foto baharu (buang lama jika ada)
        if ($request->hasFile('foto')) {
            if ($member->foto_path && Storage::disk('public')->exists($member->foto_path)) {
                Storage::disk('public')->delete($member->foto_path);
            }
            $member->foto_path = $request->file('foto')->store('members', 'public');
        }

        $member->update([
            'user_id'       => $data['user_id'] ?? null,
            'nama'          => $data['nama'],
            'no_kp'         => $data['no_kp'] ?? null,
            'telefon'       => $data['telefon'] ?? null,
            'alamat'        => $data['alamat'] ?? null,
            'tarikh_sertai' => $data['tarikh_sertai'] ?? $member->tarikh_sertai,
            'status'        => $data['status'] ?? $member->status,
            'foto_path'     => $member->foto_path,
        ]);

        // Kemaskini / cipta waris
        if ($request->filled('waris_nama')) {
            $member->nextOfKin()->updateOrCreate(
                ['member_id' => $member->id],
                $this->validateWaris($request)
            );
        }

        return redirect()->route('members.show', $member)
            ->with('success', 'Maklumat ahli berjaya dikemaskini.');
    }

    public function destroy(Member $member)
    {
        $no = $member->no_ahli;
        $member->delete();

        return redirect()->route('members.index')
            ->with('success', "Ahli {$no} berjaya dipadam.");
    }

    // ---- Validasi ----
    private function validateMember(Request $request, ?Member $member = null): array
    {
        return $request->validate([
            'user_id'       => ['nullable', 'exists:users,id'],
            'nama'          => ['required', 'string', 'max:255'],
            'no_kp'         => ['nullable', 'string', 'max:20'],
            'telefon'       => ['nullable', 'string', 'max:20'],
            'alamat'        => ['nullable', 'string'],
            'tarikh_sertai' => ['nullable', 'date'],
            'status'        => ['nullable', Rule::in(['aktif', 'tidak_aktif', 'berhenti'])],
            'foto'          => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);
    }

    private function validateWaris(Request $request): array
    {
        $v = $request->validate([
            'waris_nama'     => ['required', 'string', 'max:255'],
            'waris_no_kp'    => ['nullable', 'string', 'max:20'],
            'waris_hubungan' => ['required', 'string', 'max:50'],
            'waris_telefon'  => ['nullable', 'string', 'max:20'],
            'waris_alamat'   => ['nullable', 'string'],
        ]);

        return [
            'nama'     => $v['waris_nama'],
            'no_kp'    => $v['waris_no_kp'] ?? null,
            'hubungan' => $v['waris_hubungan'],
            'telefon'  => $v['waris_telefon'] ?? null,
            'alamat'   => $v['waris_alamat'] ?? null,
        ];
    }
}
