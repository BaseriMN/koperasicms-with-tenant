<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * tarikh_mula = permulaan tahun kewangan (cth 1 Julai tahun sebelum).
     * Untuk rekod & paparan penyata sahaja — pengiraan baki tetap guna tarikh_cutoff.
     */
    public function up(): void
    {
        Schema::table('dividend_runs', function (Blueprint $table) {
            $table->date('tarikh_mula')->nullable()->after('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('dividend_runs', function (Blueprint $table) {
            $table->dropColumn('tarikh_mula');
        });
    }
};