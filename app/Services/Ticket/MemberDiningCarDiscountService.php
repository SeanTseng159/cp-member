<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

                            
use App\Repositories\Ticket\MemberDiningCarDiscountRepository;
use App\Repositories\Ticket\MemberGiftItemRepository;
use App\Services\BaseService;


class MemberDiningCarDiscountService extends BaseService
{
    protected $repository;

    public function __construct(MemberDiningCarDiscountRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * 取得優惠卷清單
     *
     * @param      $type
     * @param      $memberId
     *
     * @return mixed
     */

    public function list($type, $memberId)
    {
        return $this->repository->list($type, $memberId);
    }

    public function find($id, $memberId)
    {
        return $this->repository->find($id, $memberId);
    }

    public function availableDiscount($memberId)
    {
        return $this->repository->availableDiscount($memberId);
        
    }

    public function createQrcode($memberId,$discountID,$code)
    {
        return $this->repository->createQrcode($memberId,$discountID,$code);

    }

    public function checkOnlyOne($discountID,$memberId)
    {
        return $this->repository->checkOnlyOne($discountID,$memberId);
    }

}
