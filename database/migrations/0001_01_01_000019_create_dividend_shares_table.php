<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bahagian dividen setiap ahli bagi satu run.
     * saham_layak: dikira auto (saham <= cut-off), tetapi boleh override manual.
     */
    public function up(): void
    {
        Schema::create('dividend_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dividend_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('saham_layak', 14, 2)->default(0);  // baki saham layak (boleh override)
            $table->decimal('saham_auto', 14, 2)->default(0);   // nilai asal dikira sistem (rujukan)
            $table->decimal('peratus', 8, 4)->default(0);       // % bahagian ahli
            $table->decimal('amaun_dividen', 14, 2)->default(0);
            $table->boolean('override')->default(false);        // true jika saham_layak diedit manual
            $table->timestamps();

            $table->unique(['dividend_run_id', 'member_id']);
            $table->index('member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividend_shares');
    }
};
