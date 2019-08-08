<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use Illuminate\Pagination\Paginator;
use DB;
use App\Models\Ticket\DiningCarMemberLevel;
use Illuminate\Database\QueryException;
use Exception;

class DiningCarMemberLevelsRepository extends BaseRepository
{

    public function __construct(DiningCarMemberLevel $model)
    {
        $this->model = $model;
    }

    

    //利用memberId 及餐車ID 查找現在是否可以升等!
    public function findCarLevel($dining_car_id = 0)
    {
        $data=$this->model->where('dining_car_id', $dining_car_id)->get();        
        
        return $data;

    }
}