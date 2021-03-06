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
use App\Models\Ticket\DiningCarMember;
use Illuminate\Database\QueryException;
use Exception;

class DiningCarMemberRepository extends BaseRepository
{

    public function __construct(DiningCarMember $model)
    {
        $this->model = $model;
    }

    /**
     * 新增
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function add($memberId = 0, $id = 0)
    {
        try {
            $model = new DiningCarMember;
            $model->member_id = $memberId;
            $model->dining_car_id = $id;
            $model->save();

            return $model;
        } catch (QueryException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 刪除
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function delete($memberId = 0, $id = 0)
    {
        return $this->model->where('member_id', $memberId)
                            ->where('dining_car_id', $id)
                            ->delete();
    }

    /**
     * 取單一
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function find($memberId = 0, $id = 0)
    {
        return $this->model->with([
                                'diningCar.memberLevels'
                            ])
                            ->where('member_id', $memberId)
                            ->where('dining_car_id', $id)
                            ->first();
    }

    /**
     * 取列表
     * @param $memberId
     * @param $params [page, limit]
     * @return mixed
     */
    public function list($memberId = 0, $params = [])
    {
        /*$currentPage = $params['page'];
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });*/     


        return $this->model->with([
                            'diningCar.mainImg',
                            'diningCar.category',
                            'diningCar.subCategory',
                            'diningCar.memberLevels',
                            'diningCar.currentQuestion'
                        ])
                        ->select('dining_car_id', 'member_id', 'amount')
                        ->where('member_id', $memberId)
                        //->paginate($params['limit']);
                        ->paginate(300);
    }

    //利用memberId 及餐車ID 查找現在是否可以升等!
    public function findLevel($memberId = 0, $dining_car_id = 0)
    {
        $data=$this->model->with('diningCar.memberLevels')
                        ->where('member_id', $memberId)
                        ->where('dining_car_id', $dining_car_id)
                        ->first();
        //echo($data->diningCar);                
        //echo($data->diningCar->memberLevels);

        return $data;


    }
}