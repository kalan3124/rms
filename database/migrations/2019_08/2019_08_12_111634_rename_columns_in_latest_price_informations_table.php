<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnsInLatestPriceInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('latest_price_informations', function (Blueprint $table) {
            $table->renameColumn('lpi_bdgt_base','lpi_bdgt_sales');
            $table->renameColumn('lpi_pg01_base','lpi_pg01_sales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('latest_price_informations', function (Blueprint $table) {
            $table->renameColumn('lpi_bdgt_sales','lpi_bdgt_base');
            $table->renameColumn('lpi_pg01_sales','lpi_pg01_base');
        });
    }
}
