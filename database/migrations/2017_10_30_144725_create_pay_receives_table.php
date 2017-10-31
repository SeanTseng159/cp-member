<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayReceivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_receives', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_number', 20)->comment('商店編號');
            $table->string('order_number', 20)->comment('訂單編號');
            $table->integer('serial_number')->comment('交易序號');
            $table->string('write_off_number', 20)->comment('銷帳編號');
            $table->string('time_paid', 20)->comment('消費者繳款時間');
            $table->string('payment_type', 20)->comment('實際支付工具');
            $table->integer('amount')->comment('實際繳款金額');
            $table->string('tel', 20)->nullable()->comment('消費者電話');
            $table->string('hash', 255)->comment('驗證碼');
            $table->tinyInteger('status')->nullable()->comment('驗證碼驗證 0:失敗 1:成功');
            $table->string('memo', 255)->nullable()->comment('備註');
            $table->string('log', 255)->nullable()->comment('log');
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
        Schema::dropIfExists('pay_receives');
    }
}
