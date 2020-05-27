<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountColumnToCompanyReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_return', function (Blueprint $table) {
            $table->decimal('cr_amount',12,2)->default(0.00);
            $table->string('cr_number');
            $table->timestamp('cr_confirmed_at')->nullable();

            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_return', function (Blueprint $table) {
            $table->dropColumn('cr_amount');
            $table->dropColumn('cr_number');
            $table->dropColumn('cr_confirmed_at');
            $table->dropForeign(['dis_id']);
            $table->dropColumn('dis_id');
        });
    }
}
