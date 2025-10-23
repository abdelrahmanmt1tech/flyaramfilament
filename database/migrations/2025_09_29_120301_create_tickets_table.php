<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $t) {

            $t->id();

            // تعاريف عامة
            $t->string('gds')->nullable();                   // Amadeus (1A)
            $t->string('airline_name')->nullable();          // SAUDI ARABIAN AIRLINES
            $t->string('validating_carrier_code', 2)->nullable(); // SV/NP/FZ/NE...

            // أرقام التذكرة
            $t->string('ticket_number_full', 20)->nullable();   // 0653000...
            $t->string('ticket_number_prefix', 3)->nullable();  // 065
            $t->string('ticket_number_core', 10)->nullable();   // 3000...
            $t->string('pnr', 10)->nullable();                  // 7XRO7M...

            // تواريخ
            $t->date('issue_date')->nullable();
            $t->date('booking_date')->nullable();

            // خصائص التذكرة
            $t->string('ticket_type')->nullable();         // تذكرة مؤكدة...
            $t->string('ticket_type_code', 10)->nullable(); // TKT/VOID/...
            $t->string('trip_type')->nullable();           // ONE-WAY / ROUND-TRIP / MULTI-SEG
            $t->boolean('is_domestic_flight')->nullable();

            // itinerary
            $t->string('itinerary_string')->nullable();    // JED/URY أو "RUH/IST IST/RUH"
            $t->string('fare_basis_out')->nullable();
            $t->string('fare_basis_in')->nullable();

            // مرجع MUC1A
            $t->string('branch_code')->nullable();         // 0101
            $t->string('office_id')->nullable();           // ULHS22220
            $t->string('created_by_user')->nullable();     // 2202U2AS

            // ربطات اختيارية
            $t->foreignId('airline_id')->nullable()->constrained('airlines')->nullOnDelete(); // validating airline لو حبيت
            $t->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $t->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $t->foreignId('sales_user_id')->nullable()->constrained('users')->nullOnDelete(); // وكيل/مندوب (User by code)
            $t->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $t->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $t->foreignId('franchise_id')->nullable()->constrained('franchises')->nullOnDelete();
            $t->foreignId('tax_type_id')->nullable()->constrained('tax_types')->nullOnDelete();


            // التسعير (التكلفة)
            $t->decimal('cost_base_amount', 12, 2)->nullable();    // السعر بدون الضرائب (من K- إن توفر)
            $t->decimal('cost_tax_amount', 12, 2)->nullable();     // مجموع الضرائب (XT)
            $t->decimal('cost_total_amount', 12, 2)->nullable();   // شامل الضرائب (عادة total)


            // التسعير (البيع)
            $t->decimal('profit_amount', 12, 2)->nullable();       // يضيفه الأدمن
            $t->decimal('discount_amount', 12, 2)->nullable();     // يخصمه الأدمن
            $t->decimal('extra_tax_amount', 12, 2)->nullable();    // ضرائب إضافية مجمّعة (لو مش عايز pivot)
            $t->decimal('sale_total_amount', 12, 2)->nullable();   // النهائي = cost_total + extra_tax + profit - discount (افتراضيًا = cost_total)


            // Carrier PNR (اختياري)
            $t->string('carrier_pnr_carrier', 2)->nullable();      // SV/NP...
            $t->string('carrier_pnr', 10)->nullable();             // 9Q2TWU...

            $t->json('price_taxes_breakdown')->nullable();         // تفصيل الضرائب (KFTF/TAX) إن رغبت

            $t->timestamps();
            $t->softDeletes();

            $t->index(['ticket_number_prefix','ticket_number_core']);
            $t->index(['pnr']);


            // ////
            $t->boolean('is_invoiced')->default(false);
            $t->boolean('is_refunded')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
