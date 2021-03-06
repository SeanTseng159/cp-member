<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/1
 * Time: 下午 2:32
 */

namespace Ksd\Mediation\Result;


use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Config\ProjectConfig;
use App\Traits\CartMoreHelper;

class CartMoreResult
{
    use ObjectHelper;
    use CartMoreHelper;


    /**
     * 處理 city pass 資料建置
     * @param $result
     */
    public function cityPass($result)
    {
        $this->id = $this->arrayDefault($result, 'cartNumber');
        $this->items = [];
        $isPhysical=false;
        foreach ($this->arrayDefault($result, 'items', []) as $item) {
            $row = new ProductResult();
            $row->source = ($this->arrayDefault($item, 'source'))? $this->arrayDefault($item, 'source'):ProjectConfig::CITY_PASS;
            $row->id = $this->arrayDefault($item, 'id');
            $row->redirectId = $this->arrayDefault($item, 'id');
            $row->name = $this->arrayDefault($item, 'name');
            $row->type = $this->arrayDefault($item, 'type');
            $row->spec = $this->arrayDefault($item, 'spec');
            $row->qty = $this->arrayDefault($item, 'quantity');
            $row->price = $this->arrayDefault($item, 'price');
            $row->additionals = $this->arrayDefault($item, 'additionals');
            $row->imageUrl = $this->arrayDefault($item, 'imageUrl');
            $row->purchase = $this->arrayDefault($item, 'purchase');
            // 先判斷status存不存在，以免跟backend api不同步
            if (isset($item['status'])) {
                $row->statusCode = $this->arrayDefault($item['status'], 'code');
                $row->statusDesc = $this->arrayDefault($item['status'], 'desc');
            }
            $row->isOneSpec = $this->arrayDefault($item, 'isOneSpec');
            //判斷是否是physical產品
            if(!$isPhysical and $this->arrayDefault($item, 'source')=='ct_pass_physical'){
                $isPhysical=true;
            }
            $this->items[] = $row;
        }
        $this->itemTotal = $this->arrayDefault($result, 'itemTotal');
        $this->totalAmount = $this->arrayDefault($result, 'totalAmount');
        if(empty($this->arrayDefault($result, 'useCoupon'))){
            $this->useCoupon = null;
        }else {
            $this->useCoupon = $this->arrayDefault($result, 'useCoupon');
        }
        if(empty($this->arrayDefault($result, 'DiscountCode'))){
            $this->DiscountCode = null;
        }else {
            $this->DiscountCode = $this->arrayDefault($result, 'DiscountCode');
        }
        $this->discountAmount = $this->arrayDefault($result, 'discountAmount');
        $this->discountTotal = $this->arrayDefault($result, 'discountTotal') ?: $this->arrayDefault($result, 'payAmount');
        $this->payAmount = $this->arrayDefault($result, 'payAmount');
        $this->shipmentAmount = $this->arrayDefault($result, 'shipmentAmount');
        $this->shipmentFree = $this->arrayDefault($result, 'shipmentFree');
        $this->canCheckout = $this->arrayDefault($result, 'canCheckout');
        //增加購物車裏面是否有實體商品
        $this->cartSource=($isPhysical)?ProjectConfig::CITY_PASS_PHYSICAL:ProjectConfig::CITY_PASS;
        $this->cartNumber= $this->arrayDefault($result, 'cartNumber');
        $this->cartName= $this->getCartInfo($this->cartNumber);
    }

    /**
     * magento 組合商品之主商品sku獲取
     * @param $key
     * @return string
     */
    public function getMainItemSku($key)
    {
        if(!empty($key)) {
            $mainKey = explode("-", $key);
            return $mainKey[0];
        }

    }
}
