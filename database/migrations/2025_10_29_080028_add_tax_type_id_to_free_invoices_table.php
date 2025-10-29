<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('free_invoices', function (Blueprint $table) {
            $table->foreignId('tax_type_id')->nullable()->after('total')->constrained('tax_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('free_invoices', function (Blueprint $table) {
            $table->dropForeign(['tax_type_id']);
            $table->dropColumn('tax_type_id');
        });
    }
};
