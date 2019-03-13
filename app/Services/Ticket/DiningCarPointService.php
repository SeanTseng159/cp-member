<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;




use App\Repositories\Ticket\DiningCarPointRepository;
use App\Services\BaseService;


class DiningCarPointService extends BaseService
{
    protected $repository;
    
    public function __construct(DiningCarPointRepository $repository)
    {
        $this->repository = $repository;
    }
    public function total($diningCarId,$memberID)
    {
        return $this->repository->total($memberID,$diningCarId);

    }


}
