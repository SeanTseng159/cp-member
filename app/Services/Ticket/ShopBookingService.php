<?php

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\ShopBookingRepository;
use Ksd\SMS\Services\EasyGoService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendBookingFinishMail;
class ShopBookingService extends BaseService
{
    use DispatchesJobs;
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
    public function findBookingLimit($id)
    {
        return $this->repository->findBookingLimit($id);
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
     * 找出今天某店家的訂單編號
     * @param  $id
     * @return mixed
     */
    public function findBookedNumber($id)
    {
        return $this->repository->findBookedNumber($id);
    }
    /**
     * 找出今天的全部訂單編號
     * @return mixed
     */
    public function findBookedAllNumber()
    {
        return $this->repository->findBookedAllNumber();
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

    /**
     * 取得訂單detail
     * @param  $code
     * @return data
     */
    public function getFromCode($code)
    {
        return $this->repository->getFromCode($code);
    }

    /**
     * 取消訂單
     * @param  $code
     * @return data
     */
    public function cancel($shopid,$code)
    {
        return $this->repository->cancel($shopid,$code);
    }


    /**
     * 取得訂位列表
     * @param  $memberID
     * @return data
     */
    public function getMemberList($memberId,$page)
    {
        return $this->repository->getMemberList($memberId,$page);
    }
    /**
     * 取得訂位列表的數量
     * @param  $memberID
     * @return data
     */
    public function getCountMemberList($memberId,$page)
    {
        return $this->repository->getCountMemberList($memberId,$page);
    }
    /**
     * 發送簡訊
     * @param  $host, $shopName, $userName, $cellphone, $datetime, $code
     * @return Trus or False
     */
    public function sendBookingSMS($host, $shopName, $userName, $cellphone, $datetime, $code)
    {
        try {
            //發送簡訊
            $easyGoService = new EasyGoService;
            $phoneNumber = '+886' . substr($cellphone, 1, 9);
            $web = "{$host}booking/{$code}";
            $message = "您好，您於{$shopName}已訂位完成，前往{$web} 查看";

            return $easyGoService->send($phoneNumber, $message);
        } catch (\Exception $e) {
            Logger::debug($e);
            return false;
        }
    }//end sendSMS

    /**
     * 寄送Email
     * @param $id
     * @param $data
     * @return bool
     */
    public function sendBookingEmail($member,$data)
    {
        if (!$member) return false;

        $job = (new SendBookingFinishMail($member,$data))->delay(5);
        $this->dispatch($job);


        return True;
    }//end sendemail
}
