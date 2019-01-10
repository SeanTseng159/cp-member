<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->create('payment_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sid', 5)->comment('特別ID');
            $table->string('name', 20)->comment('名稱');
            $table->string('sname', 30)->comment('代稱');
            $table->boolean('status')->default(false)->comment('開啟狀態');
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
        Schema::connection('backend')->dropIfExists('payment_methods');
    }
}
