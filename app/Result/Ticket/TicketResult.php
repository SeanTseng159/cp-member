<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Traits\ObjectHelper;
use App\Traits\StringHelper;
use Carbon\Carbon;

use App\Config\Ticket\TicketConfig;

class TicketResult extends BaseResult
{
    use ObjectHelper;
    use StringHelper;

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

        $prodType = $this->arrayDefault($order, 'prod_type');

        $result['id'] = $this->arrayDefault($order, 'order_detail_id');
        $result['orderNo'] = (string) $this->arrayDefault($order, 'order_no');
        // $result['detailSeq'] = (string) $this->getDetailSeq($this->arrayDefault($order, 'order_detail_seq'));
        $result['serialNumber'] = (string) $this->arrayDefault($order, 'order_detail_sn');
        $result['name'] = $this->arrayDefault($order, 'prod_name');
        $result['spec'] = $this->arrayDefault($order, 'prod_spec_name') . $this->arrayDefault($order, 'prod_spec_price_name');
        $result['place'] = $this->arrayDefault($order, 'prod_locate');
        $result['address'] = $this->arrayDefault($order, 'prod_address');
        // $result['price'] = $this->arrayDefault($order, 'price_retail');
        $result['qrcode'] = $this->arrayDefault($order, 'order_detail_qrcode');
        $result['status'] = $this->getTicketStatus($this->arrayDefault($order, 'verified_status'));
        // $result['catalogId'] = $this->arrayDefault($order, 'catalog_id');
        $result['giftAt'] = $this->arrayDefault($order, 'ticket_gift_at');
        // $result['imageUrl'] = null;
        $result['isEntity'] = false;
        $result['isOnDay'] = ($prodType === 2) ? false : $this->getIsOnDay($result['status'], $order['prod_expire_type'], $order['order_detail_expire_start'], $order['order_detail_expire_due']);
        $result['isPurchase'] = $this->getIsPurchase($prodType);
        // $result['sort'] = null;
        $result['show'] = $this->getShow($order);
        $result['items'] = ($prodType === 2) ? $this->processItems($order['combo']) : [];
        $result['member'] = $this->getMember($order['member']);

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
        //$status = collect(TicketConfig::DB_STATUS)->search($verifiedStatus);

        $status = array_search($verifiedStatus, TicketConfig::DB_STATUS);

        return (string) $status;
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

    /**
     * 取得show
     */
    public function getShow($ticket)
    {
        // 規格
        $spec = $ticket['prod_spec_name'] . $ticket['prod_spec_price_name'];
        $show[] = $this->getShowFormat('', $spec);

        // 地點
        $show[] = $this->getShowFormat('', $ticket['prod_locate']);

        // 使用效期
        $dateRange = ($ticket['prod_expire_type'] === 0) ? '無限制' : substr($ticket['order_detail_expire_start'], 0, 10) . ' ~ ' . substr($ticket['order_detail_expire_due'], 0, 10);
        $show[] = $this->getShowFormat('使用效期：', $dateRange, '#90c320');


        return $show;
    }

    /**
     * 產生show內容
     */
    public function getShowFormat($label = '', $text = '', $color = null)
    {
        $show = new \stdClass;
        $show->label = $label;
        $show->text = $text;
        $show->color = $color;

        return $show;
    }

    /**
     * 處理組合商品
     */
    public function processItems($combo)
    {
        $newItems = [];
        foreach ($combo as $item) {
            $newItems[] = $this->processItem($item);
        }

        return $newItems;
    }

    /**
     * 處理子商品
     */
    public function processItem($item)
    {
        $item = $item->toArray();

        // $result['catalogId'] = $this->arrayDefault($order, 'catalog_id');
        $result['serialNumber'] = (string) $this->arrayDefault($item, 'order_detail_sn');
        $result['name'] = $this->arrayDefault($item, 'prod_name');
        // $result['spec'] = $this->arrayDefault($order, 'prod_spec_name') . $this->arrayDefault($order, 'prod_spec_price_name');
        $result['qrcode'] = $this->arrayDefault($item, 'order_detail_qrcode');
        $status = $this->getTicketStatus($this->arrayDefault($item, 'verified_status'));
        $result['isOnDay'] = $this->getIsOnDay($status, $item['prod_expire_type'], $item['order_detail_expire_start'], $item['order_detail_expire_due']);
        $result['show'] = $this->getShow($item);

        return $result;
    }

    /**
     * 處理會員
     */
    public function getMember($member)
    {
        $result = new \stdClass;
        $result->name = $this->hideName($member->name);
        $result->phone = '+' . $member->countryCode . $this->hidePhoneNumber($member->cellphone);

        return $result;
    }
}
