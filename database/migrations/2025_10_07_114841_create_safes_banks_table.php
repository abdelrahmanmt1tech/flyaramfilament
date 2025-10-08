<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safes_banks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الخزنة أو البنك
            $table->string('type')->nullable(); // safe أو bank
            $table->string('account_number')->nullable(); // رقم الحساب لو بنك
            $table->decimal('balance', 15, 2)->default(0); // الرصيد الحالي
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safes_banks');
    }
};
