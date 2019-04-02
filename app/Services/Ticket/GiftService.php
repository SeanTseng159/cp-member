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
use Illuminate\Database\Eloquent\Collection;


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

    /**
     * 取得禮物兌換清單
     *
     * @param $modelType
     * @param $modelSpecId
     * @return mixed
     */
    public function list($modelType, $modelSpecId)
    {
        return $this->repository->list($modelType, $modelSpecId);
    }

    /**
     *  取得禮物資訊(屬於餐車)
     */
    public function getWithDiningCar($giftId)
    {
        return $this->repository->getWithDiningCar($giftId);
    }

    public function getDingingCarHasBirthDayGift()
    {
        return $this->repository->getBirthdayDingingMembers();

    }

    //發禮物，更新禮物庫存量與會員禮物清單
    public function deliveryGifts($gifts,$memberGiftItems)
    {
        return $this->repository->deliveryGifts($gifts,$memberGiftItems);
    }

}
