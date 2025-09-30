<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained('passengers')->cascadeOnDelete();

            // لكل راكب رقم تذكرته (لو مختلف)
            $table->string('ticket_number_full', 20)->nullable();
            $table->string('ticket_number_prefix', 3)->nullable();
            $table->string('ticket_number_core', 10)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ticket_id','passenger_id'], 'ticket_passengers_unique');
            $table->index(['passenger_id']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_passengers');
    }
};
