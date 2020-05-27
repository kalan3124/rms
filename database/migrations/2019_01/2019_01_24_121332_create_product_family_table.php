<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductFamilyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_family', function (Blueprint $table) {
            $table->increments('product_family_id');
            $table->string('product_family_name');

            $table->unsignedInteger('principal_id')->nullable();
            $table->foreign('principal_id')->references('principal_id')->on('principal');

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
        Schema::dropIfExists('product_family');
    }
}
