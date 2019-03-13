<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;


use App\Models\Ticket\DiningCarPointRecord;
use App\Repositories\BaseRepository;


class DiningCarPointRepository extends BaseRepository
{
    protected $model;


    public function __construct(DiningCarPointRecord $model)
    {
        $this->model = $model;
    }

    public function total($memberId, $diningCarId)
    {
        return intval($this->model
            ->allow()
            ->where('member_id', $memberId)
            ->where('dining_car_id', $diningCarId)
            ->sum('point'));


    }

}
