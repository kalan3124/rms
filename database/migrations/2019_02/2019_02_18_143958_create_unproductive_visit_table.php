<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnproductiveVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unproductive_visit', function (Blueprint $table) {
            $table->increments('un_visit_id');
            $table->string('un_visit_no')->unique();

            $table->unsignedInteger('doc_id')->nullable();
            $table->foreign('doc_id')->references('doc_id')->on('doctors');

            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->integer('visit_type')->nullable()->comment('0-Doctor, 1-Chemist');
            $table->integer('is_shedule')->nullable()->comment('0-Shedule, 1-UnShedule');
            $table->integer('shedule_id')->nullable();
            $table->integer('reason_id')->nullable();
            $table->integer('btry_lvl')->nullable();
            $table->decimal('lat', 20,15)->nullable();
            $table->decimal('lon', 20,15)->nullable();
            $table->timestamp('unpro_time')->nullable();
            $table->integer('visited_place')->nullable();

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
        Schema::dropIfExists('unproductive_visit');
    }
}
