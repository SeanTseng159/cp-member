<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result;

use App\Config\Ticket\OrderConfig;
use App\Enum\WaitingStatus;
use App\Helpers\CommonHelper;
use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Helpers\OrderHelper;
use App\Traits\ShopHelper;
use Carbon\Carbon;


class MenuOrderResult
{

    private $prefix = 'orderDish_';

    public function get($menuOrder)
    {
        $shop = new \stdClass();
        $shop->id = $menuOrder->shop->id;
        $shop->name = $menuOrder->shop->name;

        $details = $menuOrder->details;
        $menus = [];
        foreach ($details as $item) {
            $menu = new \stdClass();
            $menu->id = $item->menu_id;
            $menu->name = $item->menu->name;
            $menu->img = ImageHelper::url($item->menu->imgs->first());

            if (array_key_exists($menu->id, $menus)) {
                $menus[$menu->id]->quantity++;
                $menus[$menu->id]->price += $item->price;
            } else {
                $menu->quantity = 1;
                $menu->price = $item->price;
                $menus[$menu->id] = $menu;
            }

        }


        $order = new \stdClass();
        $order->id = $menuOrder->menu_order_no;
        $order->orderNo = ($menuOrder->order)->order_no ?? '';
        $order->orderDate = (new DateHelper)::format($menuOrder->created_at, 'Y-m-d');
        $order->diningDate = (new DateHelper)::format($menuOrder->date_time, 'Y-m-d H:i');
        $order->status = $menuOrder->status;
        $order->code = $menuOrder->code;
        if (optional($menuOrder->order)->order_status == '10')
            $order->qrcode = $this->prefix . $menuOrder->qrcode;
        $order->totalAmount = $menuOrder->amount;
        $order->totalQuantity = count($details);
        $order->payment = new \stdClass();
        $order->payment->type = $menuOrder->pay_method;
        $order->payment->status = $this->getOrderStatus($menuOrder);

        $ret = new \stdClass();
        $ret->shop = $shop;
        $ret->order = $order;
        $ret->menu = array_values($menus);

        return $ret;
    }

    private function getOrderStatus($menuOrder)
    {
        $orderHelper = new OrderHelper;
        $ret = '00';
        if (!$menuOrder->status) {
            $ret = '09';
        } else {
            if ($menuOrder->order) {
                $order = $menuOrder->order;
                $orderStatus = $order->order_status;
                $orderPayMethod = $order->order_payment_method;
                $atmVirtualAccount = $order->order_atm_virtual_account;
                $payAmount = $order->order_amount;
                $ret = $orderHelper->getStatusCode($orderStatus, $orderPayMethod, $atmVirtualAccount, $payAmount);
            }

        }
        return $ret;
    }

    public function getQrCode($qrcode)
    {
        return $this->prefix . $qrcode;
    }

}
