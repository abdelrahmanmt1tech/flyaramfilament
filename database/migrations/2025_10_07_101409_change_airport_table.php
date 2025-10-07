<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('airports', function (Blueprint $table) {
            $table->boolean("is_internal")->default(false);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string("iata_code")->nullable();
        });

    }

    public function down(): void
    {

        Schema::table('airports', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('iata_code');
        });

    }
};
