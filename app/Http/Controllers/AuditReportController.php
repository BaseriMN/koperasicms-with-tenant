<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;


class AuditReportController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->hasAnyRole(['auditor', 'super-user'])) {
            abort(403);
        }

        $simpananMasuk = (float) Transaction::where('jenis', 'simpanan')->where('arah', 'masuk')->sum('amaun');
        $simpananKeluar = (float) Transaction::where('jenis', 'simpanan')->where('arah', 'keluar')->sum('amaun');
        $sahamMasuk = (float) Transaction::where('jenis', 'saham')->where('arah', 'masuk')->sum('amaun');
        $sahamKeluar = (float) Transaction::where('jenis', 'saham')->where('arah', 'keluar')->sum('amaun');

        $stats = [
            'simpanan'        => wang($simpananMasuk - $simpananKeluar),
            'saham'           => wang($sahamMasuk - $sahamKeluar),
            'pinjaman_lulus'  => wang((float) Loan::where('status', 'approved')->sum('amount')),
            'rekod_transaksi' => Transaction::count(),
            'pinjaman_pending'=> Loan::where('status', 'pending')->count(),
        ];

        // Lejar terkini untuk jadual audit
        $records = Transaction::with('member')->latest()->limit(20)->get();

        return view('audit.index', compact('stats', 'records'));
    }

    /**
     * Export semua transaksi lejar ke CSV (streamed — jimat memori untuk data besar).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        if (! $request->user()->hasAnyRole(['auditor', 'super-user'])) {
            abort(403);
        }

        $namaFail = 'laporan-audit-lejar-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$namaFail}\"",
        ];

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            // BOM supaya Excel kenal UTF-8 (aksara Melayu betul)
            fwrite($out, "\xEF\xBB\xBF");

            // Tajuk lajur
            fputcsv($out, ['Tarikh', 'No. Ahli', 'Nama Ahli', 'Jenis', 'Arah', 'Amaun (RM)', 'Baki (RM)', 'Sumber', 'Rujukan', 'Keterangan']);

            // Stream ikut chunk supaya tak makan memori untuk ribuan rekod
            Transaction::with('member')->orderBy('created_at')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        $t->created_at?->format('Y-m-d'),
                        $t->member->no_ahli ?? '',
                        $t->member->nama ?? '',
                        ucfirst($t->jenis),
                        ucfirst($t->arah),
                        number_format((float) $t->amaun, 2, '.', ''),
                        number_format((float) $t->baki, 2, '.', ''),
                        $t->sumber ?? '',
                        $t->rujukan ?? '',
                        $t->keterangan ?? '',
                    ]);
                }
            });

            fclose($out);
        }, $namaFail, $headers);
    }
}

