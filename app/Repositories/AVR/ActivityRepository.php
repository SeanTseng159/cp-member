<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;


use App\Enum\AVRImageType;
use App\Enum\TimeStatus;
use App\Helpers\AVRImageHelper;
use App\Helpers\StringHelper;
use App\Models\AVR\Activity;
use App\Repositories\BaseRepository;
use Carbon\Carbon;


class ActivityRepository extends BaseRepository
{

    protected $model;

    public function __construct(Activity $model)
    {

        $this->model = $model;
    }

    public function list($memberID = null)
    {

        $freeActivity = [];

        $frees = $this->model
            ->launched()
            ->where('has_prod_spec_price_id', 0)
            ->get(['id', 'name', 'start_activity_time', 'end_activity_time', 'sort']);

        foreach ($frees as $free) {
            $item = new \stdClass();
            $item->id = $free->id;
            $item->name = $free->name;
            $item->sort = $free->sort;
            $item->endTime = Carbon::parse($free->end_activity_time)->format('Y-m-d');
            $item->duration = StringHelper::getDate($free->start_activity_time, $free->end_activity_time);
            $item->photo = AVRImageHelper::getImageUrl(AVRImageType::avr_activity, $free->id);
            $item->status = TimeStatus::checkStatus($free->start_activity_time, $free->end_activity_time);
            $item->orderID = 0;
            $freeActivity[] = $item;
        }

        //檢查是否有付費id ，且未退費
        $paidActivity = [];
        if ($memberID) {
            $paidActivitites = $this->model
                ->launched()
                ->where('has_prod_spec_price_id', 1)
                ->with(
                    [
                        'productPriceId',
                        'productPriceId.orderDetail' => function ($query) use ($memberID) {
                            $query->where('order_detail_member_id', $memberID);
                        }
                    ])
                ->get();

            foreach ($paidActivitites as $item) {
                $orderDetails = $item->productPriceId->orderDetail;
                foreach ($orderDetails as $orderDetail) {
                    $paid = new \stdClass();
                    $paid->id = $item->id;
                    $paid->name = $item->name;
                    $paid->sort = $item->sort;
                    $paid->endTime = Carbon::parse($item->end_activity_time)->format('Y-m-d');
                    $paid->duration = StringHelper::getDate($item->start_activity_time, $item->end_activity_time);
                    $paid->photo = AVRImageHelper::getImageUrl(AVRImageType::avr_activity, $item->id);
                    $paid->status = TimeStatus::checkStatus($item->start_activity_time, $item->end_activity_time);
                    $paid->orderID = $orderDetail->order_detail_id;
                    $paidActivity[] = $paid;

                }
            }
        }
        //檢查活動是否完成
        // 先從member_mission & mission 取得 該活動的完成數
        // 在從mission 取得總活動數
        // 計算是否相等
        $sql = "SELECT
                missions.activity_id,
                COUNT(missions.activity_id) AS member_missions,
                cnt AS activity_missions,
                order_detail_id
            FROM
                member_missions
                    INNER JOIN
                missions ON mission_id = missions.id
                    LEFT JOIN
                (SELECT
                    activity_id,
                    COUNT(*) cnt
                FROM
                    missions
                GROUP BY activity_id) tbl ON missions.activity_id = tbl.activity_id
            WHERE
                member_id = $memberID 
            GROUP BY activity_id,order_detail_id
            HAVING COUNT(missions.activity_id) = activity_missions";

        $finishList = collect(\DB::connection('avr')->select($sql));

        $result = array_merge($freeActivity, $paidActivity);
        foreach ($result as $item) {
            $activityId = $item->id;
            $orderDetailId = $item->orderID;
            $finishItem = $finishList->filter(function ($f) use ($activityId, $orderDetailId) {
                return $f->activity_id == $activityId && $f->order_detail_id == $orderDetailId;
            });
            $item->isFinish = false;
            if (count($finishItem) > 0) {
                $item->isFinish = true;
            }
        }


        $data = [];
        $result = collect($result)->groupBy(['sort', 'endTime']);

        //排序 sort,endTime , orderID desc
        foreach ($result as $sortItemList) {
            foreach ($sortItemList as $endTimeItemList) {
                $temp = collect($endTimeItemList)
                    ->sortByDesc(function ($item) {
                        return sprintf('%s', $item->orderID);
                    })
                    ->toArray();
                $data = array_merge($data, $temp);

            }
        }
        return $data;
    }


    public function detail($id, $orderId = null)
    {
        if ($orderId) {
            $data = $this->model->launched()->with(
                [
                    'missions',
                    'productPriceId',
                    'productPriceId.orderDetail' => function ($query) use ($orderId) {
                        if ($orderId) {
                            $query->where('order_detail_id', $orderId);
                        }
                    }
                ])->where('has_prod_spec_price_id', 1)
                ->where('id', $id)
                ->first();

        } else {
            $data = $this->model
                ->where('id', $id)
                ->first();
        }
        return $data;

    }


}
