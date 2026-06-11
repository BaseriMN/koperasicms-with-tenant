<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Seeder;

class MeetingSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (Meeting::exists()) {
            return;
        }

        $oleh = User::value('id');

        $samples = [
            [
                'tajuk'  => 'Mesyuarat Agung Tahunan 2024',
                'tarikh' => '2025-06-20',
                'lokasi' => 'Dewan Koperasi',
                'minit'  => 'Pembentangan penyata kewangan teraudit 2024, kelulusan pembahagian dividen, dan pemilihan Anggota Lembaga Koperasi.',
            ],
            [
                'tajuk'  => 'Mesyuarat Jawatankuasa Bulanan',
                'tarikh' => '2025-05-03',
                'lokasi' => 'Bilik Mesyuarat A',
                'minit'  => 'Semakan permohonan pinjaman ahli dan laporan aktiviti perniagaan koperasi.',
            ],
            [
                'tajuk'  => 'Mesyuarat Khas Pelaburan',
                'tarikh' => '2025-07-15',
                'lokasi' => 'Bilik Mesyuarat A',
                'minit'  => null,
            ],
        ];

        foreach ($samples as $s) {
            Meeting::create($s + ['created_by' => $oleh]);
        }
    }
}
