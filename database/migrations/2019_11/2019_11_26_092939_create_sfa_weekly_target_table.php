<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSfaWeeklyTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_weekly_target', function (Blueprint $table) {
            $table->increments('sfa_trg_wk_id');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->unsignedInteger('sfa_trg_id')->nullable();
            $table->foreign('sfa_trg_id')->references('sfa_trg_id')->on('sfa_target');
            $table->integer('sfa_trg_year')->nullable();
            $table->integer('sfa_trg_month')->nullable();
            $table->integer('sfa_trg_week_no')->nullable();
            $table->tinyInteger('week_start_date')->nuallable();
            $table->tinyInteger('week_end_date')->nuallable();
            $table->decimal('percentage',10,2)->nullable();
            $table->decimal('trg_amount',15,2)->nullable();
            $table->SoftDeletes();
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
        Schema::dropIfExists('sfa_weekly_target');
    }
}
