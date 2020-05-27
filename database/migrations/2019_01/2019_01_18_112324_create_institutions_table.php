<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstitutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->increments('ins_id');
            $table->string('ins_name');
            $table->string('ins_short_name');
            $table->string('ins_code');
            $table->string('ins_address');

            $table->unsignedInteger('ins_cat_id');
            $table->foreign('ins_cat_id')->references('ins_cat_id')->on('institution_category');

            $table->unsignedInteger('twn_id');
            $table->foreign('twn_id')->references('twn_id')->on('town');

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
        Schema::dropIfExists('institutions');
    }
}
