<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSfaCustomerTarget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_customer_target', function (Blueprint $table) {
            $table->increments('sfa_cus_target_id');
            $table->string('sfa_cus_code')->nullable();
            $table->string('sfa_sr_code')->nullable();
            $table->integer('sfa_year')->nullable();
            $table->integer('sfa_month')->nullable();
            $table->decimal('sfa_target',15,2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sfa_customer_target');
    }
}
