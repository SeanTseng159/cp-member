<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password')->comment('密碼');
            $table->string('socialld', 20)->nullable()->comment('身分證字號/護照號碼');
            $table->string('name', 200)->comment('姓名');
            $table->string('nick', 200)->nullable()->comment('匿稱');
            $table->tinyInteger('gender')->default(0)->comment('性別 1:男 2:女');
            $table->date('birthday')->nullable()->comment('出生年月日');
            $table->string('country', 6)->nullable()->comment('國籍代碼');
            $table->string('countryCode', 6)->nullable()->comment('電話國碼');
            $table->string('cellphone', 12)->nullable()->comment('行動電話號碼');
            $table->string('zipcode', 5)->nullable()->comment('郵遞區號');
            $table->string('county', 20)->nullable()->comment('縣市');
            $table->string('district', 20)->nullable()->comment('鄉鎮區');
            $table->string('address')->nullable()->comment('地址');
            $table->string('openPlateform')->comment('註冊來源 0:citypass 1: ipass 2: facebook 3: google');
            $table->string('openid', 30)->nullable()->comment('OPENID帳號');
            $table->string('avatar', 120)->nullable()->comment('大頭照');
            $table->string('active_code', 6)->comment('驗證碼');
            $table->string('token', 191)->nullable()->comment('金鑰')->unique();
            $table->string('memo', 255)->nullable()->comment('備註');
            $table->boolean('status')->default(false)->comment('啟用狀態 0:未啟用 1:啟用');
            $table->string('modifier', 30)->nullable()->comment('修改者');
            $table->softDeletes();
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
        Schema::table('members', function (Blueprint $table) {
            Schema::dropIfExists('members');
        });
    }
}
