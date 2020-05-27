<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtInvoiceLineDeliveryUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_invoice_line_delivery_uiv', function (Blueprint $table) {
            $table->increments('dist_invoice_line_id');
            $table->string('company')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->integer('item_id')->nullable();
            $table->string('party_type')->nullable();
            $table->string('series_id')->nullable();
            $table->integer('invoice_no')->nullable();
            $table->string('client_state')->nullable();
            $table->integer('identity')->nullable();
            $table->string('name')->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->string('order_no')->nullable();
            $table->integer('line_no')->nullable();
            $table->integer('release_no')->nullable();
            $table->integer('line_item_no')->nullable();
            $table->integer('pos')->nullable();
            $table->string('contract')->nullable();
            $table->string('catalog_no')->nullable();
            $table->string('description')->nullable();
            $table->integer('invoiced_qty')->nullable();
            $table->string('sale_um')->nullable();
            $table->integer('price_conv')->nullable();
            $table->string('price_um')->nullable();
            $table->decimal('sale_unit_price',12,2)->nullable();
            $table->decimal('unit_price_incl_tax',12,2)->nullable();
            $table->string('customer_po_no')->nullable();
            $table->string('rma_no')->nullable();
            $table->string('rma_line_no')->nullable();
            $table->string('rma_charge_no')->nullable();
            $table->string('configuration_id')->nullable();
            $table->integer('delivery_customer')->nullable();
            $table->string('invoice_type')->nullable();
            $table->integer('prel_update_allowed')->nullable();
            $table->string('bonus_part')->nullable();
            $table->string('salesman_code')->nullable();
            $table->string('salesman_name')->nullable();
            $table->string('odering_region')->nullable();
            $table->string('part_no')->nullable();
            $table->string('location_no')->nullable();
            $table->string('lot_batch_no')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('waiv_dev_rej_no')->nullable();
            $table->integer('qty_shipped')->nullable();
            $table->integer('delnote_no')->nullable();
            $table->timestamp('last_updated_on')->nullable();
            $table->timestamp('expiration_date')->nullable();
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
        Schema::dropIfExists('ext_invoice_line_delivery_uiv');
    }
}
