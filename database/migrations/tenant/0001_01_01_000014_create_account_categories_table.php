<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kategori akaun (dinamik). User boleh tambah & buat sub-kategori.
     * jenis: pendapatan | perbelanjaan
     * parent_id: NULL = kategori utama; jika diisi = sub-kategori.
     */
    public function up(): void
    {
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('account_categories')->cascadeOnDelete();
            $table->enum('jenis', ['pendapatan', 'perbelanjaan']);
            $table->string('nama', 120);
            $table->string('kod', 30)->nullable();           // kod akaun (cth: P-001) — pilihan
            $table->boolean('berulang')->default(false);     // true = bulanan/tetap (cth yuran)
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->unsignedInteger('susunan')->default(0);  // untuk susun paparan
            $table->timestamps();

            $table->index(['jenis', 'is_active']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_categories');
    }
};
