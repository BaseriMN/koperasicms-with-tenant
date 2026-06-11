<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindaan model pengiraan dividen:
     *  - jumlah_saham_anggota : asas saham (auto dari lejar, boleh ubah ikut audit)
     *  - peratus_auditor       : kadar dicadangkan juruaudit luar
     *  - peratus_diluluskan    : kadar diluluskan mesyuarat agung (dipakai untuk kira)
     *  - baki_dibawa_hadapan   : untung boleh agih - jumlah dividen (rekod/paparan)
     *
     * Nota: 'peratus_dividen' lama dikekalkan supaya data lama tidak rosak,
     * tetapi pengiraan baharu guna 'peratus_diluluskan'.
     */
    public function up(): void
    {
        Schema::table('dividend_runs', function (Blueprint $table) {
            $table->decimal('jumlah_saham_anggota', 16, 2)->default(0)->after('untung_bersih');
            $table->decimal('peratus_auditor', 5, 2)->default(0)->after('jumlah_saham_anggota');
            $table->decimal('peratus_diluluskan', 5, 2)->default(0)->after('peratus_auditor');
            $table->decimal('baki_dibawa_hadapan', 16, 2)->default(0)->after('jumlah_dividen');
        });
    }

    public function down(): void
    {
        Schema::table('dividend_runs', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_saham_anggota',
                'peratus_auditor',
                'peratus_diluluskan',
                'baki_dibawa_hadapan',
            ]);
        });
    }
};