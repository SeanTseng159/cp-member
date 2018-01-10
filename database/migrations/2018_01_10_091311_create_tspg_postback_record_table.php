<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTspgPostbackRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tspg_postback_record', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ret_code')->nullable();
            $table->string('tx_type')->nullable();
            $table->string('order_no')->nullable();
            $table->string('ret_msg')->nullable();
            $table->string('auth_id_resp')->nullable();
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
        Schema::dropIfExists('tspg_postback_record');
    }
}
