<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::connection('backend')->create('guest_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id')->comment('訂單ID');
            $table->string('name', 20)->comment('名稱');
            $table->string('country', 6)->nullable()->comment('國籍代碼');
            $table->string('countryCode', 6)->nullable()->comment('電話國碼');
            $table->string('cellphone', 12)->nullable()->comment('行動電話號碼');
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
        Schema::connection('backend')->dropIfExists('guest_orders');
    }
}
