<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maklumat kelulusan mesyuarat ALK untuk pinjaman.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('meeting_id')->nullable()->after('status')->constrained('meetings')->nullOnDelete();
            $table->foreignId('pencadang_id')->nullable()->after('meeting_id')->constrained('members')->nullOnDelete();
            $table->foreignId('penyokong_id')->nullable()->after('pencadang_id')->constrained('members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['meeting_id']);
            $table->dropForeign(['pencadang_id']);
            $table->dropForeign(['penyokong_id']);
            $table->dropColumn(['meeting_id', 'pencadang_id', 'penyokong_id']);
        });
    }
};