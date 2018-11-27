<?php
/**
 * User: Lee
 * Date: 2018/11/20
 * Time: 上午 9:04
 */

namespace App\Services;

use App\Repositories\OneOffCartRepository;

class OneOffCartService
{
    private $repository;

    public function __construct(OneOffCartRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 商品加入購物車
     * @param $memberId
     * @param $data [購物車內容]
     * @return mixed
     */
    public function add($memberId, $data)
    {
        return $this->repository->add($memberId, $data);
    }

    /**
     * 更新購物車內商品
     * @param $memberId
     * @param $data [購物車內容]
     * @return bool
     */
    public function update($memberId, $data)
    {
        return $this->repository->update($memberId, $data);
    }

    /**
     * 刪除購物車內商品
     * @param $memberId
     * @return bool
     */
    public function delete($memberId)
    {
        return $this->repository->delete($memberId);
    }

    /**
     * 取購物車商品
     * @param $memberId
     * @return mixed
     */
    public function find($memberId)
    {
        return $this->repository->find($memberId);
    }
}
