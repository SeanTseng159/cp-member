<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIpasspayLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ipasspay_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned()->comment('會員ID');
            $table->string('order_no', 20)->comment('訂單編號');
            $table->string('order_id', 20)->comment('訂單ID');
            $table->string('source', 10)->nullable()->comment('商品來源:ct_pass|magento');
            $table->string('platform', 10)->nullable()->comment('裝置代碼:web|app');
            $table->text('bindPayReq')->nullable()->comment('EC平台請求支付Token');
            $table->text('bindPayCallback')->nullable()->comment('付款後callback');
            $table->text('bindPayStatus')->nullable()->comment('支付確認');
            $table->text('bindRefund')->nullable()->comment('退款');
            $table->text('payNotify')->nullable()->comment('入帳通知');
            $table->text('bindPayResult')->nullable()->comment('交易結果查詢');
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
        Schema::drop('ipasspay_logs');
    }
}
