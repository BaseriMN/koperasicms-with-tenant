<?php

namespace App\Http\Controllers;

use App\Models\AccountCategory;
use App\Models\AccountEntry;
use App\Models\Member;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountEntryController extends Controller
{
    /**
     * Senarai entri bagi satu jenis + ringkasan & penapis tarikh/kategori.
     */
    public function index(Request $request, string $jenis)
    {
        $this->guardJenis($jenis);

        $dari   = $request->input('dari');
        $hingga = $request->input('hingga');

        $entries = AccountEntry::where('jenis', $jenis)
            ->with(['category.parent', 'member', 'recorder'])
            ->dalamTempoh($dari, $hingga)
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->latest('tarikh')->latest('id')
            ->paginate(25)->withQueryString();

        // Ringkasan ikut kategori (untuk kad/laporan ringkas) — laju guna groupBy
        $ringkasan = AccountEntry::where('jenis', $jenis)
            ->dalamTempoh($dari, $hingga)
            ->selectRaw('category_id, SUM(amaun) as total')
            ->groupBy('category_id')
            ->with('category.parent')
            ->get();

        $jumlahKeseluruhan = wang((float) $ringkasan->sum('total'));

        $categories = AccountCategory::where('jenis', $jenis)->aktif()
            ->orderBy('nama')->get(['id', 'nama', 'parent_id']);

        return view('akaun.entri.index', compact(
            'entries', 'jenis', 'ringkasan', 'jumlahKeseluruhan', 'categories', 'dari', 'hingga'
        ));
    }

    /**
     * Export entri (pendapatan/perbelanjaan) ke CSV. Menghormati penapis tarikh & kategori.
     */
    public function exportCsv(Request $request, string $jenis): StreamedResponse
    {
        $this->guardJenis($jenis);

        $dari   = $request->input('dari');
        $hingga = $request->input('hingga');

        $namaFail = "akaun-{$jenis}-" . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$namaFail}\"",
        ];

        $query = AccountEntry::where('jenis', $jenis)
            ->with(['category.parent', 'member'])
            ->dalamTempoh($dari, $hingga)
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->orderBy('tarikh')->orderBy('id');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($out, [
                'Tarikh', 'Kategori', 'Kategori Induk', 'Amaun (RM)',
                'No. Ahli', 'Nama Ahli', 'Penerima/Pembayar', 'Rujukan', 'Keterangan',
            ]);

            $query->chunk(300, function ($rows) use ($out) {
                foreach ($rows as $e) {
                    fputcsv($out, [
                        optional($e->tarikh)->format('Y-m-d'),
                        $e->category->nama ?? '',
                        $e->category->parent->nama ?? '',
                        number_format((float) $e->amaun, 2, '.', ''),
                        $e->member->no_ahli ?? '',
                        $e->member->nama ?? '',
                        $e->penerima_pembayar ?? '',
                        $e->rujukan ?? '',
                        $e->keterangan ?? '',
                    ]);
                }
            });

            fclose($out);
        }, $namaFail, $headers);
    }

    public function create(string $jenis)
    {
        $this->guardJenis($jenis);

        $categories = $this->selectableCategories($jenis);
        $members    = Member::where('status', 'aktif')->orderBy('no_ahli')->get(['id', 'no_ahli', 'nama']);

        return view('akaun.entri.create', compact('jenis', 'categories', 'members'));
    }

    public function store(Request $request, string $jenis)
    {
        $this->guardJenis($jenis);

        $data = $this->validateEntry($request, $jenis);

        AccountEntry::create([
            'category_id'       => $data['category_id'],
            'jenis'             => $jenis,
            'member_id'         => $data['member_id'] ?? null,
            'amaun'             => $data['amaun'],
            'tarikh'            => $data['tarikh'],
            'rujukan'           => $data['rujukan'] ?? null,
            'penerima_pembayar' => $data['penerima_pembayar'] ?? null,
            'keterangan'        => $data['keterangan'] ?? null,
            'recorded_by'       => $request->user()->id,
        ]);

        return redirect()->route('akaun.entri.index', $jenis)
            ->with('success', ucfirst($jenis) . ' berjaya direkodkan.');
    }

    public function edit(string $jenis, AccountEntry $entri)
    {
        $this->guardJenis($jenis);
        abort_unless($entri->jenis === $jenis, 404);

        $categories = $this->selectableCategories($jenis);
        $members    = Member::orderBy('no_ahli')->get(['id', 'no_ahli', 'nama']);

        return view('akaun.entri.edit', compact('jenis', 'entri', 'categories', 'members'));
    }

    public function update(Request $request, string $jenis, AccountEntry $entri)
    {
        $this->guardJenis($jenis);
        abort_unless($entri->jenis === $jenis, 404);

        $data = $this->validateEntry($request, $jenis);

        $entri->update([
            'category_id'       => $data['category_id'],
            'member_id'         => $data['member_id'] ?? null,
            'amaun'             => $data['amaun'],
            'tarikh'            => $data['tarikh'],
            'rujukan'           => $data['rujukan'] ?? null,
            'penerima_pembayar' => $data['penerima_pembayar'] ?? null,
            'keterangan'        => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('akaun.entri.index', $jenis)
            ->with('success', 'Rekod berjaya dikemaskini.');
    }

    public function destroy(string $jenis, AccountEntry $entri)
    {
        $this->guardJenis($jenis);
        abort_unless($entri->jenis === $jenis, 404);

        $entri->delete();

        return redirect()->route('akaun.entri.index', $jenis)
            ->with('success', 'Rekod berjaya dipadam.');
    }

    // ---- Helpers ----
    private function guardJenis(string $jenis): void
    {
        abort_unless(in_array($jenis, ['pendapatan', 'perbelanjaan'], true), 404);
    }

    /**
     * Kategori untuk dropdown: utamakan sub-kategori (paling spesifik),
     * dan induk yang tiada anak. Induk yang ada anak jadi optgroup di view.
     */
    private function selectableCategories(string $jenis)
    {
        return AccountCategory::where('jenis', $jenis)->aktif()
            ->with(['children' => fn ($q) => $q->aktif()])
            ->utama()
            ->orderBy('susunan')->orderBy('nama')
            ->get();
    }

    private function validateEntry(Request $request, string $jenis): array
    {
        return $request->validate([
            'category_id'       => ['required', 'exists:account_categories,id'],
            'member_id'         => ['nullable', 'exists:members,id'],
            'amaun'             => ['required', 'numeric', 'min:0.01'],
            'tarikh'            => ['required', 'date'],
            'rujukan'           => ['nullable', 'string', 'max:60'],
            'penerima_pembayar' => ['nullable', 'string', 'max:150'],
            'keterangan'        => ['nullable', 'string'],
        ]);
    }
}
