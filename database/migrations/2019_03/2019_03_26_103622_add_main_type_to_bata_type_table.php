<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMainTypeToBataTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bata_type', function (Blueprint $table) {
            $table->tinyInteger('bt_type')->comment('1=FM, 2=MR');
            $table->decimal('bt_value',10,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bata_type', function (Blueprint $table) {
            $table->dropColumn('bt_type');
            $table->dropColumn('bt_value');
        });
    }
}
