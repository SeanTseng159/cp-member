<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayReceiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_receive', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchantnumber', 20)->comment('商店編號');
            $table->string('ordernumber', 20)->comment('訂單編號');
            $table->integer('serialnumber')->comment('交易序號');
            $table->string('writeoffnumber', 20)->comment('銷帳編號');
            $table->string('timepaid', 20)->comment('消費者繳款時間');
            $table->string('paymenttype', 20)->comment('實際支付工具');
            $table->integer('amount')->comment('實際繳款金額');
            $table->string('tel', 20)->nullable()->comment('消費者電話');
            $table->string('hash', 255)->comment('驗證碼');
            $table->tinyInteger('status')->comment('驗證碼驗證 0:失敗 1:成功');
            $table->string('memo', 255)->comment('備註');
            $table->string('log', 255)->comment('log');
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
        Schema::dropIfExists('pay_receive');
    }
}
