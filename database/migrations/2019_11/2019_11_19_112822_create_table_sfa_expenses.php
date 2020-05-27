<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSfaExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_expenses', function (Blueprint $table) {
            $table->increments('sfa_exp_id');
            $table->unsignedInteger('bt_id')->nullable();
            $table->foreign('bt_id')->references('bt_id')->on('bata_type');
            $table->decimal('stationery',15,2)->nullable();
            $table->decimal('parking',15,2)->nullable();
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->String('remark')->nullable();
            $table->String('app_version')->nullable();
            $table->dateTime('exp_time')->nullable();
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
        Schema::dropIfExists('sfa_expenses');
    }
}
