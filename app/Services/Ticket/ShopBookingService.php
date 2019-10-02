<?php

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\ShopBookingRepository;

class ShopBookingService extends BaseService
{
    protected $repository;

    public function __construct(ShopBookingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 找出店鋪的相關限制
     * @param  $id
     * @return mixed
     */
    public function findBookingLimitDateTime($id)
    {
        return $this->repository->findBookingLimitDateTime($id);
    }


    /**
     * 找出後續有訂位的詳細表
     * @param  $id
     * @return mixed
     */
    public function findBookingDateBooked($id)
    {
        return $this->repository->findBookingDateBooked($id);
    }


    /**
     * 找出可訂位的詳細表
     * @param  $id
     * @return mixed
     */
    public function findBookingDateTimes($id)
    {
        return $this->repository->findBookingDateTimes($id);
    }


    /**
     * 找出某日期時間有訂位
     * @param  $id,$date,$time
     * @return mixed
     */
    public function findBookedDateTime($id,$date,$time)
    {
        return $this->repository->findBookedDateTime($id,$date,$time);
    }

    /**
     * 找出某日期時間開放的訂位人數
     * @param  $id,$date,$time
     * @return mixed
     */
    public function findBookingTimesDateTime($id,$dayOfWeek,$time)
    {
        return $this->repository->findBookingTimesDateTime($id,$dayOfWeek,$time);
    }
    /**
     * 找出今天的訂單編號
     * @param  $id
     * @return mixed
     */
    public function findBookedNumber($id)
    {
        return $this->repository->findBookedNumber($id);
    }    

    /**
     * 找出店家資訊
     * @param  $id
     * @return mixed
     */
    public function findShopInfo($id)
    {
        return $this->repository->findShopInfo($id);
    }  

    /**
     * 將訂位資料寫入DB
     * @param  $data
     */
    public function createDetail($data)
    {
        return $this->repository->createDetail($data);
    }  

    /**
     * 查詢訂單detail
     * @param  $id
     * @return data
     */
    public function getOenDetailInfo($id)
    {
        return $this->repository->getOenDetailInfo($id);
    }  

}
