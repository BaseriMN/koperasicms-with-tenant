<?php

namespace App\Http\Controllers;

use App\Models\AccountEntry;
use App\Models\DividendRun;
use App\Models\Loan;
use App\Models\Meeting;
use App\Models\OwnershipTransfer;
use App\Models\ShareTransfer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogController extends Controller
{
    /**
     * Halaman log aktiviti — himpun semua proses dari rekod sedia ada.
     * Tidak menulis apa-apa; hanya membaca (lite).
     */
    public function index(Request $request)
    {
        $log = $this->kumpulLog($request);

        // Pagination manual atas Collection
        $perPage = 40;
        $page    = $request->integer('page', 1);
        $items   = $log->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items, $log->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('log.index', ['log' => $paginator]);
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $log = $this->kumpulLog($request);
        $namaFail = 'log-aktiviti-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($log) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tarikh & Masa', 'Modul', 'Aktiviti', 'Butiran', 'Oleh']);
            foreach ($log as $row) {
                fputcsv($out, [
                    $row['masa']?->format('Y-m-d H:i') ?? '',
                    $row['modul'], $row['aktiviti'], $row['butiran'], $row['oleh'],
                ]);
            }
            fclose($out);
        }, $namaFail, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$namaFail}\"",
        ]);
    }

    /**
     * Himpun log dari semua sumber. Had setiap sumber supaya lite.
     */
    private function kumpulLog(Request $request): Collection
    {
        $had = 500; // had setiap sumber (elak beban)
        $log = collect();

        // Transaksi saham/simpanan
        foreach (Transaction::with(['member', 'recorder'])->latest()->limit($had)->get() as $t) {
            $log->push([
                'masa'     => $t->created_at,
                'modul'    => 'Transaksi',
                'aktiviti' => ucfirst($t->jenis) . ' ' . ucfirst($t->arah),
                'butiran'  => ($t->member->no_ahli ?? '—') . ' · RM ' . number_format($t->amaun, 2),
                'oleh'     => $t->recorder->name ?? '—',
            ]);
        }

        // Pindah saham
        foreach (ShareTransfer::with(['processor'])->latest()->limit($had)->get() as $s) {
            $log->push([
                'masa'     => $s->created_at,
                'modul'    => 'Pindah Saham',
                'aktiviti' => 'Pindah milik saham',
                'butiran'  => 'RM ' . number_format($s->amaun, 2),
                'oleh'     => $s->processor->name ?? '—',
            ]);
        }

        // Pindah keahlian
        foreach (OwnershipTransfer::with(['processor'])->latest()->limit($had)->get() as $o) {
            $log->push([
                'masa'     => $o->created_at,
                'modul'    => 'Pindah Keahlian',
                'aktiviti' => 'Pindah milik keahlian',
                'butiran'  => ($o->from_nama ?? '—') . ' → ' . $o->to_nama,
                'oleh'     => $o->processor->name ?? '—',
            ]);
        }

        // Pinjaman — cipta + kelulusan
        foreach (Loan::with(['member', 'pemohon', 'reviewer'])->latest()->limit($had)->get() as $l) {
            $log->push([
                'masa'     => $l->created_at,
                'modul'    => 'Pinjaman',
                'aktiviti' => 'Permohonan direkod',
                'butiran'  => ($l->member->no_ahli ?? '—') . ' · RM ' . number_format($l->amount, 2),
                'oleh'     => $l->pemohon->name ?? '—',
            ]);
            if ($l->reviewed_at && $l->status !== 'pending') {
                $log->push([
                    'masa'     => $l->reviewed_at,
                    'modul'    => 'Pinjaman',
                    'aktiviti' => $l->status === 'approved' ? 'Diluluskan' : 'Ditolak',
                    'butiran'  => ($l->member->no_ahli ?? '—') . ' · RM ' . number_format($l->amount, 2),
                    'oleh'     => $l->reviewer->name ?? '—',
                ]);
            }
        }

        // Dividen
        foreach (DividendRun::with('pengira')->latest()->limit($had)->get() as $d) {
            $log->push([
                'masa'     => $d->created_at,
                'modul'    => 'Dividen',
                'aktiviti' => 'Pengiraan dividen ' . $d->tahun,
                'butiran'  => 'RM ' . number_format($d->jumlah_dividen, 2) . ($d->status === 'dimuktamadkan' ? ' (muktamad)' : ' (draf)'),
                'oleh'     => $d->pengira->name ?? '—',
            ]);
        }

        // Entri akaun
        foreach (AccountEntry::with('recorder')->latest()->limit($had)->get() as $e) {
            $log->push([
                'masa'     => $e->created_at,
                'modul'    => 'Akaun',
                'aktiviti' => ucfirst($e->jenis),
                'butiran'  => 'RM ' . number_format($e->amaun, 2) . ' · ' . ($e->keterangan ?? ''),
                'oleh'     => $e->recorder->name ?? '—',
            ]);
        }

        // Mesyuarat
        foreach (Meeting::with('pencipta')->latest()->limit($had)->get() as $m) {
            $log->push([
                'masa'     => $m->created_at,
                'modul'    => 'Mesyuarat',
                'aktiviti' => 'Mesyuarat direkod',
                'butiran'  => $m->tajuk,
                'oleh'     => optional($m->pencipta)->name ?? '—',
            ]);
        }

        // Penapis tarikh (kalau ada)
        if ($request->dari) {
            $dari = \Illuminate\Support\Carbon::parse($request->dari)->startOfDay();
            $log = $log->filter(fn ($r) => $r['masa'] && $r['masa']->gte($dari));
        }
        if ($request->hingga) {
            $hingga = \Illuminate\Support\Carbon::parse($request->hingga)->endOfDay();
            $log = $log->filter(fn ($r) => $r['masa'] && $r['masa']->lte($hingga));
        }
        if ($request->modul) {
            $log = $log->filter(fn ($r) => $r['modul'] === $request->modul);
        }

        // Susun terkini dahulu
        return $log->sortByDesc(fn ($r) => $r['masa']?->timestamp ?? 0)->values();
    }
}