<?php

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Helpers\CommonHelper;
use Carbon\Carbon;

class MemberCouponOnlineResult extends BaseResult
{
    //供購物車在準備結帳時，列出所有member目前所持有的"店車的線上優惠券"中，有哪些是符合資格的，return符合資格的優惠
    public function listCanUsed($member_coupon_online, $cartItems, $memberID, $source_diningCar_id)
    {
        $resultObj = new \stdClass; //stdClass能為PHP建立新空物件
        $resultObj->useful = [];    //useful存放篩選到最後可被使用的優惠券
        $resultObj->useless = [];   //useless存放篩選到最後不可被使用的優惠券


        /*
        foreach每張member擁有的coupon券中，逐張篩選是否能被使用
        篩選邏輯 :
        1-判斷購物車是否為空
        2-撇除其他店車的優惠券，僅留下當前商品店車的優惠券
        3-判斷這些coupon有沒有不在使用期限的
        4-判斷是否符合首購優惠
        5-判斷是否購物車金額>優惠券所需的最低消費金額
        */
        foreach ($member_coupon_online as $key => $coupon_item) {
            $coupon_can_be_used = true;//若有任何條件不符則改為false
            $message = "";

            //判斷購物車是否為空
            if (empty($cartItems[0]->items)) {//item內存放各商品資訊，id,name等等資料
                $coupon_can_be_used = false;
                $message = '購物車內沒有商品';
            }

            //撇除其他店車的優惠券，僅留下當前商品店車的優惠券
            if($coupon_can_be_used){
                $cartNum = $cartItems[0]->id;//cartItem的id即為餐車號碼
                if($coupon_item->dining_car_id != $source_diningCar_id){//若此張優惠券的店車id與目前購物車的所屬店車id不符(EX:咖啡廳的優惠券不能用在炸雞店的商品上)
                    $coupon_can_be_used = false;
                    $message = '此為其他店的優惠券';
                    //$message = $coupon_item->dining_car_id.'--'.$source_diningCar_id;
                }
            }

            //判斷這些coupon有沒有不在使用期限的
            if($coupon_can_be_used){
                if($coupon_item->start_at > Carbon::now()){
                    $coupon_can_be_used = false;
                    $message = '還未到達優惠券可使用時間';
                }
                if($coupon_item->expire_at < Carbon::now()){
                    $coupon_can_be_used = false;
                    $message = '已經超過優惠券可使用時間';
                }
                
            }

            //判斷是否符合首購優惠

            //判斷是否購物車金額>優惠券所需的最低消費金額
            //此部分邏輯是"先計算原價折扣後的價格"，此價格仍大於消費門檻，才折價
            //EX:門檻500 A:消費510折價後490，無法使用 B:消費550折價後530，可以使用
            if ($coupon_can_be_used) {
                $cartTotalPrice = $cartItems[0]->totalAmount;//購物車總價
                $totalPriceAfterCoupon = 0;//購物車折價後價格

                //此if elseif計算折扣後價格
                if($coupon_item->online_code_type == 1){//折扣(打幾折)
                    $totalPriceAfterCoupon = (int)($cartTotalPrice * (float)$coupon_item->price/ 100);//折扣後總價 = 原價 * 折數
                }
                elseif($coupon_item->online_code_type == 2){//折價(扣幾元)
                    $totalPriceAfterCoupon = $cartTotalPrice - (int)$coupon_item->price;
                }

                if($totalPriceAfterCoupon < $coupon_item->online_code_limit_price){//若折扣後小於消費門檻，則不可使用
                    $coupon_can_be_used = false;
                    $message = '折扣後金額未達消費門檻'.'折扣後:'.$totalPriceAfterCoupon.' , 門檻: '.$coupon_item->online_code_limit_price;
                }

            } 

            if(!$coupon_can_be_used) $coupon_item['error_message'] = $message;//若流程結束判斷此優惠券不可用，將不可用原因存在coupon_item['error_message']
            $coupon_item['can_use'] = $coupon_can_be_used;//新增欄位顯示是否可用，方便前端判斷

            $result = $this->listCanUsedFormat($coupon_item);//若直接回傳可用的coupon，資料會太多太雜，故format只留下前端要的資訊，其他過濾掉

            //優惠券能用->存useful[], else 存useless[]
            $coupon_can_be_used ? $resultObj->useful[] = $result : $resultObj->useless[] = $result;

        }//foreach end

        return $resultObj;

    }//function end

    //制定一個format，讓其他function能夠只回傳必要的值給前端
    public function listCanUsedFormat($data)
    {
        //宣告一個新物件format，format內存需要回傳的值
        $format = new \stdClass; 

        $format->coupon_id = $data->id;//優惠券id
        $format->coupon_source = 'vendor';//讓前端知道此張優惠券是站方開立的
        $format->coupon_name = $data->name;//優惠券名稱
        $format->value = $data->online_code_value;//優惠券代碼(ex:vip777,xmas1225)
        $format->desc = $data->desc;//優惠券敘述
        $format->can_use = $data->can_use;//listCanUsed()判斷後是否可使用 true/false
        $format->endtime = Carbon::parse($data->expire_at)->format('Y-m-d');//優惠券到期日期 精確到到日期就好
        $format->imageUrl = '';//圖片URL 此部分暫時為空，待改
        $format->message= $data->error_message; //若優惠券不可用，會帶error_message解釋不可用原因(ex:逾期、未達折扣門檻...)
        return $format;
    }


}