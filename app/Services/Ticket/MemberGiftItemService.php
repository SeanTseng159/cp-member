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
    
    
    public function findByID($id)
    {
        return $this->repository->findByID($id);
    }
    
    public function update($memberId,$memberGiftId)
    {
        return $this->repository->update($memberId,$memberGiftId);
    }
    


    /**
     * 取得特定禮物的使用數
     * @param array $giftIds
     * @return mixed
     */
    public function getUsedCount(array $giftIds)
    {
        return $this->repository->getUsedCount($giftIds);
    }

    /*
     * 取得使用者對某Clinet(餐車)的未使用禮物數
     */
    public function getUserAvailableGiftCount($memberId,$modelType,$modelSepcID)
    {
        return $this->repository->getUserAvailableGiftCount($memberId,$modelType,$modelSepcID);
    }
    

    
}
