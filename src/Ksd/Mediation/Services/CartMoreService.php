<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:04
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Repositories\CartMoreRepository;
use Ksd\Mediation\Config\ProjectConfig;


class CartMoreService
{
    private $repository;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->repository = new CartMoreRepository($memberTokenService);
    }

    /**
     * 取得購物車簡易資訊
     * @return mixed
     */
    public function info($cartNumber)
    {
        return $this->repository->info($cartNumber);
    }

    /**
     * 取得購物車資訊
     * @return mixed
     */
    public function detail()
    {
        return $this->repository->detail();
    }

    /**
     * 取得購物車詳細資料
     * @return mixed
     */
    public function mine($cartNumber)
    {
        return $this->repository->mine($cartNumber);
    }

    /**
     * 取得購物車資訊
     * @param $parameter
     * @return mixed
     */
    public function getCartByMemberId($memberId)
    {
        return $this->repository->getCartByMemberId($memberId);
    }

 
    /**
     * 商品加入購物車
     * @param $parameters
     * @return bool
     */
    public function add($parameters)
    {
        $result = $this->repository->add($parameters);
        ($result) ? $this->updateExpiredDate($parameters) : '';
        return $result;
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {
        return $this->repository->update($parameters);
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     * @return bool
     */
    public function delete($parameters)
    {
        return $this->repository->delete($parameters);
    }


    
    
    /**
     * 更新購物車有效時間
     */
    private function updateExpiredDate($parameters)
    {
        return $this->repository->updateExpiredDate($parameters);
    }
    
}
