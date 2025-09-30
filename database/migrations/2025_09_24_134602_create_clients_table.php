<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->text('name') ->nullable();
            $table->string('company_name') ->nullable();
            $table->string('tax_number') ->nullable();
            $table->foreignId('sales_rep_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('lead_source_id')->nullable()
                ->constrained('lead_sources')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
