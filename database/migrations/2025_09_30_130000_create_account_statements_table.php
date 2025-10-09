<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_statements', function (Blueprint $table) {
            $table->id();
        
            // Polymorphic relationship fields (creates columns + index automatically)
            $table->morphs('statementable');
        
            // Account statement specific fields
            $table->date('date');
            $table->string('doc_no')->nullable();
            $table->string('ticket_id')->nullable(); // Ticket reference/ID
            $table->string('lpo_no')->nullable(); // LPO (Local Purchase Order) number
            $table->string('sector')->nullable();
            $table->enum('type',['sale','refund'])->default('sale');
        
            // Financial fields
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);

            // $table->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete();
            
        
            $table->timestamps();
        
            // Extra indexes
            $table->index('date');
            $table->index('doc_no');
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('account_statements');
    }
};
