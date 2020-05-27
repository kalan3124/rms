<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_lines', function (Blueprint $table) {
            $table->increments('return_line_id');
            $table->string('company')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->integer('item_id')->nullable();
            $table->string('party_type')->nullable();
            $table->string('series_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('client_state')->nullable();
            $table->integer('identity')->nullable();
            $table->string('name')->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->string('currency')->nullable();
            $table->string('vat_code')->nullable();
            $table->decimal('vat_rate',10,2)->nullable();
            $table->decimal('vat_curr_amount',10,2)->nullable();
            $table->decimal('net_curr_amount',10,2)->nullable();
            $table->decimal('gross_curr_amount',10,2)->nullable();
            $table->decimal('net_dom_amount',10,2)->nullable();
            $table->decimal('vat_dom_amount',10,2)->nullable();
            $table->string('reference')->nullable();
            $table->string('order_no')->nullable();
            $table->integer('line_no')->nullable();
            $table->integer('release_no')->nullable();
            $table->integer('line_item_no')->nullable();
            $table->integer('pos')->nullable();
            $table->string('contract')->nullable();
            $table->string('catalog_no')->nullable();
            $table->string('description')->nullable();
            $table->string('taxable_db')->nullable();
            $table->integer('invoiced_qty')->nullable();
            $table->string('sale_um')->nullable();
            $table->integer('price_conv')->nullable();
            $table->string('price_um')->nullable();
            $table->decimal('sale_unit_price',10,2)->nullable();
            $table->decimal('unit_price_incl_tax',10,3)->nullable();
            $table->decimal('discount',10,2)->nullable();
            $table->decimal('order_discount',10,2)->nullable();
            $table->string('customer_po_no')->nullable();
            $table->string('rma_no')->nullable();
            $table->integer('rma_line_no')->nullable();
            $table->string('rma_charge_no')->nullable();
            $table->decimal('additional_discount',10,2)->nullable();
            $table->string('configuration_id')->nullable();
            $table->integer('delivery_customer')->nullable();
            $table->string('series_reference')->nullable();
            $table->integer('number_reference')->nullable();
            $table->string('invoice_type')->nullable();
            $table->string('prel_update_allowed')->nullable();
            $table->timestamp('man_tax_liability_date')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('prepay_invoice_no')->nullable();
            $table->integer('prepay_invoice_series_id')->nullable();
            $table->integer('assortment_node_id')->nullable();
            $table->decimal('charge_percent',10,2)->nullable();
            $table->decimal('charge_percent_basis',10,2)->nullable();
            $table->string('return_reason_code')->nullable();
            $table->string('return_reason_desc')->nullable();
            $table->decimal('total order line discount %',10,2)->nullable();
            $table->string('city')->nullable();
            $table->string('salesman_code')->nullable();
            $table->string('salesman_name')->nullable();
            $table->string('odering_region')->nullable();
            $table->timestamp('last_updated_on')->nullable();    
            
            $table->unsignedInteger('inv_head_id')->nullable();
            $table->foreign('inv_head_id')->references('inv_head_id')->on('invoice');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_lines');
    }
}
