<?php
/**
 * User: Danny
 * Date: 2019/07/18
 * Time: 上午 9:42
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\MemberNotification;
use Illuminate\Pagination\Paginator;

class MemberNoticRepository extends BaseRepository
{
    protected $model;

    public function __construct(MemberNotification $model)
    {
        $this->model = $model;
    }

    public function memberNoticInfo($params)
    {
        $currentPage = $params['page'];
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        return $this->model->with(['diningCar'])
                            ->where('member_id', $params['memberId'])
                            ->orderBy('created_at','asc')
                            ->paginate($params['limit']);
    }

    public function memberNoticInfoTotal($params)
    {
        return $this->model->with(['diningCar'])
                            ->where('member_id', $params['memberId'])
                            ->count();
    }

    public function find($memberId = 0, $notificationId = 0)
    {
        return $this->model->where('member_id', $memberId)
                            ->where('id', $notificationId)
                            ->first();
    }

    public function updateReadStatus($notificationId)
    {
        if (!$notificationId) return false;

        try {
            return $this->model->where('id', $notificationId)
                ->update([
                    'read_status' => 1
                ]);
        } catch (QueryException $e) {
            return false;
        }
    }
}
