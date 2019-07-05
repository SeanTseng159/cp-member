<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountFirstTypeToDiscountCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::connection('backend')->table('discount_codes', function (Blueprint $table) {
            $table->tinyInteger('discount_first_type')->after('discount_code_value')->default(0)->comment('優惠類型 0.一般 1.首購');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropColumn('discount_codes');
    }
}
