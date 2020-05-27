<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtRegionsUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_regions_uiv', function (Blueprint $table) {
            $table->increments('ext_region_id');
            $table->string('region_code')->nullable();
            $table->string('description')->nullable();
            $table->timestamp('last_updated_on')->nullable();
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
        Schema::dropIfExists('ext_regions_uiv');
    }
}
