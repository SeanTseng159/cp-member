<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;


use App\Repositories\Ticket\CouponRepository;
use App\Services\BaseService;


class CouponService extends BaseService
{
    protected $repository;
    
    public function __construct(CouponRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取得該店家(或餐車)之優惠卷列表
     * @param $modelSpecID
     * @param $modelType
     * @return mixed
     */
    public function list($modelSpecID,$modelType)
    {
        return $this->repository->list($modelSpecID,$modelType);
    }

    /**
     * 取得會員領取店家優惠 可使用
     * @param $memberID
     * @return mixed
     */
    public function memberCurrentCouponlist($memberID) 
    {
        return $this->repository->memberCurrentCouponlist($memberID);
    }

    /**
     * 取得會員領取店家優惠 已使用
     * @param $memberID
     * @return mixed
     */
    public function memberUsedCouponlist($memberID) 
    {
        return $this->repository->memberUsedCouponlist($memberID);
    }

    /**
     * 取得會員領取店家優惠 已失效
     * @param $memberID
     * @return mixed
     */
    public function memberDisabledCouponlist($memberID) 
    {
        return $this->repository->memberDisabledCouponlist($memberID);
    }

    /**
     * 會員領取店家優惠券
     * @param $data
     * @return mixed
     */
    public function createAndCheck($data){
        return $this->repository->createAndCheck($data);
    }
    
    /**
     * 取詳細coupon資料
     *
     * @param int $id
     *
     * @return mixed
     */
    public function find($id = 0)
    {
        return $this->repository->find($id);
    }

    /**
     * 依據優惠卷編號，查詢coupon資料
     * @param $code
     * @return mixed
     */
    public function getEnableCouponByCode($code) 
    {
        return $this->repository->getEnableCouponByCode($code);
    }

    //取得優惠卷倒數過期前7天前資料
    public function findCouponEndTime()
    {
        return $this->repository->findCouponEndTime();
    }  

}
