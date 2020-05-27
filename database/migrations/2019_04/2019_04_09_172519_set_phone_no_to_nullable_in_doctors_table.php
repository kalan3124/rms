<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetPhoneNoToNullableInDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable( )->change();
            $table->string('phone_no')->nullable( )->change();
            $table->string('mobile_no')->nullable()->change();
            $table->unsignedInteger('doc_class_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable(false)->change();
            $table->string('phone_no')->nullable(false)->change();
            $table->string('mobile_no')->nullable(false)->change();
            $table->unsignedInteger('doc_class_id')->nullable(false)->change();
        });
    }
}
