<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result\Ticket;

use Carbon\Carbon;
use App\Helpers\CommonHelper;
use Hashids\Hashids;
use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;

class ShopBookingResult
{

    public function maxpeople($bookingLimit)
    {
        # code...
        $result = new \stdClass;
        $result->max = $bookingLimit->max_people;
        $result->precautions = $bookingLimit->precautions;
        return $result;
    }//end public function maxpeople


    ///取得店舖可訂位日期
    public function findBookingCanDate($bookingLimit, $bookingDateBooked, $bookingDateTimes, $bookingNumOfPeo)
    {
        //重開始到結束時間整理一下
        $startday = $bookingLimit->start_days;
        $endday = $bookingLimit->end_days;
        //
        $result = [];
        for ($i = $startday; $i <= $endday; $i++) {
            //訂位只能明天開始，所以今天加上店家的設定
            $day = Carbon::today()->addDays($i);
            //將日期轉換成星期幾? 0:星期日  1:星期一 .....等等
            $dayWeek = $day->dayOfWeek;

            //寫個function用filter 處理時間相關問題
            $filtered = $bookingDateTimes->filter(function ($value, $key) use ($dayWeek) {
                return $value->day === $dayWeek;
            });
            //將資料取出來
            $filterData = $filtered->all();

            if (empty($filterData)) {
                //pass 不用處理
                // print("pass is ".$dayWeek.'<br>');
            } else {
                //目標整理成 array
                //date   time  accept_people
                //2019/10/1  12:00  10
                //2019/10/1  13:00  10
                foreach ($filterData as $key => $value) {
                    $array = new \stdClass;
                    $array->date = $day->toDateString();
                    $array->time = Carbon::parse($value->time)->format('H:i');
                    // $array->time= $value->time;
                    $array->accept_people = $value->accept_people;
                    $array->dayOfWeek = $dayWeek;
                    $result[] = $array;
                }//end foreach
            }//end if(empty($filterData)

        }//end for ($i=$startday;$i<=$endday;$i++)


        foreach ($bookingDateBooked as $key => $value) {
            # code...
            $booking_date = $value->booking_date;
            $booking_time = $value->booking_time;
            $booking_people = $value->sum_people;
            $st = collect($result)->search(function ($value, $key) use ($booking_date, $booking_time) {
                return (Carbon::parse($value->date)->format('Y-m-d') == Carbon::parse($booking_date)->format('Y-m-d')
                    && Carbon::parse($value->time)->format('H:i') == Carbon::parse($booking_time)->format('H:i'));
            });
            //取出已經訂位人數然後減去資料
            $result[$st]->accept_people = ($result[$st]->accept_people - $booking_people);
        }

        //判斷訂位人數是否這次要求是否可以訂位!!!
        $filtered = collect($result)->filter(function ($value, $key) use ($bookingNumOfPeo) {
            return $value->accept_people >= $bookingNumOfPeo;
        })->values();
        //將是否可以訂位資料取出來!!
        $result = $filtered->all();

        $CanBookingResult = [];
        //將資料轉換成送出的json檔
        $uniqDate = collect($result)->unique('date')->values();
        foreach ($uniqDate as $key => $uniqDatevalue) {

            $filtered = collect($result)->filter(function ($value, $key) use ($uniqDatevalue) {
                return ($value->date == $uniqDatevalue->date);
            })->values();
            //做出想要的array
            $array = new \stdClass;
            //把資料塞進去就好
            $array->date = $uniqDatevalue->date;
            $array->times = $filtered->pluck('time');
            $array->dayOfWeek = Carbon::parse($uniqDatevalue->date)->dayOfWeek;
            $CanBookingResult[] = $array;
        }
        return $CanBookingResult;
    }//end public findBookingCanDate


