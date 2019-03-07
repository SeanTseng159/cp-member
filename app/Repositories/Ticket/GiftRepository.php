<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\Gift;
use App\Repositories\BaseRepository;


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
     * 依類型取詳細gift資料
     *
     * @param string $modelType
     * @param int $modelSpecId
     * @param string $type ['join', 'birthday', 'point']
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
}
