<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableToUsersTableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users',function(Blueprint $table){
            $table->smallInteger('base_allowances')->tinyInteger('base_allowances')->nullable()->change();
            $table->smallInteger('private_mileage')->tinyInteger('private_mileage')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users',function(Blueprint $table){
            $table->smallInteger('base_allowances')->tinyInteger('base_allowances')->nullable(false)->change();
            $table->smallInteger('private_mileage')->tinyInteger('private_mileage')->nullable(false)->change();
        });
    }
}
