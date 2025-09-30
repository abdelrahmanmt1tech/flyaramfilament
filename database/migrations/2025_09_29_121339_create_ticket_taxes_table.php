<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('amount');
            $table->string('code');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('ticket_id')->constrained('tickets');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_taxes');
    }
};
