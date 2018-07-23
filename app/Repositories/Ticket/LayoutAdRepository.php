<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\LayoutAd;

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
    public function getSlide()
    {
        $slide = $this->model->notDeleted()
                            ->orderBy('layout_ad_sort', 'desc')
                            ->get();

        return $slide;
    }
}
