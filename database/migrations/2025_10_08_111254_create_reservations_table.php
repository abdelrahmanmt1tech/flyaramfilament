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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            
             // مورف للعملاء، برانش، فرانشايز
             $table->nullableMorphs('related'); // سيُنشئ related_id و related_type

             $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();

             $table->foreignId('passenger_id')->nullable()->constrained()->nullOnDelete();

             $table->string('reservation_number')->nullable();
             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
