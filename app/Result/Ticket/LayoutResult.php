<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Config\BaseConfig;
use App\Result\BaseResult;
use App\Traits\ObjectHelper;
use App\Result\Ticket\ProductResult;
use App\Result\MagentoProductResult;

class LayoutResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取得資料
     * @param $data
     */
    public function home($data)
    {
        if (!$data) return null;

        $result['slide'] = $this->transformAds($data['slide']);
        $result['banner'] = $this->transformAds($data['banner']);
        $result['exploration'] = $this->transformExplorations($data['explorations']);
        $result['customize'] = $this->transformCustomizes($data['customizes'], 'layout_home');
        $result['alert'] = $this->getAlert();
        $result['hasActivity'] = env('HAS_ACTIVITY', true);
        $result['activity'] = $this->getActivity($data['activity']);

        return $result;
    }

    /**
     * 取得ads
     * @param $data
     */
    public function transformAds($ads)
    {
        $newAds = [];
        foreach ($ads as $as) {
            $newAds[] = $this->getAd($as);
        }

        return $newAds;
    }

    /**
     * 取得ad
     * @param $data
     */
    public function getAd($ad)
    {
        $ad = $ad->toArray();

        $result['adId'] = (string) $this->arrayDefault($ad, 'layout_ad_id');
        $result['adName'] = $this->arrayDefault($ad, 'layout_ad_name');
        $result['adLang'] = $this->arrayDefault($ad, 'layout_ad_lang');
        $result['adImg'] = $this->backendHost . $this->arrayDefault($ad, 'layout_ad_img');
        $result['adLinkWeb'] = $this->arrayDefault($ad, 'layout_ad_link_web');
        $result['adLinkAppType'] = (string) $this->arrayDefault($ad, 'layout_ad_link_app_type');
        $result['adLinkApp'] = $this->arrayDefault($ad, 'layout_ad_link_app');
        $result['adLinkAppTagId'] = (string) $this->arrayDefault($ad, 'layout_ad_link_app_tag_id');
        $result['adLinkAppProdId'] = $this->arrayDefault($ad, 'layout_ad_link_app_prod_id');
        $result['adStartTime'] = $this->arrayDefault($ad, 'layout_ad_starttime');
        $result['adEndTime'] = $this->arrayDefault($ad, 'layout_ad_endtime');

        return $result;
    }

    /**
     * 取得explorations
     * @param $data
     */
    public function transformExplorations($explorations)
    {
        $newExplorations = [];
        foreach ($explorations as $exploration) {
            $newExplorations[] = $this->getExploration($exploration);
        }

        return $newExplorations;
    }

    /**
     * 取得exploration
     * @param $data
     */
    public function getExploration($exploration)
    {
        $exploration = $exploration->toArray();

        $result['name'] = $this->arrayDefault($exploration, 'layout_exploration_name');
        $result['imgPath'] = $this->backendHost . $this->arrayDefault($exploration, 'layout_exploration_img');
        $result['tagId'] = (string) $this->arrayDefault($exploration, 'layout_exploration_tag_id');
        $tag = $this->arrayDefault($exploration, 'tag');
        $result['tagName'] = $this->arrayDefault($tag, 'tag_name');

        return $result;
    }

    /**
     * 取得customizes
     * @param $data
     */
    public function transformCustomizes($customizes, $dbPrefix = '')
    {
        $newCustomizes = [];
        foreach ($customizes as $customize) {
            $newCustomize = $this->getCustomize($customize, $dbPrefix);
            if ($newCustomize['items']) $newCustomizes[] = $newCustomize;
        }

        return $newCustomizes;
    }

    /**
     * 取得customize
     * @param $data
     */
    public function getCustomize($customize, $dbPrefix)
    {
        $customize = $customize->toArray();

        $result['id'] = (string) $this->arrayDefault($customize, $dbPrefix . '_id');
        $result['name'] = $this->arrayDefault($customize, $dbPrefix . '_name');
        $result['style'] = (string) $this->arrayDefault($customize, $dbPrefix . '_style');
        $items = $this->arrayDefault($customize, 'items');
        $result['items'] = $this->products($items);

        return $result;
    }

    /**
     * 取得products
     * @param $data
     */
    private function products($items)
    {
        $newItems = [];

        foreach ($items as $item) {
            if ($item->source === BaseConfig::SOURCE_TICKET) {
                $newItems[] = (new ProductResult)->get($item);
            }
            elseif ($item->source === BaseConfig::SOURCE_COMMODITY) {
                $newItems[] = (new MagentoProductResult)->get($item);
            }
        }

        return $newItems;
    }

    /**
     * 取得alert
     * @param $data
     */
    private function getAlert()
    {
        $alert = new \stdClass;
        $alert->status = env('LAYOUT_SHOW_ALERT', false);
        $alert->message = '限量優惠，凡購買高捷票券商品，可享Uber乘車折扣優惠';

        return $alert;
    }

    /**
     * 取得activity
     * @param $data
     */
    public function getActivity($activity)
    {
        if (!$activity) return null;

        $activity = $activity->toArray();

        $result = new \stdClass;
        $index_img = $this->arrayDefault($activity, 'index_img');
        $result->icon = ($index_img) ? $this->backendHost . $index_img : '';

        $auth_status = $this->arrayDefault($activity, 'auth_status', 0);
        $result->isNeedLogin = ($auth_status === 1);

        $result->link = $this->getLink($activity);

        return $result;
    }

    /**
     * 取得menu
     * @param $data
     */
    public function menu($menus, $isSub = false)
    {
        $newMenus = [];
        foreach ($menus as $menu) {
            $newMenus[] = $this->getMenu($menu, $isSub);
        }

        return $newMenus;
    }

    /**
     * 取得menu
     * @param $data
     */
    public function getMenu($menu, $isSub = false)
    {
        if (!$isSub) $menu = $menu->toArray();

        $result = new \stdClass;
        $result->id = (string) $this->arrayDefault($menu, 'tag_id');
        $result->name = $this->arrayDefault($menu, 'tag_name');
        if (!$isSub) $result->items = $this->menu($menu['sub_menus'], true);

        return $result;
    }

    /**
     * 取得menu
     * @param $data
     */
    public function oneMenu($menus)
    {
        $newMenus = [];
        foreach ($menus as $menu) {
            $newMenus[] = $menu;
        }

        return $newMenus;
    }

    /**
     * 取得category
     * @param $data
     */
    public function category($data)
    {
        $result = new \stdClass;
        $result->category = ($data['category']) ? $this->getCategory($data['category']) : null;
        $result->place = null;
        $result->keyword = null;
        $result->customizes = $this->transformCustomizes($data['customizes'], 'layout_category');

        return $result;
    }

    /**
     * 取得category
     * @param $data
     */
    public function getCategory($menu)
    {
        $menu = $menu->toArray();

        $result = new \stdClass;
        $result->id = (string) $this->arrayDefault($menu, 'tag_id');
        $result->name = $this->arrayDefault($menu, 'tag_name');

        $tag_img = $this->arrayDefault($menu, 'tag_img');
        $result->imageUrlWeb = ($tag_img) ? $this->backendHost . $tag_img : null;

        $tag_img_app = $this->arrayDefault($menu, 'tag_img_app');
        $result->imageUrlApp = ($tag_img_app) ? $this->backendHost . $tag_img_app : null;

        $result->items = $this->menu($menu['sub_menus'], true);

        return $result;
    }

    /**
     * 取得categoryProducts
     * @param $data
     * @param $page
     */
    public function categoryProducts($data, $page = 1)
    {
        $limit = 20;
        $result = new \stdClass;

        if ($data->count()) {
            $result->total = $data->count();
            $records = array_chunk($this->processCategoryProduct($data), $limit);
            $result->records = (isset($records[$page])) ? $records[$page] : [];
        }
        else {
            $result->total = 0;
            $result->records = [];
        }

        return $result;
    }

    /**
     * 處理CategoryProduct
     * @param $data
     */
    private function processCategoryProduct($products)
    {
        if (!$products) return [];

        $newProducts = [];

        foreach ($products as $product) {
            if ($product->source === BaseConfig::SOURCE_TICKET) {
                $newProducts[] = (new ProductResult)->getCategoryProduct($product);
            }
            elseif ($product->source === BaseConfig::SOURCE_COMMODITY) {
                $newProducts[] = (new MagentoProductResult)->getCategoryProduct($product);
            }
        }

        return $newProducts;
    }

    /**
     * 取得連結
     */
    private function getLink($app)
    {
        $result = new \stdClass;
        $result->type = $this->arrayDefault($app, 'link_type');

        if ($result->type === 0) {
            $result->url = (string) $this->arrayDefault($app, 'link_app');
        }
        elseif ($result->type === 1) {
            $result->url = (string) $this->arrayDefault($app, 'link_web');
        }
        else {
            $result->url = (string) $this->arrayDefault($app, 'link_app_scheme');
        }

        return $result;
    }
}
