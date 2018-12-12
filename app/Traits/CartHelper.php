<?php
/**
 * User: lee
 * Date: 2018/11/22
 * Time: 上午 10:03
 */

namespace App\Traits;

use App\Services\Ticket\ProductService;
use App\Services\Ticket\PaymentMethodService;
use App\Result\PaymentInfoResult;

trait CartHelper
{
    /**
     * 檢查購物車內商品狀態、金額、數量
     * @param $isPhysical
     * @return array
     */
    private function getCheckoutInfo($isPhysical)
    {
        $paymentMethodService = app()->build(PaymentMethodService::class);
        $all = $paymentMethodService->all();

        $result['payments'] = (new PaymentInfoResult)->getPayments($all);
        // 取付款方式
        $result['shipments'] = (new PaymentInfoResult)->getShipments($isPhysical);
            // 取發票方式
        $result['billings'] = (new PaymentInfoResult)->getBillings();

        return $result;
    }

    /**
     * 檢查購物車內商品狀態、金額、數量
     * @param $cart
     * @param $memberId
     * @return mixed
     */
    private function checkCartStatus($cart, $memberId)
    {
        if (!$cart) return 'E9030';

        // 檢查購物車內商品狀態
        $statusCode = $this->checkCartProductStatus($cart->items, $memberId);
        if ($statusCode !== '00000') return $statusCode;

        // 檢查數量
        if ($cart->totalQuantity <= 0) return 'E9031';

        // 檢查金額
        if ($cart->payAmount <= 0) return 'E9032';

        return '00000';
    }

    /**
     * 檢查購物車內商品狀態, 是否可購買
     * @param $product
     * @param $memberId
     * @param $isPurchase
     * @return mixed
     */
    private function checkCartProductStatus($products, $memberId, $isPurchase = false)
    {
        if (!$isPurchase && !$products) return 'E9030';

        $productService = app()->build(ProductService::class);

        foreach ($products as $product) {
            $prod = $productService->findByCheckout($product->id, $product->additional->spec->id, $product->additional->type->id);
            $statusCode = $this->checkProductStatus($prod, $memberId);
            if ($statusCode !== '00000') return $statusCode;

            // 處理加購
            if (!$isPurchase && $product->purchase) {
                $statusCode = $this->checkCartProductStatus($product->purchase, $memberId, true);
                if ($statusCode !== '00000') return $statusCode;
            }
        }

        return '00000';
    }

    /**
     * 檢查商品狀態, 是否可購買
     * @param $product
     * @param $memberId
     * @return mixed
     */
    private function checkProductStatus($product, $memberId)
    {
        // 檢查限購數量
        $buyQuantity = $product->quantity;
        if ($product->prod_type === 1 || $product->prod_type === 2) {
            if ($product->prod_limit_type === 1) {
                $memberBuyQuantity = $this->orderService->getCountByProdAndMember($product->product_id, $memberId);
                $buyQuantity += $memberBuyQuantity;
            }
            if ($buyQuantity > $product->prod_limit_num) return 'E9012';
        }
        elseif ($product->prod_type === 3) {
            if ($buyQuantity > $product->prod_plus_limit) return 'E9012';
        }

        // 檢查是否有庫存
        if ($product->prod_spec_price_stock <= 0 && $product->prod_spec_price_stock >= $product->quantity) return 'E9011';

        return '00000';
    }

    /**
     * 檢查優惠是否符合
     * @param $rule
     * @param $totalAmount
     * @param $totalQuantity
     * @return mixed
     */
    private function checkDiscountRule($rule = [], $totalAmount = 0, $totalQuantity = 0)
    {
        $ype = 'FQFP';
        $value1 = 2;
        $value2 = 499;

        if ($ype === 'FQFP' && $totalQuantity !== $value1) {
            return 'E9201';
        }
        elseif (($ype === 'DQFP' || $ype === 'DQFD') && $totalQuantity < $value1) {
            return 'E9202';
        }
        elseif (($ype === 'DPFQ' || $ype === 'DPFD') && $totalAmount < $value1) {
            return 'E9203';
        }

        return '00000';
    }
}
