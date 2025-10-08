<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();
            
            // الربط مع الحجز الأساسي
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            
             // ========== العلاقات العادية ==========
             $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
             $table->foreignId('safes_bank_id')->nullable()->constrained()->nullOnDelete();
             $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
             
             // ========== الحقول المشتركة بين جميع الحجوزات ==========
             $table->string('reservation_type'); // 'hotel', 'car', 'tourism', etc.
             $table->date('date')->nullable();

             $table->string('agent_name')->nullable();
             $table->string('branch')->nullable();
             $table->string('destination')->nullable();
             $table->text('special_requests')->nullable();
             $table->text('additions')->nullable();
             $table->text('notes')->nullable();
             $table->string('added_value')->nullable();
             $table->decimal('sale_amount', 10, 2)->nullable();
             $table->decimal('purchase_amount', 10, 2)->nullable();
             $table->decimal('commission_amount', 10, 2)->nullable();
             $table->decimal('cash_payment', 10, 2)->nullable();
             $table->decimal('visa_payment', 10, 2)->nullable();
             $table->string('account_number')->nullable();
             $table->date('from_date')->nullable(); // للسيارة فقط
             $table->date('to_date')->nullable(); // للسيارة فقط

             // ========== الحقول الخاصة بحجوزات الفنادق فقط ==========
             $table->string('hotel_name')->nullable(); // للفندق فقط
             $table->string('confirmation_number')->nullable(); // للفندق فقط
             $table->string('room_type')->nullable(); // للفندق فقط
             $table->string('destination_type')->nullable(); // للفندق فقط
             $table->integer('room_count')->nullable(); // للفندق فقط
             $table->date('arrival_date')->nullable(); // للفندق فقط
             $table->integer('nights_count')->nullable(); // للفندق فقط
             $table->date('departure_date')->nullable(); // للفندق فقط
             $table->decimal('room_price', 10, 2)->nullable(); // للفندق فقط
             $table->decimal('total_amount', 10, 2)->nullable(); // للفندق فقط
             
             // ========== الحقول الخاصة بحجوزات السيارات فقط ==========
             $table->string('service_type')->nullable(); // للسيارة فقط
             $table->string('document')->nullable(); // للسيارة فقط
             $table->text('service_details')->nullable(); // للسيارة فقط
             $table->text('additional_info')->nullable(); // للسيارة فقط
             $table->integer('count')->nullable(); // للسيارة فقط
             $table->decimal('unit_price', 10, 2)->nullable(); // للسيارة فقط
             
             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};
