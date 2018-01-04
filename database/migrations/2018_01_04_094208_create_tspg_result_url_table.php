<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTspgResultUrlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tspg_result_url', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ver', 20)->nullable();
            $table->string('mid', 20)->nullable();
            $table->string('s_mid', 20)->nullable();
            $table->string('tid')->nullable();
            $table->string('pay_type')->nullable();
            $table->string('tx_type')->nullable();
            $table->string('ret_value')->nullable();
            $table->string('ret_code')->nullable();
            $table->string('order_no')->nullable();
            $table->string('ret_msg')->nullable();
            $table->string('rrn')->nullable();
            $table->string('order_status')->nullable();
            $table->string('auth_type')->nullable();
            $table->string('cur')->nullable();
            $table->string('purchase_date')->nullable();
            $table->string('tx_amt')->nullable();
            $table->string('settle_amt')->nullable();
            $table->string('settle_seq')->nullable();
            $table->string('settle_date')->nullable();
            $table->string('refund_trans_amt')->nullable();
            $table->string('refund_rrn')->nullable();
            $table->string('refund_auth_id_resp')->nullable();
            $table->string('refund_date')->nullable();
            $table->string('redeem_order_no')->nullable();
            $table->string('redeem_pt')->nullable();
            $table->string('redeem_amt')->nullable();
            $table->string('post_redeem_amt')->nullable();
            $table->string('post_redeem_pt')->nullable();
            $table->string('install_order_no')->nullable();
            $table->string('install_period')->nullable();
            $table->string('install_down_pay')->nullable();
            $table->string('install_pay')->nullable();
            $table->string('install_down_pay_fee')->nullable();
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
        Schema::dropIfExists('tspg_result_url');
    }
}
