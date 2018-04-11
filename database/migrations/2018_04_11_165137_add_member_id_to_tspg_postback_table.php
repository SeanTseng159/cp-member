<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMemberIdToTspgPostbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tspg_postback', function (Blueprint $table) {
            $table->integer('member_id')->after('id')->unsigned()->comment('會員ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tspg_postback', function (Blueprint $table) {
            $table->dropColumn('member_id');
        });
    }
}
