<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->create('member_coupon', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned()->comment('使用者id');
            $table->integer('coupon_id')->unsigned()->comment('優惠卷id');
            $table->boolean('is_collected')->comment('是否收藏')->default(false);
            $table->tinyInteger('count')->unsigned()->comment('已使用張數');
            
            
            $table->index('member_id');
            
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
        Schema::connection('backend')->dropIfExists('member_coupon');
    }
}
