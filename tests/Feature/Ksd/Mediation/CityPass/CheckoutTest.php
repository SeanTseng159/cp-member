<?php

namespace Tests\Feature\Ksd\Mediation\CityPass;

use Ksd\Mediation\CityPass\Cart;
use Ksd\Mediation\CityPass\Checkout;
use Ksd\Mediation\Helper\EnvHelper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutTest extends TestCase
{
    use EnvHelper;

    private $cart;
    private $checkout;

    /**
     * 初始化購物車
     * @before
     */
    public function init()
    {
        $this->cart = new Cart();
        $this->checkout = new Checkout();
        $this->token = $this->env('MAGENTO_CUSTOMER_TOKEN');
        $this->cart->authorization($this->token);
        $this->checkout->authorization($this->token);
    }

    /**
     * 取得結帳資訊
     *
     * @return void
     */
    public function testInfo()
    {
        $this->addCart();
        $result = $this->checkout->info();
        $this->assertTrue(true);
        $this->deleteCart();
    }

    /**
     * 測試購物車增加商品功能
     */
    public function addCart()
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
     * @depends testUpdate
     * 測試購物車刪除功能
     */
    public function deleteCart()
    {
        $parameter['id'] = 19;
        $result = $this->cart->delete($parameter);
        $this->assertTrue($result);
        $this->assertFalse($this->checkProduct($parameter));
    }
}
