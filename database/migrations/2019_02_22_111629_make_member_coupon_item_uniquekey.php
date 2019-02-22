<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeMemberCouponItemUniquekey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->table('member_coupon_items', function (Blueprint $table) {
            $table->unique(['member_coupon_id', 'number']);
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
        $pdo->exec("ALTER TABLE member_coupon_items DROP INDEX member_coupon_items_member_coupon_id_number_unique");
        $pdo->commit();
    }
}
