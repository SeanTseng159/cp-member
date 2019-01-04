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
    private $limit = 20;

    public function __construct(DiningCar $model)
    {
        $this->model = $model;
    }

    /**
     * 取列表總數
     * @param  $params
     * @return mixed
     */
    public function listCount($params = [])
    {
        return $this->model->with(['category', 'subCategory'])
                            ->where('status', 1)
                            ->when($params['keyword'], function($query) use ($params) {
                                $query->where('name', 'like', '%' . $params['keyword'] . '%');
                            })
                            ->when($params['county'], function($query) use ($params) {
                                $query->where('county', $params['county']);
                            })
                            ->when($params['category'], function($query) use ($params) {
                                $query->where('dining_car_category_id', $params['category']);
                            })
                            ->where(function($query) use ($params) {
                                if (!is_null($params['openStatus'])) {
                                    $query->where('open_status', $params['openStatus']);
                                }
                            })
                            ->count();
    }

    /**
     * 取列表
     * @param  $params
     * @return mixed
     */
    public function list($params = [])
    {
        $offset = ($params['page'] - 1) * $params['limit'];

        return $this->model->with(['category', 'subCategory'])
                            ->where('status', 1)
                            ->when($params['keyword'], function($query) use ($params) {
                                $query->where('name', 'like', '%' . $params['keyword'] . '%');
                            })
                            ->when($params['county'], function($query) use ($params) {
                                $query->where('county', $params['county']);
                            })
                            ->when($params['category'], function($query) use ($params) {
                                $query->where('dining_car_category_id', $params['category']);
                            })
                            ->where(function($query) use ($params) {
                                if (!is_null($params['openStatus'])) {
                                    $query->where('open_status', $params['openStatus']);
                                }
                            })
                            ->limit($params['limit'])
                            ->offset($offset)
                            ->get();
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function find($id = 0)
    {
        return $this->model->with([
                                'category',
                                'subCategory',
                                'socialUrls',
                                'businessHoursDays.times',
                                'businessHoursDates'
                            ])
                            ->find($id);
    }
}
