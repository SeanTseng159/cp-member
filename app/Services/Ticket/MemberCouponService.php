<?php
/**
 * User: annie
 * Date: 2019/2/15
 * Time: 下午 02:40
 */


namespace App\Services\Ticket;

use App\Repositories\Ticket\MemberCouponRepository;
use App\Services\BaseService;

class MemberCouponService extends BaseService
{
    protected $repository;
    
    public function __construct(MemberCouponRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * 取得使用者之優惠卷列表
     *
     * @param      $params
     * @param null $couponId
     *
     * @return mixed
     */
    public function list($params,$couponId = null)
    {
        return $this->repository->list($params,$couponId);
    }
    
    /**
     * 取優惠卷詳細
     *
     * @param  $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }
}

{
    
}