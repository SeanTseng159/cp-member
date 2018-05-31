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
        $this->id = $this->arrayDefault($product, 'prod_id');
        $this->name = $this->arrayDefault($product, 'prod_name');
        $this->price = $this->arrayDefault($product, 'prod_price_sticker');
        $this->salePrice = $this->arrayDefault($product, 'prod_price_retail');
        $this->discount = $this->arrayDefault($product, 'discount');
        $this->characteristic = $this->arrayDefault($product, 'prod_short');
        $this->category = null;
        $this->storeName = $this->arrayDefault($product, 'prod_store');
        $this->place = $this->arrayDefault($product, 'prod_store');
        $this->tags = $this->getTags($this->arrayDefault($product, 'tags'), []);
        $this->description = $this->arrayDefault($product, 'description');
        $this->imageUrl = $this->getImg($this->arrayDefault($product, 'imgs'));
        $this->createdAt = $this->arrayDefault($product, 'created_at');
        $this->isWishlist = $this->arrayDefault($product, 'isWishlist');
        ////$this->visibility = 4;

        if ($isDetail) {
            $this->storeTelephone = '';
            $this->storeAddress = $this->getAddress($product);
            $this->imageUrls = $this->getImgs($this->arrayDefault($product, 'imgs'));
            $this->additionals = $this->getAdditional($this->arrayDefault($product, 'spec')); // 規格
            // $this->quantity = $this->quantity;
            $this->maxQuantity = $this->arrayDefault($product, 'prod_limit_num', 0);
            $this->contents = $this->getContents($product);
            $this->combos = null; // 組合
            $this->purchase = null; // 加購
            $saleStatus = $this->getSaleStatus($this->arrayDefault($product, 'prod_onsale_time'), $this->arrayDefault($product, 'prod_offsale_time'), $this->quantity);
            $this->saleStatusCode = $saleStatus['code'];
            $this->saleStatus = $saleStatus['status'];
            $this->canUseCoupon = '1';
            $this->isBook = ($this->arrayDefault($product, 'prod_kind', 1) === 2) ? true : false;
        }

        return $this->apiFormat($isDetail);
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
        $content = new \stdClass;

        for ($i=1; $i <= 3; $i++) { 
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
        return isset($imgs[0]['img_thumbnail_path']) ? ProcuctConfig::BACKEND_HOST_TEST . $imgs[0]['img_thumbnail_path'] : '';
    }

    /**
     * 取得圖片
     * @param $imgs
     * @return string
     */
    private function getImgs($imgs)
    {
        $img = new \stdClass;

        foreach ($imgs as $row) { 
            $img->generalPath = ProcuctConfig::BACKEND_HOST_TEST . $this->arrayDefault($row, 'img_path', '');
            $img->thumbnailPath = ProcuctConfig::BACKEND_HOST_TEST . $this->arrayDefault($row, 'img_thumbnail_path', '');
            $imgsAry[] = $img;
        }

        return $imgsAry;
    }

    /**
     * 取得標籤
     * @param $tags
     * @return array | null
     */
    private function getTags($tags)
    {
        if ($tags) {
            foreach ($tags as $t) {
                $tag = new \stdClass;
                $tag->id = $t->tag->tag_id;
                $tag->name = $t->tag->tag_name;
                $tagsAry[] = $tag;
            }

            return $tagsAry;
        }

        return null;
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

        return [
            'code' => ProcuctConfig::SALE_STATUS[$saleStatus],
            'status' => trans('ticket/product.sale_status.' . $saleStatus)
        ];
    }

    /**
     * 取得規格
     * @param $spec
     * @return array | null
     */
    private function getAdditional($spec)
    {
        $additional = new \stdClass;
        $additional->label = trans('ticket/product.spec');
        $additional->code = 'spec';

        foreach ($spec as $s) {
            $newSpec = new \stdClass;
            $newSpec->value = $s->prod_spec_name;
            $newSpec->value_index = $s->prod_spec_id;
            $newSpec->additionals = $this->getFare($s->specPrices);

            $additional->spec[] = $newSpec;
        }

        return $additional;
    }

    /**
     * 取得票種
     * @param $fare
     * @return array | null
     */
    private function getFare($fare)
    {
        $additional = new \stdClass;
        $additional->label = trans('ticket/product.fare');
        $additional->code = 'ticket';

        foreach ($fare as $f) {
            $newFare = new \stdClass;
            $newFare->value = $f->prod_spec_price_name;
            $newFare->value_index = $f->prod_spec_price_id;
            $newFare->id = $f->prod_spec_price_id;
            $newFare->sticker = $f->prod_spec_price_list;
            $newFare->retail = $f->prod_spec_price_value;
            $newFare->stock = $f->prod_spec_price_stock;

            $saleStatus = $this->getSaleStatus($f->prod_spec_price_onsale_time, $f->prod_spec_price_offsale_time, $f->prod_spec_price_stock);
            $newFare->saleStatus = $saleStatus['code'];
            $newFare->saleStatusCode = $saleStatus['status'];

            $additional->spec[] = $newFare;

            // 加總數量
            $this->quantity += $f->prod_spec_price_stock;
        }

        return $additional;
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
            'storeTelephone', 'storeAddress', 'place', 'tags', 'imageUrl', 'isWishlist','updatedAt','status'
        ];
        if ($isDetail) {
            $detailColumns = [
                'saleStatus', 'saleStatusCode', 'canUseCoupon', 'quantity', 'maxQuantity', 'contents', 'combos', 'additionals', 'purchase','imageUrls', 'isBook'
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
}
