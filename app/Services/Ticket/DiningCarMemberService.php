<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 * [餐車會員]
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\DiningCarMemberRepository;
use App\Repositories\Ticket\GiftRepository;

class DiningCarMemberService extends BaseService
{
    protected $repository;
    protected $giftRepository;

    public function __construct(DiningCarMemberRepository $repository, GiftRepository $giftRepository)
    {
        $this->repository = $repository;
        $this->giftRepository = $giftRepository;
    }

    /**
     * 新增
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function add($memberId = 0, $id = 0)
    {
        return $this->repository->add($memberId, $id);
    }

    /**
     * 刪除
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function delete($memberId = 0, $id = 0)
    {
        return $this->repository->delete($memberId, $id);
    }

    /**
     * 取單一
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function find($memberId = 0, $id = 0)
    {
        $memberDiningCar = $this->repository->find($memberId, $id);

        // 取禮物數
        if ($memberDiningCar) {
            $memberDiningCar->gift_count = $this->giftRepository->getMemberGiftItemsCountByDiningCarId($memberId, $id);
        }

        return $memberDiningCar;
    }

    /**
     * 是否已成為會員
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function isMember($memberId = 0, $id = 0)
    {
        $member = $this->repository->find($memberId, $id);

        return ($member) ? true : false;
    }

    /**
     * 取列表
     * @param $memberId
     * @param $params [page, limit]
     * @return mixed
     */
    public function list($memberId = 0, $params = [])
    {
        $memberDiningCars = $this->repository->list($memberId, $params);

        $total = $memberDiningCars->total();

        // 取禮物數
        if (!$memberDiningCars->isEmpty()) {
            $memberDiningCars = $memberDiningCars->transform(function ($item) use ($memberId) {
                $item->gift_count = $this->giftRepository->getMemberGiftItemsCountByDiningCarId($memberId, $item->dining_car_id);
                return $item;
            });
        }
        else {
            $memberDiningCars = $memberDiningCars->transform(function ($item) {
                $item->gift_count = 0;
                return $item;
            });
        }

        $memberDiningCars->total = $total;

        return $memberDiningCars;
    }
}
