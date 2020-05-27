<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_sales_order', function (Blueprint $table) {
            $table->increments('dist_order_id');

            $table->string('order_no');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->timestamp('order_date')->nullable();

            $table->tinyInteger('order_mode')->default(0)->comment('0 - normal');
            $table->tinyInteger('order_type')->default(0)->comment('0 - sales order');

            $table->unsignedInteger('dc_id')->nullable();
            $table->foreign('dc_id')->references('dc_id')->on('distributor_customer');

            $table->decimal('latitude',10,7)->nullable();
            $table->decimal('longitude',10,7)->nullable();
            $table->integer('battery_lvl')->nullable();

            $table->string('app_version')->nullable();

            $table->string('contract')->nullable();


            $table->unsignedInteger('ar_id')->nullable();
            $table->foreign('ar_id')->references('ar_id')->on('area');
            
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
        Schema::dropIfExists('distributor_sales_order');
    }
}
