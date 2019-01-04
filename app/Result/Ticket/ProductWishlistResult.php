<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Config\Ticket\ProcuctConfig;
use Carbon\Carbon;
use Crypt;

class ProductWishlistResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取得所有商品資料
     * @param $product
     * @param bool $isDetail
     */
    public function all($products)
    {
        if ($products->isEmpty()) return [];

        $newItems = [];

        foreach ($products as $product) {
            $newItems[] = $this->get($product);
        }

        return array_filter($newItems);
    }

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function get($wishItem)
    {
        if (!$wishItem || !$wishItem->product) return null;

        $result['source'] = ProcuctConfig::SOURCE_TICKET;
        $result['wishlistId'] = (string) $wishItem->member_id;
        $result['wishlistItemId'] = (string) $wishItem->prod_id;
        $result['category'] = $this->getCategories($wishItem->menuProds);
        $result['tags'] = [];

        $result['id'] = (string) $wishItem->product->prod_id;
        $result['name'] = (string) $wishItem->product->prod_name;

        $result['price'] = $wishItem->product->prod_price_sticker;
        $result['salePrice'] = $wishItem->product->prod_price_retail;
        $additionals = $this->getAdditional($wishItem->product->specs, $wishItem->product->prod_price_type);
        if ($additionals) {
            $lowestPrice = $this->getLowestPrice($additionals, $result['price'],$result['salePrice']);
            $result['price'] = $lowestPrice['price'];
            $result['salePrice'] = $lowestPrice['salePrice'];
        }

        $result['characteristic'] = $wishItem->product->prod_short;
        $result['storeName'] = $wishItem->product->prod_store;
        $result['place'] = $wishItem->product->prod_store;
        $result['imageUrl'] = $this->getImg($wishItem->product->img);
        $result['addAt'] = $wishItem->created_at->toDateTimeString();

        return $result;
    }

    /**
     * 取得分類
     * @param $imgs
     * @return string
     */
    private function getCategories($menuProds)
    {
        if ($menuProds->isEmpty()) return [];

        $menuProds = $menuProds->unique('tag_upper_id');

        $newCategories = [];
        foreach ($menuProds as $prod) {
            if ($prod->upperTag) {
                $tag = new \stdClass;
                $tag->id = $prod->upperTag->tag_id;
                $tag->name = $prod->upperTag->tag_name;
                $newCategories[] = $tag;
            }
        }

        return $newCategories;
    }

    /**
     * 取得圖片
     * @param $imgs
     * @return string
     */
    private function getImg($img)
    {
        return isset($img->img_thumbnail_path) ? $this->backendHost . $img->img_thumbnail_path : '';
    }

    /**
     * 取得規格
     * @param $specs
     * @param $prodPriceType
     * @return object | null
     */
    private function getAdditional($specs, $prodPriceType)
    {
        $additional = null;

        if ($specs) {
            $additional = new \stdClass;
            $additional->spec = [];

            foreach ($specs as $s) {
                $newSpec = new \stdClass;

                // 無票種
                if ($prodPriceType == 0) {
                    if (!$s->specPrices) continue;

                    foreach ($s->specPrices as $specPrice) {
                        $newSpec->sticker = $specPrice->prod_spec_price_list;
                        $newSpec->retail = $specPrice->prod_spec_price_value;

                        $saleStatus = $this->getSaleStatus($specPrice->prod_spec_price_onsale_time, $specPrice->prod_spec_price_offsale_time, $specPrice->prod_spec_price_stock);

                        if ($saleStatus['code'] == '11' || $saleStatus['code'] == '10') {
                            $additional->spec[] = $newSpec;
                        }
                    }

                }
                else {
                    if (!$s->specPrices) continue;

                    $newSpec->additionals = $this->getFare($s->specPrices);

                    // 有內容再加入
                    if ($newSpec->additionals->spec) $additional->spec[] = $newSpec;
                }
            }

            // 無內容，移除全部
            if (!$additional->spec) $additional = null;
        }

        return $additional;
    }

    /**
     * 取得票種
     * @param $fare
     * @return object | null
     */
    private function getFare($fare)
    {
        $additional = null;

        if ($fare) {
            $additional = new \stdClass;
            $additional->spec = [];

            foreach ($fare as $f) {
                $newFare = new \stdClass;
                $newFare->sticker = $f->prod_spec_price_list;
                $newFare->retail = $f->prod_spec_price_value;

                $saleStatus = $this->getSaleStatus($f->prod_spec_price_onsale_time, $f->prod_spec_price_offsale_time, $f->prod_spec_price_stock);

                // 不在販賣時間，移除
                if ($saleStatus['code'] == '11' || $saleStatus['code'] == '10') {
                    $additional->spec[] = $newFare;
                }
            }
        }

        return $additional;
    }

    /**
     * 取得最低規格價錢
     * @param $spec
     * @param $prodPriceType
     * @return object | null
     */
    private function getLowestPrice($additional, $price, $salePrice)
    {
        if (!$additional && !isset($additional->spec)) return ['price' => $price, 'salePrice' => $salePrice];

        $k = 0;
        foreach ($additional->spec as $item) {
            if (isset($item->additionals)) {
                foreach ($item->additionals->spec as $fare) {
                    $lowestPrice[$k]['price'] = $fare->sticker;
                    $lowestPrice[$k]['salePrice'] = $fare->retail;

                    $k++;
                }
            }
            else {
                $lowestPrice[$k]['price'] = $item->sticker;
                $lowestPrice[$k]['salePrice'] = $item->retail;
            }

            $k++;
        }

        $result = collect($lowestPrice)->sortBy(function ($item) {
            return $item['salePrice'];
        })->first();

        return $result;
    }

    /**
     * 取得銷售狀態
     * @param $onSaleTime
     * @param $offSaleTime
     * @param $stock
     * @return array
     */
    private function getSaleStatus($onSaleTime, $offSaleTime, $stock = 0)
    {
        if ($onSaleTime && $offSaleTime) {
            $onSaleTime = Carbon::parse($onSaleTime);
            $offSaleTime = Carbon::parse($offSaleTime);
            $now = Carbon::now();

            // 預設: 暫停銷售
            $saleStatus = ProcuctConfig::SALE_STATUS_STOP_SALE;

            if ($now->lte($onSaleTime)) {
                // 現在時間小於開賣時間: 尚未銷售
                $saleStatus = ProcuctConfig::SALE_STATUS_NOT_YET;
            }
            else if ($now->gte($onSaleTime) && $now->lte($offSaleTime)) {
                // 現在時間於銷售時間內: 熱賣中
                $saleStatus = ($stock > 0) ? ProcuctConfig::SALE_STATUS_ON_SALE : ProcuctConfig::SALE_STATUS_OFF_SALE;
            }
        }
        else {
            $saleStatus = ($stock > 0) ? ProcuctConfig::SALE_STATUS_ON_SALE : ProcuctConfig::SALE_STATUS_OFF_SALE;
        }

        return [
            'code' => ProcuctConfig::SALE_STATUS[$saleStatus],
            'status' => trans('ticket/product.sale_status.' . $saleStatus)
        ];
    }
}
