<?php

/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\MenuOrderDetail;
use App\Repositories\MenuOrderRepository;
use App\Services\UUID;
use DB;
use Illuminate\Database\QueryException;
use Exception;
use App\Exceptions\CustomException;
use App\Core\Logger;
use App\Cache\Redis;
use App\Plugins\CI_Encryption;

use App\Repositories\BaseRepository;
use App\Models\Ticket\Order;
use App\Models\Ticket\OrderDiscount;
use App\Models\Ticket\ProductSpecPrice;
use App\Models\Ticket\PromotionProdSpecPrice;

class OrderRepository extends BaseRepository
{
    protected $redis;
    protected $orderDetailRepository;
    protected $seqOrderRepository;
    protected $orderShipmentRepository;

    public function __construct(
        Order $model,
        OrderDetailRepository $orderDetailRepository,
        SeqOrderRepository $seqOrderRepository,
        OrderShipmentRepository $orderShipmentRepository
    ) {
        $this->redis = new Redis;
        $this->model = $model;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->seqOrderRepository = $seqOrderRepository;
        $this->orderShipmentRepository = $orderShipmentRepository;
    }

    /**
     * 成立訂單
     * @param $params
     * @param $cart
     * @return mixed
     * @throws CustomException
     */
    public function create($params, $cart)
    {
        try {

            DB::connection('backend')->beginTransaction();

            $orderNo = $this->seqOrderRepository->getOrderNo();
            if (!$orderNo) throw new CustomException('E9001S');

            $order = new Order;
            $order->member_id = $params->memberId;
            $order->order_no = $orderNo;
            $order->order_source = $params->device;
            $order->order_payment_gateway = $params->payment['gateway'];
            $order->order_payment_method = $params->payment['method'];
            $order->order_shipment_method = $params->shipment['id'];

            $order->order_receipt_method = 1;
            $order->order_items = $cart->totalQuantity;
            $order->order_shipment_fee = $cart->shippingFee;
            $order->order_off = isset($cart->DiscountCode) ? $cart->DiscountCode->amount : 0;
            $order->order_amount = $cart->payAmount - $order->order_off;

            $order->order_status = 0;
            $order->order_receipt_title = $params->billing['invoiceTitle'] ?? '';
            $order->order_receipt_ubn = $params->billing['unifiedBusinessNo'] ?? '';

            //綠界取消了
            // if ($params->payment['method'] === '111') {
            //     // 初始化加密 (加密信用卡)
            //     $encryption = new CI_Encryption(['driver' => 'openssl']);

            //     $order->order_credit_card_number = $encryption->encrypt($params->payment['creditCardNumber']);
            //     $order->order_credit_card_expire = $encryption->encrypt($params->payment['creditCardYear'] . $params->payment['creditCardMonth']);
            //     $order->order_credit_card_verify = $encryption->encrypt($params->payment['creditCardCode']);
            // }

            $order->created_at = date('Y-m-d H:i:s');
            $order->modified_at = date('Y-m-d H:i:s');
            $order->save();

            // 信用卡資料存快取, 等payment信用卡完成, 再處理
            /*if ($params->payment['method'] === '111') {
                $key = sprintf(CheckoutKey::CREDIT_CARD_KEY, $params->memberId);
                $this->redis->set($key, $params->payment, CacheConfig::TEN_MIN);
            }*/

            // 有實體商品才存物流資訊
            if ($params->shipment['id'] == 2) {
                $result = $this->orderShipmentRepository->createForOrder($order->order_id, $params->shipment);
                if (!$result) throw new Exception('Create Order Shipment Error');
            }

            // 建立訂單詳細
            $result = $this->orderDetailRepository->createDetails($order->member_id, $order->order_no, $order->order_payment_gateway, $cart->items);
            if (!$result) throw new Exception('Create Order Details Error');

            //建立訂單折扣紀錄
            if (!empty($cart->DiscountCode)) {
                $orderDiscount = new OrderDiscount;
                $orderDiscount->order_no = $orderNo;
                $orderDiscount->discount_id = $cart->DiscountCode->id;
                $orderDiscount->discount_type = 1;
                $orderDiscount->discount_name = $cart->DiscountCode->name;
                $orderDiscount->discount_price = $cart->DiscountCode->amount;
                $orderDiscount->created_at = date('Y-m-d H:i:s');
                $orderDiscount->modified_at = date('Y-m-d H:i:s');
                $orderDiscount->save();
            }

            // 扣掉庫存
            foreach ($cart->items as $item) {
                $psp = ProductSpecPrice::find($item->additional->type->id);
                $stock = $psp->prod_spec_price_stock - $item->quantity;

                if ($stock > 0) {
                    $psp->prod_spec_price_stock = $stock;
                    $psp->save();
                } else {
                    throw new CustomException('E9011');
                    break;
                }

                // 扣除獨立賣場 商品庫存
                if ($cart->type !== 'market') continue;

                $ppsp = PromotionProdSpecPrice::where('promotion_id', $cart->promotion['marketId'])->where('price_id', $item->additional->type->id)->first();

                $stock = $ppsp->stock - $item->quantity;
                if ($stock > 0) {
                    $ppsp->stock = $stock;
                    $ppsp->save();
                } else {
                    throw new CustomException('E9011');
                    break;
                }
            }

            DB::connection('backend')->commit();

            return $order;
        } catch (QueryException $e) {
            Logger::error('QueryException Create Order Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            throw new CustomException($e->getMessage());
            return null;
        } catch (CustomException $e) {
            Logger::error('CustomException Create Order Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            throw new CustomException($e->getMessage());
            return null;
        } catch (Exception $e) {
            Logger::error('Exception Create Order Error', $e->getMessage());
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
     * @param $memberId
     * @param $menuOrder
     * @param bool $isUpdateStock
     * @throws Exception
     */
    private function checkProdAndUpdate($memberId, $menuOrder, $isUpdateStock = false)
    {
        $menuOrderDetails = (new MenuOrderDetail())->select(
            'menu_order_id',
            'menu_id',
            'price',
            \DB::raw('count(menu_id) as qty')
        )
            ->groupBy('menu_order_id', 'menu_id', 'price')
            ->where('menu_order_id', $menuOrder->id)
            ->get();

        foreach ($menuOrder->details as $detail) {
            $menu = $detail->menu;
            $name = $menu->name;
            $prodSpecPrice = optional($menu->prodSpecPrice);
            $prodSpec = optional($prodSpecPrice->prodSpec);
            $product = optional($prodSpec->product);
            if (is_null($prodSpecPrice) || is_null($prodSpec) || is_null($product))
                throw new Exception("[$name]無法線上付款");

            //檢查限購數量
            $menuId = $menu->id;
            $limit = $product->prod_limit_num;
            $details = collect($menuOrderDetails)->filter(function ($item) use ($menuId, $limit) {
                return $item->menu_id == $menuId && $item->qty <= $limit;
            });

            $buyQuantity = $details->count();

            // 檢查是否有庫存
            if ($prodSpecPrice->prod_spec_price_stock <= 0 || $prodSpecPrice->prod_spec_price_stock < $buyQuantity) {
                throw new Exception('E9011');
            }
            //檢查可購買數量
            if ($product->prod_type === 1 || $product->prod_type === 2) {
                if ($product->prod_limit_type == 0) {
                    if ($buyQuantity > $product->prod_limit_num)
                        throw new Exception("[$name]商品超過可購買數量，無法線上付款");
                } else {
                    $memberBuyQuantity = $this->orderDetailRepository->getCountByProdAndMember($product->product_id, $memberId);
                    if (($buyQuantity + $memberBuyQuantity) > $product->prod_limit_num)
                        throw new Exception("[$name]商品超過可購買數量，無法線上付款");
                }
            } elseif ($product->prod_type === 3) {
                if ($buyQuantity > $product->prod_plus_limit)
                    throw new Exception('E9012');
            }

            if ($isUpdateStock) {
                $prodSpecPrice->prod_spec_price_stock -= $buyQuantity;
                $prodSpecPrice->save();
            }
        }
    }

    public function createByMenuOrder($params, &$menuOrder)
    {
        $this->checkProdAndUpdate($menuOrder->member_id, $menuOrder, true);

        $orderNo = $this->seqOrderRepository->getOrderNo();

        $order = new Order;
        $order->member_id = $menuOrder->member_id;
        $order->order_no = $orderNo;
        $order->order_source = $params->device;
        $order->order_payment_gateway = $params->payment['gateway'];
        $order->order_payment_method = $params->payment['method'];
        $order->order_shipment_method = 1;

        $order->order_receipt_method = 1;
        $order->order_items = $menuOrder->details()->count();
        $order->order_shipment_fee = 0;
        $order->order_off = 0;
        $order->order_amount = $menuOrder->amount;

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

        $menuOrder->order_id = $order->order_id;
        $menuOrder->qrcode = (new UUID())->setCreate()->getToString();
        $menuOrder->save();

        $prods = [];
        foreach ($menuOrder->details as $detail) {
            $prod = $detail->menu->prodSpecPrice->prodSpec->product;
            $prod->spec = $detail->menu->prodSpecPrice->prodSpec;
            $prod->specPrice = $detail->menu->prodSpecPrice;
            $prod->shop = $menuOrder->shop;
            $prods[$detail->id] = $prod;
        }

        $map = $this->orderDetailRepository->createDetailsByMenuOrder($menuOrder->member_id, $orderNo, $params->payment['gateway'], $prods);

        foreach ($menuOrder->details as $detail) {
            $detail->order_detail_id = $map[$detail->id];
            $detail->save();
        }
        return $orderNo;
    }

    /**
     * 依據訂單編號 更新
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateForRepay($orderNo, $params = [])
    {
        if (!$params) return false;

        try {
            $order = $this->findByOrderNo($orderNo);
            if (!$order) return false;

            $order->order_payment_gateway = $params->payment['gateway'];
            $order->order_payment_method = $params->payment['method'];

            // 信用卡資訊
            if ($params->payment['gateway'] === '3' && $params->payment['method'] === '111') {
                // 初始化加密 (加密信用卡)
                $encryption = new CI_Encryption(['driver' => 'openssl']);

                $order->order_credit_card_number = $encryption->encrypt($params->payment['creditCardNumber']);
                $order->order_credit_card_expire = $encryption->encrypt($params->payment['creditCardYear'] . $params->payment['creditCardMonth']);
                $order->order_credit_card_verify = $encryption->encrypt($params->payment['creditCardCode']);
            }

            $order->save();

            return $order;
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
        } catch (Exception $e) {
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

        return $this->model->with(['details.combo', 'shipment'])
            ->notDeleted()
            ->where('order_no', $orderNo)
            ->first();
    }

    /**
     * 根據 No 找單一訂單的詳細資料
     * @param $orderNo
     * @return mixed
     */
    public function findByOrderNoWithDetail($orderNo = 0)
    {
        if (!$orderNo) return null;

        return $this->model->with(['detail'])
            ->notDeleted()
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

        $order = $this->model->with(['details.combo', 'shipment', 'discountCode'])
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
        $orders = $this->model->with(['details', 'shipment'])
            ->notDeleted()
            ->where('member_id', $params['memberId'])
            ->where(function ($query) use ($params) {
                if ($params['status'] === '99') {
                    // 全部
                    $query->where('order_status', '!=', 2);
                } elseif ($params['status'] === '00') {
                    // 待付款, 重新付款
                    $query->where('order_status', 0);
                } elseif ($params['status'] === '01') {
                    // 已完成
                    $query->where('order_status', 10);
                } elseif ($params['status'] === '02') {
                    // 部分退款
                    $query->where('order_status', 23);
                } elseif ($params['status'] === '03') {
                    // 已退貨
                    $query->where('order_status', 24);
                } elseif ($params['status'] === '04') {
                    // 處理中 [退貨申請,退貨處理中,處理完成]
                    $query->whereIn('order_status', [20, 21, 22]);
                } elseif ($params['status'] === '08') {
                    // 已取消
                    $query->where('order_status', 2);
                } else {
                    $query->where('order_status', null);
                }
            })
            ->when($params['startDate'], function ($query) use ($params) {
                $query->where('created_at', '>=', $params['startDate']);
            })
            ->when($params['endDate'], function ($query) use ($params) {
                $query->where('created_at', '<=', $params['endDate']);
            })
            ->when($params['orderNo'], function ($query) use ($params) {
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
     * @param $shipmentMethod [實體 or 票券]
     * @param $shipmentStatus [運送狀態]
     * @return mixed
     */
    public function getOrdersByRecipientStatus(
        $status = 99,
        $recipientStatus = 99,
        $shipmentMethod,
        $shipmentStatus = 99
    ) {
        if ($shipmentMethod === 2) {
            // 實體商品
            $orders = $this->model->with(['detail', 'detail.productSpecPrice'])
                ->notDeleted()
                ->whereHas('shipment', function ($query) use ($shipmentStatus) {
                    $query->where('status', $shipmentStatus);
                })
                ->where('order_shipment_method', $shipmentMethod)
                ->where('order_status', $status)
                ->where('order_recipient_status', $recipientStatus)
                ->get();
        } else {
            // 票券商品
            $orders = $this->model->with(['detail', 'detail.productSpecPrice'])
                ->notDeleted()
                ->where('order_shipment_method', $shipmentMethod)
                ->where('order_status', $status)
                ->where('order_recipient_status', $recipientStatus)
                ->get();
        }

        return $orders;
    }

    /**
     * 取前一小時有付款的餐車訂單
     * @return mixed
     */
    public function getOneHourAgoPaidDiningCarOrders()
    {
        $now = date('Y-m-d H:i:s');
        $startTime = date('Y-m-d H:45:00', strtotime('-1 hour'));
        $endTime = date('Y-m-d H:45:00');

        return $this->model->select([
            'orders.order_id',
            'orders.member_id',
            'menus.dining_car_id',
            DB::raw('SUM(order_details.price_off) as total_amount')
        ])
            ->rightJoin('order_details', 'orders.order_no', '=', 'order_details.order_no')
            ->rightJoin('menus', 'order_details.prod_spec_price_id', '=', 'menus.prod_spec_price_id')
            ->rightJoin('dining_cars', 'menus.dining_car_id', '=', 'dining_cars.id')
            ->where('orders.order_status', 10)
            ->where('orders.order_paid_at', '>=', $startTime)
            ->where('orders.order_paid_at', '<', $endTime)
            ->whereIn('order_details.prod_type', [1, 2])
            ->where('dining_cars.level', '>', 0)
            ->where('dining_cars.expired_at', '>=', $now)
            ->groupBy('orders.order_id', 'dining_cars.id')
            ->get();
    }
}
