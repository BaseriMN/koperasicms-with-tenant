<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lejar tunggal untuk semua pergerakan saham & simpanan ahli.
     * Setiap baris menyimpan baki terkini (running balance) selepas transaksi.
     *
     * jenis : saham | simpanan
     * arah  : masuk (kredit) | keluar (debit)
     * sumber: rujukan jenis transaksi (deposit, pengeluaran, pindah_milik, dividen, dll)
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', ['saham', 'simpanan']);
            $table->enum('arah', ['masuk', 'keluar']);
            $table->decimal('amaun', 12, 2);
            $table->decimal('baki', 12, 2);                 // baki selepas transaksi ini
            $table->string('sumber', 30)->default('manual'); // deposit, pengeluaran, pindah_milik, dividen
            $table->string('rujukan', 50)->nullable();       // no rujukan / resit
            $table->text('keterangan')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['member_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
