<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalRequestedColumnsToDistributorInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_invoice', function (Blueprint $table) {
            $table->timestamp('di_approve_requested_at')->nullable();
            
            $table->unsignedInteger('di_approve_requested_by')->nullable();
            $table->foreign('di_approve_requested_by')->references('id')->on('users');

            $table->timestamp('di_approved_at')->nullable();

            $table->unsignedInteger('di_approved_by')->nullable();
            $table->foreign('di_approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_invoice', function (Blueprint $table) {
            $table->dropColumn('di_approve_requested_at');

            $table->dropForeign(['di_approve_requested_by']);
            $table->dropColumn('di_approve_requested_by');

            $table->dropColumn('di_approved_at');

            $table->dropForeign(['di_approved_by']);
            $table->dropColumn('di_approved_by');
        });
    }
}
