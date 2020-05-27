<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountColumnToDistributorInvoiceLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_invoice_line', function (Blueprint $table) {
            $table->decimal('dil_discount_percent',5,2)->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_invoice_line', function (Blueprint $table) {
            $table->dropColumn('dil_discount_percent');
        });
    }
}
