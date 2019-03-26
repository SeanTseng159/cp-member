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
use Carbon\Carbon;

class DiningCarService extends BaseService
{
    protected $repository;
    protected $giftRepository;

    public function __construct(DiningCarRepository $repository, GiftRepository $giftRepository)
    {
        $this->repository = $repository;
        $this->giftRepository = $giftRepository;
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
            $diningCar->gift_count = ($memberId) ? $this->giftRepository->getMemberGiftItemsCountByDiningCarId($memberId, $id) : 0;
        }

        return $diningCar;
    }

    /**
     * 是否為付費餐車
     * @param $id
     * @return bool
     */
    public function isPaid($id)
    {
        $diningCar = $this->repository->find($id);
        $isPaid = false;
        if ($diningCar->level >= 1 && $diningCar->expired_at >= Carbon::now()) {
            $isPaid = true;
        }
        return $isPaid;

    }
}
