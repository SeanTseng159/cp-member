<?php
/**
 * User: lee
 * Date: 2019/01/31
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;

use App\Models\Ticket\Menu;

class MenuRepository extends BaseRepository
{
    public function __construct(Menu $model)
    {
        $this->model = $model;
    }

    /**
     * 取單一
     * @param  $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->with(['category', 'imgs'])
                            ->where('status', 1)
                            ->find($id);
    }

    /**
     * 取關鍵字找菜單
     * @param  $keyword
     * @return mixed
     */
    public function getDiningCarsByKeyword($keyword = '')
    {
        return $this->model->select('dining_car_id')
                            ->where('status', 1)
                            ->where('name', 'like', '%' . $keyword . '%')
                            ->get();
    }
}
