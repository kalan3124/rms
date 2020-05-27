<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewStatusColumnToIntegrationSyncTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_sync_time', function (Blueprint $table) {
            $table->integer('last_sync_status')->default(1)->comment('1=Completed, 0=Locked');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_sync_time', function (Blueprint $table) {
            $table->dropColumn('last_sync_status');
        });
    }
}
