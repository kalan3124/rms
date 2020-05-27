<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSelectedEmailTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('selected_email_types', function (Blueprint $table) {
            $table->increments('set_id');

            $table->unsignedInteger('et_id')->nullable();
            $table->foreign('et_id')->references('et_id')->on('email_types');

            $table->unsignedInteger('e_id')->nullable();
            $table->foreign('e_id')->references('e_id')->on('emails');

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
        Schema::dropIfExists('selected_email_types');
    }
}
