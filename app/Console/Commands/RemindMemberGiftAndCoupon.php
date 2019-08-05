<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Logger;

use App\Services\Ticket\GiftService;
use App\Services\Ticket\MemberGiftItemService;


use App\Services\Ticket\CouponService;
use App\Services\Ticket\MemberCouponService;

class RemindMemberGiftAndCoupon extends Command
{
    protected $service;

    /**
     * The name and signature of the console command.
     *

     * @var string
     */
    protected $signature = 'run:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'consume test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */


    public function handle(GiftService $giftService,MemberGiftItemService $memberGiftItemService,CouponService $couponService,MemberCouponService $memberCouponService)
    {
        $data=$giftService ->findGiftEndTime();
        //echo (sizeof($data));
        foreach ($data as $gift )
        {
            //echo('GG');
            $giftId=$gift->id;
            //get unique memberID 不管禮物卷多少只取1次ID
            $memberGiftIdArray=$memberGiftItemService->findByGiftId($giftId);
            //$memberGiftIdUnique=array_unique($memberGiftIdArray);
            foreach ($memberGiftIdArray as $memberId) 
            {
              //echo($memberId);
              $MGId=$memberId;
              $MGpush=array('types'  => 5,
                          'typeId' => $gift->model_spec_id,
                          'url' => "",
                          'giftname' => $gift->name );
            }

            
        }

        $data=$couponService ->findCouponEndTime();
        foreach ($data as $coupon )
        {
            //取得優惠卷ID
            $couponId=$coupon->id;
            //取得優惠卷限制
            $limit_qty=$coupon->limit_qty;

            //echo($couponId);
            $memberCouponArray=$memberCouponService->findByCouponId($couponId);
            foreach ($memberCouponArray as $memberCoupon) 
            {   
                //當使用優惠卷章數與優惠卷限制相同時，不提醒
                if($memberCoupon->count == $limit_qty)
                {

                }
                else
                {

                    $MCId=$memberCoupon->member_id;
                    //echo($MCId);
                    $MCpush=array('types'  => 5,
                        'typeId' => $coupon->model_spec_id,
                        'url' => "",
                        'giftname' => $coupon->name );  
                    //echo($MCId);
                }
                
            }

        }
 
    }



}
