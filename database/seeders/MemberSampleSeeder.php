<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MemberSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (Member::count() > 10) {
            return; // sudah ada data pukal — elak duplikat
        }

        $jumlah = 1000;

        $namaDepan = ['Ahmad','Muhammad','Mohd','Siti','Nur','Nurul','Abdul','Lim','Tan','Lee','Wong','Rajesh','Suresh','Kavitha','Faizal','Aisyah','Hafiz','Zainab','Khairul','Farah','Aziz','Hassan','Ismail','Yusof','Razak','Aminah','Halimah','Salmah','Chong','Kumar'];
        $namaBelakang = ['bin Ali','bin Abdullah','binti Rahman','bin Hassan','binti Ismail','a/l Kumar','a/p Samy','Wei Chong','Mei Ling','bin Omar','binti Yusof','bin Karim','binti Salleh','a/l Raju','Ah Kaw','bin Bakar','binti Daud','bin Zakaria','binti Musa','a/p Devi'];
        $bandar = ['Kuala Lumpur','Shah Alam','Petaling Jaya','Klang','Kajang','Seremban','Ipoh','Johor Bahru','Melaka','Kuantan'];
        $statusPool = array_merge(array_fill(0, 92, 'aktif'), array_fill(0, 5, 'tidak_aktif'), array_fill(0, 3, 'berhenti'));

        $now = now()->toDateTimeString();
        $members = [];

        for ($i = 1; $i <= $jumlah; $i++) {
            $sertai = Carbon::create(2019, 1, 1)->addDays(random_int(0, 2190));

            $members[] = [
                'no_ahli'       => 'A' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'user_id'       => null,
                'nama'          => $namaDepan[array_rand($namaDepan)] . ' ' . $namaBelakang[array_rand($namaBelakang)],
                'no_kp'         => $this->randomKp(),
                'telefon'       => '01' . random_int(0, 9) . '-' . random_int(2000000, 9999999),
                'alamat'        => 'No. ' . random_int(1, 300) . ', Jalan ' . random_int(1, 30) . ', ' . $bandar[array_rand($bandar)],
                'tarikh_sertai' => $sertai->toDateString(),
                'status'        => $statusPool[array_rand($statusPool)],
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        foreach (array_chunk($members, 200) as $chunk) {
            DB::table('members')->insert($chunk);
        }

        $rows = DB::table('members')->select('id', 'tarikh_sertai')->get();
        $txns = [];

        foreach ($rows as $m) {
            $sertai = Carbon::parse($m->tarikh_sertai);

            $modal = random_int(5, 100) * 100;
            $baki = $modal;
            $tarikhModal = (clone $sertai)->addDays(random_int(5, 60));

            $txns[] = $this->txRow($m->id, 'masuk', $modal, $baki, $tarikhModal, 'Saham permulaan');

            $bilTambah = random_int(0, 3);
            for ($t = 0; $t < $bilTambah; $t++) {
                $tarikh = (clone $tarikhModal)->addDays(random_int(90, 1500));
                if ($tarikh->isFuture()) {
                    continue;
                }
                $tambah = random_int(1, 30) * 100;
                $baki += $tambah;
                $txns[] = $this->txRow($m->id, 'masuk', $tambah, $baki, $tarikh, 'Tambahan saham');
            }

            if (random_int(1, 100) <= 15 && $baki > 1000) {
                $tarikh = (clone $tarikhModal)->addDays(random_int(200, 1800));
                if (! $tarikh->isFuture()) {
                    $keluar = random_int(1, 10) * 100;
                    $baki -= $keluar;
                    $txns[] = $this->txRow($m->id, 'keluar', $keluar, $baki, $tarikh, 'Pengeluaran saham');
                }
            }
        }

        foreach (array_chunk($txns, 300) as $chunk) {
            DB::table('transactions')->insert($chunk);
        }
    }

    private function txRow(int $memberId, string $arah, float $amaun, float $baki, Carbon $tarikh, string $ket): array
    {
        return [
            'member_id'   => $memberId,
            'jenis'       => 'saham',
            'arah'        => $arah,
            'amaun'       => $amaun,
            'baki'        => $baki,
            'sumber'      => $arah === 'masuk' ? 'deposit' : 'pengeluaran',
            'rujukan'     => null,
            'keterangan'  => $ket,
            'recorded_by' => null,
            'created_at'  => $tarikh->toDateTimeString(),
            'updated_at'  => $tarikh->toDateTimeString(),
        ];
    }

    private function randomKp(): string
    {
        $thn = str_pad((string) random_int(60, 99), 2, '0', STR_PAD_LEFT);
        $bln = str_pad((string) random_int(1, 12), 2, '0', STR_PAD_LEFT);
        $hr  = str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT);
        return "{$thn}{$bln}{$hr}-" . random_int(1, 14) . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}