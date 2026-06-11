<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Akses lalai mengikut Module Access Matrix asal.
        // (super-user tidak perlu — sentiasa penuh secara automatik)
        $defaults = [
            'admin'    => ['pengurusan_staff', 'simpanan_saham', 'mesyuarat_minit'],
            'pengurus' => ['permohonan_pinjaman', 'simpanan_saham', 'mesyuarat_minit'],
            'kerani'   => ['pengurusan_staff', 'pengurusan_member', 'simpanan_saham', 'mesyuarat_minit'],
            'jk'       => ['mesyuarat_minit'],
            'auditor'  => ['laporan_audit'],
            'ahli'     => ['permohonan_pinjaman'],
        ];

        foreach ($defaults as $slug => $modules) {
            $role = Role::where('slug', $slug)->first();
            if (! $role) {
                continue;
            }

            foreach ($modules as $key) {
                DB::table('module_role')->updateOrInsert(
                    ['role_id' => $role->id, 'module_key' => $key],
                    ['role_id' => $role->id, 'module_key' => $key]
                );
            }
        }
    }
}
