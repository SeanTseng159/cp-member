<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Enum\ClientType;
use App\Enum\GiftType;
use App\Models\Gift;
use App\Repositories\BaseRepository;
use Carbon\Carbon;


class GiftRepository extends BaseRepository
{
    private $limit = 20;

    public function __construct(Gift $model)
    {
        $this->model = $model;
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
        return $this->model->where('model_type', $modelType)
            ->where('model_spec_id', $modelSpecId)
            ->where('type', $type)
            ->isActive()
            ->first();
    }


    /**
     * 取特定餐車會員的禮物數
     *
     * @param int $memberId
     * @param int $diningCarId
     *
     * @return mixed
     */
    public function getMemberGiftItemsCountByDiningCarId($memberId = 0, $diningCarId = 0)
    {
        return $this->model->join('member_gift_items', function ($join) use ($memberId) {
            $join->on('gifts.id', '=', 'member_gift_items.gift_id')
                ->where('member_gift_items.member_id', '=', $memberId);
        })
            ->where('model_spec_id', $diningCarId)
            ->select('id')
            ->count();
    }

    /**
     * 取得店家上架的禮物清單
     * @param $modelType
     * @param $modeSpecId
     *
     * @return mixed
     */
    public function list($modelType, $modeSpecId)
    {
        $client = ClientType::transform($modelType);
        $result = $this->model->where('model_type', $client)
            ->where('model_spec_id', $modeSpecId)
            ->where('type', GiftType::point)
            ->isActive()
            ->orderBy('sort')
            ->get(['id', 'name', 'points', 'qty', 'limit_qty', 'desc', 'expire_at','content']);

        return $result;
    }


    public function getPoint($giftId)
    {
        return $this->model
            ->exchangable()
            ->where('id', $giftId)
            ->get(['id', 'points', 'qty', 'limit_qty', 'expire_at']);

    }
}
