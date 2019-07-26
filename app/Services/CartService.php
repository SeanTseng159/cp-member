<?php
/**
 * User: Lee
 * Date: 2018/11/20
 * Time: 上午 9:04
 */

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\OrderDiscountRepository;

class CartService
{
    private $repository;
    private $orderDiscountRepository;

    public function __construct(CartRepository $repository, 
                                OrderDiscountRepository $orderDiscountRepository)
    {
        $this->repository = $repository;
        $this->orderDiscountRepository = $orderDiscountRepository;
    }

    /**
     * 商品加入購物車
     * @param $type
     * @param $memberId
     * @param $data [購物車內容]
     * @return mixed
     */
    public function add($type, $memberId, $data)
    {
        return $this->repository->add($type, $memberId, $data);
    }

    /**
     * 更新購物車內商品
     * @param $type
     * @param $memberId
     * @param $data [購物車內容]
     * @return bool
     */
    public function update($type, $memberId, $data)
    {
        return $this->repository->update($type, $memberId, $data);
    }

    /**
     * 刪除購物車內商品
     * @param $type
     * @param $memberId
     * @return bool
     */
    public function delete($type, $memberId)
    {
        return $this->repository->delete($type, $memberId);
    }

    /**
     * 取購物車商品
     * @param $type
     * @param $memberId
     * @return mixed
     */
    public function find($type, $memberId)
    {
        return $this->repository->find($type, $memberId);
    }

    /**
     * 購物車加入優惠折扣碼
     * @param $cart
     * @param $code
     * @param $memberId
     * @return bool
     */
    public function setAddDiscountCode($cart, $discount, $memberId)
    {
        if (empty($cart)) return false;
        //購物內容是否有達到最低金額
        if($cart->totalAmount < $discount->discount_code_limit_price)
        {
            return false;
        }
        //是否為首購
        if($discount->discount_first_type == 1)
        {
            $firstCheck =  $this->orderDiscountRepository->firstBuyCheck($memberId);
            if(!$firstCheck) return false;
        }
        foreach ($cart->items as $cartItem) {
            // 判斷商品在可用清單中tag_prods
            $tagCheck = $this->orderDiscountRepository->tagCheck($discount->discount_code_id, $cartItem['id']);
            if(!$tagCheck) return false;
            // 判斷商品不在排除清單中
            $prodCheck = $this->orderDiscountRepository->prodCheck($discount->discount_code_id, $cartItem['id']);
            if(!$prodCheck) return false;
        }

        // 折扣金額：1.折扣(x) 2.折價(-)  加價購(?)
        switch ($discount->discount_code_type) {
            case '1':
                $format = "0.%u";
                //折扣%數
                $discount_code_price = sprintf($format,$discount->discount_code_price);
                //折扣價格(四捨五入) 總金額-乘上%數後的金額
                $cart->discountAmount = $cart->totalAmount - round($cart->totalAmount * (float)$discount_code_price);
                //總折扣金額 原本折扣金額+折扣價格
                $cart->discountTotalAmount = $cart->discountTotalAmount + $cart->discountAmount;
                $cart->payAmount = $cart->discountTotalAmount;
                //此張優惠券折抵的金額
                $amount = $cart->discountAmount;
                break;
            case '2':
                $cart->discountAmount = $cart->discountAmount + $discount->discount_code_price;
                $cart->discountTotalAmount = $cart->discountAmount + $cart->discountAmount;
                $cart->payAmount = $cart->discountTotalAmount;
                $amount = $discount->discount_code_price;
                break;
            default:
                break;
        }
        
        //以下待重購
        $DiscountCode = new \stdClass();
        $DiscountCode->id = $discount->discount_code_id;
        $DiscountCode->name = $discount->discount_code_name;
        $DiscountCode->method = $discount->discount_code_type;
        $DiscountCode->price = $discount->discount_code_price;
        $DiscountCode->amount = $amount;
        $cart->discountCode = $DiscountCode;
        $this->add('buyNow', $memberId, serialize($cart));
        $data = new \stdClass();
        $data->DiscountCode = $DiscountCode;
        $data->totalAmount = $cart->totalAmount;
        $data->discountAmount =  $cart->discountAmount;
        $data->discountTotalAmount = $data->totalAmount - $data->discountAmount;
        $data->payAmount = $data->discountTotalAmount;
        $data->shippingFee = $cart->shippingFee; $cart->discountAmount;
        return $data;
    }
}
