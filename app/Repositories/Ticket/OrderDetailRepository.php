<?php
/**
 * User: lee
 * Date: 2018/09/03
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Helpers\DateHelper;
use App\Traits\InvoiceHelper;

use Carbon\Carbon;
use DB;
use Illuminate\Database\QueryException;
use Exception;
use App\Core\Logger;

use App\Repositories\BaseRepository;
use App\Models\Ticket\OrderDetail;

class OrderDetailRepository extends BaseRepository
{
    use invoiceHelper;
    protected $model;

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
                for ($i = 0; $i < $product->quantity; $i++) {
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

                    // 加購商品
                    if ($product->purchase) {
                        foreach ($product->purchase as $prod) {
                            $seq += 1;
                            $this->create($memberId, $orderNo, $paymentMethod, $seq, $seq, $prod);
                        }
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
        $orderDetail->price_retail = $product->retailPrice;
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
                ->where(function ($query) use ($member_id) {
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
                ->where(function ($query) use ($member_id) {
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

        $query = $this->model->with(['combo' => function ($query) use ($parameter) {
            if ($parameter->orderStatus === '4') {
                $query->where('member_id', $parameter->memberId)->where('order_detail_member_id', '!=', $parameter->memberId);
            } else {
                $query->where('order_detail_member_id', $parameter->memberId);
            }

            if ($parameter->orderStatus === '1' || $parameter->orderStatus === '2') {
                $query->orderBy('verified_at', 'desc');
            }
        }])
            ->where('ticket_show_status', 1)
            ->where('is_physical', 0)
            ->whereIn('prod_type', [1, 2, 3])
            ->where(function ($query) use ($parameter) {
                if ($parameter->orderStatus === '4') {
                    return $query->where('member_id', $parameter->memberId)->where('order_detail_member_id', '!=', $parameter->memberId);
                } else {
                    return $query->where('order_detail_member_id', $parameter->memberId)->where('verified_status', $parameter->status);
                }
            })
            ->where(function ($query) use ($parameter) {
                if ($parameter->orderStatus === '3') {
                    return $query->where('order_detail_expire_due', '<=', date('Y-m-d H:i:s'));
                } elseif ($parameter->orderStatus === '0') {
                    return $query->where('order_detail_expire_due', '>=', date('Y-m-d H:i:s'))
                        ->orWhere('order_detail_expire_due', null);
                }
            });
        //->offset($parameter->offset())
        //->limit($parameter->limit);

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

    //
    public function find($orderDetailID)
    {
        return $this->model->find($orderDetailID);
    }

    public function createDetailsByMenuOrder($memberId, $orderNo, $paymentMethod, $products = [])
    {
        $seq = 0;
        $map = [];

        foreach ($products as $k => $product) {
            $seq += 1;
            $oderDetail = $this->createByMenuOrder($memberId, $orderNo, $paymentMethod, $seq, $seq, $product);
            $map[$k] = $oderDetail->order_detail_id;
        }
        return $map;

    }

    public function createByMenuOrder($memberId, $orderNo, $paymentGateway, $seq, $addnlSeq, $product)
    {
        $orderDetail = new OrderDetail;
        $orderDetail->order_no = $orderNo;
        $orderDetail->order_detail_seq = str_pad($seq, 3, '0', STR_PAD_LEFT);
        $orderDetail->order_detail_addnl_seq = str_pad($addnlSeq, 3, '0', STR_PAD_LEFT);
        $orderDetail->order_detail_sn = sprintf('%s%s%s%s', substr($orderNo, 2), $product->type, $orderDetail->order_detail_seq, $orderDetail->order_detail_addnl_seq);
        $orderDetail->order_detail_member_id = $memberId;
        $orderDetail->member_id = $memberId;

        $orderDetail->supplier_id = $product->supplier_id;
        $orderDetail->catalog_id = $product->catalogId ?? 0;
        $orderDetail->category_id = $product->categoryId ?? 0;
        $orderDetail->prod_id = $product->prod_id;
        $orderDetail->prod_api = $product->prod_api ?? null;
        $orderDetail->prod_cust_id = $product->prod_cust_id;
        $orderDetail->prod_spec_id = $product->spec->prod_spec_id;
        $orderDetail->prod_spec_price_id = $product->specPrice->prod_spec_price_id;

        $orderDetail->prod_type = $product->prod_type;
        $orderDetail->is_physical = $product->is_physical;
        $orderDetail->prod_name = $product->prod_name;
        $orderDetail->prod_spec_name = $product->spec->prod_spec_name;
        $orderDetail->prod_spec_price_name = $product->specPrice->prod_spec_price_name;
        $orderDetail->prod_locate = $product->shop->name;
        $orderDetail->prod_address = $product->shop->county . $product->shop->district . $product->shop->address;
        $orderDetail->price_retail = $product->prod_price_sticker;
        $orderDetail->price_off = $product->prod_price_retail;
        $orderDetail->price_company_qty = 1;
        $orderDetail->prod_expire_type = $product->prod_expire_type;
        $orderDetail->order_detail_expire_start = strtotime($product->prod_expire_start) > 0 ? $product->prod_expire_start : null;
        $orderDetail->order_detail_expire_due = strtotime($product->prod_expire_due) > 0 ? $product->prod_expire_due : null;
        $orderDetail->sync_expire_due = ($product->group_expire_type == 1) ? 'h.' . $product->group_expire_type : NULL;
        $orderDetail->use_type = ($product->group_expire_type) ? 4 : $this->getUseType($product->prod_spec_price_use_note);
        $orderDetail->use_init_value = ($product->group_expire_type) ? NULL : $this->getUseValue($product->prod_spec_price_use_note);
        $orderDetail->use_value = $orderDetail->use_init_value;
        $orderDetail->order_payment_method = $paymentGateway;
        $orderDetail->created_at = date('Y-m-d H:i:s');
        $orderDetail->modified_at = date('Y-m-d H:i:s');
        $orderDetail->save();

        return $orderDetail;
    }


    //拿取訂單詳細資料 以付款
    public function all(){
        return $this->model->with('order')->whereHas('order', function ($query)  {
            $query->where('order_status', 10);
        })->get();
    }
}
