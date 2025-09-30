<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id();

            $table->string('iata', 50)->nullable()->index();   // JED, RUH, ...
            $table->string('name')->nullable();               // JEDDAH KING ABDUL
            $table->string('city')->nullable();               // Jeddah
            $table->string('country_code', 2)->nullable();    // SA, EG, ...
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['iata']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
