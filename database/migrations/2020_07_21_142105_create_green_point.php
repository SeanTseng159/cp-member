<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGreenPoint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('backend')->create('green_point', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->comment('兌換碼');
            $table->tinyInteger('used')->nullable()->comment('是否兌換');
            $table->integer('member_id')->nullable()->comment('會員ID');
            $table->integer('prodSpecPriceId')->comment('兌換商品ID');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::connection('backend')->dropIfExists('green_point');
    }
}
