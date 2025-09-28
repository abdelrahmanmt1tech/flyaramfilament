<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classification_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classification_id')->constrained('classifications');
            $table->morphs('classifiable'
            ,'index_classd'); // classifiable_id + classifiable_type
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classification_assignments');
    }
};
