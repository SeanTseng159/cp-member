<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJoinPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('join_partners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('company', 100)->comment('公司名稱');
            $table->string('tax_id', 20)->nullable()->comment('公司統編');
            $table->string('contact_window', 24)->comment('聯絡人');
            $table->string('phone', 16)->comment('聯絡電話');
            $table->string('email', 100)->comment('Email');
            $table->string('message')->comment('訊息');
            $table->string('line_id', 40)->nullable()->comment('LINE ID');
            $table->boolean('status')->default(false)->comment('是否已處理');
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
        Schema::dropIfExists('join_partners');
    }
}
