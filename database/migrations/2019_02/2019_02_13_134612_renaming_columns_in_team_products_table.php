<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamingColumnsInTeamProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_products', function (Blueprint $table) {
            $table->renameColumn('team_id','tm_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_products', function (Blueprint $table) {
            $table->renameColumn('tm_id','team_id');
        });
    }
}
