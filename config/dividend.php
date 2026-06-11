<?php

/*
|--------------------------------------------------------------------------
| Tetapan Dividen — Peruntukan Tabung Lalai (rujukan SKM)
|--------------------------------------------------------------------------
| Nilai ini hanya PRA-ISI semasa run dividen baharu dicipta. Admin boleh
| ubah, buang, atau tambah tabung sendiri melalui borang.
|
| Rujukan: Akta Koperasi 1993 (Seksyen 57) & arahan SKM —
|   - Kumpulan Wang Rizab Berkanun: minimum 25% untung bersih
|   - Sumbangan Kumpulan Wang Amanah Pendidikan Koperasi (KWAPK): 2%
| Kadar sebenar tertakluk kepada arahan SKM semasa & undang-undang kecil koperasi.
*/

return [

    'peratus_dividen_default' => 7.00, // % daripada untung boleh agih

    'tabung_default' => [
        ['nama_tabung' => 'Kumpulan Wang Rizab Statutori', 'jenis_kira' => 'peratus', 'nilai' => 12, 'susunan' => 1],
        ['nama_tabung' => 'Kumpulan Wang Amanah Pendidikan Koperasi', 'jenis_kira' => 'peratus', 'nilai' => 2,  'susunan' => 2],
        ['nama_tabung' => 'Kumpulan Wang Pembangunan', 'jenis_kira' => 'peratus', 'nilai' => 1,  'susunan' => 3],
        ['nama_tabung' => 'Peruntukan Cukai', 'jenis_kira' => 'peratus', 'nilai' => 2.5,  'susunan' => 4],
    ],
];
