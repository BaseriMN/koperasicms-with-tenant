<?php

namespace App\Http\Controllers;

use App\Models\DividendAllocation;
use App\Models\DividendRun;
use App\Models\DividendShare;
use App\Services\DividendService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DividendController extends Controller
{
    public function __construct(private DividendService $service)
    {
    }

    /**
     * Senarai semua run dividen.
     */
    public function index()
    {
        $runs = DividendRun::withCount('shares')->latest('tahun')->paginate(15);

        return view('akaun.dividen.index', compact('runs'));
    }

    /**
     * Borang cipta run baharu.
     */
    public function create()
    {
        $tahunCadang  = now()->year - 1;                  // biasanya kira untuk tahun lalu
        $mulaCadang   = $tahunCadang . '-01-01';          // 1 Jan tahun kewangan
        $cutoffCadang = $tahunCadang . '-12-31';          // 31 Dis (hujung tahun kewangan)

        // Auto-kira jumlah saham anggota setakat cutoff cadangan (boleh diubah admin)
        $sahamCadang = $this->service->kiraJumlahSahamSetakat($cutoffCadang);

        return view('akaun.dividen.create', [
            'tahunCadang'   => $tahunCadang,
            'mulaCadang'    => $mulaCadang,
            'cutoffCadang'  => $cutoffCadang,
            'sahamCadang'   => $sahamCadang,
            'tabungDefault' => config('dividend.tabung_default'),
            'peratusDividen'=> config('dividend.peratus_dividen_default'),
        ]);
    }

    /**
     * Simpan run baharu + tabung default + jana bahagian ahli.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun'                => ['required', 'integer', 'min:2000', 'max:2100', 'unique:dividend_runs,tahun'],
            'tarikh_mula'          => ['required', 'date'],
            'tarikh_cutoff'        => ['required', 'date', 'after:tarikh_mula'],
            'untung_bersih'        => ['required', 'numeric', 'min:0'],
            'jumlah_saham_anggota' => ['required', 'numeric', 'min:0'],
            'peratus_auditor'      => ['required', 'numeric', 'min:0', 'max:100'],
            'peratus_diluluskan'   => ['required', 'numeric', 'min:0', 'max:100'],
            'catatan'              => ['nullable', 'string'],
        ], [
            'tarikh_cutoff.after' => 'Tarikh cutoff mesti selepas tarikh mula tahun kewangan.',
        ]);

        $run = DividendRun::create([
            'tahun'                => $data['tahun'],
            'tarikh_mula'          => $data['tarikh_mula'],
            'tarikh_cutoff'        => $data['tarikh_cutoff'],
            'untung_bersih'        => $data['untung_bersih'],
            'jumlah_saham_anggota' => $data['jumlah_saham_anggota'],
            'peratus_auditor'      => $data['peratus_auditor'],
            'peratus_diluluskan'   => $data['peratus_diluluskan'],
            'peratus_dividen'      => $data['peratus_diluluskan'], // selari (data lama)
            'catatan'              => $data['catatan'] ?? null,
            'status'               => 'draf',
            'dikira_oleh'          => $request->user()->id,
        ]);

        // Pra-isi tabung default SKM
        foreach (config('dividend.tabung_default') as $t) {
            $run->allocations()->create($t);
        }

        $this->service->kiraRingkasan($run);
        $this->service->janaBahagianAhli($run);

        return redirect()->route('akaun.dividen.show', $run)
            ->with('success', "Pengiraan dividen tahun {$run->tahun} dicipta.");
    }

    /**
     * Papar butiran run: ringkasan, tabung, bahagian ahli.
     */
    public function show(DividendRun $dividen)
    {
        $dividen->load(['allocations', 'pengira']);
        $shares = $dividen->shares()->with('member')
            ->orderByDesc('amaun_dividen')
            ->paginate(30);

        return view('akaun.dividen.show', compact('dividen', 'shares'));
    }

    /**
     * Kemaskini maklumat asas run (hanya semasa draf).
     */
    public function update(Request $request, DividendRun $dividen)
    {
        abort_if($dividen->isMuktamad(), 403, 'Run telah dimuktamadkan.');

        $data = $request->validate([
            'tarikh_mula'          => ['required', 'date'],
            'tarikh_cutoff'        => ['required', 'date', 'after:tarikh_mula'],
            'untung_bersih'        => ['required', 'numeric', 'min:0'],
            'jumlah_saham_anggota' => ['required', 'numeric', 'min:0'],
            'peratus_auditor'      => ['required', 'numeric', 'min:0', 'max:100'],
            'peratus_diluluskan'   => ['required', 'numeric', 'min:0', 'max:100'],
            'catatan'              => ['nullable', 'string'],
        ], [
            'tarikh_cutoff.after' => 'Tarikh cutoff mesti selepas tarikh mula tahun kewangan.',
        ]);

        $cutoffBerubah = $dividen->tarikh_cutoff->toDateString() !== $data['tarikh_cutoff'];

        $dividen->update($data + [
            'peratus_dividen' => $data['peratus_diluluskan'], // selari (data lama)
        ]);
        $this->service->kiraRingkasan($dividen);

        // Jika cutoff berubah, kira semula saham layak ahli (kecuali yang di-override)
        if ($cutoffBerubah) {
            $this->service->janaBahagianAhli($dividen);
        } else {
            $this->service->agihMengikutSaham($dividen);
        }

        return redirect()->route('akaun.dividen.show', $dividen)
            ->with('success', 'Maklumat dividen dikemaskini.');
    }

    /**
     * Tambah tabung peruntukan (draf sahaja).
     */
    public function tambahTabung(Request $request, DividendRun $dividen)
    {
        abort_if($dividen->isMuktamad(), 403);

        $data = $request->validate([
            'nama_tabung' => ['required', 'string', 'max:120'],
            'jenis_kira'  => ['required', Rule::in(['peratus', 'amaun'])],
            'nilai'       => ['required', 'numeric', 'min:0'],
        ]);

        $dividen->allocations()->create($data + ['susunan' => ($dividen->allocations()->max('susunan') ?? 0) + 1]);
        $this->service->kiraRingkasan($dividen);
        $this->service->agihMengikutSaham($dividen);

        return back()->with('success', 'Tabung ditambah.');
    }

    /**
     * Buang satu tabung (draf sahaja).
     */
    public function buangTabung(DividendRun $dividen, DividendAllocation $tabung)
    {
        abort_if($dividen->isMuktamad(), 403);
        abort_unless($tabung->dividend_run_id === $dividen->id, 404);

        $tabung->delete();
        $this->service->kiraRingkasan($dividen);
        $this->service->agihMengikutSaham($dividen);

        return back()->with('success', 'Tabung dibuang.');
    }

    /**
     * Override saham layak seorang ahli (draf sahaja).
     */
    public function overrideSaham(Request $request, DividendRun $dividen, DividendShare $bahagian)
    {
        abort_if($dividen->isMuktamad(), 403);
        abort_unless($bahagian->dividend_run_id === $dividen->id, 404);

        $data = $request->validate([
            'saham_layak' => ['required', 'numeric', 'min:0'],
        ]);

        $this->service->overrideSaham($bahagian, (float) $data['saham_layak']);

        return back()->with('success', 'Saham layak ahli dikemaskini.');
    }

    /**
     * Muktamadkan run — rekod perbelanjaan + agih ke lejar saham ahli.
     */
    public function muktamad(DividendRun $dividen, Request $request)
    {
        abort_if($dividen->isMuktamad(), 403, 'Run telah dimuktamadkan.');

        if ($dividen->jumlah_dividen <= 0) {
            return back()->with('error', 'Jumlah dividen mesti lebih daripada sifar sebelum dimuktamadkan.');
        }

        $this->service->muktamad($dividen, $request->user()->id);

        return redirect()->route('akaun.dividen.show', $dividen)
            ->with('success', "Dividen tahun {$dividen->tahun} dimuktamadkan & diagihkan ke saham ahli.");
    }

    /**
     * Penyata dividen seorang ahli (untuk cetak).
     */
    public function penyata(DividendRun $dividen, DividendShare $bahagian)
    {
        abort_unless($bahagian->dividend_run_id === $dividen->id, 404);
        $bahagian->load('member');

        return view('akaun.dividen.penyata', compact('dividen', 'bahagian'));
    }
}
