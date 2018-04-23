<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ksd\Mediation\Config\ProjectConfig;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id');
            $table->enum('type', [ProjectConfig::MAGENTO, ProjectConfig::CITY_PASS])->comment('購物車類型:magento,ct_pass');
            $table->dateTime('last_notified_at')->comment('上次通知消費者未結帳時間');
            $table->dateTime('began_at')->comment('計算購物車過期起始時間');
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
        Schema::dropIfExists('carts');
    }
}
