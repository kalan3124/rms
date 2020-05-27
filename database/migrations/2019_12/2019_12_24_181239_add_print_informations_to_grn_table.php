<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrintInformationsToGrnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('good_received_note_line', function (Blueprint $table) {
            $table->string('grnl_uom');
            $table->string('grnl_loc_no');
            $table->integer('grnl_line_no');
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
            $table->dropColumn('grnl_uom');
            $table->dropColumn('grnl_loc_no');
            $table->dropColumn('grnl_line_no');
        });
    }
}
