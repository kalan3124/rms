<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfirmedStatusToGoodReceivedNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('good_received_note', function (Blueprint $table) {
            $table->decimal('grn_org_amount',14,2);
            $table->timestamp('grn_confirmed_at')->nullable();
            
            $table->unsignedInteger('grn_confirmed_by')->nullable();
            $table->foreign('grn_confirmed_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('good_received_note', function (Blueprint $table) {
            $table->dropForeign(['grn_confirmed_by']);
            $table->dropColumn('grn_confirmed_by');

            $table->dropColumn('grn_org_amount');
            $table->dropColumn('grn_confirmed_at');
        });
    }
}
