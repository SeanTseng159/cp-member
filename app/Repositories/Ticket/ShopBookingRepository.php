<?php

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;

use App\Models\Ticket\DiningCarBookingDetail;
use App\Models\Ticket\DiningCarBookingLimit;
use App\Models\Ticket\DiningCarBookingTimes;

class ShopBookingRepository extends BaseRepository
{
    /**
     * Default model.
     *
     * @var string
     */
    protected $diningCarBookingDetail;
    protected $diningCarBookingLimit;
    protected $diningCarBookingTimes;

    public function __construct(DiningCarBookingDetail $diningCarBookingDetail,DiningCarBookingLimit $diningCarBookingLimit,DiningCarBookingTimes $diningCarBookingTimes)
    {
        $this->diningCarBookingDetail = $diningCarBookingDetail;
        $this->diningCarBookingLimit = $diningCarBookingLimit;
        $this->diningCarBookingTimes = $diningCarBookingTimes;
    }

    /**
     * 依ID找單一筆
     * @param int $Shop_Id
     * @return mixed
     */
    public function findBookingLimit($id = 0)
    {
        return $this->diningCarBookingLimit
                        ->where('id', $id)
                        ->first();
    }
}
