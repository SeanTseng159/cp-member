<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

                            

use App\Services\BaseService;

use App\Repositories\Ticket\MemberDiscountRepository;



class MemberDiscountService extends BaseService
{
    protected $repository;

    public function __construct(MemberDiscountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listCanUsed($memberID)
    {
        return $this->repository->listCanUsed($memberID);
    }

    public function listProdDiscount($memberID)
    {
        return $this->repository->listProdDiscount($memberID);
    }

    public function createAndCheck($data){
        return $this->repository->createAndCheck($data);
    }

    //取得有效票
    public function current($memberID){
        return $this->repository->current($memberID);
    }

    //取得失效
    public function disabled($memberID){
        return $this->repository->disabled($memberID);
    }

    //取得使用過
    public function used($memberID){
        return $this->repository->used($memberID);
    }

    // 取得
    public function setMemberCodeUsedById($memberID,$discountID){
        return $this->repository->setMemberCodeUsedById($memberID,$discountID);
    }

}
