<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('free_invoices', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('free_invoiceable'); //  


            // بيانات المستفيد
            $table->string('beneficiary_name')->nullable();
            $table->string('beneficiary_address')->nullable();
            $table->string('beneficiary_tax_number')->nullable();
            $table->string('beneficiary_phone')->nullable();
            $table->string('beneficiary_email')->nullable();

            // عناصر الفاتورة (json)
            $table->json('items'); // [ {name, quantity, price}, {...} ]

            // الإجمالي
            $table->decimal('total', 12, 2)->default(0);

            // التواريخ
            $table->date('issue_date');
            $table->date('due_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_invoices');
    }
};
