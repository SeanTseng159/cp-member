<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyToOrderShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->table('order_shipments', function (Blueprint $table) {
            $table->string('company', 20)->after('address')->nullable()->comment('貨運公司');
            $table->datetime('delivery_time')->after('trace_code')->nullable()->comment('出貨時間');
            $table->datetime('arrived_time')->after('delivery_time')->nullable()->comment('到貨時間');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('backend')->table('order_shipments', function (Blueprint $table) {
            $table->dropColumn('company');
        });
    }
}
