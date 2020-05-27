<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSfaChemistDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sr_chemist_details', function (Blueprint $table) {
            $table->increments('sr_chem_id');
            $table->string('chem_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('address')->nullable();
            $table->integer('mobile number')->nullable();
            $table->string('email')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->string('image_url')->nullable();
            $table->tinyInteger('update_status')->nullable()->comments('1 = true 0 = false')->default(0);
            $table->unsignedInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users');
            $table->unsignedInteger('created_u_id')->nullable();
            $table->foreign('created_u_id')->references('id')->on('users');
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
        Schema::dropIfExists('sr_chemist_details');
    }
}
