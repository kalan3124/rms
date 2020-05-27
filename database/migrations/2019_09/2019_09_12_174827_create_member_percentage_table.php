<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberPercentageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_percentage', function (Blueprint $table) {
            $table->increments('mp_id');

            $table->unsignedInteger('tmu_id')->nullable();
            $table->foreign('tmu_id')->references('tmu_id')->on('team_users');
            
            $table->decimal('mp_percent')->default(0);

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
        Schema::dropIfExists('member_percentage');
    }
}
