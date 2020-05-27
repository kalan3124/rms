<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtSiteUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_site_uiv', function (Blueprint $table) {
            $table->increments('site_id');
            $table->string('contract')->nullable();
            $table->string('description')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            $table->string('country_db')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('sfa_coordinator')->nullable();
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
        Schema::dropIfExists('ext_site_uiv');
    }
}
