<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result;

use App\Enum\WaitingStatus;
use App\Helpers\CommonHelper;
use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Traits\ShopHelper;
use Carbon\Carbon;


class MenuOrderResult
{


    public function get($menuOrder, $isShowMenu = true)
    {
        $shop = new \stdClass();
        $shop->id = $menuOrder->shop->id;
        $shop->name = $menuOrder->shop->name;

        $details = $menuOrder->details;
        $menus = [];
        $totalAmount = 0;
        foreach ($details as $item) {
            $menu = new \stdClass();
            $menu->id = $item->menu_id;
            $menu->name = $item->menu->name;

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
        $order->orderDate = (new DateHelper)::format($menuOrder->created_at, 'Y-m-d');
        $order->diningDate = (new DateHelper)::format($menuOrder->date_time, 'Y-m-d H:i');
        $order->status = $menuOrder->status;
        $order->code = $menuOrder->code;
        $order->totalAmount = $menuOrder->amount;
        $order->payment = new \stdClass();
        $order->payment->type = $menuOrder->pay_method;
        $order->payment->status = $menuOrder->order ? $menuOrder->order->order_status : '00';
        $ret = new \stdClass();
        $ret->shop = $shop;
        $ret->order = $order;
        if ($isShowMenu) {
            $ret->menu = array_values($menus);
        }


        return $ret;
    }


}
