<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/14
 * Time: 下午 04:57
 */

namespace Ksd\Mediation\Result;

use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Helper\EnvHelper;

class WishlistResult
{
    use EnvHelper;
    use ObjectHelper;

    /**
     * magento 收藏清單資料建置
     * @param $result
     */
    public function magento($result)
    {
        $this->source = 'magento';
        $this->wishlistItemId = $this->arrayDefault($result, 'wishlist_item_id');
        $this->id = $this->arrayDefault($result['product'], 'sku');
        $this->name = $this->arrayDefault($result['product'], 'name');
        $this->price = $this->arrayDefault($result['product'], 'price');
        $this->price = $this->arrayDefault($result['product'], 'price');
        $this->characteristic = null;
        $this->storeName = null;
        $this->place = null;
        $this->thumbnaiPath = $this->magentoImageUrl($this->arrayDefault($result['product'], 'thumbnail'));

/*
        foreach ($result['product'] as $item) {
            $this->items[]=[
            'id' => $this->arrayDefault($item, 'sku'),
            'name' => $this->arrayDefault($item, 'name'),
            'qty' => $this->arrayDefault($item, 'qty'),
            'price' => $this->arrayDefault($item, 'price'),
            'salePrice' => $this->arrayDefault($item, 'price'),
            'thumbailPath' => $this->magentoImageUrl($this->arrayDefault($item, 'thumbnail'))
            ];
        }
*/
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
}