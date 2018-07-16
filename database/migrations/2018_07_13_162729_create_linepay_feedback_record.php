<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinepayFeedbackRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linepay_feedback_record', function (Blueprint $table) {
            $table->increments('id');
            $table->string('orderId')->nullable();
            $table->string('transactionId')->nullable();
            $table->integer('amount')->nullable();
            $table->string('device')->nullable();
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
        Schema::dropIfExists('linepay_feedback_record');
    }
}
