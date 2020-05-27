<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProductTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_product_target', function (Blueprint $table) {
            $table->increments('upt_id');
            $table->unsignedInteger('ut_id')->nullable();
            $table->foreign('ut_id')->references('ut_id')->on('user_target');
            $table->unsignedInteger('tmup_id')->nullable();
            $table->foreign('tmup_id')->references('tmup_id')->on('team_user_products');
            $table->decimal('upt_value',10,2);
            $table->integer('upt_qty');
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
        Schema::dropIfExists('user_product_target');
    }
}
