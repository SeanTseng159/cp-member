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

    public function magento($result, $isDetail = false)
    {
        $this->source = ProjectConfig::MAGENTO;
        $this->id = $this->arrayDefault($result, 'sku');
        $this->name = $this->arrayDefault($result, 'name');
        $this->price = $this->arrayDefault($result, 'price');
        $this->salePrice = null;
        $this->characteristic = null;
        $this->category = null;
        $this->storeName = null;
        $this->place = null;
        $this->tags = null;
        $this->description = $this->customAttributes($result['custom_attributes'], 'description');
        $this->imageUrl = $this->magentoImageUrl($this->customAttributes($result['custom_attributes'], 'image'));
        $this->createdAt = $this->arrayDefault($result, 'created_at');

        if ($isDetail) {
            $this->canUseCoupon = null;
            $this->storeTelephone = null;
            $this->storeAddress = null;
            $this->quantity = null;
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

    private function magentoImageUrl($path)
    {
        $basePath = $this->env('MAGENTO_PRODUCT_PATH');
        return $basePath . $path;
    }
}