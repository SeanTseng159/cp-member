<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;


use App\Models\AVR\Activity;
use App\Repositories\BaseRepository;


class ActivityRepository extends BaseRepository
{
    private $limit = 20;
    protected $missionModel;

    public function __construct(Activity $model)
    {

        $this->missionModel = $model;
    }

    public function list()
    {
        $launchData = $this->missionModel->launched()->orderBy('sort')->get();
        return $launchData;
    }


    public function detail($id)
    {
        $data = $this->missionModel
            ->where('id', $id)
            ->first();
        return $data;

    }

}
