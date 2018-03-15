<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 下午 1:53
 */

namespace Ksd\Mediation\Result;


use Illuminate\Support\Facades\Log;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Helper\ObjectHelper;

class ProductResult
{
    use EnvHelper;
    use ObjectHelper;

    /**
     * 處理 magento 資料建置
     * @param $result
     * @param bool $isDetail
     */
    public function magento($result, $isDetail = false)
    {
        $this->source = ProjectConfig::MAGENTO;
        $this->id = $this->arrayDefault($result, 'sku');
        $this->name = $this->arrayDefault($result, 'name');
        $this->price = $this->arrayDefault($result, 'price');

        $this->salePrice = $this->customAttributes($result['custom_attributes'], 'special_price', null);
        $this->salePrice = empty($this->salePrice) ? null : intval($this->salePrice);

        $this->discount = $this->countDiscount($this->salePrice, $this->price);
        $this->characteristic = html_entity_decode(trim(strip_tags($this->customAttributes($result['custom_attributes'], 'short_description'))),ENT_QUOTES, "UTF-8");
        $this->category['id'] = $this->arrayDefault($result, 'type_id');
        $this->category['name'] = $this->getItemType($this->arrayDefault($result, 'type_id'));
        $this->storeName = null;
        $this->place = null;
        $this->tags = null;
        $this->imageUrl = $this->magentoImageUrl($this->customAttributes($result['custom_attributes'], 'image'));
        $this->createdAt = $this->arrayDefault($result, 'created_at');
        $this->productId = $this->arrayDefault($result, 'id');
        $this->customAttributes = $this->arrayDefault($result, 'custom_attributes');
        $this->isWishlist = false;
        $this->updatedAt = $this->arrayDefault($result, 'updated_at');
        $this->status = $this->arrayDefault($result, 'status');
        $this->visibility = $this->arrayDefault($result, 'visibility');

        if ($isDetail) {
            $this->contents = [
                [
                    'title' => __('product.content.title_label'),
                    'description' => $this->customAttributes($result['custom_attributes'], 'description')
                ]
            ];
            $this->saleStatusCode = $result['extension_attributes']['stock_item']['is_in_stock'] ? '11' : '10';
            $this->saleStatus = $this->getSaleStatus($this->saleStatusCode);
            $this->canUseCoupon = '1';
            $this->storeTelephone = null;
            $this->storeAddress = null;
            $this->quantity = $result['extension_attributes']['stock_item']['qty'];
            $this->maxQuantity = $result['extension_attributes']['stock_item']['max_sale_qty'];
            $this->additionals = null;
            $this->purchase = null;
            if (array_key_exists('media_gallery_entries', $result)) {
                $this->imageUrls = [];
                foreach ($result['media_gallery_entries'] as $mediaEntry) {
                    $this->imageUrls[] = [
                        'generalPath' => $this->magentoImageUrl($mediaEntry['file']),
                        'thumbnailPath' => in_array('thumbnail', $mediaEntry['types']) ? $this->magentoImageUrl($mediaEntry['file'] ): ''
                    ];
                }
            }
            $this->isBook = false;
        }
    }

