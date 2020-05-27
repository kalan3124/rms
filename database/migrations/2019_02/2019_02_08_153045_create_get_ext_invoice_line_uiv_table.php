<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetExtInvoiceLineUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('get_ext_invoice_line_uiv', function (Blueprint $table) {
            $table->increments('inv_line_id');
            $table->string('company')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->integer('item_id')->nullable();
            $table->string('party_type')->nullable();
            $table->string('series_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('client_state')->nullable();
            $table->integer('identity')->nullable();
            
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
        Schema::dropIfExists('get_ext_invoice_line_uiv');
    }
}
