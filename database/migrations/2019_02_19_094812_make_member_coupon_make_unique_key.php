<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeMemberCouponMakeUniqueKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->table('member_coupon', function (Blueprint $table) {
            $table->unique(['member_id', 'coupon_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $pdo = DB::connection('backend')->getPdo();
        $pdo->beginTransaction();
        $pdo->exec("ALTER TABLE member_coupon DROP INDEX member_coupon_member_id_coupon_id_unique");
        $pdo->commit();
        
    }
}
