<?php

namespace Tests\Feature\Ksd\Mediation\CityPass;

use Ksd\Mediation\CityPass\Cart;
use Ksd\Mediation\Helper\EnvHelper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use EnvHelper;

    protected $cart;
    protected $token;


    /**
     * 初始化購物車
     * @before
     */
    public function init()
    {
        $this->cart = new Cart();
        $this->token = $this->env('MEMBER_TOKEN');
        $this->cart->authorization($this->token);
    }


    /**
     * 測試購物車簡易資料
     *
     * @return void
     */
    public function testInfo()
    {
        $info = $this->cart->info();
        $this->assertNotEmpty($info);
    }

    /**
     * 測試購物車詳細資料
     */
    public function testDetail()
    {
        $detail = $this->cart->detail();
        $this->assertNotEmpty($detail);
    }

    /**
     * 測試購物車增加商品功能
     */
    public function testAdd()
    {
        $parameter['id'] = 8;
        $parameter['quantity'] = 1;
        $parameter['additionals']['priceId'] = 19;
        $parameter['additionals']['usageTime'] = '';

        $result = $this->cart->add($parameter);

        $this->assertTrue($result);
        $this->assertTrue($this->checkProduct($parameter));
    }

    /**
     * @depends testAdd
     * 測試購物車更新功能
     */
    public function testUpdate()
    {
        $parameter['id'] = 8;
        $parameter['quantity'] = 2;
        $parameter['additionals']['priceId'] = 19;
        $parameter['additionals']['usageTime'] = '';

        $result = $this->cart->update($parameter);
        $this->assertTrue($result);
        $this->assertTrue($this->checkProduct($parameter));
    }

    /**
     * @depends testUpdate
     * 測試購物車刪除功能
     */
    public function testDelete()
    {
        $parameter['id'] = 19;
        $result = $this->cart->delete($parameter);
        $this->assertTrue($result);
        $this->assertFalse($this->checkProduct($parameter));
    }

    /**
     * 檢查購物車商品數量是否更新
     * @param $product
     * @return bool
     */
    private function checkProduct($product)
    {
        $detail = $this->cart->detail();
        foreach ($detail->items as $item) {
            if ($item->id == $product['id'] && $item->qty == $product['quantity']) {
                return true;
            }
        }
        return false;
    }
}
