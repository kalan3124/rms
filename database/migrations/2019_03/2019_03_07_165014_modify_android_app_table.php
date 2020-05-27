<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyAndroidAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('android_app', function (Blueprint $table) {
            $table->integer('aa_v_type')->nullable()->after('aa_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('android_app', function (Blueprint $table) {
            $table->dropColumn('aa_v_type');  
        });
    }
}
