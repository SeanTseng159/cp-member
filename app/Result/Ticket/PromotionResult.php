<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Config\Ticket\ProcuctConfig;
use Carbon\Carbon;


class PromotionResult extends BaseResult
{


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取得所有商品資料
     * @param $promotions
     * @return array
     */
    public function all($promotions)
    {;

        if (!$promotions)
            return [];

        $newItems = [];

        foreach ($promotions as $promotion) {
            $newItems[] = $this->get($promotion);
        }

        return $newItems;
    }

    /**
     * 取得資料
     * @param $promotion
     * @return \stdClass|null
     */
    public function get($promotion)
    {
        if (!$promotion) return null;

        $condition = $promotion->conditions[0];
        $product = optional($promotion->prodSpecPrices[0])->proudct;



        $data = new \stdClass();

        $data->source = 'market';
        $data->id = $promotion->id;
        $data->name = $promotion->title;

        $data->characteristic = $this->getPromotionString(
            optional($promotion)->condition_type,
            optional($promotion)->offer_type,
            optional($condition)->condition,
            optional($condition)->offer);
        $data->imageUrl = optional($product)->img ?
            $this->backendHost . $product->img->img_thumbnail_path : '';

        return $data;
    }


    private static function getPromotionString($condition_type, $offerType, $condition, $offer)
    {

        $retString = '';
        switch ($condition_type) {
            case 1:
                $retString = "滿 $condition 元";
                break;
            case 2:
                $retString = "滿 $condition 件";
                break;
            case 3:
                $retString = "滿 $condition 件";
                break;
        }
        switch ($offerType) {
            case 1:
                $offer = (int)$offer;
                $retString .= "折 $offer 元";
                break;
            case 2:
                $discount = $offer * 100;
                $retString .= "打 $discount 折";
                break;
            case 3:
                //todo 後台沒這個選項
                $retString .= "送禮物 ";
                break;
            case 4:
                $retString .= "$offer 元";
                break;
        }
        $retString = str_replace(' ', '', $retString);
        return $retString;
    }

}
