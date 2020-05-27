<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users',function(Blueprint $table){
            $table->string('contact_no')->after('name');

            $table->unsignedInteger('u_tp_id')->nullable()->after('email_verified_at');
            $table->foreign('u_tp_id')->references('u_tp_id')->on('user_types');

            $table->string('user_name')->after('u_tp_id');

            $table->unsignedInteger('divi_id')->nullable()->after('password');
            $table->foreign('divi_id')->references('divi_id')->on('division');

            $table->tinyInteger('price_list')->default('0')->comment('1-Actual price, 2-Budget price')->after('divi_id');
            $table->tinyInteger('base_allowances')->default('0')->comment('0-not allocated, 1-allocated')->after('price_list');
            $table->tinyInteger('private_mileage')->default('0')->comment('0-not allocated, 1-allocated')->after('base_allowances');
            $table->softDeletes()->after('remember_token');
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
            $table->dropColumn('contact_no');
            $table->dropColumn('u_tp_id');
            $table->dropColumn('user_name');
            $table->dropColumn('divi_id');
            $table->dropColumn('price_list');
            $table->dropColumn('base_allowances');
            $table->dropColumn('private_mileage');
            $table->dropColumn('deleted_at');
        });
    }
}
