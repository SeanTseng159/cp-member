<?php
/**
 * User: lee
 * Date: 2018/09/03
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use Illuminate\Database\QueryException;

use App\Repositories\BaseRepository;
use App\Models\Ticket\OrderDetail;

class OrderDetailRepository extends BaseRepository
{

    protected $memberModel;

    public function __construct(OrderDetail $model)
    {
        $this->model = $model;
    }

    /**
     * 更新 發票相關
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateRecipient($id, $status, $price)
    {
        if (!$id) return false;

        try {
            return $this->model->where('order_detail_id', $id)
                                ->update([
                                    'recipient_status' => $status,
                                    'recipient_price' => $price
                                ]);
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getMrtCertificate($order_detail_sn, $member_id)
    {
        if (empty($order_detail_sn) || empty($member_id)) return false;

        try {
            return $this->model->where([
                                    'order_detail_sn' => $order_detail_sn,
                                    'prod_api' => 1
                                ])
                                ->where(function($query) use ($member_id){
                                    $query->where('member_id', $member_id)
                                          ->orWhere('order_detail_member_id', $member_id);
                                })
                                ->select(
                                    'order_no',
                                    'prod_name',
                                    'prod_spec_name',
                                    'prod_spec_price_name',
                                    'price_company_qty',
                                    'price_off',
                                    'print_mrt_certificate_at',
                                    'created_at'
                                )->first();

        } catch (QueryException $e) {
            return false;
        }
    }

    public function printMrtCertificate($order_detail_sn, $member_id)
    {
        if (empty($order_detail_sn) || empty($member_id)) return false;

        try {
            return $this->model->where([
                                    'order_detail_sn' => $order_detail_sn,
                                    'prod_api' => 1
                                ])
                                ->whereNull('print_mrt_certificate_at')
                                ->where(function($query) use ($member_id){
                                    $query->where('member_id', $member_id)
                                          ->orWhere('order_detail_member_id', $member_id);
                                })->update(['print_mrt_certificate_at' => date('Y-m-d H:i:s')]);

        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 取票券列表
     * @param $lang
     * @param $parameter
     * @return mixed
     */
    public function tickets($lang, $parameter)
    {
        if (!$parameter) return null;

        $query = $this->model->with(['combo' => function($query) use ($parameter) {
                                if ($parameter->orderStatus === '1' || $parameter->orderStatus === '2')
                                return $query->orderBy('verified_at', 'desc');
                            }])
                            ->where('member_id', $parameter->memberId)
                            ->where('ticket_show_status', 1)
                            ->whereIn('prod_type', [1, 2, 3])
                            ->where(function($query) use ($parameter) {
                                if ($parameter->orderStatus === '4') {
                                    return $query->where('order_detail_member_id', '!=', $parameter->memberId);
                                }
                                else {
                                    return $query->where('order_detail_member_id', $parameter->memberId)->where('verified_status', $parameter->status);
                                }
                            })
                            ->where(function($query) use ($parameter) {
                                if ($parameter->orderStatus === '3') {
                                    return $query->where('order_detail_expire_due', '<=', date('Y-m-d H:i:s'));
                                }
                                elseif ($parameter->orderStatus === '0') {
                                    return $query->where('order_detail_expire_due', '>=', date('Y-m-d H:i:s'))
                                        ->orWhere('order_detail_expire_due', null);
                                }
                            })
                            ->offset($parameter->offset())
                            ->limit($parameter->limit);

        switch ($parameter->orderStatus) {
            case '1':
            case '2':
                $orderDetails = $query->orderBy('verified_at', 'desc')->get();
                break;
            case '4':
                $orderDetails = $query->orderBy('ticket_gift_at', 'desc')->get();
                break;
            default:
                $orderDetails = $query->orderBy('created_at', 'desc')->get();
                break;
        }

        if ($orderDetails->isEmpty()) return null;

        return $orderDetails;
    }
}
