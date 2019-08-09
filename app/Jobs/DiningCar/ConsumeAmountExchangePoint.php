<?php
/**
 * User: lee
 * Date: 2019/03/15
 * Time: 上午 9:42
 */

namespace App\Jobs\DiningCar;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Services\Ticket\DiningCarPointService;
use App\Services\FCMService;
use Cache;
use Log;
use App\Helpers\CommonHelper;
use App\Services\Ticket\DiningCarMemberService;

class ConsumeAmountExchangePoint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $member;
    private $consumeAmount;
    private $key;
    private $diningCarId;
    private $rule;
    private $addmemberCheck;
    private $giftCheck;
    private $diningCarName;
    private $giftName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($member, $consumeAmount, $key = '', $diningCarId = '', $rule = '',$addmemberCheck = false ,$giftCheck = false ,$diningCarName = '' ,$giftName ='')
    {
        $this->member = $member;
        $this->consumeAmount = $consumeAmount;
        $this->key = $key;
        $this->diningCarId = $diningCarId;
        $this->rule = $rule;
        $this->addmemberCheck = $addmemberCheck;
        $this->giftCheck = $giftCheck;
        $this->diningCarName = $diningCarName;
        $this->giftName = $giftName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DiningCarPointService $pointService ,FCMService $fcmService,DiningCarMemberService $diningCarMemberService)
    {
        \Log::info('進來handle了');
        if ($this->getCache($this->key)) return;

        if ($this->member && $this->consumeAmount > 0) {
            $this->setCache($this->key);
            \Log::info('111');
            //查詢尚未寫入資料表的的總和
            $Info=$diningCarMemberService->findLevel($this->member->member_id, $this->diningCarId);
            if(empty($Info))
            {
                $car=$diningCarMemberService->findCarLevel($this->diningCarId);
                $amount=0;
            }
            else
            { 
                $car=$Info->diningCar->memberLevels;
                $amount=$Info->amount;
            }
            \Log::info('222');
            //查詢現在的等級
            $stkey=$this->findNowLevel($car,$amount);
            //判斷加上新的消費後是否有改變等級
            $nekey=$this->checkIfLevelUp($car,$amount, $this->consumeAmount,$stkey);


            //寫入消費記錄及點數並記錄兌換
            $pointService->consumeAmountExchangePoint($this->member, $this->consumeAmount);
            \Log::info('333');
            //推播升等提示!!
            if($nekey>$stkey)
            {
                $memberId=array($this->member->member_id);
                    //echo($MCId);
                $pushData=array('prodType'  => 5,
                        'prodId' => $this->diningCarId,
                        'url' => [],
                        'name' => $car[$nekey]->name );  
                $fcmService->memberNotify('diningCarMemberLevelUp',$memberId,$pushData);
            }
            \Log::info('444');
            //推播
            $data['point'] = floor($this->consumeAmount / $this->rule->point);
            $data['url'] = CommonHelper::getWebHost('zh-TW/diningCar/detail/' . $this->diningCarId);
            $data['prodType'] = 5;
            $data['prodId'] = $this->diningCarId;
            $data['addmemberCheck'] = $this->addmemberCheck;
            $data['giftCheck'] = $this->giftCheck;
            $data['diningCarName'] = $this->diningCarName;
            $data['giftName'] = $this->giftName;
            $memberIds[0] = $this->member->member_id;
            $fcmService->memberNotify('getPoint',$memberIds,$data);
            \Log::info('555');





        }
    }

    private function getCache($key)
    {
        $key = sprintf('ConsumeAmountExchangePoint::%s', $key);

        return (Cache::get($key)) ? true : false;
    }

    private function setCache($key)
    {
        $key = sprintf('ConsumeAmountExchangePoint::%s', $key);

        return Cache::put($key, true, 3);
    }

    public function findNowLevel($car,$amount)
    {
        $totalStKey=sizeof($car)-1;
        if($totalStKey==0){$stkey=0;}
        else{
            foreach ($car as $key=>$da ) 
            {
                //echo($da->limit);
                //檢查門檻值拿到該有的key
                if( $amount >= $da->limit )
                {
                    $stkey=$key;
                }
                else
                {
                    $stkey=$key-1;
                    break;
                }
 
            }
        }
        return $stkey;
    }

    public function checkIfLevelUp($car,$amount,$consumeAmount,$stkey)
    {
        $totalStKey=sizeof($car)-1;
        //加上剛剛的消費判斷，先判斷是否已經是頂級會員了!
        if($totalStKey>$stkey)
        {
            foreach (range($stkey,$totalStKey) as  $key) 
            {
                if($amount+$consumeAmount >= $car[$key]->limit)
                {
                    $nekey=$key;
                }
                else
                {
                    $nekey=$key-1;
                    break;
                }
            }   

        }
        else
        {
            $nekey=$stkey;
        }

        return $nekey;
    }



}
