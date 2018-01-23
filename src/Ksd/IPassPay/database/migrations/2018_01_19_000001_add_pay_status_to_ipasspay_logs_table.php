<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class addPayStatusToIpasspayLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ipasspay_logs', function (Blueprint $table) {
            $table->string('pay_type', 20)->nullable()->after('platform')->comment('付款方式');
            $table->boolean('pay_status')->default(false)->after('pay_type')->comment('付款狀態');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ipasspay_logs', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
}
