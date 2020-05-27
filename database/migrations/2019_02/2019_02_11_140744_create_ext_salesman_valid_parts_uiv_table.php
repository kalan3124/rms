<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtSalesmanValidPartsUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_salesman_valid_parts_uiv', function (Blueprint $table) {
            $table->increments('smv_part_id');
            $table->string('salesman_code')->nullable();
            $table->string('contract')->nullable();
            $table->string('catalog_no')->nullable();
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
        Schema::dropIfExists('ext_salesman_valid_parts_uiv');
    }
}
