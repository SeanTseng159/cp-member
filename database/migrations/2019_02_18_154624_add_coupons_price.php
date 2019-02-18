<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouponsPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->table('coupons', function (Blueprint $table) {
            $table->integer('price')->comment('優惠券價值')->default(0)->after('off_sale_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('backend')->table('coupons', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
}
