<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use Carbon\Carbon;
use App\Result\BaseResult;
use App\Config\Ticket\ProcuctConfig;
use App\Helpers\ImageHelper;
use App\Traits\StringHelper;

class DiningCarMenuResult extends BaseResult
{
    use StringHelper;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取菜單列表
     * @param $menus
     */
    public function list($menuCategories)
    {
        if ($menuCategories->isEmpty()) return [];

        $newItems = [];
        foreach ($menuCategories as $category) {
            $newCategory = $this->getMenuCategories($category);
            if ($newCategory) $newItems[] = $newCategory;
        }

        return $newItems;
    }

    /**
     * 菜單資訊
     * @param $category
     */
    private function getMenuCategories($category)
    {
        $menus = $this->getMenus($category->menus);

        if (!$menus) return [];

        $result = new \stdClass;
        $result->categoryName = $category->name;
        $result->menus = $menus;

        return $result;
    }

    /**
     * 取菜單列表
     * @param $menus
     */
    private function getMenus($menus)
    {
        if ($menus->isEmpty()) return [];

        $newMenus = [];
        foreach ($menus as $menu) {
            // var_dump($menu);
            $newMenu = $this->getMenu($menu);
            if ($newMenu) $newMenus[] = $newMenu;
        }

        return $newMenus;
    }

    /**
     * 取菜單列表
     * @param $data
     */
    public function getMenu($menu, $isDetail = false)
    {
        if (!$menu) return null;

        $result = new \stdClass;
        $result->id = $menu->id;
        $result->diningCarId = $menu->dining_car_id;
        $result->name = $menu->name;
        $result->product = $this->getProduct($menu->prodSpecPrice);
        $result->price = ($result->product) ? $result->product->price : $menu->price;

        if ($isDetail) {
            $result->categoryName = $menu->category->name;
            $result->imgs = ImageHelper::urls($menu->imgs);
            $result->content = $menu->content;
        }
        else {
            $result->img = ImageHelper::url($menu->mainImg);
            $result->content = $this->outputStringLength($menu->content, 30);
        }

        if(empty($result->product)){
            $result=[];
        }

        return $result;
    }

    /**
     * 取綁定商品
     * @param $prodSpecPrice
     */
    private function getProduct($prodSpecPrice)
    {
        if (!$prodSpecPrice || !$prodSpecPrice->prodSpec || !$prodSpecPrice->prodSpec->product) return null;

        $product = new \stdClass;
        $product->source = ($prodSpecPrice->prodSpec->product->is_physical) ? ProcuctConfig::SOURCE_TPASS_PHYSICAL : ProcuctConfig::SOURCE_TICKET;
        $product->id = $prodSpecPrice->prodSpec->product->prod_id;
        $product->specId = $prodSpecPrice->prodSpec->prod_spec_id;
        $product->priceId = $prodSpecPrice->prod_spec_price_id;
        $product->price = $prodSpecPrice->prod_spec_price_value;
        $product->stock = $this->getStock($prodSpecPrice);
        $product->maxLimit = $prodSpecPrice->prodSpec->product->prod_limit_num;

        return $product;
    }

    /**
     * 取庫存
     * @param $prodSpecPrice
     */
    private function getStock($prodSpecPrice)
    {
        // 檢查票種銷售時間
        if (!$this->checkOnSale($prodSpecPrice->prod_spec_price_onsale_time, $prodSpecPrice->prod_spec_price_offsale_time)) return 0;

        return $prodSpecPrice->prod_spec_price_stock;
    }

    /**
     * 取綁定商品
     * @param $onSaleTime
     * @param $offSaleTime
     */
    private function checkOnSale($onSaleTime, $offSaleTime)
    {
        $result = true;
        if ($onSaleTime && $offSaleTime) {
            $onSaleTime = Carbon::parse($onSaleTime);
            $offSaleTime = Carbon::parse($offSaleTime);
            $now = Carbon::now();

            if ($now->lte($onSaleTime) || $now->gte($offSaleTime)) {
                $result = false;
            }
        }

        return $result;
    }
}
