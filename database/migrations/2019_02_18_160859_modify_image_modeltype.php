<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyImageModeltype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pdo = DB::connection('backend')->getPdo();
        $pdo->beginTransaction();
        $pdo->exec("ALTER TABLE images CHANGE COLUMN model_type model_type ENUM('dining_car','coupon') NOT NULL COMMENT '使用的地方，ex.餐車、優惠卷'");
        $pdo->commit();

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
        $pdo->exec("ALTER TABLE images CHANGE COLUMN model_type model_type ENUM('dining_car') NOT NULL COMMENT '使用的地方，ex.餐車'");
        $pdo->commit();
    }
}
