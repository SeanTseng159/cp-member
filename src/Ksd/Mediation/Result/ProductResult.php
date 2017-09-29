<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 下午 1:53
 */

namespace Ksd\Mediation\Result;


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
        $this->salePrice = $this->customAttributes($result['custom_attributes'], 'special_price', 0);
        $this->discount = $this->countDiscount($this->salePrice, $this->price);
        $this->characteristic = null;
        $this->category = null;
        $this->storeName = null;
        $this->place = null;
        $this->tags = null;
        $this->description = $this->customAttributes($result['custom_attributes'], 'description');
        $this->imageUrl = $this->magentoImageUrl($this->customAttributes($result['custom_attributes'], 'image'));
        $this->createdAt = $this->arrayDefault($result, 'created_at');
        $this->productId = $this->arrayDefault($result, 'id');
        $this->customAttributes = $this->arrayDefault($result, 'custom_attributes');
        $this->isWishlist = false;
        if ($isDetail) {
            $this->saleStatus = $result['extension_attributes']['stock_item']['is_in_stock'] ? '11' : '10';
            $this->canUseCoupon = null;
            $this->storeTelephone = null;
            $this->storeAddress = null;
            $this->quantity = $result['extension_attributes']['stock_item']['qty'];
            $this->contents = null;
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
        $this->characteristic = $this->arrayDefault($result, 'characteristic');
        $this->category = null;
        $this->storeName = $this->arrayDefault($result, 'storeName');
        $this->place = $this->arrayDefault($result, 'place');
        $this->tags = $this->arrayDefault($result, 'tags');
        $this->description = $this->arrayDefault($result, 'description');
        $this->imageUrl = $this->arrayDefault($result, 'imageUrl');
        $this->createdAt = $this->arrayDefault($result, 'createdAt');

        if ($isDetail) {
            $this->canUseCoupon = $this->arrayDefault($result, 'canUseCoupon');
            $this->storeTelephone = $this->arrayDefault($result, 'storeTelephone');
            $this->storeAddress = $this->arrayDefault($result, 'storeAddress');
            $this->quantity = $this->arrayDefault($result, 'quantity');
            $this->contents = $this->arrayDefault($result, 'contents');
            $this->additionals = $this->arrayDefault($result, 'additionals');
            $this->purchase = $this->arrayDefault($result, 'purchase');
            $this->imageUrls = $this->arrayDefault($result, 'imageUrls');
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
     * 處理折扣折數
     * @param $salePrice
     * @param $price
     * @return string
     */
    public function countDiscount($salePrice, $price)
    {
        if ($salePrice===0 || $price === 0) {
            return '';
        }
        $discount = (int) (($salePrice / $price) * 100);
        return sprintf("%d打折", $discount);
    }
}