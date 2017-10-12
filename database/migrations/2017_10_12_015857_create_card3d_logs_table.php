<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCard3dLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card3d_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('errorCode', 4)->nullable()->comment('錯誤代碼');
            $table->string('errorMessage')->nullable()->comment('錯誤訊息');
            $table->string('eci', 2)->comment('3D驗證結果代碼');
            $table->string('cavv', 28)->nullable()->comment('驗證token');
            $table->string('xid', 20)->comment('訂單編號');
            $table->string('platform', 10)->comment('購買平台');
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
        Schema::dropIfExists('card3d_logs');
    }
}
