<?php

namespace Database\Seeders;

use App\Models\DividendRun;
use App\Services\DividendService;
use Illuminate\Database\Seeder;

class DividendSeeder extends Seeder
{
    public function run(): void
    {
        $tahun = 2024;

        // Elak duplikat jika dijalankan semula
        if (DividendRun::where('tahun', $tahun)->exists()) {
            return;
        }

        $service = app(DividendService::class);

        // Cipta run contoh — status kekal DRAF (belum dimuktamadkan)
        $run = DividendRun::create([
            'tahun'           => $tahun,
            'tarikh_cutoff'   => "{$tahun}-06-30",
            'untung_bersih'   => 50000,   // contoh untung bersih selepas audit
            'peratus_dividen' => 80,      // 80% daripada untung boleh agih jadi dividen
            'catatan'         => 'Data contoh — pengiraan dividen tahun ' . $tahun,
            'status'          => 'draf',
            'dikira_oleh'     => null,
        ]);

        // Pra-isi tabung default SKM
        foreach (config('dividend.tabung_default') as $t) {
            $run->allocations()->create($t);
        }

        // Kira ringkasan + agihan ahli (guna logik service sebenar)
        $service->kiraRingkasan($run);
        $service->janaBahagianAhli($run);

        // Nota: sengaja TIDAK dimuktamadkan supaya boleh diuji di view dahulu.
    }
}
