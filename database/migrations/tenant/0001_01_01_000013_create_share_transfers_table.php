<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sejarah pindah milik SAHAM antara ahli.
     * Setiap pemindahan turut menjana 2 baris dalam 'transactions'
     * (keluar dari pemberi, masuk ke penerima).
     */
    public function up(): void
    {
        Schema::create('share_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('to_member_id')->constrained('members')->cascadeOnDelete();
            $table->decimal('amaun', 12, 2);
            $table->string('sebab', 100)->nullable();
            $table->date('tarikh_pindah');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_transfers');
    }
};
