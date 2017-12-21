<?php

namespace Tests\Feature\Ksd\Mediation\Magento;

use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Magento\Cart;
use Ksd\Mediation\Magento\Checkout;
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
        $this->cart->userAuthorization($this->token);
        $this->checkout->userAuthorization($this->token);
    }

    /**
     * 測試取得結帳資訊
     *
     * @return void
     */
    public function testInfo()
    {
        $this->addCart();
        $result = $this->checkout->info();
        $this->assertAttributeNotEmpty('payments', $result);
        $this->assertAttributeNotEmpty('shipments', $result);

        $this->deleteCart();
    }

    /**
     * 增加購物車商品
     */
    private function addCart()
    {
        $parameter['id'] = '3M_FA-X50T';
        $parameter['quantity'] = 1;
        $this->cart->add([$parameter]);
    }

    /**
     * 刪除購物車商品
     */
    public function deleteCart()
    {
        $parameter['id'] = '3M_FA-X50T';
        $this->cart->delete([$parameter]);
    }
}
