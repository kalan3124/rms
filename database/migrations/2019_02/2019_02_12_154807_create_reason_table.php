<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reason', function (Blueprint $table) {
            $table->increments('rsn_id');
            $table->string('rsn_name');
            $table->tinyInteger('rsn_type')->nullable()->comment(
                '0-sales order, 1-invoice products,2-unproducte_calls,3-company_return,4-shop_close,5-write_off,6-on delivery rtn,7-shop close,8-market return,9-order source,10-special days,11-merchandising mva,12-merchandising common category display,13-merchandising air space, 14-merchandising posm'
            );
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
        Schema::dropIfExists('reason');
    }
}
