<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 9:42
 */

namespace App\Traits;

trait MapHelper
{
    /**
    * 計算地圖面積範圍
    * @param $minLat [最小緯度]
    * @param $maxLat [最大緯度]
    * @param $minLng [最小經度]
    * @param $maxLng [最大經度]
    */
    public function calcMapRange($minLat, $maxLat, $minLng, $maxLng)
    {
        // 處理緯度
        if ($minLat > $maxLat) {
            $range['latitude'] = [$maxLat, $minLat];
        } else {
            $range['latitude'] = [$minLat, $maxLat];
        }

        // 處理經度
        if ($minLng > $maxLng) {
            $range['longitude'] = [$maxLng, $minLng];
        } else {
            $range['longitude'] = [$minLng, $maxLng];
        }

        return $range;
    }

    /**
    * 計算兩組經緯度座標 之間的距離
    * @param $lat1 緯度1
    * @param $lng1 經度1
    * @param $lat2 緯度2
    * @param $lng2 經度2
    * @param $len_type （1:m or 2:km);
    * @param $decimal 取小點後幾位
    * @return m or km
    */
    public function calcDistance($lat1, $lng1, $lat2, $lng2, $lenType = 2, $decimal = 2)
    {
        $PI = 3.1415926535898;
        $earthRadius = 6378.137;

        $radLat1 = $lat1 * $PI / 180.0;
        $radLat2 = $lat2 * $PI / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * $PI / 180.0) - ($lng2 * $PI / 180.0);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $s = $s * $earthRadius;
        $s = round($s * 1000);

        if ($lenType > 1) $s /= 1000;

        return round($s, $decimal);
    }
}
