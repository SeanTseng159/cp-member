<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->create('member_discount', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('discount_code_id')->nullable()->comment('優惠券ID');
            $table->integer('member_id')->nullable()->comment('會員ID');
            $table->integer('used')->nullable()->comment('是否使用，使用過: 1 沒用過：0');
            $table->integer('status')->nullable()->comment('狀態,1:開 0：關');
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
        Schema::connection('backend')->dropIfExists('member_discount');
    }
}
