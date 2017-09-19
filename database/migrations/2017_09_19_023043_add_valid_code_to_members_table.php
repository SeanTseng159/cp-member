<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValidCodeToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->renameColumn('active_code', 'validPhoneCode');
            $table->renameColumn('email_active_code', 'validEmailCode');
            $table->renameColumn('is_registered', 'isRegistered');
            $table->boolean('isValidPhone')->default(false)->after('avatar')->comment('電話認證狀態');
            $table->boolean('isValidEmail')->default(false)->after('active_code')->comment('Email認證狀態');
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
            $table->renameColumn('validPhoneCode', 'active_code');
            $table->renameColumn('validEmailCode', 'email_active_code');
            $table->renameColumn('isRegistered', 'is_registered');
            $table->dropColumn('isValidPhone');
            $table->dropColumn('isValidEmail');
        });
    }
}
