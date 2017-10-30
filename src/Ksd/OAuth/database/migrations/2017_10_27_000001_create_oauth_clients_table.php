<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('app 名稱');
            $table->uuid('uid')->comment('app ID');
            $table->string('secret', 64)->comment('app 密碼');
            $table->string('grant_type', 20)->default('auth_code')->comment('授權方式');
            $table->text('scopes')->nullable();
            $table->text('redirect')->nullable()->comment('導向網址');
            $table->string('code', 64)->nullable()->comment('授權碼');
            $table->dateTime('expires_at')->nullable()->comment('授權到期時間');
            $table->boolean('revoked')->default(false)->comment('是否永久取消授權');
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
        Schema::drop('oauth_clients');
    }
}
