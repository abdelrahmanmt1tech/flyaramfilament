<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('airlines', function (Blueprint $table) {

            // لو الأعمدة موجودة بالاسم القديم: code, code_string
            if (Schema::hasColumn('airlines','code')) {
                $table->renameColumn('code', 'iata_prefix'); // "06" → "006" بعد باكفِل
            }
            if (Schema::hasColumn('airlines','code_string')) {
                $table->renameColumn('code_string', 'iata_code'); // "SU"
            }

            // أعمدة إضافية/تصحيحات
            if (!Schema::hasColumn('airlines','icao_code')) {
                $table->string('icao_code', 3)->nullable()->after('iata_code');
            }

            // أطوال مناسبة وفهارس
            $table->string('iata_prefix', 3)->nullable()->change(); // نص بطول 3
            $table->string('iata_code', 2)->nullable()->change();   // حرفان

            $table->index('iata_code');
            $table->index('iata_prefix');



        });
    }

    public function down(): void
    {
        Schema::table('airlines', function (Blueprint $table) {
            // رجّع الأسماء لو احتجت
            if (Schema::hasColumn('airlines','iata_prefix')) {
                $table->renameColumn('iata_prefix', 'code');
            }
            if (Schema::hasColumn('airlines','iata_code')) {
                $table->renameColumn('iata_code', 'code_string');
            }
            if (Schema::hasColumn('airlines','icao_code')) {
                $table->dropColumn('icao_code');
            }
        });
    }
};
