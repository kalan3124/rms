<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->increments('i_id');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->date('i_due_date')->nullable();
            $table->date('i_cmplt_date')->nullable();
            $table->tinyInteger('i_application')->comment('1=App,2=Backend,3=App/Backend')->default(0);
            $table->string('i_module')->nullable();
            $table->string('i_description')->nullable();
            $table->string('i_cmnt_shl')->nullable();
            $table->string('i_cmnt_cl')->nullable();
            $table->tinyInteger('i_label')->comment('1=New Feature, 0=Issue')->default(0);
            $table->tinyInteger('i_status')->comment('0=Pending,1=Completed,2=Issue Occured')->default(0);
            $table->integer('i_num')->default(0);
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
        Schema::dropIfExists('issues');
    }
}
