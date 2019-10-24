<?php

namespace App\Repositories;


use App\Core\Logger;
use App\Models\MenuOrder;
use App\Models\MenuOrderDetail;
use App\Models\Ticket\Menu;
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
    protected $seqMenuOrderRepository;

    public function __construct(MenuOrder $model, MenuOrderDetail $menuOrderDetail,
                                Menu $menu,
                                SeqMenuOrderRepository $seqMenuOrderRepository)
    {
        $this->menuOrder = $model;
        $this->menuOrderDetail = $menuOrderDetail;
        $this->menu = $menu;
        $this->seqMenuOrderRepository = $seqMenuOrderRepository;
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
            $menuOrder->amount =$total ;
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
        return $this->menuOrder->with('shop', 'details', 'details.menu', 'order')
            ->where('code', $code)
            ->first();
    }

    public function updateStatus($code, $status = false)
    {
        return $this->menuOrder
            ->where('code', $code)
            ->update(['status' => $status]);

    }

    public function memberList($memberId)
    {
        return $this->menuOrder->with('shop', 'details', 'details.menu', 'order')
            ->where('member_id', $memberId)
            ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->orderBy('created_at', 'desc')
            ->get();

    }

    private function getCode($menu_odre_id)
    {
        $hashids = new Hashids('citypass_menu_order', 7);
        return $hashids->encode($menu_odre_id);
    }
}
