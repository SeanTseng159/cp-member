<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDeviceidFromNotificationMobilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_mobiles', function (Blueprint $table) {
            if (Schema::hasColumn('notification_mobiles', 'device_id')) {
                $table->dropColumn('device_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_mobiles', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_mobiles', 'device_id')) {
                $table->uuid('device_id')->comment('裝置UUID');;
            }
        });
    }
}
