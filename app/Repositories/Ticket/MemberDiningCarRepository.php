<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use Illuminate\Pagination\Paginator;
use App\Models\Ticket\MemberDiningCar;

class MemberDiningCarRepository extends BaseRepository
{

    public function __construct(MemberDiningCar $model)
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
        $model = new MemberDiningCar;
        $model->member_id = $memberId;
        $model->dining_car_id = $id;
        $model->save();

        return $model;
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
        return $this->model->where('member_id', $memberId)
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
        $currentPage = $params['page'];
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        return $this->model->with([
                                'diningCar.subCategory',
                                'diningCar.memberCard' => function($query) use ($memberId) {
                                    $query->where('member_id', $memberId);
                                },
                                'diningCar.memberLevels'
                            ])
                            ->where('member_id', $memberId)
                            ->whereHas('diningCar.category', function($query) use ($params) {
                                $query->when($params['category'], function ($query) use ($params) {
                                    $query->where('dining_car_category_id', $params['category']);
                                });
                            })
                            ->paginate($params['limit']);
    }

    /**
     * 依據會員取相關餐車
     * @param $memberId
     * @return mixed
     */
    public function getAllByMemberId($memberId = 0)
    {
        return $this->model->select('dining_car_id')
                            ->where('member_id', $memberId)
                            ->get();
    }

    /**
     * 依據會員取相關餐車分類
     * @param $memberId
     * @return mixed
     */
    public function getCategoriesByMemberId($memberId = 0)
    {
        return $this->model->with(['diningCar'])
                            ->where('member_id', $memberId)
                            ->get();
    }
}
