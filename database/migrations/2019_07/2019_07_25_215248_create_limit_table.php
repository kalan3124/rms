<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLimitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('limit', function (Blueprint $table) {
            $table->increments('lmt_id');

            $table->tinyInteger('lmt_main_type')->nullable()->comment('1=Expense');
            $table->tinyInteger('lmt_sub_type')->nullable();

            $table->integer('lmt_ref_id')->nullable();

            $table->tinyInteger('lmt_frequency')->nullable()->comment('1=Daily,2=Monthly,3=Yearly');

            $table->decimal('lmt_min_amount',10,2);
            $table->decimal('lmt_max_amount',10,2)->nullable();

            $table->date('lmt_start_at')->nullable();
            $table->date('lmt_end_at')->nullable();

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
        Schema::dropIfExists('limit');
    }
}
