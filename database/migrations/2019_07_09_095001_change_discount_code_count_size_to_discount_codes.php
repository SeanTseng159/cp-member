<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDiscountCodeCountSizeToDiscountCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::connection('backend')->table('discount_codes', function (Blueprint $table) {
            $table->integer('discount_code_limit_count')->unsigned()->after('discount_code_limit_price')->default(1)->comment('優惠代碼有效總數')->change();
            $table->integer('discount_code_used_count')->unsigned()->after('discount_code_limit_count')->default(0)->comment('優惠代碼已使用總數')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
