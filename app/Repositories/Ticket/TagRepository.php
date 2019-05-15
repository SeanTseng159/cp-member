<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\Tag;

class TagRepository extends BaseRepository
{

    public function __construct(Tag $model)
    {
        $this->model = $model;
    }

    /**
     * 取選單資料
     * @return mixed
     */
    public function all($lang)
    {
        return $this->model->with(['subMenus' => function($query) {
                                return $query->where('tag_status', 1)
                                            ->where('tag_type', 5)
                                            ->orderBy('tag_top', 'desc')
                                            ->orderBy('tag_sort', 'asc')
                                            ->get();
                            }])
                            ->where('tag_upper_id', 0)
                            ->where('tag_type', 5)
                            ->where('tag_status', 1)
                            ->orderBy('tag_top', 'desc')
                            ->orderBy('tag_sort', 'asc')
                            ->get();
    }

    /**
     * 取單一選單資料
     * @return mixed
     */
    public function one($lang, $id)
    {
        return $this->model->with(['subMenus' => function($query) {
                                return $query->where('tag_status', 1)
                                            ->where('tag_type', 5)
                                            ->orderBy('tag_top', 'desc')
                                            ->orderBy('tag_sort', 'asc')
                                            ->get();
                            }])
                            ->where('tag_id', $id)
                            ->where('tag_status', 1)
                            ->get();
    }

    /**
     * 取單一選單資料
     * @return mixed
     */
    public function oneWithUpperId($lang, $id)
    {
        return $this->model->with(['subMenus' => function($query) {
                                return $query->where('tag_status', 1)
                                            ->where('tag_type', 5)
                                            ->orderBy('tag_top', 'desc')
                                            ->orderBy('tag_sort', 'asc')
                                            ->get();
                            }])
                            ->where('tag_id', $id)
                            ->where('tag_upper_id', 0)
                            ->where('tag_status', 1)
                            ->first();
    }

    /**
     * 取所有子選單
     * @return mixed
     */
    public function getSubTagsOnlyId($lang, $id)
    {
        return $this->model->select('tag_id')
                            ->where('tag_upper_id', $id)
                            ->where('tag_type', 5)
                            ->where('tag_status', 1)
                            ->get();
    }
}
