<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDistributorReturnTableToBackend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_return', function (Blueprint $table) {
            $table->dropColumn('invoice_type');
            $table->dropColumn('batteryLevel');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->unsignedInteger('dsr_id')->nullable();
            $table->foreign('dsr_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_return', function (Blueprint $table) {
            $table->tinyInteger('invoice_type')->nullable();
            $table->integer('batteryLevel')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->dropForeign(['dsr_id']);
            $table->dropColumn('dsr_id');
        });
    }
}
