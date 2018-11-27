<?php
/**
 * User: lee
 * Date: 2018/08/27
 * Time: 上午 10:03
 */

namespace App\Traits;

use Carbon\Carbon;

trait ProductHelper
{

    /**
     * 確認商品是否可使用
     * @param $product
     * @return array
     */
    private function checkExpire($product)
    {
        $result = true;
        $now = Carbon::now();

        if ($product->prod_expire_type === 3 || $product->prod_expire_type === 4) {
            $result = $now->between(Carbon::parse($product->prod_expire_start), Carbon::parse($product->prod_expire_due));
        }

        return $result;
    }
}
