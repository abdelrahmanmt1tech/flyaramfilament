<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // الجهة المرتبطة (عميل، مورد، فرع ... إلخ)
            $table->morphs('paymentable'); // paymentable_type + paymentable_id

            // طريقة الدفع
            $table->string('payment_method')->comment('مثال: نقدي، تحويل بنكي، بطاقة ...');

            // المبلغ
            $table->decimal('amount', 12, 2);

            // تاريخ الدفع
            $table->date('payment_date');

            // الملاحظات
            $table->text('notes')->nullable();

            // الحساب / الخزينة
            $table->string('account')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
