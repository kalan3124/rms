<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAndroidAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('android_app', function (Blueprint $table) {
            $table->increments('aa_id');
            $table->string('aa_v_name');
            $table->string("aa_description");
            $table->timestamp("aa_start_time")->nullable()->default(null);
            $table->string("aa_url");
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
        Schema::dropIfExists('android_app');
    }
}
