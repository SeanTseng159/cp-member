<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationMobilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_mobiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('platform', 20)->comment('平台 iOS:iOS Android:Android');
            $table->string('mobile_token', 255)->comment('推播token');
            $table->integer('member_id')->nullable()->unsigned()->comment('用戶id');
            $table->uuid('device_id')->comment('裝置UUID');;
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
        Schema::dropIfExists('notification_mobiles');
    }
}
