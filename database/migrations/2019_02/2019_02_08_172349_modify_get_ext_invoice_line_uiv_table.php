<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyGetExtInvoiceLineUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('get_ext_invoice_line_uiv', function (Blueprint $table) {
            $table->string('name')->nullable()->after('identity');
            $table->timestamp('invoice_date')->nullable()->after('name');
            $table->string('currency')->nullable()->after('invoice_date');
            $table->string('vat_code')->nullable()->after('currency');
            $table->double('vat_rate',8,2)->nullable()->after('vat_code');

            $table->double('vat_curr_amount',8,2)->nullable()->after('vat_rate');
            $table->double('net_curr_amount',8,2)->nullable()->after('vat_curr_amount');
            $table->double('gross_curr_amount',8,2)->nullable()->after('net_curr_amount');
            $table->double('net_dom_amount',8,2)->nullable()->after('gross_curr_amount');
            $table->double('vat_dom_amount',8,2)->nullable()->after('net_dom_amount');

            $table->string('reference')->nullable()->after('vat_dom_amount');
            $table->string('order_no')->nullable()->after('reference');

            $table->integer('line_no')->nullable()->after('order_no');
            $table->integer('release_no')->nullable()->after('line_no');
            $table->integer('line_item_no')->nullable()->after('release_no');
            $table->integer('pos')->nullable()->after('line_item_no');

            $table->string('contract')->nullable()->after('pos');
            $table->string('catalog_no')->nullable()->after('contract');
            $table->string('description')->nullable()->after('catalog_no');
            $table->string('taxable_db')->nullable()->after('description');
            $table->integer('invoiced_qty')->nullable()->after('taxable_db');

            $table->string('sale_um')->nullable()->after('invoiced_qty');
            $table->integer('price_conv')->nullable()->after('sale_um');
            $table->string('price_um')->nullable()->after('price_conv');

            $table->double('sale_unit_price',8,2)->nullable()->after('price_um');
            $table->double('unit_price_incl_tax',8,2)->nullable()->after('sale_unit_price');
            $table->double('discount',8,2)->nullable()->after('unit_price_incl_tax');
            $table->double('order_discount',8,2)->nullable()->after('discount');

            $table->string('customer_po_no')->nullable()->after('order_discount');
            $table->string('rma_no')->nullable()->after('customer_po_no');
            $table->string('rma_line_no')->nullable()->after('rma_no');
            $table->string('rma_charge_no')->nullable()->after('rma_line_no');
            $table->double('additional_discount',8,2)->nullable()->after('rma_charge_no');
            $table->string('configuration_id')->nullable()->after('additional_discount');
            $table->integer('delivery_customer')->nullable()->after('configuration_id');

            $table->string('series_reference')->nullable()->after('delivery_customer');
            $table->string('number_reference')->nullable()->after('series_reference');
            $table->string('invoice_type')->nullable()->after('number_reference');
            $table->string('prel_update_allowed')->nullable()->after('invoice_type');

            $table->timestamp('man_tax_liability_date')->nullable()->after('prel_update_allowed');
            $table->timestamp('payment_date')->nullable()->after('man_tax_liability_date');

            $table->string('prepay_invoice_no')->nullable()->after('payment_date');
            $table->integer('prepay_invoice_series_id')->nullable()->after('prepay_invoice_no');
            $table->integer('assortment_node_id')->nullable()->after('prepay_invoice_series_id');
            $table->double('charge_percent',8,2)->nullable()->after('assortment_node_id');
            $table->string('charge_percent_basis')->nullable()->after('charge_percent');

            $table->string('bonus_part')->nullable()->after('charge_percent_basis');
            $table->double('total order line discount %',8,2)->nullable()->after('bonus_part');
            $table->double('total order line discount amt',8,2)->nullable()->after('total order line discount %');

            $table->string('city')->nullable()->after('total order line discount amt');
            $table->string('salesman_code')->nullable()->after('city');
            $table->string('salesman_name')->nullable()->after('salesman_code');
            $table->string('odering_region')->nullable()->after('salesman_name');
            $table->timestamp('last_updated_on')->nullable()->after('odering_region');

            $table->softDeletes()->after('last_updated_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('get_ext_invoice_line_uiv', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('invoice_date'); 
            $table->dropColumn('currency');     
            $table->dropColumn('vat_code');     
            $table->dropColumn('vat_rate');
            $table->dropColumn('vat_curr_amount');
            $table->dropColumn('net_curr_amount');
            $table->dropColumn('gross_curr_amount');
            $table->dropColumn('net_dom_amount');
            $table->dropColumn('vat_dom_amount');
            $table->dropColumn('reference');     
            $table->dropColumn('order_no');       
            $table->dropColumn('line_no');      
            $table->dropColumn('release_no');      
            $table->dropColumn('line_item_no');      
            $table->dropColumn('pos');      
            $table->dropColumn('contract');     
            $table->dropColumn('catalog_no');     
            $table->dropColumn('description');     
            $table->dropColumn('taxable_db');      
            $table->dropColumn('invoiced_qty');      
            $table->dropColumn('sale_um');
            $table->dropColumn('price_conv');     
            $table->dropColumn('price_um');      
            $table->dropColumn('sale_unit_price');
            $table->dropColumn('unit_price_incl_tax');
            $table->dropColumn('discount');
            $table->dropColumn('order_discount');
            $table->dropColumn('customer_po_no');     
            $table->dropColumn('rma_no');     
            $table->dropColumn('rma_line_no');     
            $table->dropColumn('rma_charge_no');     
            $table->dropColumn('additional_discount');
            $table->dropColumn('configuration_id');      
            $table->dropColumn('delivery_customer');      
            $table->dropColumn('series_reference');     
            $table->dropColumn('number_reference');     
            $table->dropColumn('invoice_type');     
            $table->dropColumn('prel_update_allowed');         
            $table->dropColumn('man_tax_liability_date');        
            $table->dropColumn('payment_date');      
            $table->dropColumn('prepay_invoice_no');      
            $table->dropColumn('prepay_invoice_series_id');      
            $table->dropColumn('assortment_node_id');     
            $table->dropColumn('charge_percent');
            $table->dropColumn('charge_percent_basis');      
            $table->dropColumn('bonus_part');     
            $table->dropColumn('total order line discount %');
            $table->dropColumn('total order line discount amt');
            $table->dropColumn('city');     
            $table->dropColumn('salesman_code');     
            $table->dropColumn('salesman_name');     
            $table->dropColumn('odering_region');        
            $table->dropColumn('last_updated_on');
            $table->dropSoftDeletes();
        });
    }
}
