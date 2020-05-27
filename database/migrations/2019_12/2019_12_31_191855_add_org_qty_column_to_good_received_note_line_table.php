<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrgQtyColumnToGoodReceivedNoteLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('good_received_note_line', function (Blueprint $table) {
            $table->decimal('grnl_org_qty',12,2);
            $table->decimal('grnl_price',12,2);
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
            $table->dropColumn('grnl_org_qty');
            $table->dropColumn('grnl_price');
        });
    }
}
