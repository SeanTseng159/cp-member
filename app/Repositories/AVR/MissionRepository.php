<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\AVR;



use App\Models\AVR\Mission;
use App\Repositories\BaseRepository;


class MissionRepository extends BaseRepository
{
    private $limit = 20;
    protected $model;

    public function __construct(Mission $model)
    {

        $this->model = $model;
    }

    public function detail($id)
    {
        $data = $this->model->find($id);

        return $data;

    }

}
