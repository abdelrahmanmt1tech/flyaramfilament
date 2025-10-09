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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->morphs('invoiceable'); // invoiceable_id + invoiceable_type

            // Invoice details
            $table->enum('type', ['sale', 'purchase', 'refund']);
            $table->boolean('is_drafted')->default(true);
        
            // Totals
            $table->decimal('total_taxes', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
        
            // رقم الفاتورة
            $table->string('invoice_number')->unique();
            $table->string('reference_num')->nullable();

            $table->text('notes')->nullable();

            $table->date('due_date')->nullable()->default(now());

            $table->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete();

        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
