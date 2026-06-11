<?php

namespace App\Http\Controllers;

use App\Models\AccountCategory;
use App\Models\AccountEntry;
use Illuminate\Http\Request;

class AccountReportController extends Controller
{
    /**
     * Penyata Untung Rugi: pendapatan vs perbelanjaan, dipecahkan ikut kategori.
     */
    public function untungRugi(Request $request)
    {
        $dari   = $request->input('dari', now()->startOfYear()->toDateString());
        $hingga = $request->input('hingga', now()->toDateString());

        $pendapatan = $this->breakdown('pendapatan', $dari, $hingga);
        $perbelanjaan = $this->breakdown('perbelanjaan', $dari, $hingga);

        $jumlahPendapatan = $pendapatan->sum('total');
        $jumlahPerbelanjaan = $perbelanjaan->sum('total');
        $lebihan = wang($jumlahPendapatan - $jumlahPerbelanjaan);

        return view('akaun.penyata', compact(
            'pendapatan', 'perbelanjaan',
            'jumlahPendapatan', 'jumlahPerbelanjaan', 'lebihan',
            'dari', 'hingga'
        ));
    }

    /**
     * Pecahan jumlah ikut kategori UTAMA (sub digabung ke induk) — laju & kemas.
     * Pulangan: collection of ['nama' => ..., 'total' => ...]
     */
    private function breakdown(string $jenis, string $dari, string $hingga)
    {
        // Jumlah per category_id dalam tempoh
        $totals = AccountEntry::where('jenis', $jenis)
            ->whereBetween('tarikh', [$dari, $hingga])
            ->selectRaw('category_id, SUM(amaun) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        if ($totals->isEmpty()) {
            return collect();
        }

        $categories = AccountCategory::where('jenis', $jenis)
            ->whereIn('id', $totals->keys())
            ->with('parent')
            ->get();

        // Kumpul ke kategori induk
        $byParent = [];
        foreach ($categories as $cat) {
            $induk = $cat->parent?->nama ?? $cat->nama;
            $byParent[$induk] = wang(($byParent[$induk] ?? 0) + (float) $totals[$cat->id]);
        }

        return collect($byParent)
            ->map(fn ($total, $nama) => ['nama' => $nama, 'total' => $total])
            ->sortByDesc('total')
            ->values();
    }
}
