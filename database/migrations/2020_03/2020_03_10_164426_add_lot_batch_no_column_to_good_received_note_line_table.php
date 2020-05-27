<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLotBatchNoColumnToGoodReceivedNoteLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('good_received_note_line', function (Blueprint $table) {
            $table->string('grnl_lot_batch_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('good_received_note_line', function (Blueprint $table) {
            $table->dropColumn('grnl_lot_batch_no');
        });
    }
}
