<?php

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\DiningCarPointRule;

class DiningCarPointRuleRepository extends BaseRepository
{
    /**
     * Default model.
     *
     * @var string
     */
    protected $model;

    public function __construct(DiningCarPointRule $model)
    {
        $this->model = $model;
    }

    /**
     * 依類型找單一筆
     * @param int $diningCarId
     * @param int $type
     * @return mixed
     */
    public function findByType($diningCarId = 0, $type = 0)
    {
        return $this->model->isActive()
                            ->where('type', $type)
                            ->where('dining_car_id', $diningCarId)
                            ->first();
    }
}
