<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinepayStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linepay_stores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type')->comment('營業類別');
            $table->string('address');
            $table->float('longitude',14,9)->comment('經度');
            $table->float('latitude',14,9)->comment('緯度');
            $table->string('phone');
            $table->string('business_hour')->comment('營業時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('linepay_stores');
    }
}
