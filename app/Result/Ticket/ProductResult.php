<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Config\Ticket\ProcuctConfig;
use App\Traits\ObjectHelper;
use Carbon\Carbon;

class ProductResult extends BaseResult
{
    use ObjectHelper;

    private $quantity = 0;
    private $backendHost;

    public function __construct()
    {
        $this->setBackendHost();
    }

    /**
     * 設定後端網址
     */
    private function setBackendHost()
    {
        if (env('APP_ENV') === 'production') {
            $this->backendHost = ProcuctConfig::BACKEND_HOST;
        }
        elseif (env('APP_ENV') === 'beta') {
            $this->backendHost = ProcuctConfig::BACKEND_HOST_BETA;
        }
        else {
            $this->backendHost = ProcuctConfig::BACKEND_HOST_TEST;
        }
    }

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function get($product, $isDetail = false)
    {
        if (!$product) return null;

        $product = $product->toArray();

        $this->source = ProcuctConfig::SOURCE_TICKET;
        $this->id = (string) $this->arrayDefault($product, 'prod_id');
        $this->name = $this->arrayDefault($product, 'prod_name');
        $this->price = (string) $this->arrayDefault($product, 'prod_price_sticker');
        $this->salePrice = (string) $this->arrayDefault($product, 'prod_price_retail');
        $this->discount = $this->arrayDefault($product, 'discount');
        $this->characteristic = $this->arrayDefault($product, 'prod_short');
        $this->storeName = $this->arrayDefault($product, 'prod_store');
        $this->place = $this->arrayDefault($product, 'prod_store');
        $this->imageUrl = $this->getImg($this->arrayDefault($product, 'imgs'));
        $this->isWishlist = $this->arrayDefault($product, 'isWishlist', false);

        // 規格
        $this->additionals = $this->getAdditional($this->arrayDefault($product, 'specs'), $product['prod_price_type']);
        if ($this->additionals) {
            $lowestPrice = $this->getLowestPrice($this->additionals, $this->price, $this->salePrice);
            $this->price = $lowestPrice['price'];
            $this->salePrice = $lowestPrice['salePrice'];
        }

        if ($isDetail) {
            // 舊欄位先保留
            $this->category = [];
            $this->tags = [];

            $this->categories = $this->getMenuCategories($this->arrayDefault($product, 'categories', []));
            $this->keywords = $this->getKeywords($this->arrayDefault($product, 'keywords', []));
            $this->storeTelephone = '';
            $this->storeAddress = $this->getAddress($product);
            $this->imageUrls = $this->getImgs($this->arrayDefault($product, 'imgs'));
            //$this->additionals = $this->getAdditional($this->arrayDefault($product, 'spec'), $product['prod_price_type']); // 規格
            if ($this->additionals) {
                $this->additionals->expire = $this->getExpire($product);
                $this->additionals->bookable = $this->getBookable($product);
            }
            // $this->quantity = $this->quantity;
            $this->maxQuantity = $this->arrayDefault($product, 'prod_limit_num', 0);
            $this->contents = $this->getContents($product);
            $this->combos = $this->getCombo($this->arrayDefault($product, 'combos')); // 組合
            $this->purchase = $this->getPurchase($this->arrayDefault($product, 'purchase')); // 加購
            $this->maxPurchase = $this->arrayDefault($product, 'prod_plus_limit', null);
            $saleStatus = $this->getSaleStatus($this->arrayDefault($product, 'prod_onsale_time'), $this->arrayDefault($product, 'prod_offsale_time'), $this->quantity);
            $this->saleStatusCode = $saleStatus['code'];
            $this->saleStatus = $saleStatus['status'];
            $this->canUseCoupon = '1';
            $this->isBook = ($this->arrayDefault($product, 'prod_bookable', 0) === 1) ? true : false;
        }

        return $this->apiFormat($isDetail);
    }

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function getOnlyPurchase($product)
    {
        if (!$product) return null;

        $product = $product->toArray();

        $this->purchase = $this->getPurchase($this->arrayDefault($product, 'purchase')); // 加購
        $this->maxPurchase = $this->arrayDefault($product, 'prod_plus_limit', null);

        return $this->apiFormatForOnlyPurchase();
    }

