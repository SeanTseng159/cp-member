<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Config\Ticket\OrderConfig;
use Carbon\Carbon;

use App\Services\MemberService;
use App\Services\Ticket\OrderRefundService;
use App\Services\Ticket\UberCouponService;

use App\Traits\StringHelper;

class OrderResult extends BaseResult
{
    use StringHelper;

    private $isCommodity = false;
    private $source;
    private $quantity = 0;
    private $memberService;
    private $orderRefundService;
    private $uberCouponService;
    private $members;
    private $time;

    public function __construct()
    {
        parent::__construct();

        $this->memberService = app()->build(MemberService::class);
        $this->orderRefundService = app()->build(MemberService::class);
        $this->uberCouponService = app()->build(UberCouponService::class);
        $this->time = time();
    }

    /**
     * 處理所有取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function getAll($orders, $isDetail = false)
    {
        if (!$orders) return null;

        $newOrders = [];
        foreach ($orders as $order) {
            $newOrders[] = $this->get($order, $isDetail);
        }

        return $newOrders;
    }

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function get($order, $isDetail = false)
    {
        if (!$order) return null;

        $order = $order->toArray();

        $this->isCommodity = ($order['order_shipment_method'] === 2) ? true : false;
        $this->source = ($this->isCommodity) ? OrderConfig::SOURCE_CT_COMMODITY : OrderConfig::SOURCE_TICKET;

        $result['source'] = $this->source;
        $result['orderNo'] = (string) $this->arrayDefault($order, 'order_no');
        // 小計, 總金額減運費
        $result['totalAmount'] = $this->arrayDefault($order, 'order_amount', 0) - $this->arrayDefault($order, 'order_shipment_fee', 0) + $this->arrayDefault($order, 'order_off', 0);
        // 折扣價格
        $result['discountAmount'] = $this->arrayDefault($order, 'order_off', 0);
        // 折扣後總計
        $result['discountTotalAmount'] = $result['totalAmount'] - $result['discountAmount'];
        // 運費
        $result['shippingFee'] = $this->arrayDefault($order, 'order_shipment_fee', 0);
        // 付款價格
        $result['payAmount'] = $this->arrayDefault($order, 'order_amount');

        $result['isRePayment'] = $this->isRepay($order['order_status'], $order['order_payment_method'], $order['order_atm_virtual_account']);
        $result['orderStatusCode'] = $this->getMergeStatusCode($this->changeStatusCode($order['order_status'], $result['isRePayment']));
        $result['orderStatus'] = $this->getOrderStatus($result['orderStatusCode']);
        $result['orderDate'] = $this->arrayDefault($order, 'created_at');
        $result['payment'] = $this->getPayment($order);
        $result['shipment'] = $this->getShipment($order['order_shipment_method'], $order['shipment'], $order['order_shipment_fee']);
        $result['items'] = $this->processItems($order['details'], $isDetail);
        $result['orderer'] = $this->getOrderer($order['member_id']);

        return $result;
    }

    /**
     * 是否可重新付款
     * @param $orderStatus
     * @param $orderPayMethod
     * @param $atmVirtualAccount
     * @return string
     */
    private function isRepay($orderStatus, $orderPayMethod, $atmVirtualAccount)
    {
        $isRepay = false;

        $orderPayMethod = $orderPayMethod ?: 0;

        if (OrderConfig::PAYMENT_METHOD[$orderPayMethod] === 'atm') {
            $isRepay = empty($atmVirtualAccount);
        }
        else {
            $isRepay = ($orderStatus === 0) ? true : false;
        }

        return $isRepay;
    }

    /**
     * 訂單狀態
     * @param $key
     * @param $isRePay
     * @return string
     */
    private function changeStatusCode($code, $isRePay = false)
    {
        if ($code === 0 && $isRePay) $code = 3;

        switch ($code) {
            case 0:
                return '00';
            case 1:
                return '01';
            case 2:
                return '02';
            case 3:
                return '03';
            case 10:
                return '10';
            case 20:
                return '20';
            case 21:
                return '21';
            case 22:
                return '22';
            case 23:
                return '23';
            case 24:
                return '24';
        }

        return '02';
    }

