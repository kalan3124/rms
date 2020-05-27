<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtSalesmanValidCustUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_salesman_valid_cust_uiv', function (Blueprint $table) {
            $table->increments('smv_cust_id');
            $table->string('salesman_code')->nullable();
            $table->integer('customer_id')->nullable();
            $table->timestamp('from_date')->nullable();
            $table->timestamp('to_date')->nullable();
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
        Schema::dropIfExists('ext_salesman_valid_cust_uiv');
    }
}
