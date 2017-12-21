<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewslettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique()->comment('信箱');
            $table->integer('member_id')->default(0)->unsigned()->comment('會員ID');
            $table->tinyInteger('schedule')->default(0)->comment('頻率 0:完全體驗 1:每週一次');
            $table->boolean('status')->default(true)->comment('啟用狀態 0:未啟用 1:啟用');
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
        Schema::dropIfExists('newsletters');
    }
}
