<?php
/**
 * User: Lee
 * Date: 2018/11/20
 * Time: 上午 9:04
 */

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\OrderDiscountRepository;
use App\Repositories\EmployeeRepository;


class CartService
{
    private $repository;
    private $orderDiscountRepository;
    private $employeeRepository;
    public function __construct(CartRepository $repository, 
                                OrderDiscountRepository $orderDiscountRepository,
                                EmployeeRepository $employeeRepository)
    {
        $this->repository = $repository;
        $this->orderDiscountRepository = $orderDiscountRepository;
        $this->employeeRepository = $employeeRepository;
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
        //整理資料 
        $cart=$this->countDiscount($cart, $discount, $memberId);
        
        //儲存到redis
        //$this->add('buyNow', $memberId, serialize($cart));

        $data = new \stdClass();
        $data->DiscountCode = $cart->DiscountCode ;
        $data->totalAmount = $cart->totalAmount;
        $data->discountAmount =  $cart->DiscountCode->amount;
        $data->discountTotalAmount = $cart->totalAmount - $cart->DiscountCode->amount;
        $data->payAmount = $data->discountTotalAmount +  $cart->shippingFee;
        $data->shippingFee = $cart->shippingFee;        

        return $data;
    }


    //整理判斷 優惠倦計算
    public function countDiscount($cart, $discount, $memberId)
    {        
        if (empty($cart)) return false;
        //購物內容是否有達到最低金額
        
        if($cart->totalAmount < $discount->discount_code_limit_price) return false;
        //是否為首購
        
        if($discount->discount_first_type == 1)
        {
            $firstCheck =  $this->orderDiscountRepository->firstBuyCheck($memberId);
            if(!$firstCheck) return false;
        }
        
        foreach ($cart->items as $cartItem) {
            // 判斷商品在可用清單中tag_prods
            $tagCheck = $this->orderDiscountRepository->tagCheck($discount->discount_code_id, $cartItem->id);
            if(!$tagCheck) return false;
            // 判斷商品不在排除清單中
            $prodCheck = $this->orderDiscountRepository->prodCheck($discount->discount_code_id, $cartItem->id);
            if(!$prodCheck) return false;
        }

        // 折扣金額：1.折扣(x) 2.折價(-)  加價購(?)
        $amount = 0;
        $discount_code_type = $discount->discount_code_type;
        $discount_code_price = $discount->discount_code_price;
        $discount_code_off_max = $discount->discount_code_off_max;
        // 如果折抵 
        if(1 == $discount_code_type){
            $discountPrice = round($cart->totalAmount * (100 - $discount_code_price) / 100);
            $amount = $discountPrice > $discount_code_off_max && $discount_code_off_max > 0?$discount_code_off_max:$discountPrice;
        }else{
            $amount = $discount_code_price;
        }

        if ($amount > $cart->totalAmount)
            $amount = $cart->totalAmount;
        
        //以下待重購
        $DiscountCode = new \stdClass();
        $DiscountCode->id = $discount->discount_code_id;
        $DiscountCode->name = $discount->discount_code_name;
        $DiscountCode->method = $discount->discount_code_type;
        $DiscountCode->price = $discount->discount_code_price;
        $DiscountCode->amount = $amount;
        $cart->DiscountCode = $DiscountCode;
        $cart->discountAmount =  $DiscountCode->amount;
        $cart->discountTotalAmount = $cart->totalAmount - $DiscountCode->amount;
        $cart->payAmount = $cart->discountTotalAmount +  $cart->shippingFee;

        return $cart;
    }

    /**
     * 透過CartNumber找到該購物車的店舖dining_car_id
     * @param $cartNumber
     * @return int $dining_car_id
     */
    public function getDingingCarIDByCartNumber($cartNumber)
    {
        /*此function主要要透過cartNumber參數，找到這台購物車的產品是哪個店車(dining_car)所有
          cart_item_type的備註與實際作用不同，此欄位目前看來實際是紀錄此購物車內的產品是由哪個供應商(suppliers)所提供，也就是cart_item_type其實確切是supplier_id
          而有同時記錄suppiers與dining_car_id兩欄位的資料表其實是employees資料表
          故此function會到employee資料表內尋找supplier_id = cartNumber的欄位，並抓出其中有的dining_car_id
        */

        $dining_car_id = $this->employeeRepository->getDiningCarID($cartNumber);
        return $dining_car_id;
    }
}
