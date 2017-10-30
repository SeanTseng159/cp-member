<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthClientMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_client_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oauth_client_id')->unsigned()->comment('oauth client ID');
            $table->integer('member_id')->unsigned()->comment('會員ID');
            $table->boolean('revoked')->default(true)->comment('是否取消授權');
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
        Schema::drop('oauth_client_members');
    }
}
