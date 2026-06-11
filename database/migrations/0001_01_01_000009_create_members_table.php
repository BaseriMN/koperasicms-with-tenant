<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keahlian koperasi. Nombor ahli (AXXXX) kekal walaupun pindah milik.
     * 'user_id' = pemilik semasa (akaun login). Boleh NULL jika belum ditetapkan.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('no_ahli', 10)->unique();          // AXXXX
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama');                            // nama pemilik semasa
            $table->string('no_kp', 20)->nullable();           // no kad pengenalan
            $table->string('telefon', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->date('tarikh_sertai')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'berhenti'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
