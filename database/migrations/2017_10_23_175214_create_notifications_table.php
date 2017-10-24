<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 20)->comment('標題');
            $table->string('body')->comment('內容');
            $table->integer('type')->comment('類別');
            $table->string('url', 255)->comment('url');
            $table->integer('platform')->comment('推播平台');
            $table->dateTime('time')->comment('發送時間');
            $table->integer('sent')->comment('發送狀態 0.未發送 1.已發送');
            $table->integer('status')->comment('推播狀態 0.不啟用 1.啟用');
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
        Schema::dropIfExists('notifications');
    }
}