    /**
     * 處理 city pass 資料建置
     * @param $result
     * @param bool $isDetail
     */
    public function cityPass($result, $isDetail = false)
    {
        $this->source = ProjectConfig::CITY_PASS;
        $this->id = $this->arrayDefault($result, 'id');
        $this->name = $this->arrayDefault($result, 'name');
        $this->price = $this->arrayDefault($result, 'price');
        $this->salePrice = $this->arrayDefault($result, 'salePrice');
        $this->discount = $this->arrayDefault($result, 'discount');
        $this->characteristic = $this->arrayDefault($result, 'characteristic');
        $this->category = null;
        $this->storeName = $this->arrayDefault($result, 'storeName');
        $this->place = $this->arrayDefault($result, 'place');
        $this->tags = $this->arrayDefault($result, 'tags');
        $this->description = $this->arrayDefault($result, 'description');
        $this->imageUrl = $this->arrayDefault($result, 'imageUrl');
        $this->createdAt = $this->arrayDefault($result, 'createdAt');
        $this->isWishlist = $this->arrayDefault($result, 'isWishlist');
        $this->visibility = 4;

        if ($isDetail) {
            $this->saleStatusCode = $this->arrayDefault($result, 'saleStatus');
            $this->saleStatus = $this->getSaleStatus($this->arrayDefault($result, 'saleStatus'));
            $this->canUseCoupon = $this->arrayDefault($result, 'canUseCoupon');
            $this->storeTelephone = $this->arrayDefault($result, 'storeTelephone');
            $this->storeAddress = $this->arrayDefault($result, 'storeAddress');
            $this->quantity = $this->arrayDefault($result, 'quantity');
            $this->maxQuantity = $this->arrayDefault($result, 'quantity');
            $this->contents = $this->arrayDefault($result, 'contents');
            $this->additionals = $this->arrayDefault($result, 'additionals');
            $this->purchase = $this->arrayDefault($result, 'purchase');
            $this->imageUrls = $this->arrayDefault($result, 'imageUrls');
            $this->isBook = $this->arrayDefault($result, 'isBook');
        }
    }

    /**
     * 取得 magento 圖片對應路徑
     * @param $path
     * @return string
     */
    private function magentoImageUrl($path)
    {
        $basePath = $this->env('MAGENTO_PRODUCT_PATH');
        return $basePath . $path;
    }

    /**
     * 處理折扣
     * @param $salePrice
     * @param $price
     * @return string
     */
    public function countDiscount($salePrice, $price)
    {
        if (empty($salePrice) || empty($price)) {
            return '';
        }
        if($price!==0){
            $discount = (int) (($salePrice / $price) * 100);
            if($discount===100){
                return null;
            }else{
                $discount = strval($discount);
                $discountText = (substr($discount,1,1)) !== '0' ? $discount : substr($discount,0,1);
                return __('product.discount', ['discount' => $discountText]);
            }
        }
        return '';
    }

    /**
     * magento商品類型轉換
     * @return string
     */
    public function getItemType($key)
    {
        switch ($key) {
            case 'simple':
                return __('product.item_type.simple');
            case 'virtual':
                return __('product.item_type.virtual');
            case 'downloadable':
                return __('product.item_type.downloadable');
            case 'configurable':
                return __('product.item_type.configurable');
            case 'grouped':
                return __('product.item_type.grouped');
            case 'bundle':
                return __('product.item_type.bundle');
        }

    }

    /**
     * api response 資料格式化
     * @param bool $isDetail
     * @return \stdClass
     */
    public function apiFormat($isDetail = true)
    {
        $data = new \stdClass();
        $columns = [
            'source', 'id', 'name',  'price', 'salePrice', 'discount', 'characteristic', 'category', 'storeName',
            'storeTelephone', 'storeAddress', 'place', 'tags', 'imageUrl', 'isWishlist','updatedAt','status', 'visibility'
        ];
        if ($isDetail) {
            $detailColumns = [
                'saleStatus', 'saleStatusCode', 'canUseCoupon', 'quantity', 'contents', 'additionals', 'purchase','imageUrls', 'isBook'
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
     * 建立規格商品
     * @param $configurableProductResult
     */
    public function magentoConfigurableProduct($configurableProductResult)
    {
        $this->additionals = $configurableProductResult['additionals'];
        $this->configurableProducts = $configurableProductResult['configurableProducts'];
        $this->configurableProductOptions = $configurableProductResult['configurableProductOptions'];
        if(!empty($this->configurableProducts)) {
            $configurableProducts = $this->configurableProducts[0];
            $this->price = $configurableProducts->price;
            $this->salePrice = $configurableProducts->salePrice;
            $this->discount = $this->countDiscount($this->salePrice, $this->price);
        }

    }

    /**
     * 狀態轉換
     * @return string
     */
    public function getSaleStatus($key)
    {
        switch ($key) {
            case '11': # 熱賣中
                return __('product.sale_status.on_sale');
            case '20': # 結束銷售
                return __('product.sale_status.end_sale');
            case '10': # 已完售
                return __('product.sale_status.has_been_sale');
            default:
                return __('product.sale_status.not_yet_sale');
        }
    }


}
