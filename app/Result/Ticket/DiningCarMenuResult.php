<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use Carbon\Carbon;
use App\Helpers\ImageHelper;

class DiningCarMenuResult extends BaseResult
{
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
        $result->price = $menu->price;

        if ($isDetail) {
            $result->categoryName = $menu->category->name;
            $result->imgs = ImageHelper::urls($menu->imgs);
            $result->content = $menu->content;
            $result->product = $this->getProduct($menu->prodSpecPrice);
        }
        else {
            $result->img = ImageHelper::url($menu->mainImg);
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
        $product->id = $prodSpecPrice->prodSpec->product->prod_id;
        $product->spec_id = $prodSpecPrice->prodSpec->prod_spec_id;
        $product->price_id = $prodSpecPrice->prod_spec_price_id;
        $product->stock = $prodSpecPrice->prod_spec_price_stock;
        $product->maxLimit = $prodSpecPrice->prodSpec->product->prod_limit_num;

        return $product;
    }
}
