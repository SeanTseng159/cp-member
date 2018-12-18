<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->create('order_shipments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned()->comment('訂單序號');
            $table->string('user_name', 100)->comment('收件人姓名');
            $table->string('country_code', 6)->comment('電話國碼');
            $table->string('cellphone', 12)->comment('手機號碼');
            $table->string('zipcode', 5)->comment('區碼');
            $table->string('address')->comment('地址');
            $table->string('trace_code', 20)->nullable()->comment('物流追蹤碼');
            $table->tinyInteger('method')->default(0)->comment('運送方式 1:宅配到府 2:貨到付款 3:超商取貨 4:超商取貨付款');
            $table->tinyInteger('status')->default(1)->comment('運送狀態 1:備貨中 2:發貨中 3:已發貨 4:已到達 5:已取貨 6:已退貨');
            $table->timestamps();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('backend')->dropIfExists('order_shipments');
    }
}
