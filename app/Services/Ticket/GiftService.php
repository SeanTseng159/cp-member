<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;


use App\Repositories\Ticket\GiftRepository;
use App\Repositories\Ticket\MemberGiftItemRepository;
use App\Services\BaseService;


class GiftService extends BaseService
{
    protected $repository;
    protected $memberGiftItemRepository;

    public function __construct(GiftRepository $repository, MemberGiftItemRepository $memberGiftItemRepository)
    {
        $this->repository = $repository;
        $this->memberGiftItemRepository = $memberGiftItemRepository;
    }


    /**
     * 依類型取詳細gift資料
     *
     * @param string $modelType
     * @param int $modelSpecId
     * @param string $type ['join', 'birthday', 'point']
     *
     * @return mixed
     */
    public function findByType($modelType = '', $modelSpecId = 0, $type = '')
    {
        return $this->repository->findByType($modelType, $modelSpecId, $type);
    }

    /**
     * 發送加入會員禮物
     *
     * @param int $diningCarId
     * @param int $memberId
     *
     * @return \App\Models\Ticket\Gift | null
     */
    public function giveAddDiningCarMemberGift($diningCarId = 0, $memberId = 0)
    {
        $result = false;
        $gift = $this->findByType('dining_car', $diningCarId, 'join');

        if ($gift) {
            $giftItem = $this->memberGiftItemRepository->create($memberId, $gift->id);
            $result = ($giftItem) ? true : false;
        }

        return ($result) ? $gift : null;
    }
}