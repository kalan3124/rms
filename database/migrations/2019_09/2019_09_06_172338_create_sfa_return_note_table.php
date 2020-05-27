<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSfaReturnNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_return_note', function (Blueprint $table) {
            $table->increments('rn_id');
            $table->string('rn_no')->unique()->nullable();

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');

            $table->tinyInteger('is_sheduled')->default(0)->comment('sheduled - 1, unsheduled - 2');

            $table->string('remark')->nullable();

            $table->tinyInteger('sr_availability')->default(0)->comment('available - 1, not available - 0');
            $table->tinyInteger('mr_availability')->default(0)->comment('available - 1, not available - 0');

            $table->decimal('latitude',10,7)->nullable();
            $table->decimal('longitude',10,7)->nullable();
            $table->timestamp('rn_time')->nullable();
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
        Schema::dropIfExists('sfa_return_note');
    }
}
