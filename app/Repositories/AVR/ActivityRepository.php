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

        $frees = $this->model->active()
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
            $paidActivitites = $this->model->launched()->with(
                [
                    'productPriceId',
                    'productPriceId.orderDetail' => function ($query) use ($memberID) {
                        $query->where('order_detail_member_id', $memberID);
                    }
                ])->where('has_prod_spec_price_id', 1)
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
        $result = array_merge($freeActivity, $paidActivity);


        $result = collect($result)
            ->sortByDesc(function ($item) {
                return sprintf('%s', $item->orderID);
            })
            ->sortBy(function ($item) {
                return sprintf('%s-%s', $item->sort,$item->endTime);
            })
            ->toArray();

        return array_values($result);
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
