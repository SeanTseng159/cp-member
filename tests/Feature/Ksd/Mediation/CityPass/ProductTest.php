<?php

namespace Tests\Feature\Ksd\Mediation\CityPass;

use Ksd\Mediation\CityPass\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    /**
     * 測試 商品清單
     *
     * @return void
     */
    public function testAll()
    {
        $product = new Product();
        $result = $product->all();
        $this->assertNotEmpty($result);
    }

    /**
     * 測試 id 查詢功能
     */
    public function testFind()
    {
        $product = new Product();
        $row = $product->find('test');
        $this->assertEmpty($row);
    }

    /**
     * 測試 關鍵字 搜尋商品功能
     */
    public function testSearch()
    {
        $product = new Product();
        $key = new \stdClass();
        $key->search = 'test';
        $result = $product->search($key);
        $this->assertEmpty($result);
    }
}
