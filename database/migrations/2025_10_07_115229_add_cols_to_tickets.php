<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {

            $table->string("pnr_branch_code")->nullable();
            $table->string("pnr_office_id")->nullable();
            $table->string("issuing_office_id")->nullable();
            $table->string("issuing_carrier")->nullable();
            $table->string("sales_rep")->nullable();

        });


        Schema::table('branches', function (Blueprint $table) {
            $table->string("iata_code")->nullable();
        });








    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn("pnr_branch_code") ;
            $table->dropColumn("pnr_office_id") ;
            $table->dropColumn("issuing_office_id") ;
            $table->dropColumn("issuing_carrier") ;
            $table->dropColumn("sales_rep") ;

        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn("iata_code") ;
        });




    }
};
