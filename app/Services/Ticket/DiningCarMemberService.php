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
use App\Repositories\Ticket\DiningCarMemberLevelsRepository;
use App\Repositories\Ticket\DiningCarPointRecordRepository as PointRecordRepository;

class DiningCarMemberService extends BaseService
{
    protected $repository;
    protected $giftRepository;
    protected $pointRecordRepository;
    protected $diningCarMemberLevelsRepository;

    public function __construct(DiningCarMemberRepository $repository, GiftRepository $giftRepository, PointRecordRepository $pointRecordRepository,DiningCarMemberLevelsRepository $diningCarMemberLevelsRepository)
    {
        $this->repository = $repository;
        $this->giftRepository = $giftRepository;
        $this->pointRecordRepository = $pointRecordRepository;
        $this->diningCarMemberLevelsRepository=$diningCarMemberLevelsRepository;
    }

    /**
     * 新增
     * @param $memberId
     * @param $diningCarId
     * @return mixed
     */
    public function add($memberId = 0, $diningCarId = 0)
    {
        return $this->repository->add($memberId, $diningCarId);
    }

    /**
     * 刪除
     * @param $memberId
     * @param $diningCarId
     * @return mixed
     */
    public function delete($memberId = 0, $diningCarId = 0)
    {
        return $this->repository->delete($memberId, $diningCarId);
    }

    /**
     * 取單一
     * @param $memberId
     * @param $diningCarId
     * @return mixed
     */
    public function find($memberId = 0, $diningCarId = 0)
    {
        $memberDiningCar = $this->repository->find($memberId, $diningCarId);

        // 取禮物數
        if ($memberDiningCar) {
            $memberDiningCar->giftCount = $this->giftRepository->getMemberGiftItemsCountByDiningCarId($memberId, $diningCarId);
            $memberDiningCar->totalPoint = $this->pointRecordRepository->getTotalPointByDiningCarId($memberId, $diningCarId);
        }

        return $memberDiningCar;
    }

    /**
     * 取單一
     * @param $memberId
     * @param $diningCarId
     * @return mixed
     */
    public function easyFind($memberId = 0, $diningCarId = 0)
    {
        return $this->repository->find($memberId, $diningCarId);
    }

    /**
     * 是否已成為會員
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function isMember($memberId = 0, $diningCarId = 0)
    {
        $member = $this->repository->find($memberId, $diningCarId);

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
        return $this->repository->list($memberId, $params);
    }

    //利用memberId 及餐車ID 查找，找出餐車的升等規則,現在是否可以升等!
    public function findLevel($memberId = 0, $dining_car_id = 0)
    {
        return $this->repository->findLevel($memberId, $dining_car_id);
    }

    //利用餐車ID 查找Level，找出餐車的升等規則,現在是否可以升等!
    public function findCarLevel( $dining_car_id = 0)
    {
        return $this->diningCarMemberLevelsRepository->findCarLevel($dining_car_id);
    }
}
