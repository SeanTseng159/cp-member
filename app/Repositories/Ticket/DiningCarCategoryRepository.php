<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\DiningCarCategory;

class DiningCarCategoryRepository extends BaseRepository
{

    public function __construct(DiningCarCategory $model)
    {
        $this->model = $model;
    }

    /**
     * 取列表
     * @param $type ['all', 'main', 'sub']
     * @return mixed
     */
    public function categories($type = 'all', $id = 0)
    {
        return $this->model->where(function($query) use ($type, $id) {
                                if ($type === 'main' || $type === 'sub') {
                                    return $query->where('parent_id', $id);
                                }
                            })
                            ->where('status', 1)
                            ->get();
    }
}
