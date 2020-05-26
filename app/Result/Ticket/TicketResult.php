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

    private $now;
    private $orderStatus;

    public function __construct($orderStatus = '99')
    {
        $this->now = Carbon::now();
        $this->orderStatus = $orderStatus;
    }

    /**
     * 處理所有取得資料
     * @param $orderStatus
     * @param $tickets
     * @param $member
     * @param bool $isDetail
     */
    public function getAll($tickets, $member = null, $isDetail = false)
    {
        if (!$tickets) return null;

        $newTickets = [];
        foreach ($tickets as $ticket) {
            $newTickets[] = $this->get($ticket, $member, $isDetail);
        }

        return $newTickets;
    }

    /**
     * 取得資料
     * @param $order
     * @param $member
     * @param bool $isDetail
     */
    public function get($order, $member = null, $isDetail = false)
    {
        if (!$order) return null;

        $order = $order->toArray();

        $prodType = $this->arrayDefault($order, 'prod_type');

        // 取票券狀態，如果轉贈狀態，恆之轉贈狀態
        $status = $this->getTicketStatus($this->orderStatus, $order);

        // 取組合同步失效狀態
        $comboStatusAndDesc = $this->getComboStatusAndDescription($prodType, $order['sync_expire_due'], $order['use_value'], $status);

        $result['id'] = $this->arrayDefault($order, 'order_detail_id');
        $result['orderNo'] = (string) $this->arrayDefault($order, 'order_no');
        // $result['detailSeq'] = (string) $this->getDetailSeq($this->arrayDefault($order, 'order_detail_seq'));
        $result['serialNumber'] = (string) $this->arrayDefault($order, 'order_detail_sn');
        $result['name'] = $this->arrayDefault($order, 'prod_name');
        $result['spec'] = $this->arrayDefault($order, 'prod_spec_name') . $this->arrayDefault($order, 'prod_spec_price_name');
        $result['place'] = $this->arrayDefault($order, 'prod_locate');
        $result['address'] = $this->arrayDefault($order, 'prod_address');
        $result['price'] = $this->arrayDefault($order, 'price_off');
        $result['qrcode'] = ($prodType === 2) ? null : $this->arrayDefault($order, 'order_detail_qrcode');
        $result['status'] = $status;
        $result['catalogId'] = $this->arrayDefault($order, 'catalog_id');
        $result['giftAt'] = $this->arrayDefault($order, 'ticket_gift_at');
        // $result['imageUrl'] = null;
        // $result['isEntity'] = false;
        $result['isOnDay'] = ($prodType === 2) ? false : $this->getIsOnDay($result['status'], $order['prod_expire_type'], $order['order_detail_expire_start'], $order['order_detail_expire_due']);
        $result['isPurchase'] = $this->getIsPurchase($prodType);
        $result['items'] = ($prodType === 2) ? $this->processItems($order['combo'], $comboStatusAndDesc['status'], $comboStatusAndDesc['expireDate']) : [];
        $result['show'] = $this->getShow($order, $status, $result['giftAt']);
        $result['comboDescription'] = $comboStatusAndDesc['description'];
        $result['member'] = ($member) ? $this->getMember($member) : $this->arrayDefault($order, 'order_detail_member_id');

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
    public function getTicketStatus($orderStatus, $order, $comboStatus = '99')
    {
        $verifiedStatus = $this->arrayDefault($order, 'verified_status', '99');
        $orderMemberId = $this->arrayDefault($order, 'member_id');
        $ownMemberId = $this->arrayDefault($order, 'order_detail_member_id');

        // 檢查是否已轉贈
        if ($orderStatus === '4' && $orderMemberId !== $ownMemberId) return '4';

        $now = $this->now->toDateTimeString();

        if ($verifiedStatus == '11') {
            if ($comboStatus === '3') {
                return '1';
            }
            elseif ($order['use_type'] === 1 && $order['use_value'] > 0) {
                return '2';
            }
            elseif ($order['use_type'] === 4) {
                $useValueAry = explode('~', $order['use_value']);
                if ($now > $useValueAry[0] && $now < $useValueAry[1]) return '2';
            }

            return '1';
        }
        elseif ($verifiedStatus == '10') {
            // 同步效期失效，還未使用，都註記成已失效
            if ($comboStatus === '3') {
                return '3';
            }
            elseif ($order['order_detail_expire_due'] && $now > $order['order_detail_expire_due']) {
                return '3';
            }
            elseif ($order['order_detail_expire_start'] && $now < $order['order_detail_expire_start']) {
                return '5';
            }

            return '0';
        }

        return '99';
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
    public function getShow($ticket, $status = '99', $date = '')
    {
        // 規格
        $spec = $ticket['prod_spec_name'] . $ticket['prod_spec_price_name'];
        $show[] = $this->getShowFormat('', $spec);

        // 地點
        $show[] = $this->getShowFormat('', $ticket['prod_locate']);

        // 使用效期
        if (in_array($status, ['3', '4']) && $date) {
            $dateRange = '~ ' . substr($date, 0, 16);
        }
        else {
            $dateRange = ($ticket['prod_expire_type'] === 0) ? '無限制' : substr($ticket['order_detail_expire_start'], 0, 16) . ' ~ ' . substr($ticket['order_detail_expire_due'], 0, 16);
        }
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
    public function processItems($combo, $comboStatus = '99', $expireDate = '')
    {
        $newItems = [];

        foreach ($combo as $item) {
            $newItems[] = $this->processItem($item, $comboStatus, $expireDate);
        }

        return $newItems;
    }

    /**
     * 處理子商品
     */
    public function processItem($item, $comboStatus, $expireDate)
    {
        $result['orderNo'] = (string) $this->arrayDefault($item, 'order_no');
        $result['serialNumber'] = (string) $this->arrayDefault($item, 'order_detail_sn');
        $result['name'] = $this->arrayDefault($item, 'prod_name');
        $result['catalogId'] = $this->arrayDefault($item, 'catalog_id');
        $result['status'] = $this->getTicketStatus($this->orderStatus, $item, $comboStatus);
        $result['statusName'] = TicketConfig::DB_STATUS_NAME[$result['status']];
        $result['qrcode'] = ($result['status'] === '3') ? '' : $this->arrayDefault($item, 'order_detail_qrcode');
        $result['isOnDay'] = ($result['status'] === '3') ? false : $this->getIsOnDay($result['status'], $item['prod_expire_type'], $item['order_detail_expire_start'], $item['order_detail_expire_due']);
        $result['show'] = $this->getShow($item, $result['status'], $expireDate);

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
    public function getComboStatusAndDescription($type, $syncExpireDue, $useValue, $ticketStatus)
    {
        $description = new \stdClass;
        $description->text = '';
        $description->date = '';

        $result = [
            'status' => '99',
            'description' => $description,
            'expireDate' => ''
        ];

        if ($type !== 2 || !$syncExpireDue) return $result;

        if ($ticketStatus === '0') {
            $expireAry = explode('.', $syncExpireDue);

            if ($expireAry[0] === 'h') {
                 // 未啟用
                $result['status'] = '0';
                $result['description']->text = '本套票組合商品，其任一張開通使用，整組需於 %s 使用完畢，逾期未使用，視同失效';
                $result['description']->date = $expireAry[1] . '小時內';
            } elseif($expireAry[0] === 'd') {
                // 未啟用
                $result['status'] = '0';
                $result['description']->text = '本套票組合商品，其任一張開通使用，整組需於 %s 使用完畢，逾期未使用，視同失效';
                $result['description']->date = $expireAry[1] . '日內';
            }
        }
        elseif ($ticketStatus === '1' || $ticketStatus === '2' || $ticketStatus === '3') {
            $now = $this->now->toDateTimeString();
            $useValueAry = explode('~', $useValue);

            $result['description']->date = date('Y年m月d日 H時i分', strtotime($useValueAry[1]));
            $result['expireDate'] = $useValueAry[1];

            if ($now > $useValueAry[0] && $now < $useValueAry[1]) {
                // 啟用中
                $result['status'] = '1';
                $result['description']->text = '本套票組合商品，需於 %s 使用完畢，逾期未使用，視同失效';
                $result['description']->date .= '內';
            }
            else {
                // 啟用後失效
                $result['status'] = '3';
                $result['description']->text = '本套票組合商品，超過使用時間 %s ，逾期未使用之票券，已視同失效';
            }
        }

        return $result;
    }
}
