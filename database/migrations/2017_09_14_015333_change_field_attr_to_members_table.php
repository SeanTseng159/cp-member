<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldAttrToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('email')->nullable()->comment('信箱')->change();
            $table->string('password', 72)->nullable()->comment('密碼')->change();
            $table->string('name', 200)->nullable()->comment('姓名')->change();
            $table->string('countryCode', 6)->comment('電話國碼')->change();
            $table->string('cellphone', 12)->comment('行動電話號碼')->change();
            $table->string('openPlateform', 10)->comment('註冊來源 0:citypass 1: ipass 2: facebook 3: google')->change();
            $table->string('active_code', 6)->comment('手機驗證碼')->change();
            $table->renameColumn('openid', 'openId');
            $table->string('email_active_code')->after('active_code')->comment('email驗證碼');
            $table->boolean('is_registered')->default(false)->after('status')->comment('註冊狀態 0:未註冊 1:已註冊完成');
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
            $table->string('email')->nullable(false)->change();
            $table->string('password')->comment('密碼')->change();
            $table->string('name', 200)->nullable(false)->comment('姓名')->change();
            $table->string('countryCode', 6)->nullable()->comment('電話國碼')->change();
            $table->string('cellphone', 12)->nullable()->comment('行動電話號碼')->change();
            $table->string('openPlateform', 10)->comment('註冊來源 0:citypass 1: ipass 2: facebook 3: google')->change();
            $table->string('active_code', 6)->comment('驗證碼')->change();
            $table->renameColumn('openId', 'openid');
            $table->dropColumn('email_active_code');
            $table->dropColumn('is_registered');
        });
    }
}