    /**
     * 訂單合併狀態碼
     * @param $key
     * @param $isRePay
     * @return string
     */
    private function getMergeStatusCode($code)
    {
        if ($code === '10') $mergeCode = '01';
        else if ($code === '00' || $code === '01') $mergeCode = '00';
        else if ($code === '23') $mergeCode = '02';
        else if ($code === '24') $mergeCode = '03';
        else if ($code === '20' || $code === '21' || $code === '22') $mergeCode = '04';
        else if ($code === '02') $mergeCode = '08';
        else if ($code === '03') $mergeCode = '07';

        return $mergeCode;
    }

    /**
     * 訂單狀態
     * @param $mergeCode
     * @return string
     */
    private function getOrderStatus($mergeCode)
    {
        return trans('ticket/order.status.' . OrderConfig::STATUS[$mergeCode]);
    }

    /**
     *  訂單付款資訊
     * @param $order
     * @return string
     */
    private function getPayment($order)
    {
        $payment = new \stdClass;
        $payment->gateway = OrderConfig::PAYMENT_GATEWAY[$this->arrayDefault($order, 'order_payment_gateway', 0)];
        $payment->method = OrderConfig::PAYMENT_METHOD[$this->arrayDefault($order, 'order_payment_method', 0)];
        $payment->title = trans('ticket/order.payment.method.' . $payment->method);
        $payment->amount = $this->arrayDefault($order, 'order_amount');

        $payment->bankId = '';
        $payment->bankName = '';
        $payment->virtualAccount = '';
        $payment->paymentPeriod = '';

        if ($payment->method === OrderConfig::PAYMENT_METHOD[211]) {
            $payment->bankId = $this->arrayDefault($order, 'order_atm_bank_id');
            $payment->bankName = ($payment->bankId) ? trans('bank.name.' . OrderConfig::BANK_NAME[$payment->bankId]) : '';
            $payment->virtualAccount = $this->arrayDefault($order, 'order_atm_virtual_account');
            $payment->paymentPeriod = Carbon::parse($order['created_at'])->subDay()->format('Y-m-d 23:30:00');
        }

        return $payment;
    }

    /**
     *  運費資訊
     * @param $method
     * @param $shipment
     * @return string
     */
    private function getShipment($method = 1, $shipment = null, $shipmentFee = 0)
    {
        $isShipment = ($method === 2);

        if ($isShipment && $shipment) {
            $newShipment = new \stdClass;
            $newShipment->name = $shipment['user_name'];
            $newShipment->address = $shipment['zipcode'] . ' ' . $shipment['address'];
            $newShipment->phone = '+' . $shipment['country_code'] . $shipment['cellphone'];
            $newShipment->description = trans('common.delivery');
            $newShipment->statusCode = $shipment['status'];
            $newShipment->status = OrderConfig::SHIPMENT_STATUS[$newShipment->statusCode];
            $newShipment->traceCode = $shipment['trace_code'];
            $newShipment->fee = $shipmentFee;
        }
        else {
            $newShipment = new \stdClass;
            $newShipment->name = '';
            $newShipment->address = '';
            $newShipment->phone = '';
            $newShipment->description = trans('common.ticket');
            $newShipment->statusCode = 5;
            $newShipment->status = OrderConfig::SHIPMENT_STATUS[$newShipment->statusCode];
            $newShipment->traceCode = '';
            $newShipment->fee = $shipmentFee;
        }

        return $newShipment;
    }

    /**
     *  取得訂單購買項目
     * @param $orderDetail
     * @return string
     */
    private function processItems($orderDetail, $isDetail = false)
    {
        $items = [];
        if ($orderDetail) {
            if ($isDetail) {
                foreach ($orderDetail as $detail) {
                    $items[] = $this->getItem($detail, $isDetail);
                }
            }
            else {
                foreach ($orderDetail as $detail) {
                    if (!isset($items[$detail['prod_cust_id']])) {
                        $items[$detail['prod_cust_id']] = $this->getItem($detail, $isDetail);
                    }
                    else {
                        $items[$detail['prod_cust_id']]->quantity++;
                    }
                }

                // 重新整理輸出Items
                $items = $this->remapItems($items);
            }
        }

        return $items;
    }

