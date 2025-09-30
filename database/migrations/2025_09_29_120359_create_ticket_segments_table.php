<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_segments', function (Blueprint $table) {

            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();

            $table->unsignedInteger('segment_index')->nullable();

            $table->foreignId('origin_airport_id')->nullable()->constrained('airports')->nullOnDelete();
            $table->foreignId('destination_airport_id')->nullable()->constrained('airports')->nullOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('airport_routes')->nullOnDelete();
            $table->foreignId('carrier_airline_id')->nullable()->constrained('airlines')->nullOnDelete();

            $table->string('carrier_code', 2)->nullable();  // SV, FZ...
            $table->string('flight_number')->nullable();    // 1291

            $table->string('booking_class', 2)->nullable(); // Q, N, U...
            $table->string('status', 10)->nullable();       // OK/HK..
            $table->string('equipment', 10)->nullable();    // 321/7M8...
            $table->string('baggage', 10)->nullable();      // 1PC/30K...
            $table->string('meal', 255)->nullable();          // M/N/V...

            $table->dateTime('departure_at')->nullable();
            $table->dateTime('arrival_at')->nullable();
            $table->string('origin_country', 2)->nullable();
            $table->string('destination_country', 2)->nullable();

            $table->string('fare_basis')->nullable();       // ربط من M- حسب الترتيب (اختياري)

            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_segments');
    }
};
