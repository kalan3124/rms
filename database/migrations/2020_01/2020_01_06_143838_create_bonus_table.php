<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus', function (Blueprint $table) {
            $table->increments('bns_id');
            $table->string('bns_name');
            $table->string('bns_code');
            $table->date('bns_start_date');
            $table->date('bns_end_date');
            $table->integer('bns_qty');
            $table->integer('bns_free_qty');
            $table->tinyInteger('bns_all');
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
        Schema::dropIfExists('bonus');
    }
}
