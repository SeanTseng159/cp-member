<?php

namespace App\Repositories;


use App\Core\Logger;
use App\Models\MenuOrder;
use App\Models\MenuOrderDetail;
use App\Models\Ticket\Menu;
use App\Repositories\Ticket\OrderRepository;
use App\Repositories\Ticket\SeqMenuOrderRepository;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;


class MenuOrderRepository extends BaseRepository
{
    /**
     * Default model.
     *
     * @var string
     */
    protected $menuOrder;
    protected $menuOrderDetail;
    protected $menu;
    protected $orderRepository;
    protected $seqMenuOrderRepository;
    protected $limit = 20;


    public function __construct(MenuOrder $model, MenuOrderDetail $menuOrderDetail, Menu $menu,
                                OrderRepository $repository, SeqMenuOrderRepository $seqMenuOrderRepository)


    {
        $this->menuOrder = $model;
        $this->menuOrderDetail = $menuOrderDetail;
        $this->menu = $menu;
        $this->seqMenuOrderRepository = $seqMenuOrderRepository;
        $this->orderRepository = $repository;
    }


    public function create($shopId, $menu, $payment, $cellphone, $time, $remark, $memberId = null)
    {
        try {

            $menuIds = array_column($menu, 'id');
            $count = $this->menu->where('dining_car_id', $shopId)->whereIn('id', $menuIds)->count();

            if ($count != count($menuIds))
                throw new \Exception('請選擇同一店鋪的商品');

            \DB::connection('backend')->beginTransaction();

            $oderNo = $this->seqMenuOrderRepository->getOrderNo();
            $id = $this->menuOrder->insertGetId([
                'member_id' => $memberId,
                'dining_car_id' => $shopId,
                'menu_order_no' => $oderNo,
                'pay_method' => $payment,
                'cellphone' => $cellphone,
                'date_time' => $time,
                'note' => $remark,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $this->menuOrder->where('id', $id)
                ->update([
                    'code' => $this->getCode($id)
                ]);

            $details = [];
            foreach ($menu as $item) {
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $insert = [];
                    $insert['menu_order_id'] = $id;
                    $insert['menu_id'] = $item['id'];
                    $insert['created_at'] = Carbon::now();
                    $insert['updated_at'] = Carbon::now();
                    $details[] = $insert;
                }
            }
            $this->menuOrderDetail->insert($details);

            //取得price 資料
            $menuOrder = $this->get($id);
            $total = 0;
            foreach ($menuOrder->details as $detail) {
                $detail->price = $detail->menu->price;
                $detail->save();

                $total += $detail->menu->price;
            }
            $menuOrder->amount = $total;
            $menuOrder->save();
            \DB::connection('backend')->commit();
            return $id;

        } catch (Exception $e) {

            Logger::error('Exception Create Order Error', $e->getMessage());
            \DB::connection('backend')->rollBack();
            throw new Exception($e->getMessage());
        }


    }

    public function get($menuOrderID)
    {
        return $this->menuOrder->with('shop', 'details', 'details.menu', 'order')
            ->where('id', $menuOrderID)
            ->first();
    }

    public function getByOrderNo($menuOrderNo)
    {
        return $this->menuOrder->with('shop', 'details', 'details.menu', 'order')
            ->where('menu_order_no', $menuOrderNo)
            ->first();
    }

    public function getByCode($code)
    {
        return $this->menuOrder->with('shop', 'details.menu.imgs', 'order')
            ->where('code', $code)
            ->first();
    }

    public function updateStatus($code, $status = false)
    {
        return $this->menuOrder
            ->where('code', $code)
            ->update(['status' => $status]);

    }

    public function memberList($memberId, $page)
    {
        return $this->menuOrder->with('shop', 'details', 'details.menu', 'order')
            ->where('member_id', $memberId)
            ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->orderBy('created_at', 'desc')
            ->forPage($page, $this->limit)
            ->get();
    }

    public function getPageInfo($memberId)
    {
        $count = $this->menuOrder
            ->where('member_id', $memberId)
            ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->count();
        $totalPage = ceil($count / $this->limit);
        return[$count,$totalPage];
    }


    public function checkOrderProdStatus($memberId, $menuOrderNo)
    {
//        $menuOrder = $this->menuOrder->where('menu_order_no', $menuOrderNo)->first();
//        $menuOrderDetails = $this->menuOrderDetail->select(
//            'menu_order_id',
//            'menu_id',
//            'price',
//            \DB::raw('count(menu_id) as qty'))
//            ->groupBy('menu_order_id', 'menu_id', 'price')
//            ->where('menu_order_id', $menuOrder->id)
//            ->get();


//        foreach ($menuOrder->details as $detail) {
//            $menu = $detail->menu;
//            $name = $menu->name;
//            $prodSpecPrice = optional($menu->prodSpecPrice);
//            $prodSpec = optional($prodSpecPrice->prodSpec);
//            $product = optional($prodSpec->product);
//            if (is_null($prodSpecPrice) || is_null($prodSpec) || is_null($product))
//                throw new Exception("[$name]無法線上付款");
//
//            //檢查限購數量
//            $menuId = $menu->id;
//            $limit = $product->prod_limit_num;
//            $details = collect($menuOrderDetails)->filter(function ($item) use ($menuId, $limit) {
//                return $item->menu_id == $menuId && $item->qty <= $limit;
//            });
//            $buyQuantity = $details->count();
//
//            if ($product->prod_type === 1 || $product->prod_type === 2) {
//                if ($product->prod_limit_type == 0 ) {
//
//
//                    if ($buyQuantity > $product->prod_limit_num)
//                        throw new Exception("[$name]商品超過可購買數量，無法線上付款");
//
//                }
//                else {
//                    $memberBuyQuantity = $this->orderService->getCountByProdAndMember($product->product_id, $memberId);
//                    $buyQuantity += $memberBuyQuantity;
//                }
//            }
//            elseif ($product->prod_type === 3) {
//                if ($buyQuantity > $product->prod_plus_limit) return 'E9012';
//            }
//                if ($buyQuantity > $product->prod_limit_num) return 'E9012';


//            if ($product->prod_type === 1 || $product->prod_type === 2) {
//                if ($product->prod_limit_type === 1) {
//                    $memberBuyQuantity = $this->orderService->getCountByProdAndMember($product->product_id, $memberId);
//                    $buyQuantity += $memberBuyQuantity;
//                }
//                if ($buyQuantity > $product->prod_limit_num) return 'E9012';
//            } elseif ($product->prod_type === 3) {
//                if ($buyQuantity > $product->prod_plus_limit) return 'E9012';
//            }
//        }

        $menuOrder = $this->menuOrder
            ->with('shop', 'details.menu.prodSpecPrice.prodSpec.product')
            ->where('menu_order_no', $menuOrderNo)
            ->where('member_id', $memberId)
            ->first();


        if (!$menuOrder)
            throw new Exception("點餐單號錯誤");

        //todo check

        return $menuOrder;
    }

    public function createOrder($params, $menuOrder)
    {
        return \DB::connection('backend')->transaction(function () use ($params, $menuOrder) {
            return $this->orderRepository->createByMenuOrder($params, $menuOrder);
        });


    }

    private function getCode($menu_odre_id)
    {
        $hashids = new Hashids('citypass_menu_order', 7);
        return $hashids->encode($menu_odre_id);
    }


}
