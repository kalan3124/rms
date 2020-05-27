<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDailyTarget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_daily_target', function (Blueprint $table) {
            $table->increments('sfa_daily_target_id');
            $table->date('target_day')->nullable();
            $table->decimal('day_target',15,2)->nullable();
            $table->string('sr_code')->nullable();
            $table->string('ar_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sfa_daily_target');
    }
}
