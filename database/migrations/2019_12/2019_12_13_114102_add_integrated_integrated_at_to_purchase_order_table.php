<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIntegratedIntegratedAtToPurchaseOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->timestamp('integrated_at')->nullable();


            $table->unsignedInteger('sr_id')->nullable()->after('dis_id');
            $table->foreign('sr_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->dropColumn('integrated_at');

            $table->dropForeign(['sr_id']);
            $table->dropColumn('sr_id');
        });
    }
}