    /**
     *  重新整理Items, 去除key
     * @param $detail
     * @return string
     */
    private function remapItems($items)
    {
        if (!$items) return [];

        foreach ($items as $item) {
            $newItems[] = $item;
        }

        return $newItems;
    }

    /**
     *  處理購買項目
     * @param $item
     * @param $isDetail
     * @return string
     */
    private function getItem($item, $isDetail = false, $isCombo = false, $comboIsSyncExpire = false, $comboSyncExpireDate = '')
    {
        $newDetail = new \stdClass;
        $newDetail->source = $this->source;
        $newDetail->productId = $item['prod_id'];
        $newDetail->orderNoSeq = $item['order_no'] . '-' . str_pad($item['order_detail_seq'], 3, '0', STR_PAD_LEFT);
        $newDetail->sn = (string) $item['order_detail_sn'];
        $newDetail->name = $item['prod_name'];
        $newDetail->spec = $this->getSpecName($item['prod_spec_name'], $item['prod_spec_price_name']);
        $newDetail->quantity = $item['price_company_qty'];
        $newDetail->price = $item['price_off'];
        $newDetail->description = ($this->isCommodity) ? trans('common.commodity') : trans('common.ticket');
        $newDetail->statusCode = $statusCode = $this->itemUsedStatusCode($item, $comboIsSyncExpire);
        $newDetail->status = $this->itemUsedStatus($statusCode);
        // $newDetail->discount = null;
        $newDetail->hasVoucher = $this->getHasVoucher($statusCode, $item['prod_type'], $item['prod_api']);

        if ($isDetail) {
            $newDetail->qrcode = ($statusCode === '10' || $statusCode === '11') ? $this->arrayDefault($item, 'order_detail_qrcode') : '';
            $newDetail->show = $this->getShowList($newDetail->statusCode, $item);
            $newDetail->pinCode = ($statusCode === '10') ? $this->getPinCode($this->arrayDefault($item, 'trust_pin')) : '';
            $newDetail->usageTime = $this->arrayDefault($item, 'order_detail_expire_usage', '');

            // 主商品代轉贈時間, 子商品帶同步失效時間
            $expireDate = ($isCombo) ? $comboSyncExpireDate : $item['ticket_gift_at'];
            $newDetail->expireTime = $this->getUseExpireTime($item['prod_expire_type'], $item['order_detail_expire_start'], $item['order_detail_expire_due'], $newDetail->statusCode, $expireDate);

            if (!$isCombo) {
                // 組合商品，需檢查同步失效
                $prodType = $this->arrayDefault($item, 'prod_type');
                $comboIsSyncExpire = ($prodType === 2) ? $this->checkComboIsSyncExpire($statusCode, $item['sync_expire_due'], $item['use_value']) : false;

                if ($newDetail->statusCode === '05') {
                    // 轉贈
                    $newDetail->combos = $this->getCombos($item['combo'], $comboIsSyncExpire, $expireDate);
                }
                else {
                    $comboSyncExpireDate = $this->getComboSyncExpireDate($statusCode, $item['sync_expire_due'], $item['use_value']);
                    $newDetail->combos = $this->getCombos($item['combo'], $comboIsSyncExpire, $comboSyncExpireDate);
                }
            }
        }

        return $newDetail;
    }

    /**
     *  取組合 子商品
     * @param $detail
     * @return string
     */
    private function getCombos($combos = [], $comboIsSyncExpire = false, $comboSyncExpireDate = '')
    {
        if (!$combos) return [];

        foreach ($combos as $combo) {
            $newCombos[] = $this->getItem($combo, true, true, $comboIsSyncExpire, $comboSyncExpireDate);
        }

        return $newCombos;
    }

