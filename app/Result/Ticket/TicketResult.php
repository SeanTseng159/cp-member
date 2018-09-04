<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Traits\ObjectHelper;
use Carbon\Carbon;

use App\Config\Ticket\TicketConfig;

class TicketResult extends BaseResult
{
    use ObjectHelper;

    private $backendHost;

    public function __construct()
    {
        // $this->setBackendHost();
    }

    /**
     * 設定後端網址
     */
    private function setBackendHost()
    {
        if (env('APP_ENV') === 'production') {
            $this->backendHost = TicketConfig::BACKEND_HOST;
        }
        elseif (env('APP_ENV') === 'beta') {
            $this->backendHost = TicketConfig::BACKEND_HOST_BETA;
        }
        else {
            $this->backendHost = TicketConfig::BACKEND_HOST_TEST;
        }
    }

    /**
     * 處理所有取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function getAll($tickets, $isDetail = false)
    {
        if (!$tickets) return null;

        $newTickets = [];
        foreach ($tickets as $ticket) {
            $newTickets[] = $this->get($ticket, $isDetail);
        }

        return $newTickets;
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

        $result['id'] = $this->arrayDefault($order, 'order_detail_id');
        $result['orderNo'] = (string) $this->arrayDefault($order, 'order_no');
        // $result['detailSeq'] = (string) $this->getDetailSeq($this->arrayDefault($order, 'order_detail_seq'));
        $result['serialNumber'] = (string) $this->arrayDefault($order, 'order_detail_sn');
        $result['name'] = $this->arrayDefault($order, 'prod_name');;
        $result['spec'] = $this->arrayDefault($order, 'prod_spec_name') . $this->arrayDefault($order, 'prod_spec_price_name');
        $result['place'] = $this->arrayDefault($order, 'prod_locate');
        $result['address'] = $this->arrayDefault($order, 'prod_address');
        $result['price'] = $this->arrayDefault($order, 'price_retail');
        $result['qrcode'] = $this->arrayDefault($order, 'order_detail_qrcode');
        $result['status'] = $this->getTicketStatus($this->arrayDefault($order, 'verified_status'));
        // $result['catalogId'] = $this->arrayDefault($order, 'catalog_id');
        $result['giftAt'] = $this->arrayDefault($order, 'ticket_gift_at');
        // $result['imageUrl'] = null;
        $result['isEntity'] = false;
        $result['isOnDay'] = $this->getIsOnDay($result['status'], $order['prod_expire_type'], $order['order_detail_expire_start'], $order['order_detail_expire_due']);
        $result['isPurchase'] = $this->getIsPurchase($this->arrayDefault($order, 'prod_type'));
        // $result['sort'] = null;
        //$result['items'] = $this->processItems($order['detail']);

        /*if ($isDetail) {
        }*/

        return $result;
    }

    /**
     * 取序號
     */
    public function getDetailSeq($seq)
    {
        return str_pad($seq, 2, '0', STR_PAD_LEFT);
    }

    /**
     * 票券狀態
     */
    public function getTicketStatus($verifiedStatus)
    {
        $status = '99';

        switch($verifiedStatus) {
            case 10:
                $status = '0';
                break;
        }

        return $status;
    }

    /**
     * 是否在可使用日期
     */
    public function getIsOnDay($ticketStatus, $type, $expireStart, $expireDue)
    {
        if (!in_array($ticketStatus, ['0', '2'])) return false;

        $isOnDay = false;

        if ($type === 0) {
            $isOnDay = true;
        }
        else {
            if (!$expireStart || !$expireDue) return $isOnDay;

            $now = Carbon::now();
            $start = Carbon::parse($expireStart);
            $due = Carbon::parse($expireDue);

            if ($now->gte($start) && $now->lte($due)) $isOnDay = true;
        }

        return $isOnDay;
    }

    /**
     * 是否加購
     */
    public function getIsPurchase($type)
    {
        return ($type === 3);
    }
}
