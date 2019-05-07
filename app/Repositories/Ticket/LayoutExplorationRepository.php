<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\LayoutExploration;

class LayoutExplorationRepository extends BaseRepository
{

    public function __construct(LayoutExploration $model)
    {
        $this->missionModel = $model;
    }

    /**
     * 取首頁熱門探索
     * @return mixed
     */
    public function all($lang)
    {
        $data = $this->missionModel->with('tag')
                            ->notDeleted()
                            ->where('layout_exploration_lang', $lang)
                            ->where('layout_exploration_status', 1)
                            ->orderBy('layout_exploration_sort', 'asc')
                            ->get();

        return $data;
    }
}
