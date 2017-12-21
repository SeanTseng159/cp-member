<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTspgPostbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tspg_postback', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_id', 20)->nullable()->comment('訂單ID');
            $table->string('order_no', 20)->nullable()->comment('訂單編號');
            $table->string('order_device', 20)->nullable()->comment('裝置代碼:web1|app2');
            $table->string('order_source')->nullable()->comment('訂單來源');
            $table->string('back_url')->nullable()->comment('3D驗證URL');
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
        Schema::dropIfExists('tspg_postback');
    }
}
