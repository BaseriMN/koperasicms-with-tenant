<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,               // 1. Cipta 7 peranan
            PermissionSeeder::class,         // 2. Cipta kebenaran & petakan ke peranan
            SuperUserSeeder::class,          // 3. Cipta Super User (Muhamad Baseri)
            ModuleAccessSeeder::class,       // 4. Akses modul lalai bagi setiap peranan
            AccountCategorySeeder::class,    // 5. Cipta kategori akaun
            SettingSeeder::class,            // 6. Tetapan koperasi (logo, nama, tema)
            //MemberSampleSeeder::class,       // 7. Ahli + saham + simpanan contoh
            //AccountEntrySampleSeeder::class, // 8. Entri pendapatan & perbelanjaan contoh
            //LoanSampleSeeder::class,         // 9. Permohonan pinjaman contoh
            //MeetingSampleSeeder::class,      // 10. Mesyuarat contoh
        ]);
    }
}
