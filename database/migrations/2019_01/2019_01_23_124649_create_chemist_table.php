<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChemistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chemist', function (Blueprint $table) {
            $table->increments('chemist_id');
            $table->string('chemist_name');
            $table->string('chemist_code');
            $table->string('chemist_address');
            $table->string('telephone');
            $table->double('credit_amount', 8, 2);

            $table->unsignedInteger('twn_id')->nullable();
            $table->foreign('twn_id')->references('twn_id')->on('town');

            $table->unsignedInteger('chemist_class_id')->nullable();
            $table->foreign('chemist_class_id')->references('chemist_class_id')->on('chemist_class');

            $table->unsignedInteger('chemist_type_id')->nullable();
            $table->foreign('chemist_type_id')->references('chemist_type_id')->on('chemist_types');

            $table->unsignedInteger('chemist_mkd_id')->nullable();
            $table->foreign('chemist_mkd_id')->references('chemist_mkd_id')->on('chemist_market_description');
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
        Schema::dropIfExists('chemist');
    }
}
