<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Peruntukan tabung bagi setiap run (dinamik — admin boleh tambah/buang).
     * jenis_kira = peratus : nilai 25 bermaksud 25% daripada untung bersih asal
     * jenis_kira = amaun   : nilai 5000 bermaksud RM5,000 tetap
     */
    public function up(): void
    {
        Schema::create('dividend_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dividend_run_id')->constrained()->cascadeOnDelete();
            $table->string('nama_tabung', 120);
            $table->enum('jenis_kira', ['peratus', 'amaun'])->default('peratus');
            $table->decimal('nilai', 14, 2)->default(0);   // % atau RM, ikut jenis_kira
            $table->decimal('amaun', 14, 2)->default(0);   // hasil dikira (RM)
            $table->unsignedInteger('susunan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividend_allocations');
    }
};
