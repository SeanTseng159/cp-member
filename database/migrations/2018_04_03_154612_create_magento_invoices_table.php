<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMagentoInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magento_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_id', 20)->nullable()->comment('訂單ID');
            $table->tinyInteger('type')->default(1)->comment('發票種類: 1:電子發票|2:實體發票');
            $table->tinyInteger('method')->default(1)->comment('發票方案: 1:二聯式|2:三聯式');
            $table->string('title', 64)->comment('發票統編抬頭');
            $table->string('ubn', 16)->comment('發票統編號碼');
            $table->tinyInteger('status')->default(0)->comment('發票狀態: 0:未開立|1:已開立|2:刪除|3:折讓');
            $table->timestamps();

            $table->unique('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('magento_invoices');
    }
}
