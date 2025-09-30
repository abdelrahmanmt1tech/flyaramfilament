<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('airport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_airport_id')->nullable()->constrained('airports')->nullOnDelete();
            $table->foreignId('destination_airport_id')->nullable()->constrained('airports')->nullOnDelete();
            $table->string('display_name')->nullable(); // مثال: "JED - RUH" أو "جدة - الرياض"
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['origin_airport_id','destination_airport_id'], 'route_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airport_routes');
    }
};
