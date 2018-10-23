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
    private $now;

    public function __construct()
    {
        $this->now = Carbon::now();
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
        $result['qrcode'] = ($prodType === 2) ? null : $this->arrayDefault($order, 'order_detail_qrcode');
        $result['status'] = $this->getTicketStatus($this->arrayDefault($order, 'verified_status'));
        // $result['catalogId'] = $this->arrayDefault($order, 'catalog_id');
        $result['giftAt'] = $this->arrayDefault($order, 'ticket_gift_at');
        // $result['imageUrl'] = null;
        $result['isEntity'] = false;
        $result['isOnDay'] = ($prodType === 2) ? false : $this->getIsOnDay($result['status'], $order['prod_expire_type'], $order['order_detail_expire_start'], $order['order_detail_expire_due']);
        $result['isPurchase'] = $this->getIsPurchase($prodType);
        $result['items'] = ($prodType === 2) ? $this->processItems($order['combo']) : [];
        $result['show'] = $this->getShow($order);
        $result['comboDescription'] = $this->getComboDescription($prodType, $order['sync_expire_due'], $order['use_value'], $result['status']);
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

            $start = Carbon::parse($expireStart);
            $due = Carbon::parse($expireDue);

            if ($this->now->gte($start) && $this->now->lte($due)) $isOnDay = true;
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
     * 取得會員參數
     */
    public function getMember($member)
    {
        $result = new \stdClass;
        $result->id = $member->id;
        $result->name = $this->hideName($member->name);
        $result->phone = '+' . $member->countryCode . $this->hidePhoneNumber($member->cellphone);

        return $result;
    }

    /**
     * 取得組合商品說明
     */
    public function getComboDescription($type, $syncExpireDue, $useValue, $ticketStatus)
    {
        $result = new \stdClass;
        $result->text = '';
        $result->date = '';

        if ($type !== 2 || !$syncExpireDue) return $result;

        if ($ticketStatus === '0') {
            $expireAry = explode('.', $syncExpireDue);

            if ($expireAry[0] === 'h') {
                 // 未啟用
                $status = '0';
                $result->text = '本套票組合商品，其任一張開通使用，整組需於 %s 使用完畢，逾期未使用，視同失效';
                $result->date = $expireAry[1] . '小時內';
            }
        }
        elseif ($ticketStatus === '1' || $ticketStatus === '3') {
            $now = $this->now->toDateTimeString();
            $useValueAry = explode('~', $useValue);

            $result->date = date('Y年m月d日 H時i分', strtotime($useValueAry[1]));

            if ($now > $useValueAry[0] && $now < $useValueAry[1]) {
                // 啟用中
                $status = '1';
                $result->text = '本套票組合商品，需於 %s 使用完畢，逾期未使用，視同失效';
                $result->date .= '內';
            }
            else {
                // 啟用後失效
                $status = '3';
                $result->text = '本套票組合商品，超過使用時間 %s ，逾期未使用之票券，已視同失效';
            }
        }

        return $result;
    }
}
