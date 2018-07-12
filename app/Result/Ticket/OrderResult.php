<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Config\Ticket\OrderConfig;
use App\Traits\ObjectHelper;
use Carbon\Carbon;

class OrderResult extends BaseResult
{
    use ObjectHelper;

    private $quantity = 0;
    private $backendHost;

    public function __construct()
    {
        $this->backendHost = (env('APP_ENV') === 'production') ? OrderConfig::BACKEND_HOST : OrderConfig::BACKEND_HOST_TEST;
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
        $result['items'] = $this->processItems($order['detail']);

        /*if ($isDetail) {
        }*/

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
        $payment->amount = (string) $this->arrayDefault($order, 'order_amount');

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
     *  取得訂單購買項目
     * @param $orderDetail
     * @return string
     */
    private function processItems($orderDetail)
    {
        $items = [];
        if ($orderDetail) {
            foreach ($orderDetail as $detail) {
                if (!isset($items[$detail['prod_cust_id']])) {
                    $items[$detail['prod_cust_id']] = $this->getItem($detail);
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
    private function getItem($detail)
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
        $imageUrl = collect($detail['product_img'])->first();
        $newDetail->imageUrl = ($imageUrl) ? $this->backendHost . $imageUrl['img_thumbnail_path'] : '';

        return $newDetail;
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
     * item使用狀態碼
     * @param $detail
     * @return string
     */
    private function itemUsedStatusCode($detail)
    {
        if ($detail['order_detail_member_id'] !== $detail['member_id']) {
            return '05';
        }

        switch ($detail['verified_status']) {
            case 0:
                return '00';
            case 1:
                return '01';
            case 10:
                return '10';
            case 11:
                return '11';
            case 20:
                return '20';
            case 21:
                return '21';
            case 22:
                return '22';
            case 23:
                return '23';
        }

        return '00';
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
}
