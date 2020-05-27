<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBatchIdToDistributorReturnBonusItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_return_bonus_item', function (Blueprint $table) {
            
            $table->unsignedInteger('db_id')->nullable();
            $table->foreign('db_id')->references('db_id')->on('distributor_batches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_return_bonus_item', function (Blueprint $table) {
            
            $table->dropForeign(['db_id']);
            $table->dropColumn('db_id');
        });
    }
}
