<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;




use App\Repositories\Ticket\DiningCarPointRecordRepository;
use App\Services\BaseService;


class DiningCarPointRecordService extends BaseService
{
    protected $repository;
    
    public function __construct(DiningCarPointRecordRepository $repository)
    {
        $this->repository = $repository;
    }
    public function total($diningCarId,$memberID)
    {
        return $this->repository->total($memberID,$diningCarId);
    }
    public function create($memberId,$diningCarId,$point,$expired_at,$giftId,$qty)
    {

        return $this->repository->create($memberId,$diningCarId,$point,$expired_at,$giftId,$qty);
    }
    public function getPointRecord($type,$memberId)
    {
        return $this->repository->getRecordList($type,$memberId);
    }



}
