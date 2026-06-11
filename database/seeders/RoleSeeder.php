<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super User',      'slug' => 'super-user', 'description' => 'Muhamad Baseri – akses penuh sistem'],
            ['name' => 'Admin Koperasi',  'slug' => 'admin',      'description' => 'Urus sistem, ahli, laporan'],
            ['name' => 'Pengurus',        'slug' => 'pengurus',   'description' => 'Lulus permohonan, urus mesyuarat'],
            ['name' => 'Kerani',          'slug' => 'kerani',     'description' => 'Data entry, urusan harian'],
            ['name' => 'Jawatankuasa',    'slug' => 'jk',         'description' => 'Pemantau aktiviti koperasi'],
            ['name' => 'Auditor',         'slug' => 'auditor',    'description' => 'Semak rekod kewangan'],
            ['name' => 'Ahli Biasa',      'slug' => 'ahli',       'description' => 'Akses portal koperasi'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
