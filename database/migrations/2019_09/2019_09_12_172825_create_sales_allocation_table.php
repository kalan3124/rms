<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesAllocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_allocation', function (Blueprint $table) {
            $table->increments('sa_id');

            $table->unsignedInteger('tm_id')->nullable();
            $table->foreign('tm_id')->references('tm_id')->on('teams');
            
            $table->tinyInteger('sa_ref_type')->nullable()->comment('1=Towns, 2=Customers , 3=Products')->default(1);
            $table->unsignedInteger('sa_ref_id')->nullable();

            $table->tinyInteger('sa_ref_mode')->default(1)->comment('1=Include 0=Exclude');

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
        Schema::dropIfExists('sales_allocation');
    }
}
