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
        $this->storeName = $product->storeName;
        $this->place = $product->place;
        $this->imageUrl = $product->imageUrl;
        $this->isWishlist = $product->isWishlist;

        if ($isDetail) {
            $this->categories = $this->getMenuCategories($product->categories);
            $this->keywords = $this->getKeywords($product->keywords);
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
        $this->id = $product->data->id;
        $this->name = $product->data->name;
        $this->price = (string) $product->data->price;
        $this->salePrice = ($product->data->salePrice) ? (string) $product->data->salePrice : $this->price;
        $this->discount = $product->data->discount;
        $this->characteristic = $product->data->characteristic;
        $this->category = [];
        $this->storeName = $product->data->storeName;
        $this->place = $product->data->place;
        $this->tags = [];
        $this->imageUrl = $product->data->imageUrl;
        $this->isWishlist = $product->data->isWishlist;

        // $this->category = $this->getCategories($product->categories, true);
        // $this->tags = $this->getTags($product->tags, true);

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
     * api response 資料格式化
     * @param bool $isDetail
     * @return \stdClass
     */
    private function apiFormat($isDetail = true)
    {
        $data = new \stdClass();
        $columns = [
            'source', 'id', 'name',  'price', 'salePrice', 'discount', 'characteristic', 'storeName', 'storeAddress', 'place', 'imageUrl', 'isWishlist'
        ];
        if ($isDetail) {
            $detailColumns = [
                'categories', 'keywords', 'storeTelephone', 'saleStatus', 'saleStatusCode', 'quantity', 'maxQuantity', 'additionals', 'contents', 'combos', 'purchase', 'maxPurchase', 'imageUrls', 'canUseCoupon', 'isBook'
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
