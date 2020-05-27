<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductiveVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productive_visit', function (Blueprint $table) {
            $table->increments('pro_visit_id');
            $table->string('pro_visit_no')->unique();

            $table->unsignedInteger('doc_id')->nullable();
            $table->foreign('doc_id')->references('doc_id')->on('doctors');

            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->integer('visit_type')->nullable()->comment('0-Doctor, 1-Chemist');
            $table->integer('is_shedule')->nullable()->comment('0-Shedule, 1-UnShedule');
            $table->integer('shedule_id')->nullable();
            $table->string('audio_path')->nullable();
            $table->unsignedInteger('promo_id')->nullable();
            $table->foreign('promo_id')->references('promo_id')->on('promotion');
            $table->longText('promo_remark')->nullable();
            $table->longText('pro_summary')->nullable();

            $table->unsignedInteger('join_field_id')->nullable();
            $table->foreign('join_field_id')->references('id')->on('users');

            $table->timestamp('pro_start_time')->nullable();
            $table->timestamp('pro_end_time')->nullable();
            $table->decimal('lat', 20,15)->nullable();
            $table->decimal('lon', 20,15)->nullable();
            $table->integer('btry_lvl')->nullable();

            $table->unsignedInteger('visited_place')->nullable();
            $table->foreign('visited_place')->references('vt_id')->on('visit_type');

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
        Schema::dropIfExists('productive_visit');
    }
}