    /**
     * 取得加購商品資料
     * @param $product
     * @param bool $isDetail
     */
    public function getPurchaseProduct($product)
    {
        if (!$product) return null;

        $product = $product->toArray();

        $this->id = (string) $this->arrayDefault($product, 'prod_id');
        $this->name = $this->arrayDefault($product, 'prod_name');
        $this->price = (string) $this->arrayDefault($product, 'prod_price_sticker');
        $this->salePrice = (string) $this->arrayDefault($product, 'prod_price_retail');
        $this->imageUrl = $this->getImg($this->arrayDefault($product, 'imgs'));
        // 規格
        $this->additionals = $this->getAdditional($this->arrayDefault($product, 'specs'), $product['prod_price_type']);
        if ($this->additionals) {
            $lowestPrice = $this->getLowestPrice($this->additionals, $this->price, $this->salePrice);
            $this->price = $lowestPrice['price'];
            $this->salePrice = $lowestPrice['salePrice'];
        }

        $saleStatus = $this->getSaleStatus($this->arrayDefault($product, 'prod_onsale_time'), $this->arrayDefault($product, 'prod_offsale_time'), $this->quantity);
        $this->saleStatusCode = $saleStatus['status'];
        $this->saleStatus = $saleStatus['code'];

        return $this->apiFormatForPurchase();
    }

    /**
     * 取得組合商品(內容物) 資料
     * @param $product
     * @param bool $isDetail
     */
    public function getComboItem($product)
    {
        if (!$product) return null;

        $product = $product->toArray();

        $this->source = ProcuctConfig::SOURCE_TICKET;
        $this->id = (string) $this->arrayDefault($product, 'prod_id');
        $this->name = $this->arrayDefault($product, 'prod_name');
        $this->characteristic = $this->arrayDefault($product, 'prod_short');
        $this->storeName = $this->arrayDefault($product, 'prod_store');
        $this->place = $this->arrayDefault($product, 'prod_store');
        $this->imageUrl = $this->getImg($this->arrayDefault($product, 'imgs'));
        $this->imageUrls = $this->getImgs($this->arrayDefault($product, 'imgs'));
        $this->contents = $this->getContents($product);

        return $this->apiFormatForComboItem();
    }

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function getCategoryProduct($product)
    {
        if (!$product) return null;

        $product = $product->toArray();

        $this->source = ProcuctConfig::SOURCE_TICKET;
        $this->id = (string) $this->arrayDefault($product, 'prod_id');
        $this->name = $this->arrayDefault($product, 'prod_name');
        $this->price = (string) $this->arrayDefault($product, 'prod_price_sticker');
        $this->salePrice = (string) $this->arrayDefault($product, 'prod_price_retail');
        $this->discount = $this->arrayDefault($product, 'discount');
        $this->characteristic = $this->arrayDefault($product, 'prod_short');
        $this->storeName = $this->arrayDefault($product, 'prod_store');
        $this->place = $this->arrayDefault($product, 'prod_store');
        $this->imageUrl = $this->getMainImg($this->arrayDefault($product, 'img'));
        $this->isWishlist = $this->arrayDefault($product, 'isWishlist', false);
        $this->category = [];
        $this->tags = [];

        // 規格
        $this->additionals = $this->getAdditional($this->arrayDefault($product, 'specs'), $product['prod_price_type']);
        if ($this->additionals) {
            $lowestPrice = $this->getLowestPrice($this->additionals, $this->price, $this->salePrice);
            $this->price = $lowestPrice['price'];
            $this->salePrice = $lowestPrice['salePrice'];
        }

        //$this->category = $this->getCategories($this->arrayDefault($product, 'categories', []), true);
        //$this->tags = $this->getTags($this->arrayDefault($product, 'tags', []), true);

        return $this->apiFormatForCategory();
    }

