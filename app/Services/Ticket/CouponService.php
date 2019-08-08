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

    //取得優惠卷倒數過期前7天前資料
    public function findCouponEndTime()
    {
        return $this->repository->findCouponEndTime();
    }  

}