    /**
     * item使用狀態碼
     * @param $detail
     * @return string
     */
    private function itemUsedStatusCode($detail, $comboIsSyncExpire = false)
    {
        $statusCode = '99';

        // 已轉贈
        if ($detail['order_detail_member_id'] !== $detail['member_id']) {
            return '05';
        }

        // 取資料庫驗證狀態碼
        $statusCode = str_pad($detail['verified_status'], 2, '0', STR_PAD_LEFT);

        // 過期未使用，同失效
        if ($statusCode === '10' && $detail['prod_expire_type'] !== 0 && $this->time > strtotime($detail['order_detail_expire_due'])) {
                $statusCode = '01';
        }

        // 組合同步失效，未使用，同失效
        if ($comboIsSyncExpire && $statusCode === '10') $statusCode = '01';

        return $statusCode;
    }

    /**
     * 取規格名稱
     * @param $code
     * @return string
     */
    private function getSpecName($specName = '', $priceName = '')
    {
        if ($priceName) {
            if ($specName === $priceName) return $priceName;

            return $specName . '/' . $priceName;
        }

        return $specName;
    }

    /**
     * item使用狀態
     * @param $code
     * @return string
     */
    private function itemUsedStatus($code)
    {
        return trans('ticket/order.usedStatus.' . OrderConfig::USED_STATUS[$code]);
    }

    /**
     * 檢查組合是否同步失效
     *
     * @param   $item
     * @param   $comboSyncStatus
     * @return  String
     */
    private function checkComboIsSyncExpire($statusCode, $syncExpireDue, $useValue)
    {
        if ($statusCode === '11' && $syncExpireDue && $useValue) {
            $useValueAry = explode('~', $useValue);
            if ($this->time > strtotime($useValueAry[1])) return true;
        }

        return false;
    }

    /**
     * 取組合同步失效到期日
     *
     * @param   $item
     * @param   $comboSyncStatus
     * @return  String
     */
    private function getComboSyncExpireDate($statusCode, $syncExpireDue, $useValue)
    {
        if ($statusCode === '11' && $syncExpireDue && $useValue) {
            $useValueAry = explode('~', $useValue);
            return $useValueAry[1];
        }

        return '';
    }

    /**
     * 取 show
     * @param $statusCode
     * @param $item
     * @return string
     */
    private function getShowList($statusCode, $item)
    {
        $orderSeq = $item['order_no'] . '-' . str_pad($item['order_detail_seq'], 3, '0', STR_PAD_LEFT);

        // 已轉贈
        if ($statusCode === '05') {
            $member = $this->getMember($item['order_detail_member_id']);
            $show[] = ["label" => "訂單編號：", "text" => $orderSeq, "color" => null];
            $show[] = ["label" => "轉贈對象：", "text" => ($member) ? $this->hideName($member->name) : '', "color" => null];
            $show[] = ["label" => "手機號碼：", "text" => ($member) ? '+' . $member->countryCode . $this->hidePhoneNumber($member->cellphone) : '', "color" => null];
            $show[] = ["label" => "轉贈時間：", "text" => $item['ticket_gift_at'], "color" => null];
        }
        // 未使用 or 已使用
        elseif ($statusCode === '10' || $statusCode === '11') {
            $show[] = ["label" => "訂單編號：", "text" => $orderSeq, "color" => null];
            $show[] = ["label" => "票券號碼：", "text" => (string) $item['order_detail_sn'], "color" => null];
            $show[] = ["label" => "地點：", "text" => $item['prod_locate'], "color" => null];
            $show[] = ["label" => "地址：", "text" => $item['prod_address'], "color" => null];
            $show[] = ["label" => "使用效期：", "text" => $this->getUseExpireTime($item['prod_expire_type'], $item['order_detail_expire_start'], $item['order_detail_expire_due']), "color" => null];

            if ($item['order_detail_expire_usage']) {
                $show[] = ["label" => "預定使用時間：", "text" => $item['order_detail_expire_usage'], "color" => null];
            }

            // Uber Code
            $uber = $this->getUberCode($item['order_detail_id']);
            if ($uber) {
                $show[] = ["label" => "UBER優惠序號：", "text" => $uber['code'], "color" => "#ea4335"];
                $show[] = ["label" => "優惠內容：", "text" => $uber['msg'], "color" => "#ea4335"];
                $show[] = ["label" => "優惠期限：", "text" => $uber['limitDate'], "color" => "#ea4335"];
            }
        }
        elseif ($statusCode === '23') {
            $orderRefund = $this->orderRefundService->find($item['refund_id']);
            $orderRefundTime = ($orderRefund) ? $orderRefund->order_refund_payment_date : '';
            $show[] = ["label" => "訂單編號：", "text" => $orderSeq, "color" => null];
            $show[] = ["label" => "票券號碼：", "text" => (string) $item['order_detail_sn'], "color" => null];
            $show[] = ["label" => "", "text" => "已於 {$orderRefundTime} 完成退貨", "color" => "#90c320"];
        }
        elseif ($statusCode === '02') {
            $show[] = ["label" => "訂單編號：", "text" => $orderSeq, "color" => null];
            $show[] = ["label" => "票券號碼：", "text" => (string) $item['order_detail_sn'], "color" => null];
            $show[] = ["label" => "地點：", "text" => $item['prod_locate'], "color" => null];
            $show[] = ["label" => "地址：", "text" => $item['prod_address'], "color" => null];
            $show[] = ["label" => "使用效期：", "text" => $this->getUseExpireTime($item['prod_expire_type'], $item['order_detail_expire_start'], $item['order_detail_expire_due']), "color" => null];
            $show[] = ["label" => "", "text" => "*此商品已超過使用效期", "color" => "#90c320"];
        }
        else {
            $show[] = ["label" => "訂單編號：", "text" => $orderSeq, "color" => null];
            $show[] = ["label" => "票券號碼：", "text" => (string) $item['order_detail_sn'], "color" => null];
        }

        return $show;
    }

