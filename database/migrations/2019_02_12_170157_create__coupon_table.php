<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sort')->unsigned()->comment('排序');
            $table->string('name',25)->comment('優惠名稱');
            $table->dateTime('start_at')->nullable()->comment('使用開始時間');
            $table->dateTime('expire_at')->nullable()->comment('使用結束時間');
            $table->dateTime('on_sale_at')->nullable()->comment('上架開始時間');
            $table->dateTime('off_sale_at')->nullable()->comment('下架結束時間');
            $table->integer('qty')->unsigned()->comment('可用數量');
            $table->integer('limit_qty')->unsigned()->comment('使用限制');
            $table->string('content',30)->comment('優惠卷內容');
            $table->string('desc',500)->comment('使用方法說明內文');
            $table->boolean('status')->comment('狀態')->default(false);
            $table->integer('editor')->unsigned()->comment('編輯者');
            $table->string('model_name')->comment('使用namespace全名');
            $table->enum('model_type', ['dining_car'])->comment('使用的地方，ex.餐車');
            $table->integer('model_spec_id')->unsigned()->comment('使用(商品、餐車、商店)的id');
            
            $table->index('model_type');
            
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
        Schema::connection('backend')->dropIfExists('coupons');
    }
}
