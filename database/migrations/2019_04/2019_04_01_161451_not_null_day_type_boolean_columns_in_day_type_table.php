<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NotNullDayTypeBooleanColumnsInDayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('day_type', function (Blueprint $table) {
            $table->smallInteger('dt_is_working')->nullable(true)->change();
            $table->smallInteger('dt_bata_enabled')->nullable(true)->change();
            $table->smallInteger('dt_mileage_enabled')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('day_type', function (Blueprint $table) {
            $table->smallInteger('dt_is_working')->nullable(false)->change();
            $table->smallInteger('dt_bata_enabled')->nullable(false)->change();
            $table->smallInteger('dt_mileage_enabled')->nullable(false)->change();
        });
    }
}
