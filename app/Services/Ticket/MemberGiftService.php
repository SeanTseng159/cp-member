<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Repositories\Ticket\MemberGiftRepository;
use App\Services\BaseService;


class MemberGiftService extends BaseService
{
    protected $repository;
    
    public function __construct(MemberGiftRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取得該店家(或餐車)之優惠卷列表
     * @param  $params
     * @return mixed
     */
    public function list($params)
    {
        return $this->repository->list($params);
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
}
