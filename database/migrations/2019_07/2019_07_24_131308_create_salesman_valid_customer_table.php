<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesmanValidCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salesman_valid_customer', function (Blueprint $table) {
            $table->increments('smv_cust_id');
            $table->string('salesman_code')->nullable();
            $table->string('customer_id')->nullable();
            $table->timestamp('from_date')->nullable();
            $table->timestamp('to_date')->nullable();
            $table->timestamp('last_updated_on')->nullable();

            $table->unsignedInteger('u_id')->nullable()->comment('sr_id');
            $table->foreign('u_id')->references('id')->on('users');

            $table->unsignedInteger('chemist_id')->nullable()->comment('chemist_id');
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');

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
        Schema::dropIfExists('salesman_valid_customer');
    }
}
