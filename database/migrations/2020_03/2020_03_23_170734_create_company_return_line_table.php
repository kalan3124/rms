<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyReturnLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_return_line', function (Blueprint $table) {
            $table->increments('crl_id');

            $table->unsignedInteger('cr_id')->nullable();
            $table->foreign('cr_id')->references('cr_id')->on('company_return');

            $table->unsignedInteger('grnl_id')->nullable();
            $table->foreign('grnl_id')->references('grnl_id')->on('good_received_note_line');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('db_id')->nullable();
            $table->foreign('db_id')->references('db_id')->on('distributor_batches');

            $table->integer('crl_qty');

            $table->unsignedInteger('rsn_id')->nullable();
            $table->foreign('rsn_id')->references('rsn_id')->on('reason');

            $table->tinyInteger('crl_salable')->default(0)->comment("0=No, 1= Yes");

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
        Schema::dropIfExists('company_return_line');
    }
}
