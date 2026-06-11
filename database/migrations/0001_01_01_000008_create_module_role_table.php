<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyimpan akses modul bagi setiap peranan.
     * Kewujudan baris = peranan tersebut DIBENARKAN akses modul itu.
     */
    public function up(): void
    {
        Schema::create('module_role', function (Blueprint $table) {
            $table->string('module_key', 50);
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['module_key', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_role');
    }
};
