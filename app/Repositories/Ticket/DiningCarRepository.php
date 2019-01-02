<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\DiningCar;

class DiningCarRepository extends BaseRepository
{

    public function __construct(DiningCar $model)
    {
        $this->model = $model;
    }

    /**
     * 取列表
     * @return mixed
     */
    public function list()
    {
        return $this->model->with(['category', 'subCategory'])
                            ->where('status', 1)
                            ->get();
    }
}
