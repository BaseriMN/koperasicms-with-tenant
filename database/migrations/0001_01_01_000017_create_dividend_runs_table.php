<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu rekod pengiraan dividen bagi satu tahun.
     * status: draf = boleh ubah & kira semula; dimuktamadkan = dikunci + dah masuk lejar/akaun.
     */
    public function up(): void
    {
        Schema::create('dividend_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');               // tahun dividen (cth 2025)
            $table->date('tarikh_cutoff');                       // saham selepas tarikh ini tak dikira
            $table->decimal('untung_bersih', 14, 2)->default(0); // selepas audit
            $table->decimal('jumlah_peruntukan', 14, 2)->default(0); // jumlah semua tabung
            $table->decimal('untung_boleh_agih', 14, 2)->default(0); // untung_bersih - peruntukan
            $table->decimal('peratus_dividen', 5, 2)->default(0);    // % daripada untung boleh agih
            $table->decimal('jumlah_dividen', 14, 2)->default(0);    // amaun sebenar diagih
            $table->enum('status', ['draf', 'dimuktamadkan'])->default('draf');
            $table->date('tarikh_muktamad')->nullable();
            $table->foreignId('dikira_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique('tahun'); // satu run per tahun
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividend_runs');
    }
};
