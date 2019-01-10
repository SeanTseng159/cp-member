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
}
