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
     * 取得禮物清單
     *
     * @param      $type
     * @param      $memberId
     *
     * @param null $client
     * @param null $clientId
     *
     * @return mixed
     */
    public function list($type,$memberId,$client=null,$clientId=null)
    {
        return $this->repository->list($type,$memberId,$client,$clientId);
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
