<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiningCarMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->create('dining_car_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dining_car_id')->comment('餐車ID');
            $table->unsignedInteger('member_id')->comment('會員ID');
            $table->integer('amount')->default(0)->comment('累積消費金額');
            $table->integer('gift')->default(0)->comment('禮物券張數');
            $table->integer('point')->default(0)->comment('累積點數');
            $table->softDeletes();
            $table->timestamps();

            $table->index('dining_car_id');
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('backend')->dropIfExists('dining_car_members');
    }
}
