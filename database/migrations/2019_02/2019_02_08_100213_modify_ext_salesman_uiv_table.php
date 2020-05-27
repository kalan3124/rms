<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyExtSalesmanUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ext_salesman_uiv', function (Blueprint $table) {
            $table->renameColumn('id','salesman_id');
            $table->string('salesman_code')->nullable()->after('id');
            $table->string('name')->nullable()->after('salesman_code');
            $table->string('blocked_for_use')->nullable()->after('name');
            $table->timestamp('last_updated_on')->nullable()->after('blocked_for_use');
            $table->softDeletes()->after('last_updated_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ext_salesman_uiv', function (Blueprint $table) {
            $table->renameColumn('salesman_id', 'id');
            $table->dropColumn('salesman_code');
            $table->dropColumn('name');
            $table->dropColumn('blocked_for_use');
            $table->dropColumn('last_updated_on');
            $table->dropSoftDeletes();
        });
    }
}
