<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPrintMrtReceiptColumnToDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->table('order_details', function (Blueprint $table) {
             $table->datetime('print_mrt_certificate_at')->nullable()->default(null)->after('prod_api')->comment('列印時間');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('backend')->table('order_details', function (Blueprint $table) {
             $table->dropColumn('print_mrt_certificate_at');
        });
    }
}
