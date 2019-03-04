<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyMemberGift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->table('member_gift_items', function ($table) {
            $table->dropForeign('member_gift_items_member_gift_id_foreign');
        });
        
        Schema::connection('backend')->drop('member_gifts');
        
        
        Schema::connection('backend')->table('member_gift_items', function ($table) {
            $table->dropColumn('member_gift_id');
            
            $table->unsignedInteger('member_id')->comment('會員ID')->after('id');
            $table->unsignedInteger('gift_id')->comment('禮物ID')->after('member_id');
            
            $table->unique(['member_id', 'gift_id','number']);
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('backend')->create('member_gifts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned()->comment('使用者id');
            $table->integer('gift_id')->unsigned()->comment('禮物卷id');
            $table->unsignedTinyInteger('send_count')->comment('已兌換張數');
            $table->tinyInteger('count')->unsigned()->comment('已使用張數');
            $table->unique(['member_id', 'gift_id']);
            $table->timestamps();
        });
        
        Schema::connection('backend')->table('member_gift_items', function ($table) {
            $table->dropColumn('gift_id');
            $table->dropColumn('member_id');
            $table->dropUnique('member_gift_items_member_id_gift_id_number_unique');
            
            $table->integer('member_gift_id')->unsigned()->after('id');
            
            $table->foreign('member_gift_id')->references('id')->on('member_gifts');
            
            
        });
        
        
        
    }
}
