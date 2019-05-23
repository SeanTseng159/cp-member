<?php
/**
 * User: lee
 * Date: 2019/01/31
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;

use App\Models\Ticket\MenuCategory;

class MenuCategoryRepository extends BaseRepository
{
    public function __construct(MenuCategory $model)
    {
        $this->model = $model;
    }

    /**
     * 取列表
     * @param  $params
     * @return mixed
     */
    public function list($params = [])
    {
        return $this->model->with(['menus.mainImg', 'menus.prodSpecPrice'])
                            ->where('dining_car_id', $params['diningCarId'])
                            ->orderBy('sort', 'asc')
                            ->get();
    }
}
