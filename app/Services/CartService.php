<?php
/**
 * User: Lee
 * Date: 2018/11/20
 * Time: 上午 9:04
 */

namespace App\Services;

use App\Repositories\CartRepository;

class CartService
{
    private $repository;

    public function __construct(CartRepository $repository)
    {
        $this->repository = $repository;
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
    public function setAddDiscountCode($cart, $code, $memberId)
    {
        if (empty($cart)) return false;


        foreach ($cart->items as $cartItem) {

            // 判斷商品在可用清單中

            // 判斷商品不在排除清單中
        }

        // 比對最低可用金額

        // 折扣金額：1.折扣(x) 2.折價(-)  加價購(?)

        $cart->discountCode = $code;
        $this->add('buyNow', $memberId, serialize($cart));
    }
}
