<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodReceivedNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_received_note', function (Blueprint $table) {
            $table->increments('grn_id');

            $table->string('grn_no');

            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');

            $table->unsignedInteger('dsr_id')->nullable();
            $table->foreign('dsr_id')->references('id')->on('users');

            $table->unsignedInteger('po_id')->nullable();
            $table->foreign('po_id')->references('po_id')->on('purchase_order');

            $table->decimal('grn_amount');

            $table->timestamp('grn_date');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_received_note');
    }
}
