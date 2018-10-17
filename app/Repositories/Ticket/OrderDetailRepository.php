<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use Illuminate\Database\QueryException;

use App\Repositories\BaseRepository;
use App\Models\Ticket\OrderDetail;

class OrderDetailRepository extends BaseRepository
{

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
}
