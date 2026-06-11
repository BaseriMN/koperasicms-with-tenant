// database/migrations/xxxx_create_trial_balance_tables.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table untuk rekod pendapatan/perbelanjaan
        Schema::create('account_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('account_categories')->onDelete('restrict');
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->date('entry_date');
            $table->enum('jenis', ['debit', 'credit']); // debit=belanja, credit=pendapatan
            $table->string('description', 255)->nullable();
            $table->string('reference_no', 50)->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['entry_date', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_entries');
    }
};