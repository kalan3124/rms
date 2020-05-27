<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyExtSalesPartUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ext_sales_part_uiv', function (Blueprint $table) {
            $table->integer('ifs')->nullable()->after('id');
            $table->string('site')->nullable()->after('ifs');
            $table->string('sales_part_no')->nullable()->after('site');
            $table->string('sales_part_description')->nullable()->after('sales_part_no');
            $table->string('part_no')->nullable()->after('sales_part_description');
            $table->string('sales_group')->nullable()->after('part_no');
            $table->string('sales_group_desc')->nullable()->after('sales_group');

            $table->string('sales_price_group')->nullable()->after('sales_group_desc');
            $table->string('sales_price_group_desc')->nullable()->after('sales_price_group');
            $table->string('sales_uom')->nullable()->after('sales_price_group_desc');
            $table->string('active')->nullable()->after('sales_uom');
            $table->timestamp('date_entered')->nullable()->after('active');

            $table->double('price',8,2)->nullable()->after('date_entered');
            $table->double('price_incl_tax',8,2)->nullable()->after('price');
            $table->string('price_uom')->nullable()->after('price_incl_tax');

            $table->string('tax_code')->nullable()->after('price_uom');
            $table->string('tax_code_desc')->nullable()->after('tax_code');
            $table->timestamp('last_updated_on')->nullable()->after('tax_code_desc');
            $table->integer('bonus_part')->nullable()->after('last_updated_on');
            $table->integer('non_returnable')->nullable()->after('bonus_part');
            $table->string('short_description')->nullable()->after('non_returnable');
            $table->string('inv_part_no')->nullable()->after('short_description');
            $table->string('inv_part_desc')->nullable()->after('inv_part_no');
            $table->string('unit_code')->nullable()->after('inv_part_desc');
            $table->string('accounting_group')->nullable()->after('unit_code');
            $table->string('accounting_group_desc')->nullable()->after('accounting_group');
            $table->string('product_code')->nullable()->after('accounting_group_desc');
            $table->string('product_code_desc')->nullable()->after('product_code');
            $table->integer('product_family')->nullable()->after('product_code_desc');
            $table->string('product_family_desc')->nullable()->after('product_family');
            $table->string('type_code')->nullable()->after('product_family_desc');
            $table->string('moving_status')->nullable()->after('type_code');

            $table->string('hs_code')->nullable()->after('moving_status');
            $table->string('atc_code')->nullable()->after('hs_code');
            $table->string('device_type')->nullable()->after('atc_code');
            $table->string('generic_name')->nullable()->after('device_type');
            $table->string('manufacturer_name')->nullable()->after('generic_name');

            $table->string('pack_size')->nullable()->after('manufacturer_name');
            $table->string('part_approval_status')->nullable()->after('pack_size');
            $table->string('part_type')->nullable()->after('part_approval_status');
            $table->string('product_type')->nullable()->after('part_type');
            $table->timestamp('product_valid_from')->nullable()->after('product_type');
            $table->string('self_life')->nullable()->after('product_valid_from');
            $table->string('strength')->nullable()->after('self_life');
            $table->string('therapeutic_class')->nullable()->after('strength');
            $table->softDeletes()->after('therapeutic_class');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ext_sales_part_uiv', function (Blueprint $table) {
            $table->dropColumn('ifs');
            $table->dropColumn('site');
            $table->dropColumn('sales_part_no');
            $table->dropColumn('sales_part_description');
            $table->dropColumn('part_no');
            $table->dropColumn('sales_group');
            $table->dropColumn('sales_group_desc');
            $table->dropColumn('sales_price_group');
            $table->dropColumn('sales_price_group_desc');
            $table->dropColumn('sales_uom');
            $table->dropColumn('active');
            $table->dropColumn('date_entered');
            $table->dropColumn('price');
            $table->dropColumn('price_incl_tax');
            $table->dropColumn('price_uom');
            $table->dropColumn('tax_code');
            $table->dropColumn('tax_code_desc');
            $table->dropColumn('last_updated_on');
            $table->dropColumn('bonus_part');
            $table->dropColumn('non_returnable');
            $table->dropColumn('short_description');
            $table->dropColumn('inv_part_no');
            $table->dropColumn('inv_part_desc');
            $table->dropColumn('unit_code');
            $table->dropColumn('accounting_group');
            $table->dropColumn('accounting_group_desc');
            $table->dropColumn('product_code');
            $table->dropColumn('product_code_desc');
            $table->dropColumn('product_family');
            $table->dropColumn('product_family_desc');
            $table->dropColumn('type_code');
            $table->dropColumn('moving_status');
            $table->dropColumn('hs_code');
            $table->dropColumn('atc_code');
            $table->dropColumn('device_type');
            $table->dropColumn('generic_name');
            $table->dropColumn('manufacturer_name');
            $table->dropColumn('pack_size');
            $table->dropColumn('part_approval_status');
            $table->dropColumn('part_type');
            $table->dropColumn('product_type');
            $table->dropColumn('product_valid_from');
            $table->dropColumn('self_life');
            $table->dropColumn('strength');
            $table->dropColumn('therapeutic_class');
            $table->dropSoftDeletes();
        });
    }
}
