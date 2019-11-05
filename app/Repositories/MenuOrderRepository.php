<?php

namespace App\Repositories;


use App\Core\Logger;
use App\Models\MenuOrder;
use App\Models\MenuOrderDetail;
use App\Models\Ticket\Menu;
use App\Repositories\Ticket\OrderDetailRepository;
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
    protected $orderDetailRepository;
    protected $limit = 20;


    public function __construct(MenuOrder $model, MenuOrderDetail $menuOrderDetail, Menu $menu,
                                OrderRepository $repository,
                                OrderDetailRepository $orderDetailRepository,
                                SeqMenuOrderRepository $seqMenuOrderRepository)


    {
        $this->menuOrder = $model;
        $this->menuOrderDetail = $menuOrderDetail;
        $this->menu = $menu;
        $this->seqMenuOrderRepository = $seqMenuOrderRepository;
        $this->orderRepository = $repository;
        $this->orderDetailRepository = $orderDetailRepository;
    }


    public function create($shopId, $menu, $payment, $cellphone, $time, $remark, $memberId = null)
    {
        $menuIds = array_column($menu, 'id');
        $count = $this->menu->where('dining_car_id', $shopId)->whereIn('id', $menuIds)->count();

        if ($count != count($menuIds))
            throw new \Exception('請選擇同一店鋪的商品');

        if ($payment == 1) {

            $illegalItem = [];
            //檢查是否為可顯上購買
            $menuModels = $this->menu
                ->with('prodSpecPrice.prodSpec.product')
                ->whereIn('id', $menuIds)
                ->get();

            foreach ($menuModels as $item) {
                $name = $item->name;
                $prodSpecPrice = optional($item->prodSpecPrice);
                $prodSpec = optional($prodSpecPrice->prodSpec);
                $product = optional($prodSpec->product);
                if (is_null($prodSpecPrice) || is_null($prodSpec) || is_null($product))
                    $illegalItem [] = "[$name]無法線上付款";

                //檢查限購數量
                $ids = array_column($menu, 'id');
                $id = array_search($item->id, $ids);
                $buyQuantity = $menu[$id]['quantity'];

                // 檢查是否有庫存
                if ($prodSpecPrice->prod_spec_price_stock <= 0) {
                    $illegalItem [] = "[$name]無庫存";
                }

                if ($prodSpecPrice->prod_spec_price_stock < $buyQuantity) {
                    $i = $prodSpecPrice->prod_spec_price_stock;
                    $illegalItem [] = "[$name]限購 $i 個";
                }

                //檢查可購買數量
                if ($product->prod_type === 1 || $product->prod_type === 2) {
                    if ($product->prod_limit_type == 0) {
                        if ($buyQuantity > $product->prod_limit_num) {
                            $i = $product->prod_limit_num;
                            $illegalItem [] = "[$name]最多可購買 $i 個";
                        }
                    } else {
                        $memberBuyQuantity = $this->orderDetailRepository->getCountByProdAndMember($product->product_id, $memberId);
                        if (($buyQuantity + $memberBuyQuantity) > $product->prod_limit_num) {
                            $i = $product->prod_limit_num - $memberBuyQuantity;
                            $illegalItem [] = "[$name]最多可購買 $i 個";
                        }

                    }
                } elseif ($product->prod_type === 3) {
                    if ($buyQuantity > $product->prod_plus_limit) {
                        $i = $product->prod_plus_limit;
                        $illegalItem [] = "[$name]最多可購買 $i 個";
                    }
                }
            }
            if (count($illegalItem) > 0) {
                $str = implode("\n", $illegalItem);
                throw new Exception($str);
            }
        } //檢查線受購買完畢

        try {
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
            \DB::connection('backend')->rollBack();
            Logger::error('Exception Create Order Error', $e->getMessage());
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
        return [$count, $totalPage];
    }


    public function checkOrderProdStatus($memberId, $menuOrderNo)
    {


        $menuOrder = $this->menuOrder
            ->with('shop', 'details.menu.prodSpecPrice.prodSpec.product')
            ->where('menu_order_no', $menuOrderNo)
            ->where('member_id', $memberId)
            ->first();


        if (!$menuOrder)
            throw new Exception("點餐單號錯誤");

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