    /**
     * 取得地址
     * @param $product
     * @return string
     */
    private function getAddress($product)
    {
        return  $this->arrayDefault($product, 'prod_zipcode', '') .
                $this->arrayDefault($product, 'prod_county', '') .
                $this->arrayDefault($product, 'prod_district', '') .
                $this->arrayDefault($product, 'prod_address', '');
    }

    /**
     * 取得描述
     * @param $product
     * @return string
     */
    private function getContents($product)
    {
        for ($i=1; $i <= 3; $i++) {
            $content = new \stdClass;
            $content->title = $this->arrayDefault($product, 'prod_tabs' . $i, '');
            $content->description = $this->arrayDefault($product, 'prod_desc' . $i, '');
            $contents[] = $content;
        }

        return $contents;
    }

    /**
     * 取得圖片
     * @param $imgs
     * @return string
     */
    private function getImg($imgs)
    {
        return isset($imgs[0]['img_thumbnail_path']) ? $this->backendHost . $imgs[0]['img_thumbnail_path'] : '';
    }

    /**
     * 取得圖片
     * @param $imgs
     * @return string
     */
    private function getMainImg($img)
    {
        return ($img) ? $this->backendHost . $img['img_thumbnail_path'] : '';
    }

    /**
     * 取得圖片
     * @param $imgs
     * @return array | null
     */
    private function getImgs($imgs)
    {
        $imgsAry = [];

        if ($imgs) {
            foreach ($imgs as $row) {
                $img = new \stdClass;
                $img->generalPath = $this->backendHost . $this->arrayDefault($row, 'img_path', '');
                $img->thumbnailPath = $this->backendHost . $this->arrayDefault($row, 'img_thumbnail_path', '');
                $imgsAry[] = $img;
            }
        }

        return $imgsAry;
    }

    /**
     * 取得產品所有分類
     * @param $tags
     * @return array
     */
    private function getMenuCategories($categories)
    {
        $categoriesAry = [];

        if ($categories) {
            foreach ($categories as $c) {
                $category = new \stdClass;
                $category->id = $c->tag_id;
                $category->name = $c->tag_name;
                $categoriesAry[] = $category;
            }
        }

        return $categoriesAry;
    }

    /**
     * 取得父標籤
     * @param $tags
     * @return array
     */
    private function getCategories($categories, $isAry = false)
    {
        $categoriesAry = [];

        if ($categories) {
            foreach ($categories as $c) {
                if (!$c->upperTag) continue;

                $category = new \stdClass;
                if ($isAry) {
                    $category->id = $c->upperTag['tag_id'];
                    $category->name = $c->upperTag['tag_name'];
                }
                else {
                    $tag->id = $c->upperTag->tag_id;
                    $tag->name = $c->upperTag->tag_name;
                }
                $categoriesAry[] = $category;
            }
        }

        return collect($categoriesAry)->unique('id');
    }

    /**
     * 取得標籤
     * @param $tags
     * @return array
     */
    private function getTags($tags, $isAry = false)
    {
        $tagsAry = [];

        if ($tags) {
            foreach ($tags as $t) {
                if (!$t->tag) continue;

                $tag = new \stdClass;
                if ($isAry) {
                    $tag->id = $t->tag['tag_id'];
                    $tag->name = $t->tag['tag_name'];
                }
                else {
                    $tag->id = $t->tag->tag_id;
                    $tag->name = $t->tag->tag_name;
                }
                $tagsAry[] = $tag;
            }
        }

        return collect($tagsAry)->unique('id');
    }

