<?php

namespace App\Console\Commands;

use App\Core\Logger;
use App\Services\Ticket\GiftService;
use App\Services\Ticket\MemberGiftItemService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use stdClass;

class GiveBirthdayGift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:birthday_gift';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'give birthday gift to member of dining car';

    protected $giftService;
    protected $memberGiftItemService;
    protected $duration = 30;//(day)

    /**
     * Create a new command instance.
     *
     * @param GiftService $giftService
     * @param MemberGiftItemService $memberGiftItemService
     */
    public function __construct(GiftService $giftService, MemberGiftItemService $memberGiftItemService)
    {
        parent::__construct();

        $this->giftService = $giftService;
        $this->memberGiftItemService = $memberGiftItemService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $memberGiftItemInsert = [];
            $giftUpdate = [];

            Logger::info('give birthday gift -- start', null);
            //取得有設定生日禮物的餐車
            $gifts = $this->giftService->getDingingCarHasBirthDayGift($this->duration);

            if (!$gifts) {
                Logger::info('give birthday gift finish -- no member', null);
                return true;
            }

            foreach ($gifts as $gift) {

                $oriGiftQty = $gift->qty;
                $giftQty = $oriGiftQty;
                $dingingCar = $gift->diningCar;
                if (!$dingingCar) {
                    continue;
                }

                //餐車的會員
                $dingingCarMembers = $dingingCar->members;
                if (!$dingingCarMembers)
                    continue;

                foreach ($dingingCarMembers as $dingingCarMember) {
                    $memberInfo = $dingingCarMember->member;
                    if ($memberInfo) {
                        //檢查上次取得時間與現在是否已經超過12個月
                        $canGetBirthday = $this->memberGiftItemService->canGetBirthday($memberInfo->id, $gift->id);
                        if (!$canGetBirthday)
                            continue;

                        //發送禮物劵(發完為止)
                        if ($giftQty <= 0)
                            continue;

                        $number = $this->memberGiftItemService->getMaxNumber($memberInfo->id, $gift->id);
                        $memberGiftItemInsert[] = [
                            'member_id' => $memberInfo->id,
                            'gift_id' => $gift->id,
                            'number' => ++$number,
                            'updated_at' => Carbon::now(),
                            'created_at' => Carbon::now()
                        ];
                        $giftQty--;
                    }

                }

                //更新庫存量
                if ($oriGiftQty != $giftQty) {
                    $giftObj = new stdClass();
                    $giftObj->gift_id = $gift->id;
                    $giftObj->qty = $giftQty;
                    $giftUpdate[] = $giftObj;

                }

            }
            $this->giftService->deliveryGifts($giftUpdate, $memberGiftItemInsert);
            Logger::info('give birthday gift -- finish', null);
            return true;

        } catch (\Exception $e) {
            Logger::Error("give birthday gift error : ", $e);
            return true;
        }
    }
}
