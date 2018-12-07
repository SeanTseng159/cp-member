<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use DB;
use Illuminate\Database\QueryException;
use Exception;
use App\Exceptions\CustomException;
use App\Core\Logger;
use App\Cache\Redis;
use App\Plugins\CI_Encryption;

use App\Repositories\BaseRepository;
use App\Models\Ticket\Order;
use App\Models\Ticket\ProductSpecPrice;
use App\Repositories\Ticket\OrderDetailRepository;
use App\Repositories\Ticket\SeqOrderRepository;

use App\Cache\Key\CheckoutKey;
use App\Cache\Config as CacheConfig;

class OrderRepository extends BaseRepository
{
    protected $redis;
    protected $orderDetailRepository;
    protected $seqOrderRepository;

    public function __construct(Order $model,
                                OrderDetailRepository $orderDetailRepository,
                                SeqOrderRepository $seqOrderRepository)
    {
        $this->redis = new Redis;
        $this->model = $model;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->seqOrderRepository = $seqOrderRepository;
    }

    /**
     * 成立訂單
     * @param $cart
     * @param $memberId
     * @return mixed
     */
    public function create($params, $cart)
    {
        try {
            DB::connection('backend')->beginTransaction();

            $orderNo = $this->seqOrderRepository->getOrderNo();
            if (!$orderNo) throw new CustomException('E9001');

            $order = new Order;
            $order->member_id = $params->memberId;
            $order->order_no = $orderNo;
            $order->order_source = $params->device;
            $order->order_payment_gateway = $params->payment['gateway'];
            $order->order_payment_method = $params->payment['method'];
            $order->order_shipment_method = $params->shipment['id'];
            $order->shipment_user = $params->shipment['userName'] ?? '';
            $order->shipment_address = $params->shipment['address'] ?? '';
            $order->shipment_phone = $params->shipment['phone'] ?? '';

            $order->order_receipt_method = 1;
            $order->order_items = $cart->totalQuantity;
            $order->order_shipment_fee = $cart->shippingFee;
            $order->order_off = $cart->discountAmount;
            $order->order_amount = $cart->payAmount;
            $order->order_status = 0;
            $order->order_receipt_title = $params->billing['invoiceTitle'] ?? '';
            $order->order_receipt_ubn = $params->billing['unifiedBusinessNo'] ?? '';

            if ($params->payment['method'] === '111') {
                // 初始化加密 (加密信用卡)
                $encryption = new CI_Encryption(['driver' => 'openssl']);

                $order->order_credit_card_number = $encryption->encrypt($params->payment['creditCardNumber']);
                $order->order_credit_card_expire = $encryption->encrypt($params->payment['creditCardYear'] . $params->payment['creditCardMonth']);
                $order->order_credit_card_verify = $encryption->encrypt($params->payment['creditCardCode']);
            }

            $order->created_at = date('Y-m-d H:i:s');
            $order->modified_at = date('Y-m-d H:i:s');
            $order->save();

            // 信用卡資料存快取, 等payment信用卡完成, 再處理
            /*if ($params->payment['method'] === '111') {
                $key = sprintf(CheckoutKey::CREDIT_CARD_KEY, $params->memberId);
                $this->redis->set($key, $params->payment, CacheConfig::TEN_MIN);
            }*/

            // 建立訂單詳細
            $result = $this->orderDetailRepository->createDetails($order->member_id, $order->order_no, $order->order_payment_gateway, $cart->items);
            if (!$result) throw new Exception('Create Order Details Error');

            // 扣掉庫存
            foreach ($cart->items as $item) {
                $psp = ProductSpecPrice::find($item->additional->type->id);
                $stock = $psp->prod_spec_price_stock - $item->quantity;
                if ($stock > 0) {
                    $psp->prod_spec_price_stock = $stock;
                    $psp->save();
                }
                else {
                    throw new CustomException('E9011');
                    break;
                }
            }

            DB::connection('backend')->commit();

            return $order;
        } catch (QueryException $e) {
            Logger::error('Create Order Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            throw new CustomException('E9001');
            return null;
        } catch (QueryException $e) {
            Logger::error('Create Order Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            throw new CustomException($e->getMessage());
            return null;
        } catch (Exception $e) {
            Logger::error('Create Order Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            throw new CustomException('E9001');
            return null;
        }
    }

    /**
     * 依據訂單編號 更新
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateByOrderNo($orderNo, $data = [])
    {
        if (!$data) return false;

        try {
            return $this->model->where('order_no', $orderNo)
                                ->update($data);
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 依據訂單編號 更新 信用卡
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateCC($orderNo, $data = [])
    {
        if (!$data) return false;

        try {
            $order = $this->findByOrderNo($orderNo);
            if (!$order) return false;

            // 初始化加密 (加密信用卡)
            $encryption = new CI_Encryption(['driver' => 'openssl']);

            $order->order_credit_card_number = $encryption->encrypt($data['creditCardNumber']);
            $order->order_credit_card_expire = $encryption->encrypt($data['creditCardYear'] . $data['creditCardMonth']);
            $order->order_credit_card_verify = $encryption->encrypt($data['creditCardCode']);

            $order->save();

            return $order;
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 更新 發票狀態
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateRecipientStatus($id, $status)
    {
        if (!$id) return false;

        try {
            return $this->model->where('order_id', $id)
                                ->update([
                                    'order_recipient_status' => $status
                                ]);
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 根據 No 找單一訂單
     * @param $orderNo
     * @return mixed
     */
    public function findByOrderNo($orderNo = 0)
    {
        if (!$orderNo) return null;

        return $this->model->notDeleted()
                            ->where('order_no', $orderNo)
                            ->first();
    }

    /**
     * 根據 No 找單一訂單 [未失效]
     * @param $memberId
     * @param $orderNo
     * @return mixed
     */
    public function findCanShowByOrderNo($memberId = 0, $orderNo = 0)
    {
        if (!$orderNo) return null;

        $order = $this->model->with(['details.combo'])
                            ->notDeleted()
                            ->where('member_id', $memberId)
                            ->where('order_no', $orderNo)
                            ->where('order_status', '!=', 2)
                            ->first();

        return $order;
    }

    /**
     * 根據 No 找可付款訂單
     * @param $orderNo
     * @return mixed
     */
    public function findCanPay($orderNo = 0)
    {
        if (!$orderNo) return null;

        return $this->model->where('order_no', $orderNo)
                            ->where('order_status', 0)
                            ->first();
    }

    /**
     * 根據 會員 id 取得訂單列表
     * @param $params [memberId, startDate, endDate, status, orderNo]
     * @return mixed
     */
    public function getMemberOrdersByDate($params = [])
    {
        $orders = $this->model->with(['details'])
                            ->notDeleted()
                            ->where('member_id', $params['memberId'])
                            ->where(function($query) use ($params) {
                                if ($params['status'] === '99') {
                                    // 全部
                                    $query->where('order_status', '!=', 2);
                                }
                                elseif ($params['status'] === '00') {
                                    // 待付款, 重新付款
                                    $query->where('order_status', 0);
                                }
                                elseif ($params['status'] === '01') {
                                    // 已完成
                                    $query->where('order_status', 10);
                                }
                                elseif ($params['status'] === '02') {
                                    // 部分退款
                                    $query->where('order_status', 23);
                                }
                                elseif ($params['status'] === '03') {
                                    // 已退貨
                                    $query->where('order_status', 24);
                                }
                                elseif ($params['status'] === '04') {
                                    // 處理中 [退貨申請,退貨處理中,處理完成]
                                    $query->whereIn('order_status', [20, 21, 22]);
                                }
                                elseif ($params['status'] === '08') {
                                    // 已取消
                                    $query->where('order_status', 2);
                                }
                                else {
                                    $query->where('order_status', null);
                                }
                            })
                            ->when($params['startDate'], function($query) use ($params) {
                                $query->where('created_at', '>=', $params['startDate']);
                            })
                            ->when($params['endDate'], function($query) use ($params) {
                                $query->where('created_at', '<=', $params['endDate']);
                            })
                            ->when($params['orderNo'], function($query) use ($params) {
                                $query->where('order_no', $params['orderNo']);
                            })
                            ->orderBy('created_at', 'desc')
                            ->get();

        return $orders;
    }

    /**
     * 依據 發票狀態 取得付款成功訂單列表
     * @param $status
     * @param $recipientStatus
     * @return mixed
     */
    public function getOrdersByRecipientStatus($status = 10, $recipientStatus = 99)
    {
        $orders = $this->model->with(['detail', 'detail.productSpecPrice'])
                                ->notDeleted()
                                ->where('order_status', $status)
                                ->where('order_recipient_status', $recipientStatus)
                                ->get();

        return $orders;
    }
}
