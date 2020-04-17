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
    public function info()
    {
        return $this->repository->info();
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
     * 取得一次性購物車資訊並加入購物車(依來源)
     * @param $parameter
     * @return mixed
     */
    public function oneOff($parameter)
    {
        return $this->repository->oneOff($parameter);
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
     * 刪除購物車快取
     */
    public function cleanCache()
    {
        return $this->repository->cleanCache();
    }

    /**
     * 刪除購物車快取_magento
     */
    public function cleanCacheMagento()
    {
        return $this->repository->cleanCacheMagento();
    }

    /**
     * 刪除購物車快取_ct_pass
     */
    public function cleanCacheCityPass()
    {
        return $this->repository->cleanCacheCityPass();
    }
    
    /**
     * 取得過期購物車會員 id
     * @param type $expire_days 過期天數
     * @return array
     */
    public function expiredCartMemberIds($source, $expire_days)
    {
        return $this->repository->expiredCartMemberIds($source, $expire_days);
    }
    
    /**
     * 刪除過期購物車紀錄
     * @param string $source
     * @param int $memberId
     */
    public function deleteExpiredCart($source, $memberId, $itemIds)
    {
        try {
            $this->deleteByItemIds($source, $memberId, $itemIds);
            $this->repository->deleteExpiredRecord($source, $memberId);
            $result = true;
        } catch (Exception $ex) {
            $result = false;
        }
        return $result;
    }
    
    private function deleteByItemIds($source, $memberId, $itemIds)
    {
        return $this->repository->deleteByItemIds($source, $memberId, $itemIds);
    }
    
    /**
     * 更新購物車有效時間
     */
    private function updateExpiredDate($parameters)
    {
        return $this->repository->updateExpiredDate($parameters);
    }
    
}
