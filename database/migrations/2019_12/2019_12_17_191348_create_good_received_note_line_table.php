<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodReceivedNoteLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_received_note_line', function (Blueprint $table) {
            $table->increments('grnl_id');
            
            $table->unsignedInteger('grn_id')->nullable();
            $table->foreign('grn_id')->references('grn_id')->on('good_received_note');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('db_id')->nullable();
            $table->foreign('db_id')->references('db_id')->on('distributor_batches');

            $table->integer('grnl_qty');

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
        Schema::dropIfExists('good_received_note_line');
    }
}
