<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSfaUnproductiveVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_unproductive_visits', function (Blueprint $table) {
            $table->increments('sfa_un_id');
            $table->string('un_visit_no')->unique();

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');

            $table->tinyInteger('is_sheduled')->default(0)->comment('sheduled - 1, unsheduled - 2');

            $table->unsignedInteger('rsn_id')->nullable();
            $table->foreign('rsn_id')->references('rsn_id')->on('reason');

            $table->decimal('latitude',10,7)->nullable();
            $table->decimal('longitude',10,7)->nullable();
            $table->timestamp('unpro_time')->nullable();
            $table->integer('battery_level')->nullable();
            $table->string('app_version')->nullable();

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
        Schema::dropIfExists('sfa_unproductive_visits');
    }
}
