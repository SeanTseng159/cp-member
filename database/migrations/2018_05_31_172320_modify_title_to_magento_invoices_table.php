<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTitleToMagentoInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('magento_invoices', function (Blueprint $table) {
            $table->string('title', 64)->nullable()->comment('發票統編抬頭')->change();
            $table->string('ubn', 16)->nullable()->comment('發票統編號碼')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('magento_invoices', function (Blueprint $table) {
            $table->string('title', 64)->comment('發票統編抬頭')->change();
            $table->string('ubn', 16)->comment('發票統編號碼')->change();
        });
    }
}
