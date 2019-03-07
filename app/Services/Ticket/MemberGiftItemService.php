<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;



use App\Repositories\Ticket\MemberGiftItemRepository;
use App\Services\BaseService;


class MemberGiftItemService extends BaseService
{
    protected $repository;
    
    public function __construct(MemberGiftItemRepository $repository)
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
    
    public function list($type,$memberId,$client,$clientId)
    {
        return $this->repository->list($type,$memberId,$client,$clientId);
    }
    
    
    public function find($memberID,$memberGiftId)
    {
        return $this->repository->find($memberID,$memberGiftId);
    }
    
    public function update($memberId,$memberGiftId)
    {
        return $this->repository->update($memberId,$memberGiftId);
    }
    
    public function findByItemID($id)
    {
        return $this->repository->findByItemID($id);
    }
    
    
    
}
