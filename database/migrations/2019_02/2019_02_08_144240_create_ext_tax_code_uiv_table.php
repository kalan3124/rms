<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtTaxCodeUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_tax_code_uiv', function (Blueprint $table) {
            $table->increments('tax_id');
            $table->string('company')->nullable();
            $table->string('fee_code')->nullable();
            $table->string('description')->nullable();
            $table->double('fee_rate',8,2)->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('fee_type')->nullable();
            $table->timestamp('last_updated_on')->nullable();
            
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
        Schema::dropIfExists('ext_tax_code_uiv');
    }
}
