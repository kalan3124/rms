<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSrColumnNameInSiteAllocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_allocation', function (Blueprint $table) {
            $table->renameColumn('sr_id','dis_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_allocation', function (Blueprint $table) {
            $table->renameColumn('dis_id','sr_id');
        });
    }
}
