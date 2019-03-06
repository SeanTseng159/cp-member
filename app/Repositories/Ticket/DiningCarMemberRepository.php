<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use Illuminate\Pagination\Paginator;
use App\Models\Ticket\DiningCarMember;

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
        $model = new DiningCarMember;
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
        return $this->model->with([
                                'diningCar.memberLevels',
                                'gifts' => function($query) use ($id) {
                                    $query->where('model_spec_id', $id);
                                },
                                'gifts.memberGiftItems' => function($query) use ($memberId) {
                                    $query->where('member_id', $memberId);
                                }
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
        $currentPage = $params['page'];
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        return $this->model->with([
                                'diningCar.mainImg',
                                'diningCar.category',
                                'diningCar.subCategory',
                                'diningCar.memberLevels'
                            ])
                            ->where('member_id', $memberId)
                            //->paginate($params['limit']);
                            ->paginate(3000);
    }
}
