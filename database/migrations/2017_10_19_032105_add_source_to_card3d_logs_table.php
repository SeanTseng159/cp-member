<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceToCard3dLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('card3d_logs', function (Blueprint $table) {
            $table->integer('totalAmount')->after('xid')->comment('結帳金額');
            $table->string('source', 7)->after('platform')->comment('來源');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('card3d_logs', function (Blueprint $table) {
            $table->dropColumn('totalAmount');
            $table->dropColumn('source');
        });
    }
}
