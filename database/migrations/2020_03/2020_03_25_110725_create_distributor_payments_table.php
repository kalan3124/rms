<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dc_id')->nullable();
            $table->foreign('dc_id')->references('dc_id')->on('distributor_customer');
            $table->decimal('amount',10,2);
            $table->decimal('balance',10,2)->default(0.00);
            $table->date('date');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->string('p_code')->nullable();
            $table->datetime('printed_at')->nullable();
            $table->unsignedInteger('payment_type_id')->nullable();
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
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
        Schema::dropIfExists('distributor_payments');
    }
}
