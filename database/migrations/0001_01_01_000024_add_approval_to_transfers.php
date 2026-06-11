<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maklumat kelulusan mesyuarat ALK untuk pindah saham & pindah keahlian.
     */
    public function up(): void
    {
        foreach (['share_transfers', 'ownership_transfers'] as $jadual) {
            Schema::table($jadual, function (Blueprint $table) {
                $table->foreignId('meeting_id')->nullable()->after('tarikh_pindah')->constrained('meetings')->nullOnDelete();
                $table->foreignId('pencadang_id')->nullable()->after('meeting_id')->constrained('members')->nullOnDelete();
                $table->foreignId('penyokong_id')->nullable()->after('pencadang_id')->constrained('members')->nullOnDelete();
                $table->text('catatan_kelulusan')->nullable()->after('penyokong_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['share_transfers', 'ownership_transfers'] as $jadual) {
            Schema::table($jadual, function (Blueprint $table) {
                $table->dropForeign(['meeting_id']);
                $table->dropForeign(['pencadang_id']);
                $table->dropForeign(['penyokong_id']);
                $table->dropColumn(['meeting_id', 'pencadang_id', 'penyokong_id', 'catatan_kelulusan']);
            });
        }
    }
};