    /**
     * 取使用效期
     * @param $expireType
     * @param $expireStart
     * @param $expireDue
     * @param $status
     * @param $date
     * @return string
     */
    private function getUseExpireTime($expireType, $expireStart, $expireDue, $status = '99', $date = '')
    {
        if (in_array($status, ['01', '05']) && $date) return '~ ' . substr($date, 0, 16);

        if ($expireType === 0) return '無限制';

        $expireTimeStart = substr($expireStart, 0, 16);
        $expireTimeEnd   = substr($expireDue, 0, 16);

        return "{$expireTimeStart}~{$expireTimeEnd}";
    }

    /**
     * 取 uber code
     * @param $id
     * @return string
     */
    private function getUberCode($id)
    {
        $uberCoupon = $this->uberCouponService->findByOrderDetailId($id);
        if ($uberCoupon) {
            $uberLimitDate = date('Y-m', strtotime("+1 month", strtotime($uberCoupon->publish_date)));
            $uberLimitDate = sprintf("使用期限至 %s-10 23:59", $uberLimitDate);

            $uberTypeMsg = '';
            if ($uberCoupon->type == 1) {
                $uberTypeMsg = '2 趟 75 折優惠 (限高屏地區上或下車方可折抵)';
            }
            elseif ($uberCoupon->type == 2) {
                $uberTypeMsg = '5 趟 7 折優惠 (限高屏地區上或下車方可折抵)';
            }

            return [
                'code' => $uberCoupon->code,
                'msg' => $uberTypeMsg,
                'limitDate' => $uberLimitDate
            ];
        }

        return null;
    }

    /**
     * 取 PinCode
     * @param $pincode
     * @return string
     */
    private function getPinCode($pincode)
    {
        if (!$pincode) return '';

        $return = "票券應記載事項\n";
        $return .= "一、本券發行人：高盛大股份有限公司，地址：高雄市苓雅區中正一路265號9樓，統編：53890045，負責人：郭朝榮，消費爭議處理專線：(07)752-8568。\n";
        $return .= "二、本券之面額已先存入發行人於新光銀行開立之信託專戶，專款專用；所稱專用係指供發行人履行交付商品或提供服務義務使用。本信託受益人為發行人，非本券持有人，本券信託期間自發行日(購買日)起算一年，信託期間屆滿後由新光銀行將信託專戶餘額交由發行人領回，但本券持有人仍得依法向發行人請求履行相關義務。\n";
        $return .= "三、本券持有人得以禮券編號(PIN CODE碼)向受託銀行網頁查詢及確認信託相關資訊。\n";
        $return .= "四、若本公司發生破產宣告、遭撤銷登記、歇業、解散、或非前述原因但有無法營運事實等其他事由，致無法履行本券之相關義務者，視為本公司同意本券受益權歸屬本券持有人，此時本券持有人應以禮券編號(PIN CODE碼)、手機號碼等向受託銀行申報受益權利，若本券持有人日後無法提供前述資料，則受託銀行將無法配合辦理受益權移轉相關事宜，請本券持有人妥善記錄相關資料以保障自身權益。\n";
        $return .= "禮券編號(PIN CODE碼)：" . $pincode;

        return $return;
    }

