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
    use ObjectHelper;

    private $backendHost;

    public function __construct()
    {
        $this->backendHost = (env('APP_ENV') === 'production') ? BaseConfig::BACKEND_HOST : BaseConfig::BACKEND_HOST_TEST;
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
        $result['customize'] = $this->transformCustomizes($data['customizes']);
        $result['alert'] = $this->getAlert();
        $result['hasActivity'] = env('HAS_ACTIVITY', true);

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
    public function transformCustomizes($customizes)
    {
        $newCustomizes = [];
        foreach ($customizes as $customize) {
            $newCustomizes[] = $this->getCustomize($customize);
        }

        return $newCustomizes;
    }

    /**
     * 取得customize
     * @param $data
     */
    public function getCustomize($customize)
    {
        $customize = $customize->toArray();

        $result['id'] = (string) $this->arrayDefault($customize, 'layout_home_id');
        $result['name'] = $this->arrayDefault($customize, 'layout_home_name');
        $result['style'] = (string) $this->arrayDefault($customize, 'layout_home_style');
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

        $productResult = app()->build(ProductResult::class);
        $magentoProductResult = app()->build(MagentoProductResult::class);

        foreach ($items as $item) {
            if ($item->source === BaseConfig::SOURCE_TICKET) {
                $newItems[] = $productResult->get($item);
            }
            elseif ($item->source === BaseConfig::SOURCE_COMMODITY) {
                $newItems[] = $magentoProductResult->get($item);
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
}
