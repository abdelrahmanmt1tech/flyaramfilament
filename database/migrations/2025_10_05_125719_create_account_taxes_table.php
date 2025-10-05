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
        Schema::create('account_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // sales_tax, purchase_tax, etc.
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->decimal('tax_value', 12, 2)->nullable();
            $table->foreignId('tax_types_id')->nullable()->constrained('tax_types')->nullOnDelete();
            $table->boolean('is_returned')->default(false);
            $table->string('zakah_id')->nullable();
            $table->json('zakah_response')->nullable();
            $table->string('zakah_status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_taxes');
    }
};
