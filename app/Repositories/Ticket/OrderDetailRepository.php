<?php
/**
 * User: lee
 * Date: 2018/09/03
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use DB;
use Illuminate\Database\QueryException;
use Exception;
use App\Core\Logger;

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
     * 批次成立訂單
     * @param $memberId
     * @param $orderNo
     * @param $products
     * @return mixed
     */
    public function createDetails($memberId, $orderNo, $paymentMethod, $products = [])
    {
        try {
            // DB::connection('backend')->beginTransaction();

            $seq = 0;
            foreach ($products as $k => $product) {
                $seq += 1;
                $this->create($memberId, $orderNo, $paymentMethod, $seq, $seq, $product);
                $mainSeq = $seq;

                // 子商品
                if ($product->groups) {
                    foreach ($product->groups as $group) {
                        $seq += 1;
                        $this->create($memberId, $orderNo, $paymentMethod, $seq, $mainSeq, $group);
                    }
                }
            }

            // DB::connection('backend')->commit();

            return true;
        } catch (QueryException $e) {
            Logger::error('Create OrderDetail QueryException Error', $e->getMessage());
            // DB::connection('backend')->rollBack();
            return false;
        } catch (Exception $e) {
            Logger::error('Create OrderDetail Error', $e->getMessage());
            // DB::connection('backend')->rollBack();
            return false;
        }
    }

    /**
     * 成立訂單-單項記錄
     * @param $memberId
     * @param $orderNo
     * @param $paymentGateway
     * @param $seq
     * @param $products
     * @return mixed
     */
    public function create($memberId, $orderNo, $paymentGateway, $seq, $addnlSeq, $product)
    {
        $orderDetail = new OrderDetail;
        $orderDetail->order_no = $orderNo;
        $orderDetail->order_detail_seq = str_pad($seq, 3, '0', STR_PAD_LEFT);
        $orderDetail->order_detail_addnl_seq = str_pad($addnlSeq, 3, '0', STR_PAD_LEFT);
        $orderDetail->order_detail_sn = sprintf('%s%s%s%s', substr($orderNo, 2), $product->type, $orderDetail->order_detail_seq, $orderDetail->order_detail_addnl_seq);
        $orderDetail->order_detail_member_id = $memberId;
        $orderDetail->member_id = $memberId;

        $orderDetail->supplier_id = $product->supplierId;
        $orderDetail->catalog_id = $product->catalogId;
        $orderDetail->category_id = $product->categoryId;
        $orderDetail->prod_id = $product->id;
        $orderDetail->prod_api = $product->api;
        $orderDetail->prod_cust_id = $product->custId;
        $orderDetail->prod_spec_id = $product->additional->spec->id;
        $orderDetail->prod_spec_price_id = $product->additional->type->id;

        $orderDetail->prod_type = $product->type;
        $orderDetail->is_physical = $product->isPhysical;
        $orderDetail->prod_name = $product->name;
        $orderDetail->prod_spec_name = $product->additional->spec->name;
        $orderDetail->prod_spec_price_name = $product->additional->type->name;
        $orderDetail->prod_locate = $product->store;
        $orderDetail->prod_address = $product->address;
        $orderDetail->price_retail = $product->price;
        $orderDetail->price_off = $product->price;
        $orderDetail->price_company_qty = 1;
        $orderDetail->prod_expire_type = $product->expireType;
        $orderDetail->order_detail_expire_start = $product->expireStart;
        $orderDetail->order_detail_expire_due = $product->expireDue;
        $orderDetail->sync_expire_due = ($product->groupExpireType == 1) ? 'h.' . $product->groupExpireDue : NULL;
        $orderDetail->use_type = ($product->groupExpireType) ? 4 : $this->getUseType($product->additional->type->useType);
        $orderDetail->use_init_value = ($product->groupExpireType) ? NULL : $this->getUseValue($product->additional->type->useType);
        $orderDetail->use_value = $orderDetail->use_init_value;
        // $orderDetail->order_detail_expire_usage = '';
        $orderDetail->order_payment_method = $paymentGateway;

        $orderDetail->created_at = date('Y-m-d H:i:s');
        $orderDetail->modified_at = date('Y-m-d H:i:s');
        $orderDetail->save();

        return $orderDetail;
    }

    /**
     * 取得使用條件類型
     */
    private function getUseType($useType = 1)
    {
        if (!$useType) return 1;

        return $useType;
    }

    /**
     * 取得使用條件值
     */
    private function getUseValue($useValue = 1)
    {
        if (!$useValue) return '1';

        return $useValue;
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
     * 取會員某商品購買數
     * @param $productId
     * @param $memberId
     * @return mixed
     */
    public function getCountByProdAndMember($productId = 0, $memberId = 0)
    {
        try {
            return $this->model->where('prod_id', $productId)
                                ->where('member_id', $memberId)
                                ->whereNotNull('order_paid_at')
                                ->count();
        } catch (QueryException $e) {
            return 0;
        }
    }

    /** 取票券列表
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
