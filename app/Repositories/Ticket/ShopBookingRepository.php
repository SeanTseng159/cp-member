<?php

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;

use App\Models\Ticket\DiningCarBookingDetail;
use App\Models\Ticket\DiningCarBookingLimit;
use App\Models\Ticket\DiningCarBookingTimes;
use Carbon\Carbon;
//use DB;
class ShopBookingRepository extends BaseRepository
{
    /**
     * Default model.
     *
     * @var string
     */
    protected $diningCarBookingDetail;
    protected $diningCarBookingLimit;
    protected $diningCarBookingTimes;
    protected $limit;
    

    public function __construct(DiningCarBookingDetail $diningCarBookingDetail,DiningCarBookingLimit $diningCarBookingLimit,DiningCarBookingTimes $diningCarBookingTimes)
    {
        $this->diningCarBookingDetail = $diningCarBookingDetail;
        $this->diningCarBookingLimit = $diningCarBookingLimit;
        $this->diningCarBookingTimes = $diningCarBookingTimes;
    }

    /**
     * 找出店鋪的相關限制
     * @param int $Shop_Id
     * @return mixed
     */
    public function findBookingLimit($id = 0)
    {
        return $this->diningCarBookingLimit
                        ->where('shop_id', $id)
                        ->first();
    }



    /**
    *找出後續有訂位的詳細表
    *
    */
    public function findBookingDateBooked($id = 0)
    {
        $findDays = Carbon::now()->addDays(1);
        return $this->diningCarBookingDetail
                        ->select(\DB::raw('sum(booking_people) as sum_people'),'booking_date','booking_time')
                        ->where('shop_id', $id)
                        ->where('booking_date','>=', $findDays)
                        ->groupBy('booking_date','booking_time')
                        ->where('status', 1)
                        ->get();
    }
        

    /**
     * 找出可訂位的詳細表
     * @param  $id
     * @return mixed
     */
    public function findBookingDateTimes($id)
    {
        return $this->diningCarBookingTimes
                    ->where('shop_id', $id)
                    ->whereNull('deleted_at')
                    ->where('status',1)
                    ->orderBy('day','asc')
                    ->orderBy('time','asc')
                    ->get();
    }
                        
    /**
    *找出某日期時間有訂位，status檢查是否有被取消，還可以再訂位
    *
    */
    public function findBookedDateTime($id=0,$date,$time)
    {
        
        return $this->diningCarBookingDetail
                        ->select(\DB::raw('sum(booking_people) as sum_people'),'booking_date','booking_time')
                        ->where('shop_id', $id)
                        ->where('booking_date', $date)
                        ->where('booking_time', $time)
                        ->groupBy('booking_date','booking_time')
                        ->where('status', 1)
                        ->first();
    }                    
    /**
     * 找出某日期時間相關限制
     */
    public function findBookingTimesDateTime($id = 0,$dayOfWeek,$time)
    {
        return $this->diningCarBookingTimes
                        ->where('shop_id', $id)
                        ->where('day',$dayOfWeek)
                        ->where('time',$time)
                        ->where('status','>=',1)
                        ->first();
    }
    /**
     * 找出今天某一個店家的的訂單編號,要做排序用，不用管是否有被取消訂單，status不用管
     */
    public function findBookedNumber($id)
    {
        $findDays = Carbon::today();
        return $this->diningCarBookingDetail
                        ->select(\DB::raw('max(booking_number) as max_number'))
                        ->where('shop_id', $id)
                        ->where('created_at','>=', $findDays)
                        ->first();
    }

    /**
     * 找出今天所有訂單
     */
    public function findBookedAllNumber()
    {
        $findDays = Carbon::today();
        return $this->diningCarBookingDetail
                        ->select(\DB::raw('max(booking_number) as max_number'))
                        ->where('created_at','>=', $findDays)
                        ->first();
    }




    /**
     * 找出店家資訊
     * @param  $id
     * @return mixed
     */
    public function findShopInfo($id)
    {
        return $this->diningCarBookingLimit->with([
            'shopInfo'
        ])
            ->where('shop_id', $id)
            ->first();
    }  


    /**
     * 將訂位資料寫入DB
     */
    public function createDetail($data= null)
    {
        return $this->diningCarBookingDetail->create([
            'shop_id' => $data->shop->id,
            'member_id' => $data->member->memberID,
            'phone' => $data->member->phone,
            'name' => $data->member->name,
            'booking_number' => $data->booking->number,
            'booking_dayofweek' => $data->booking->dayOfWeek,
            'booking_date' => $data->booking->date,
            'booking_time' => $data->booking->time,
            'booking_people' => $data->booking->people,
            'demand' => $data->member->demand,
            'status' => 1,
            'editor' => 1,
            'code'=>$data->booking->code]);
    }//end public function createDetail


    /**
     * 查詢訂單detail
     */
    public function getOenDetailInfo($id= 0)
    {
        return $this->diningCarBookingDetail
                    ->where('id',$id)
                    ->first();
    }//end public function getOenDetailInfo


    /**
     * 取得訂單detail
     */
    public function getFromCode($code= 0)
    {
        return $this->diningCarBookingDetail
                    ->where('code',$code)
                    ->first();
    }//end  function getFromCode

    /**
     * 取消訂單
     */
    public function cancel($shopid,$code)
    {
        return $this->diningCarBookingDetail
                    ->where('shop_id',$shopid)
                    ->where('code',$code)
                    ->update(['status' => 0,'editor'=>1]);
                    

    }//end  function cancel


    /**
     * 取得訂位列表
     */
    public function getMemberList($memberId,$page)
    {
    
        $limit=5;
        $findDays = Carbon::today()->modify('-30 days');
        $data= $this->diningCarBookingDetail
                    ->with(['shopLimit','diningCar','mainImg'])
                    ->where('member_Id',$memberId)
                    ->where('booking_date','>=',$findDays)
                    ->forPage($page,$limit)
                    ->get();
        return $data;

    }//end  function getMemberList

    public function getCountMemberList($memberId,$page)
    {
        $findDays = Carbon::today()->modify('-30 days');
        $count= $this->diningCarBookingDetail
                    ->select(\DB::raw('count(id) as count_data'))
                    ->with(['shopLimit','diningCar','mainImg'])
                    ->where('member_Id',$memberId)
                    ->where('booking_date','>=',$findDays)
                    ->first();

        return $count;
    }//end  function getMemberList

}//end class
