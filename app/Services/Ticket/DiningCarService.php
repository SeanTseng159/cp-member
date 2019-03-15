<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\DiningCarRepository;
use App\Repositories\Ticket\GiftRepository;
use App\Repositories\Ticket\DiningCarPointRecordRepository as PointRecordRepository;

class DiningCarService extends BaseService
{
    protected $repository;
    protected $giftRepository;
    protected $pointRecordRepository;

    public function __construct(DiningCarRepository $repository, GiftRepository $giftRepository, PointRecordRepository $pointRecordRepository)
    {
        $this->repository = $repository;
        $this->giftRepository = $giftRepository;
        $this->pointRecordRepository = $pointRecordRepository;
    }

    /**
     * 取列表
     * @param  $params
     * @return mixed
     */
    public function list($params = [])
    {
        return $this->repository->list($params);
    }

    /**
     * 取地圖列表
     * @param  $params
     * @return mixed
     */
    public function map($params = [])
    {
        return $this->repository->map($params);
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function find($id = 0, $memberId = 0)
    {
        $diningCar = $this->repository->find($id, $memberId);

        // 取禮物數
        if ($diningCar) {
            $diningCar->giftCount = ($memberId) ? $this->giftRepository->getMemberGiftItemsCountByDiningCarId($memberId, $id) : 0;
            $diningCar->totalCount = ($memberId) ? $this->pointRecordRepository->getTotalPointByDiningCarId($memberId, $id) : 0;
        }

        return $diningCar;
    }
}
