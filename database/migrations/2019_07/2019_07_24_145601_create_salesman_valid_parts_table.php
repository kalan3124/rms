<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesmanValidPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salesman_valid_parts', function (Blueprint $table) {
            $table->increments('smv_part_id');
            $table->string('salesman_code')->nullable();
            $table->string('contract')->nullable();
            $table->string('catalog_no')->nullable();
            $table->timestamp('from_date')->nullable();
            $table->timestamp('to_date')->nullable();
            $table->timestamp('last_updated_on')->nullable();

            $table->unsignedInteger('u_id')->nullable()->comment('sr_id');
            $table->foreign('u_id')->references('id')->on('users');

            $table->unsignedInteger('product_id')->nullable()->comment('product_id');
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
        Schema::dropIfExists('salesman_valid_parts');
    }
}
