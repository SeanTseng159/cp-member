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
    protected $model;

    public function __construct(Activity $model)
    {

        $this->model = $model;
    }

    public function list()
    {


        $launchData = $this->model->launched()->orderBy('sort')->get();
        return $launchData;

    }


}
