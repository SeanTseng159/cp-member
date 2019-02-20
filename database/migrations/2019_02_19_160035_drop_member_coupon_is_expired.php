<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropMemberCouponIsExpired extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->table('member_coupon', function (Blueprint $table) {
            $table->dropColumn('is_expired');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('backend')->table('member_coupon', function (Blueprint $table) {
            $table->boolean('is_expired')->comment('是否過期')->default(false)->after('is_collected');
        });
    }
}
