<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtInventPartInStockUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_invent_part_in_stock_uiv', function (Blueprint $table) {
            $table->increments('inpts_id');
            $table->integer('rn')->nullable();
            $table->string('contract')->nullable();
            $table->string('part_no')->nullable();
            $table->string('location_no')->nullable();
            $table->string('lot_batch_no')->nullable();
            $table->string('serial_no')->nullable();
            $table->integer('w_d_r_no')->nullable();
            $table->timestamp('expiration_date')->nullable();
            $table->timestamp('last_activity_date')->nullable();
            $table->timestamp('last_count_date')->nullable();
            $table->string('location_type')->nullable();
            $table->integer('qty_in_transit')->nullable();
            $table->integer('qty_onhand')->nullable();
            $table->integer('qty_reserved')->nullable();
            $table->integer('available_qty')->nullable();
            $table->timestamp('receipt_date')->nullable();
            $table->integer('availability_control_id')->nullable();
            $table->timestamp('create_date')->nullable();
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
        Schema::dropIfExists('ext_invent_part_in_stock_uiv');
    }
}
