<?php

namespace Database\Seeders;

use App\Models\AccountCategory;
use Illuminate\Database\Seeder;

class AccountCategorySeeder extends Seeder
{
    public function run(): void
    {
        /*
         * PENDAPATAN — berdasarkan senarai pengguna + rujukan lazim SKM:
         *  - Yuran & fi keahlian
         *  - Pulangan simpanan tetap & hibah/faedah bank
         *  - Pendapatan aktiviti perniagaan (dengan sub)
         *  - Dividen pelaburan, sewa, faedah pinjaman ahli, lain-lain
         */
        $pendapatan = [
            ['nama' => 'Yuran Ahli (Bulanan)',           'berulang' => true,  'kod' => 'P-YRN-B'],
            ['nama' => 'Yuran Masuk / Pendaftaran',       'berulang' => false, 'kod' => 'P-YRN-M'],
            ['nama' => 'Pendapatan Simpanan Tetap',       'berulang' => false, 'kod' => 'P-STF'],
            ['nama' => 'Hibah / Faedah Bank',             'berulang' => false, 'kod' => 'P-HBH'],
            ['nama' => 'Faedah Pinjaman Ahli',            'berulang' => false, 'kod' => 'P-FPA'],
            ['nama' => 'Dividen Pelaburan',               'berulang' => false, 'kod' => 'P-DVD'],
            ['nama' => 'Sewa Hartanah / Aset',            'berulang' => false, 'kod' => 'P-SWA'],
            ['nama' => 'Pendapatan Lain-lain',            'berulang' => false, 'kod' => 'P-LL'],
            [
                'nama' => 'Pendapatan Aktiviti Perniagaan', 'berulang' => false, 'kod' => 'P-PRN',
                'children' => [
                    ['nama' => 'Perniagaan 1', 'kod' => 'P-PRN-1'],
                    ['nama' => 'Perniagaan 2', 'kod' => 'P-PRN-2'],
                    ['nama' => 'Perniagaan 3', 'kod' => 'P-PRN-3'],
                ],
            ],
        ];

        /*
         * PERBELANJAAN — asas sahaja; selebihnya pengguna boleh tambah sendiri.
         */
        $perbelanjaan = [
            ['nama' => 'Gaji & Elaun Kakitangan', 'kod' => 'B-GJI'],
            ['nama' => 'Sewa & Utiliti',          'kod' => 'B-SWU'],
            ['nama' => 'Dividen Kepada Ahli',     'kod' => 'B-DVD'],
            ['nama' => 'Perbelanjaan Pentadbiran','kod' => 'B-PTB'],
            ['nama' => 'Perbelanjaan Mesyuarat',  'kod' => 'B-MSY'],
        ];

        $this->seedSet('pendapatan', $pendapatan);
        $this->seedSet('perbelanjaan', $perbelanjaan);
    }

    private function seedSet(string $jenis, array $items): void
    {
        foreach ($items as $i => $item) {
            $children = $item['children'] ?? [];
            unset($item['children']);

            $parent = AccountCategory::updateOrCreate(
                ['jenis' => $jenis, 'nama' => $item['nama'], 'parent_id' => null],
                array_merge([
                    'is_active' => true,
                    'berulang'  => $item['berulang'] ?? false,
                    'susunan'   => $i,
                ], $item)
            );

            foreach ($children as $j => $child) {
                AccountCategory::updateOrCreate(
                    ['jenis' => $jenis, 'nama' => $child['nama'], 'parent_id' => $parent->id],
                    array_merge([
                        'is_active' => true,
                        'berulang'  => false,
                        'susunan'   => $j,
                        'parent_id' => $parent->id,
                    ], $child)
                );
            }
        }
    }
}
