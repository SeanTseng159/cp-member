<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImportSupplierToLinestore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('backend')->table('suppliers', function (Blueprint $table) {
            $table->tinyInteger('supplier_import_linepay_store')->after('supplier_address')->default(0)->comment('是否匯入linepay地圖 0:尚未匯入;1:已匯入');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('backend')->table('suppliers', function (Blueprint $table) {
            $table->dropColumn('supplier_import_linepay_store');
        });
    }
}
