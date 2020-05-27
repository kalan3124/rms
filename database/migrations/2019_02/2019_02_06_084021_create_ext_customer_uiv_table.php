<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtCustomerUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_customer_uiv', function (Blueprint $table) {
            $table->increments('ifs');
            $table->integer('customer_id');
            $table->string('name')->nullable();
            $table->string('default_language')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_name')->nullable();
            $table->integer('address_identity')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('city')->comments('Town/Sub Town')->nullable();
            $table->string('city_name')->comments('Town Name')->nullable();
            $table->string('county')->comments('Town/ Area')->nullable();
            $table->string('county_name')->comments('Area Name')->nullable();
            $table->string('state')->comments('District')->nullable();
            $table->string('state_name')->comments('District Name')->nullable();

            $table->integer('phone')->nullable();
            $table->integer('fax')->nullable();

            $table->string('email')->nullable();
            $table->string('delivery_terms')->nullable();
            $table->string('delivery_description')->nullable();
            $table->string('district')->nullable();
            $table->string('district_description')->nullable();
            $table->string('region')->nullable();
            $table->string('region_description')->nullable();
            $table->string('ship_via')->nullable();
            $table->string('ship_via_description')->nullable();
            $table->string('customer_group')->nullable();
            $table->string('customer_group_description')->comments('CUSTOMERGROUP')->nullable();

            $table->string('payment_term')->nullable();
            $table->string('peyment_term_description')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('numeration_group')->nullable();
            $table->integer('no_of_invoice_copies')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('payment_method_description')->nullable();
            $table->string('credit_analyst')->nullable();
            $table->double('credit_limit', 8, 2)->nullable();
            $table->integer('credit_blocked')->nullable();
            $table->integer('allowed_overdue_days')->nullable();
            $table->double('allowed_overdue_amount',8,2)->nullable();

            $table->string('cust_stat_group')->comments('Customer Class')->nullable();
            $table->string('cust_price_grp')->nullable();
            $table->string('cust_price_grp_description')->comments('Price Group')->nullable();

            $table->string('salesman')->nullable();
            $table->string('salesman_name')->nullable();
            $table->integer('market')->nullable();
            $table->string('market_description')->comments('Customer Type')->nullable();

            $table->string('currency')->nullable();
            $table->string('credit_control_group')->nullable();
            $table->string('type')->nullable();
            $table->string('type_db')->nullable();
            $table->string('order_type')->nullable();
            $table->string('order_type_description')->nullable();

            $table->string('priority')->nullable();
            $table->string('sfa_price_list')->nullable();
            $table->string('sfa_price_list_description')->nullable();
            $table->string('sfa_customer_type')->nullable();
            $table->string('sfa_customer_type_description')->nullable();
            $table->string('cust_class')->nullable();
            $table->string('cust_class_description')->nullable();
            $table->string('association_no')->nullable();
            $table->timestamp('cust_inactive_date')->nullable();
            $table->timestamp('last_updated_on')->nullable();

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
        Schema::dropIfExists('ext_customer_uiv');
    }
}
