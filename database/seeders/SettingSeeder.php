<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::putMany([
            'nama_koperasi'   => 'Koperasi Contoh Berhad',
            'nama_pendek'     => 'Koperasi',
            'no_pendaftaran'  => '',          // No pendaftaran SKM
            'logo_path'       => '',          // kosong = guna fallback huruf
            'tema_palet'      => 'emerald_gold',
            'tema_mode'       => 'light',     // light | dark
        ]);
    }
}
