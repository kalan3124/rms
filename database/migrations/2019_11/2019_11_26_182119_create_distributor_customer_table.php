<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_customer', function (Blueprint $table) {
            $table->increments('dc_id');
            $table->string('dc_code');
            $table->string('dc_name');
            $table->string('dc_address');
            $table->string('dc_image_url')->nullable();
            $table->decimal('dc_lon', 10, 7)->nullable();
            $table->decimal('dc_lat', 10, 7)->nullable();
            
            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
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
        Schema::dropIfExists('distributor_customer');
    }
}
