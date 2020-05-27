<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesInChemistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chemist', function (Blueprint $table) {
            $table->integer('mobile_number')->nullaball();
            $table->string('email')->nullaball();
            $table->decimal('lat', 20,15)->nullable();
            $table->decimal('lon', 20,15)->nullable();
            $table->unsignedInteger('updated_u_id')->nullable();
            $table->foreign('updated_u_id')->references('id')->on('users');
            $table->string('image_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chemist', function (Blueprint $table) {
            $table->dropForeign(['updated_u_id']);
            $table->dropColumn('updated_u_id');
            $table->dropColumn('mobile_number');
            $table->dropColumn('email');
            $table->dropColumn('lat');
            $table->dropColumn('lon');
            $table->dropColumn('image_url');
        });
    }
}