    /**
     * 取得關鍵字
     * @param $tags
     * @return array
     */
    private function getKeywords($keywords, $isAry = false)
    {
        $keywordsAry = [];

        if ($keywords) {
            foreach ($keywords as $k) {
                if (!$k->keyword) continue;

                $keyword = new \stdClass;
                if ($isAry) {
                    $keyword->id = $k->keyword['keyword_id'];
                    $keyword->name = $k->keyword['keyword_text'];
                }
                else {
                    $keyword->id = $k->keyword->keyword_id;
                    $keyword->name = $k->keyword->keyword_text;
                }
                $keywordsAry[] = $keyword;
            }
        }

        return $keywordsAry;
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
            $additional->label = trans('ticket/product.spec');
            $additional->code = 'spec';
            $additional->spec = [];

            foreach ($specs as $s) {
                $newSpec = new \stdClass;
                $newSpec->value = $s['prod_spec_name'];
                $newSpec->value_index = $s['prod_spec_id'];

                // 無票種
                if ($prodPriceType == 0) {
                    if (!$s['spec_prices']) continue;

                    foreach ($s['spec_prices'] as $specPrices) {
                        $newSpec->value_index = (string) $specPrices['prod_spec_price_id'];
                        $newSpec->id = (string) $specPrices['prod_spec_price_id'];
                        $newSpec->sticker = (string) $specPrices['prod_spec_price_list'];
                        $newSpec->retail = (string) $specPrices['prod_spec_price_value'];
                        $newSpec->stock = $specPrices['prod_spec_price_stock'];
                        $saleStatus = $this->getSaleStatus($specPrices['prod_spec_price_onsale_time'], $specPrices['prod_spec_price_offsale_time'], $specPrices['prod_spec_price_stock']);
                        $newSpec->saleStatus = $saleStatus['code'];
                        $newSpec->saleStatusCode = $saleStatus['status'];

                        if ($newSpec->saleStatus == '11' || $newSpec->saleStatus == '10') {
                            // 加總數量
                            $this->quantity += $specPrices['prod_spec_price_stock'];

                            $additional->spec[] = $newSpec;
                        }
                    }

                }
                else {
                    if (!$s['spec_prices']) continue;

                    $newSpec->additionals = $this->getFare($s['spec_prices']);

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
            $additional->label = trans('ticket/product.fare');
            $additional->code = 'ticket';
            $additional->spec = [];

            foreach ($fare as $f) {
                $newFare = new \stdClass;
                $newFare->value = $f['prod_spec_price_name'];
                $newFare->value_index = (string) $f['prod_spec_price_id'];
                $newFare->id = (string) $f['prod_spec_price_id'];
                $newFare->sticker = (string) $f['prod_spec_price_list'];
                $newFare->retail = (string) $f['prod_spec_price_value'];
                $newFare->stock = $f['prod_spec_price_stock'];

                $saleStatus = $this->getSaleStatus($f['prod_spec_price_onsale_time'], $f['prod_spec_price_offsale_time'], $f['prod_spec_price_stock']);
                $newFare->saleStatus = $saleStatus['code'];
                $newFare->saleStatusCode = $saleStatus['status'];

                // 不在販賣時間，移除
                if ($newFare->saleStatus == '11' || $newFare->saleStatus == '10') {
                    // 加總數量
                    $this->quantity += $f['prod_spec_price_stock'];

                    $additional->spec[] = $newFare;
                }
            }
        }

        return $additional;
    }

    /**
     * 取得使用時間
     * @param $product
     * @return array
     */
    private function getExpire($product)
    {

        $expire = new \stdClass;
        $expire->type = $product['prod_expire_type'];
        $expire->value = '';

        if ($expire->type == 1) {
            $expire->value = (string) $product['prod_expire_daycount'];
        }
        else if ($expire->type == 2) {
            $expire->value = $product['prod_expire_due'];
        }
        else if ($expire->type == 3) {
            $expire->value = $product['prod_expire_start'] . ',' . $product['prod_expire_due'];
        }
        else {
            $expire->value = '';
        }

        return $expire;
    }

    /**
     * 取得使用時間
     * @param $product
     * @return array
     */
    private function getBookable($product)
    {
        $bookable = null;

        if ($product['prod_bookable'] == 1) {
            $bookable = new \stdClass;
            $bookable->dateStart = Carbon::today()->format('Y-m-d');
            $bookable->dateEnd = Carbon::parse($product['prod_expire_due'])->format('Y-m-d');
        }

        return $bookable;
    }

    /**
     * 取得加購
     * @param $additionals
     * @return array
     */
    private function getPurchase($additionals)
    {
        $purchaseAry = [];

        if ($additionals) {
            foreach ($additionals as $additional) {
                $purchase = (new ProductResult)->getPurchaseProduct($additional->product, true);

                if ($purchase->saleStatus === ProcuctConfig::SALE_STATUS[ProcuctConfig::SALE_STATUS_ON_SALE]) $purchaseAry[] = $purchase;
            }
        }

        return $purchaseAry;
    }

    /**
     * 取得組合
     * @param $combos
     * @return array
     */
    private function getCombo($combos)
    {
        $combosAry = null;

        if ($combos) {
            foreach ($combos as $combo) {
                $newCombo = new \stdClass();
                $newCombo->source = ProcuctConfig::SOURCE_TICKET;
                $newCombo->id = (string) $combo->prod_group_prod_id;
                $newCombo->name = $combo->product->prod_name;
                $combosAry[] = $newCombo;
            }
        }

        return $combosAry;
    }

    /**
     * api response 資料格式化
     * @param bool $isDetail
     * @return \stdClass
     */
    private function apiFormat($isDetail = true)
    {
        $data = new \stdClass();
        $columns = [
            'source', 'id', 'name',  'price', 'salePrice', 'characteristic', 'storeName',
             'storeAddress', 'place', 'imageUrl', 'isWishlist', 'discount'
        ];
        if ($isDetail) {
            $detailColumns = [
                'category', 'tags', 'categories', 'keywords', 'storeTelephone', 'saleStatus', 'saleStatusCode', 'quantity', 'maxQuantity', 'additionals', 'contents', 'combos', 'purchase', 'maxPurchase', 'imageUrls', 'canUseCoupon', 'isBook'
            ];
            $columns = array_merge($columns, $detailColumns);
        }

        foreach ($columns as $column) {
            if (property_exists($this, $column)) {
                $data->$column = $this->$column;
            }
        }
        return $data;
    }

    /**
     * api response 資料格式化
     * @return \stdClass
     */
    private function apiFormatForOnlyPurchase()
    {
        $data = new \stdClass();
        $columns = ['purchase', 'maxPurchase'];

        foreach ($columns as $column) {
            if (property_exists($this, $column)) {
                $data->$column = $this->$column;
            }
        }
        return $data;
    }

    /**
     * api response 資料格式化
     * @param bool $isDetail
     * @return \stdClass
     */
    private function apiFormatForCategory()
    {
        $data = new \stdClass();
        $columns = [
            'source', 'id', 'name',  'price', 'salePrice', 'characteristic', 'category', 'tags', 'storeName',
             'storeAddress', 'place', 'imageUrl', 'isWishlist', 'discount'
        ];

        foreach ($columns as $column) {
            if (property_exists($this, $column)) {
                $data->$column = $this->$column;
            }
        }
        return $data;
    }

    /**
     * api response 資料格式化
     * @param bool $isDetail
     * @return \stdClass
     */
    private function apiFormatForPurchase()
    {
        $data = new \stdClass();
        $columns = [
            'id', 'name', 'saleStatus', 'saleStatusCode', 'price', 'salePrice', 'imageUrl', 'quantity', 'additionals'
        ];

        foreach ($columns as $column) {
            if (property_exists($this, $column)) {
                $data->$column = $this->$column;
            }
        }

        return $data;
    }

    /**
     * api response 資料格式化
     * @param bool $isDetail
     * @return \stdClass
     */
    private function apiFormatForComboItem()
    {
        $data = new \stdClass();
        $columns = [
            'source', 'id', 'name', 'characteristic', 'storeName', 'place', 'imageUrl', 'imageUrls', 'contents'
        ];

        foreach ($columns as $column) {
            if (property_exists($this, $column)) {
                $data->$column = $this->$column;
            }
        }

        return $data;
    }
}