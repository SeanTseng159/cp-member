<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::connection('backend')->create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('folder',255)->comment('資料夾名稱');
            $table->string('filename',255)->comment('檔案名稱');
            $table->string('ext',255)->comment('副檔名');
            $table->timestamp('version')->comment('版號');
            $table->string('origin_name')->comment('原始檔名');
            $table->integer('width')->comment('寬');
            $table->integer('height')->comment('高');
            $table->integer('size')->comment('檔案大小');
            $table->json('compressed_info')->comment('壓縮檔案資訊 b:大圖;m:中圖;s:小圖 ex.{"compressed_size_tags":["b", "m", "s"]}, "compressed_size":[1920, 960, 480] ');
            $table->tinyInteger('sort',false,true)->comment('排序圖片排序1為封面');
            $table->string('model_name')->comment('使用namespace全名');
            $table->enum('model_type', ['dining_car'])->comment('使用的地方，ex.餐車');
            $table->integer('model_spec_id')->unsigned()->comment('使用之(商品、餐車、商店)的id');
            $table->index('model_type');
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
        Schema::connection('backend')->dropIfExists('images');
    }
}
