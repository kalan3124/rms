<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTownTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('town', function (Blueprint $table) {
            $table->string('twn_name')->nullable()->change();
            $table->string('twn_short_name')->nullable()->change();
            $table->string('twn_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('town', function (Blueprint $table) {
            $table->string('twn_name')->nullable(false)->change();
            $table->string('twn_short_name')->nullable(false)->change();
            $table->string('twn_code')->nullable(false)->change();
        });
    }
}
