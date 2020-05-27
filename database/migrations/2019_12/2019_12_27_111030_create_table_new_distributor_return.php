<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNewDistributorReturn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_return', function (Blueprint $table) {
            $table->increments('dis_return_id');
            $table->tinyInteger('invoice_type')->nullable();
            $table->tinyInteger('invoice_mode')->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
           
            $table->unsignedInteger('dist_order_id')->nullable();
            $table->foreign('dist_order_id')->references('dist_order_id')->on('distributor_sales_order');
            
            $table->dateTime('return_date')->nullable();
           
            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');
           
            $table->unsignedInteger('dc_id')->nullable();
            $table->foreign('dc_id')->references('dc_id')->on('distributor_customer');
           
            $table->integer('batteryLevel')->nullable();
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
        Schema::dropIfExists('distributor_return');
    }
}
