<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sejarah pindah milik KEAHLIAN (nombor ahli kekal, pemilik bertukar).
     * Menyimpan snapshot pemilik lama & maklumat pemilik baru.
     */
    public function up(): void
    {
        Schema::create('ownership_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();

            // Pemilik lama (snapshot)
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_nama')->nullable();

            // Pemilik baru
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('to_nama');
            $table->string('to_no_kp', 20)->nullable();

            $table->string('sebab', 100)->nullable();        // cth: Kematian, Serahan, Hadiah
            $table->date('tarikh_pindah');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ownership_transfers');
    }
};
