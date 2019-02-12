<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberCouponItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->create('member_coupon_items', function (Blueprint $table) {
            $table->integer('member_user_id')->unsigned();
            
            $table->boolean('status')->comment('使用狀態');
            $table->dateTime('used_time')->nullable()->comment('使用/核銷時間');
    
            $table->foreign('member_user_id')->references('id')->on('member_coupon');
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
        Schema::connection('backend')->dropIfExists('member_coupon_items');
    }
}
