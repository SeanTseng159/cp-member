<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result;

use App\Result\BaseResult;
use App\Config\Ticket\ProcuctConfig;
use App\Traits\ObjectHelper;

class MagentoProductResult extends BaseResult
{
    use ObjectHelper;

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function get($product, $isDetail = false)
    {
        if (!$product) return null;

        $this->source = ProcuctConfig::SOURCE_COMMODITY;
        $this->id = $product->id;
        $this->name = $product->name;
        $this->price = (string) $product->price;
        $this->salePrice = ($product->salePrice) ? (string) $product->salePrice : $this->price;
        $this->discount = $product->discount;
        $this->characteristic = $product->characteristic;
        $this->category = null;
        $this->storeName = $product->storeName;
        $this->place = $product->place;
        $this->tags = $product->tags;
        $this->imageUrl = $product->imageUrl;
        $this->isWishlist = $product->isWishlist;

        if ($isDetail) {
            $this->storeTelephone = $product->storeTelephone;
            $this->storeAddress = $product->storeAddress;
            $this->imageUrls = $product->imageUrls;
            $this->additionals = $this->getAdditional($product->additionals); // 規格
            $this->quantity = $product->quantity;
            $this->maxQuantity = $product->maxQuantity;
            $this->contents = $product->contents;
            $this->combos = $product->combos; // 組合
            $this->purchase = $product->purchase; // 加購
            $this->maxPurchase = null;
            $this->saleStatusCode = $product->saleStatusCode;
            $this->saleStatus = $product->saleStatus;
            $this->canUseCoupon = $product->canUseCoupon;
            $this->isBook = $product->isBook;
        }

        return $this->apiFormat($isDetail);
    }

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function getCategoryProduct($product)
    {
        if (!$product) return null;

        $this->source = ProcuctConfig::SOURCE_COMMODITY;
        $this->id = $product->id;
        $this->name = $product->name;
        $this->price = (string) $product->price;
        $this->salePrice = ($product->salePrice) ? (string) $product->salePrice : $this->price;
        $this->discount = $product->discount;
        $this->characteristic = $product->characteristic;
        $this->category = null;
        $this->storeName = $product->storeName;
        $this->place = $product->place;
        $this->tags = $product->tags;
        $this->imageUrl = $product->imageUrl;
        $this->isWishlist = $product->isWishlist;

        $this->category = $this->getCategories($product->categories, true);
        $this->tags = $this->getTags($product->tags, true);

        return $this->apiCategoryFormat();
    }

    /**
     * 取得規格
     * @param $spec
     * @param $additionals
     * @return object | null
     */
    private function getAdditional($additionals)
    {
        if (isset($additionals->spec)) {
            foreach ($additionals->spec as $key => $spec) {
                if (isset($spec->additionals)) {
                    foreach ($spec->additionals->spec as $k => $s) {
                        $spec->additionals->spec[$k]->salePrice = $s->salePrice ?: $s->price;
                    }
                }
                else {
                    $additionals->spec[$key]->salePrice = $spec->salePrice ?: $spec->price;
                }
            }
        }
        else {
            $additionals = null;
        }

        return $additionals;
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

        return $categoriesAry;
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

        return $tagsAry;
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
            'source', 'id', 'name',  'price', 'salePrice', 'discount', 'characteristic', 'category', 'storeName',
            'storeTelephone', 'storeAddress', 'place', 'tags', 'imageUrl', 'isWishlist','status'
        ];
        if ($isDetail) {
            $detailColumns = [
                'saleStatus', 'saleStatusCode', 'quantity', 'maxQuantity', 'additionals', 'contents', 'combos', 'purchase', 'maxPurchase', 'imageUrls', 'canUseCoupon', 'isBook'
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
     * @param bool $isDetail
     * @return \stdClass
     */
    private function apiCategoryFormat()
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
}
