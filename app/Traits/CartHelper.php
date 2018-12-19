<?php
/**
 * User: lee
 * Date: 2018/11/22
 * Time: 上午 10:03
 */

namespace App\Traits;

use App\Services\Ticket\ProductService;
use App\Services\Ticket\PromotionService;
use App\Services\Ticket\PaymentMethodService;
use App\Result\PaymentInfoResult;

use App\Traits\MarketHelper;

trait CartHelper
{
    use MarketHelper;

    /**
     * 檢查購物車內商品狀態、金額、數量
     * @param $isPhysical
     * @return array
     */
    private function getCheckoutInfo($isPhysical = false)
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
     * @param $cartType
     * @param $cart
     * @param $memberId
     * @return mixed
     */
    private function checkCartStatus($cartType, $cart, $memberId)
    {
        if (!$cartType || !$cart) return 'E9030';

        // 檢查購物車內商品狀態
        $statusCode = $this->checkCartProductStatus($cartType, $cart->items, $memberId);
        if ($statusCode !== '00000') return $statusCode;

        // 檢查數量
        if ($cart->totalQuantity <= 0) return 'E9031';

        // 檢查金額
        if ($cart->payAmount <= 0) return 'E9032';

        return '00000';
    }

    /**
     * 檢查購物車內商品狀態, 是否可購買
     * @param $cartType
     * @param $products
     * @param $memberId
     * @param $isPurchase
     * @return mixed
     */
    private function checkCartProductStatus($cartType, $products, $memberId, $isPurchase = false)
    {
        if (!$isPurchase && !$products) return 'E9030';

        // 賣場需取特定價格跟庫存
        if ($cartType === 'market') {
            $promotionService = app()->build(PromotionService::class);
        }
        else {
            $productService = app()->build(ProductService::class);
        }

        foreach ($products as $product) {
            // 賣場需取特定價格跟庫存
            if ($cartType === 'market') {
                $prod = $promotionService->product($product->id, $product->additional->spec->id, $product->additional->type->id);
            }
            else {
                $prod = $productService->findByCheckout($product->id, $product->additional->spec->id, $product->additional->type->id);
            }

            // 檢查商品狀態, 是否可購買
            $statusCode = $this->checkProductStatus($cartType, $prod, $product->quantity, $memberId);
            if ($statusCode !== '00000') return $statusCode;

            // 處理加購商品, 是否可購買
            if (!$isPurchase && $product->purchase) {
                $statusCode = $this->checkCartProductStatus($cartType, $product->purchase, $memberId, true);
                if ($statusCode !== '00000') return $statusCode;
            }
        }

        return '00000';
    }

    /**
     * 檢查商品狀態, 是否可購買
     * @param $cartType
     * @param $product
     * @param $memberId
     * @return mixed
     */
    private function checkProductStatus($cartType, $product, $quantity, $memberId)
    {
        if ($cartType === 'market') {
            // 檢查賣場可銷庫量是否足夠
            if ($product->marketStock <= 0 || $product->marketStock < $quantity) return 'E9011';
        }
        else {
            // 檢查限購數量
            $buyQuantity = $quantity;
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
        }

        // 檢查是否有庫存
        if ($product->prod_spec_price_stock <= 0 || $product->prod_spec_price_stock < $quantity) return 'E9011';

        return '00000';
    }

    /**
     * 檢查優惠是否符合
     * @param $promotion
     * @param $totalAmount
     * @param $totalQuantity
     * @return mixed
     */
    private function checkDiscountRule($promotion, $totalAmount = 0, $totalQuantity = 0)
    {
        $lower = $promotion->conditions->min('condition');

        // 優惠條件
        switch ($promotion->condition_type) {
            case 1:
                if ($totalAmount < $lower) return 'E9203';
                break;
            case 2:
                if ($totalQuantity < $lower) return 'E9202';
                break;
            case 3:
                if ($totalQuantity !== $lower) return 'E9201';
                break;
        }

        return '00000';
    }

    /**
     * 取符合的優惠條件
     * @param $promotion [App\Models\Ticket\Promotion]
     * @param $totalAmount
     * @param $totalQuantity
     * @return int
     */
    private function getFitCondition($promotion, $totalAmount, $totalQuantity)
    {
        $offer = 0;
        switch ($promotion->condition_type) {
            case 1:
                $offer = $this->mappingConditionOffer($promotion->conditions, $totalAmount);
                break;
            case 2:
            case 3:
                $offer = $this->mappingConditionOffer($promotion->conditions, $totalQuantity);
                break;
        }

        $condition = $promotion->conditions->where('offer', $offer)->first();

        return $this->getCondition($promotion->condition_type, $promotion->offer_type, $condition);
    }

    /**
     * 計算優惠價錢
     * @param $promotion [App\Models\Ticket\Promotion]
     * @param $totalAmount
     * @param $totalQuantity
     * @return int
     */
    private function calcDiscountAmount($promotion, $totalAmount, $totalQuantity)
    {
        if (!$promotion) return 0;

        // 取符合的優惠條件
        $fitCondition = $this->getFitCondition($promotion, $totalAmount, $totalQuantity);

        $discountAmount = 0;
        $offer = $this->getOffer($promotion->offer_type, $fitCondition['offer']);
        switch ($promotion->offer_type) {
            case 1:
                $discountAmount = $offer;
                break;
            case 2:
                $discountAmount = $totalAmount - floor($totalAmount * ($offer / 100));
                break;
            case 3:
                $discountAmount = $totalAmount - $offer;
                break;
        }

        return $discountAmount;
    }

    /**
     * 取符合優惠值
     * @param $promotionModel [App\Models\Ticket\Promotion]
     * @return int
     */
    private function mappingConditionOffer($conditions, $unit)
    {
        if (!$conditions) return 0;

        $fitUnit = 0;
        $fitOffer = 0;
        foreach ($conditions as $condition) {
            if ($unit >= $fitUnit) $fitUnit = $unit;

            if ($fitUnit >= $condition->condition) {
                $fitOffer = $condition->offer;
            }
        }

        return $fitOffer;
    }

    /**
     * 計算運費
     * @param $shippingFeeModel [App\Models\Ticket\ShippingFee || App\Models\Ticket\PromotionShipping]
     * @param $quantity
     * @return int
     */
    private function calcShippingFee($shippingFeeModel, $quantity = 0)
    {
        if (!$shippingFeeModel || !$quantity) return 0;

        $fee = 0;
        $maxQuantity = 0;
        $maxfee = 0;
        foreach ($shippingFeeModel as $feeModel) {
            if ($quantity >= $feeModel->lower && $quantity <= $feeModel->upper) {
                $fee = $feeModel->fee;
            }

            if ($feeModel->upper > $maxQuantity) {
                $maxQuantity = $feeModel->upper;
                $maxfee = $feeModel->fee;
            }
        }

        // 如果數量大於最大運費數量, 則等於最大運費
        if ($quantity > $maxQuantity) $fee = $maxfee;

        return $fee;
    }
}
