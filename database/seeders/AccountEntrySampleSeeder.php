<?php

namespace Database\Seeders;

use App\Models\AccountCategory;
use App\Models\AccountEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountEntrySampleSeeder extends Seeder
{
    public function run(): void
    {
        if (AccountEntry::exists()) {
            return;
        }

        // Peta nama kategori -> id (dicipta oleh AccountCategorySeeder)
        $idPend = AccountCategory::where('jenis', 'pendapatan')->pluck('id', 'nama');
        $idBel  = AccountCategory::where('jenis', 'perbelanjaan')->pluck('id', 'nama');

        $now = now()->toDateTimeString();
        $rows = [];

        // Faktor pertumbuhan setiap tahun (koperasi membesar)
        $faktor = [2022 => 0.82, 2023 => 0.91, 2024 => 1.00];

        foreach ([2022, 2023, 2024] as $tahun) {
            $f = $faktor[$tahun];

            // ---- PENDAPATAN (asas 2024, didarab faktor) ----
            $pendapatan = [
                ['Yuran Ahli (Bulanan)',          120000, 'YRN'],
                ['Yuran Masuk / Pendaftaran',       8000, 'DFT'],
                ['Pendapatan Simpanan Tetap',      18500, 'STF'],
                ['Hibah / Faedah Bank',             3200, 'HBH'],
                ['Faedah Pinjaman Ahli',           96000, 'FPA'],
                ['Dividen Pelaburan',              14500, 'DVD'],
                ['Sewa Hartanah / Aset',           36000, 'SWA'],
                ['Perniagaan 1',                   62000, 'PRN1'],
                ['Perniagaan 2',                   38000, 'PRN2'],
                ['Perniagaan 3',                   15000, 'PRN3'],
            ];

            foreach ($pendapatan as [$nama, $asas, $kodRuj]) {
                if (! isset($idPend[$nama])) {
                    continue;
                }
                // Pecah jadi 12 bulan supaya rekod nampak realistik
                $tahunan = round($asas * $f, 2);
                $rows = array_merge($rows, $this->pecahBulanan(
                    $idPend[$nama], 'pendapatan', $tahunan, $tahun, $kodRuj, $now
                ));
            }

            // ---- PERBELANJAAN ----
            $perbelanjaan = [
                ['Gaji & Elaun Kakitangan',  108000, 'GJI'],
                ['Sewa & Utiliti',            43200, 'SWU'],
                ['Perbelanjaan Pentadbiran',  22000, 'PTB'],
                ['Perbelanjaan Mesyuarat',     9500, 'MSY'],
            ];

            foreach ($perbelanjaan as [$nama, $asas, $kodRuj]) {
                if (! isset($idBel[$nama])) {
                    continue;
                }
                $tahunan = round($asas * $f, 2);
                $rows = array_merge($rows, $this->pecahBulanan(
                    $idBel[$nama], 'perbelanjaan', $tahunan, $tahun, $kodRuj, $now
                ));
            }
        }

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('account_entries')->insert($chunk);
        }
    }

    /**
     * Pecah jumlah tahunan kepada 12 entri bulanan (akhir setiap bulan).
     */
    private function pecahBulanan(int $catId, string $jenis, float $tahunan, int $tahun, string $kodRuj, string $now): array
    {
        $rows = [];
        $sebulan = round($tahunan / 12, 2);
        $terkumpul = 0;

        for ($bln = 1; $bln <= 12; $bln++) {
            // bulan terakhir ambil baki supaya jumlah tepat
            $amaun = $bln === 12 ? round($tahunan - $terkumpul, 2) : $sebulan;
            $terkumpul += $amaun;
            $hari = (int) date('t', mktime(0, 0, 0, $bln, 1, $tahun));
            $tarikh = sprintf('%04d-%02d-%02d', $tahun, $bln, $hari);

            $rows[] = [
                'category_id'       => $catId,
                'jenis'             => $jenis,
                'member_id'         => null,
                'amaun'             => $amaun,
                'tarikh'            => $tarikh,
                'rujukan'           => "{$kodRuj}-{$tahun}-" . str_pad((string) $bln, 2, '0', STR_PAD_LEFT),
                'penerima_pembayar' => null,
                'keterangan'        => null,
                'recorded_by'       => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        return $rows;
    }
}