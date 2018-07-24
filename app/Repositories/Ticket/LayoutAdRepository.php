<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\LayoutAd;
use Carbon\Carbon;

class LayoutAdRepository extends BaseRepository
{

    public function __construct(LayoutAd $model)
    {
        $this->model = $model;
    }

    /**
     * 取首頁Banner
     * @return mixed
     */
    public function getByArea($areaId = 0, $lang)
    {
        $date = Carbon::now()->toDateTimeString();
        $slide = $this->model->notDeleted()
                            ->where('layout_ad_area_id', $areaId)
                            ->where('layout_ad_lang', $lang)
                            ->where('layout_ad_status', 1)
                            ->where('layout_ad_starttime', '<=', $date)
                            ->where('layout_ad_endtime', '>=', $date)
                            ->orderBy('layout_ad_top', 'desc')
                            ->orderBy('layout_ad_sort', 'asc')
                            ->get();

        return $slide;
    }
}
