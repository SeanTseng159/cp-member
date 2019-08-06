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
use App\Helpers\CommonHelper;

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
    public function handle(DiningCarPointService $pointService ,FCMService $fcmService)
    {
        if ($this->getCache($this->key)) return;

        if ($this->member && $this->consumeAmount > 0) {
            $this->setCache($this->key);
            $pointService->consumeAmountExchangePoint($this->member, $this->consumeAmount);
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
}