    /**
     * 取 has Voucher
     * @param $pincode
     * @return string
     */
    private function getHasVoucher($statusCode, $prodType, $prodApi)
    {
        return (in_array($statusCode, [ '10', '11', '05', '01']) && $prodType === 1 && $prodApi === 1);
    }

    /**
     * 取會員
     * @param $pincode
     * @return string
     */
    private function getOrderer($memberId)
    {
        $orderer = $this->getMember($memberId);

        return ($orderer) ? $this->hideName($orderer->name) : '';
    }

    /**
     * 取會員
     * @param $pincode
     * @return string
     */
    private function getMember($memberId)
    {
        if (!isset($this->members[$memberId])) {
            $this->members[$memberId] = $this->memberService->find($memberId);
        }

        return $this->members[$memberId];
    }


    /*********************
    /* 過渡期用
    *********************/

    /**
     * 處理所有取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function getAllByV1($orders, $isDetail = false)
    {
        if (!$orders) return null;

        $newOrders = [];
        foreach ($orders as $order) {
            $newOrders[] = $this->getByV1($order, $isDetail);
        }

        return $newOrders;
    }

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function getByV1($order, $isDetail = false)
    {
        if (!$order) return null;
        $order = $order->toArray();
        $result['source'] = OrderConfig::SOURCE_TICKET;
        $result['id'] = $this->arrayDefault($order, 'order_id');
        $result['orderNo'] = (string) $this->arrayDefault($order, 'order_no');
        $result['orderAmount'] = $this->arrayDefault($order, 'order_amount');
        $result['orderDiscountAmount'] = $this->arrayDefault($order, 'order_off', 0);
        $result['isRePayment'] = $this->isRepay($order['order_status'], $order['order_payment_method'], $order['order_atm_virtual_account']);
        $result['orderStatusCode'] = $this->getMergeStatusCode($this->changeStatusCode($order['order_status'], $result['isRePayment']));
        $result['orderStatus'] = $this->getOrderStatus($result['orderStatusCode']);
        $result['orderDate'] = $this->arrayDefault($order, 'created_at');
        $result['payment'] = $this->getPayment($order);
        $result['shipping'] = null;
        $result['items'] = $this->processItemsV1($order['details']);

        return $result;
    }

    /**
     *  取得訂單購買項目
     * @param $orderDetail
     * @return string
     */
    private function processItemsV1($orderDetail)
    {
        $items = [];
        if ($orderDetail) {
            foreach ($orderDetail as $detail) {
                if (!isset($items[$detail['prod_cust_id']])) {
                    $items[$detail['prod_cust_id']] = $this->getItemV1($detail);
                }
                else {
                    $items[$detail['prod_cust_id']]->quantity++;
                }
            }
            // 重新整理輸出Items
            $items = $this->remapItems($items);
        }
        return $items;
    }
    /**
     *  處理購買項目
     * @param $detail
     * @return string
     */
    private function getItemV1($detail)
    {
        $newDetail = new \stdClass;
        $newDetail->source = OrderConfig::SOURCE_TICKET;
        $newDetail->itemId = (string) $detail['prod_id'];
        $newDetail->no = null;
        $newDetail->name = $detail['prod_name'];
        $newDetail->spec = $detail['prod_spec_name'];
        $newDetail->quantity = 1;
        $newDetail->price = $detail['price_off'];
        $newDetail->description = trans('common.ticket');
        $newDetail->statusCode = $this->itemUsedStatusCode($detail);
        $newDetail->status = $this->itemUsedStatus($newDetail->statusCode);
        $newDetail->discount = null;
        $newDetail->imageUrl = '';

        return $newDetail;
    }
}
