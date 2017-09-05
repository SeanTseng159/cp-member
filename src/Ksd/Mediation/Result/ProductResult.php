<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 下午 1:53
 */

namespace Ksd\Mediation\Result;


use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Helper\ObjectHelper;

class ProductResult
{
    use EnvHelper;
    use ObjectHelper;

    public function magento($result, $isDetail = false)
    {
        $this->source = 'magento';
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

    private function magentoImageUrl($path)
    {
        $basePath = $this->env('MAGENTO_PRODUCT_PATH');
        return $basePath . $path;
    }
}