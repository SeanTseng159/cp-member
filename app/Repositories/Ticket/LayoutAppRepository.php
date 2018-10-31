<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\LayoutApp;
use Carbon\Carbon;

class LayoutAppRepository extends BaseRepository
{
    private $date;

    public function __construct(LayoutApp $model)
    {
        $this->model = $model;

        $this->date = Carbon::now()->toDateTimeString();
    }

    /**
     * 取全部
     * @return mixed
     */
    public function all()
    {
        $data = $this->model->where('status', 1)
                            ->where('start_time', '<=', $this->date)
                            ->where('end_time', '>=', $this->date)
                            ->orderBy('sort', 'asc')
                            ->get();

        return $data;
    }

    /**
     * 取顯示於首頁app
     * @return mixed
     */
    public function findInHome()
    {
        $slide = $this->model->where('status', 1)
                            ->where('index_display', 1)
                            ->where('start_time', '<=', $this->date)
                            ->where('end_time', '>=', $this->date)
                            ->first();

        return $slide;
    }
}
