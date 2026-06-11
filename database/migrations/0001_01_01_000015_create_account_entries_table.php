<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rekod sebenar pendapatan / perbelanjaan.
     * 'jenis' disalin dari kategori untuk pertanyaan & laporan yang laju
     * (elak join ke categories setiap kali jumlah dikira).
     * 'member_id' pilihan — cth yuran ahli boleh dikaitkan dengan ahli.
     */
    public function up(): void
    {
        Schema::create('account_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('account_categories')->restrictOnDelete();
            $table->enum('jenis', ['pendapatan', 'perbelanjaan']);  // denormalize utk laju
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->decimal('amaun', 14, 2);
            $table->date('tarikh');
            $table->string('rujukan', 60)->nullable();       // no resit/baucar
            $table->string('penerima_pembayar', 150)->nullable(); // kepada/daripada
            $table->text('keterangan')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jenis', 'tarikh']);
            $table->index('category_id');
            $table->index('member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_entries');
    }
};