    public function finishedBooking($bookingTimesDateTime, $bookedDateTime, $bookedNumber, $shopInfo, $request, $id,
                                    $memberID, $memberDiningCars)
    {

        //訂單標號與今天日期相關
        if (empty($bookedNumber->max_number)) {
            $day = Carbon::today();
            $number = (((int)$day->format('Ymd')) * 10000 + 1);
        } else {
            $number = ((int)$bookedNumber->max_number) + 1;
        }//end if

        $result = new \stdClass;
        //不一定有訂位資料

        if (empty($bookedDateTime)) {
            $result->status = True;
        } else {
            //再次檢查之前的訂位人數+這次訂位人數是否會超過店家的負荷
            //超過人數直接送走來開
            $total_people = ($bookedDateTime->sum_people) + ($request->input('people'));
            if ($total_people > $bookingTimesDateTime->accept_people) {
                $result->status = False;
                return $result;
            } else {
                $result->status = True;
            }
        }//end if

        //另用訂單資訊，產生取消訂單的亂數碼
        $hashids = new Hashids('', 7, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'); // all lowercase
        //利用id產生邀請碼
        $code = $hashids->encode((string)$number);
        $booking = new \stdClass;
        $booking->number = $number;
        $booking->date = $request->input('date');
        $booking->time = $request->input('time');
        $booking->dayOfWeek = $request->input('dayOfWeek');
        $booking->people = $request->input('people');
        $booking->code = $code;
        $member = new \stdClass;
        $member->name = $request->input('name');
        $member->phone = $request->input('phone');
        $member->demand = $request->input('demand');
        $member->memberID = $memberID;

        $favoriteList = collect($memberDiningCars)->filter(function ($value, $key) use ($id) {
            return ($value->dining_car_id == $id);
        });
        $shop = new \stdClass;
        $shop->id = $id;
        $shop->name = $shopInfo->shopInfo->name;
        $shop->shareUrl = CommonHelper::getWebHost('zh-TW/shop/detail/' . $id);
        $shop->precautions = $shopInfo->precautions;
        $shop->isFavorite = (count($favoriteList)) > 0 ? true : false;

        $shop->canOnlineOrder = $shopInfo->shopInfo->canOrdering;
        if (empty($shopInfo->mainImg->folder)) {
            $shop->img = '';
        } else {

            $shop->img = ImageHelper::url($shopInfo->mainImg);
        }


        //整理進入result裡面
        $result->booking = $booking;
        $result->member = $member;
        $result->shop = $shop;
        return $result;
    }//end public finishedBooking

    public function getOenDetailInfo($shopInfo, $dataDetailInfo, $memberDiningCars)
    {

        $booking = new \stdClass;
        $booking->number = $dataDetailInfo->booking_number;
        $booking->date = $dataDetailInfo->booking_date;
        $booking->time = Carbon::parse($dataDetailInfo->booking_time)->format('H:i');
        $booking->dayOfWeek = $dataDetailInfo->booking_dayofweek;
        $booking->people = $dataDetailInfo->booking_people;
        $booking->code = $dataDetailInfo->code;
        $member = new \stdClass;
        $member->name = $dataDetailInfo->name;
        $member->phone = $dataDetailInfo->phone;
        $member->demand = $dataDetailInfo->demand;
        $member->memberID = $dataDetailInfo->member_id;
        $favoriteList = collect($memberDiningCars)->filter(function ($value, $key) use ($dataDetailInfo) {
            return ($value->dining_car_id == $dataDetailInfo->shop_id);
        });
        $shop = new \stdClass;
        $shop->id = $shopInfo->shop_id;
        $shop->name = $shopInfo->shopInfo->name;
        $shop->shareUrl = CommonHelper::getWebHost('zh-TW/shop/detail/' . $shopInfo->shop_id);
        $shop->precautions = $shopInfo->precautions;
        $shop->isFavorite = (count($favoriteList)) > 0 ? true : false;
        if (empty($dataDetailInfo->mainImg->folder)) {
            $shop->img = '';
        } else {

            $shop->img = ImageHelper::url($dataDetailInfo->mainImg);
        }

        //整理進入result裡面
        $result = new \stdClass;
        $result->status = ($dataDetailInfo->status) > 0 ? true : false;
        $result->booking = $booking;
        $result->member = $member;
        $result->shop = $shop;

        return $result;
    }//end public function getOenDetailInfo


    public function memberList($data, $memberDiningCars, $dataCount, $page)
    {
        $ret = [];
        //$ret[]=['total'=>count($data)];
        foreach ($data as $key => $bookingRecord) {

            //result
            $result = new \stdClass();
            $result->id = $bookingRecord->id;
            //booking
            $result->booking = new \stdClass();
            $result->booking->number = $bookingRecord->booking_number;
            $result->booking->code = $bookingRecord->code;
            $result->booking->date = DateHelper::chinese($bookingRecord->booking_date, '%Y/%m/%d');

            $result->booking->time = Carbon::parse($bookingRecord->booking_time)->format('H:i');
            $result->booking->people = $bookingRecord->booking_people;
            $result->booking->status = ($bookingRecord->status) > 0 ? true : false;
            //member
            $result->member = new \stdClass();
            $result->member->name = $bookingRecord->name;
            $result->member->phone = $bookingRecord->phone;
            $result->member->demand = $bookingRecord->demand;
            //shop
            $result->shop = new \stdClass();
            $favoriteList = collect($memberDiningCars)->filter(function ($value, $key) use ($bookingRecord) {
                return ($value->dining_car_id == $bookingRecord->shop_id);
            });
            $result->shop->id = $bookingRecord->diningCar->id;
            $result->shop->name = $bookingRecord->diningCar->name;
            $result->shop->isFavorite = (count($favoriteList)) > 0 ? true : false;
            $result->shop->shareUrl = CommonHelper::getWebHost('zh-TW/shop/detail/' . $bookingRecord->shop_id);
            $result->shop->precautions = $bookingRecord->shopLimit->precautions;
            if (empty($bookingRecord->mainImg->folder)) {
                $result->shop->img = '';
            } else {

                $result->shop->img = ImageHelper::url($bookingRecord->mainImg);
            }

            $ret[] = $result;

        }
        $ans = new \stdClass();
        $ans->total = $dataCount->count_data;
        $ans->page = $page;
        $ans->list = $ret;
        return $ans;
    }//end public	function memberList
}//end class
