<?php


namespace App\Http\Controllers\Api\V1;


use App\Core\Logger;
use App\Services\Ticket\DiningCarPointService;
use App\Services\Ticket\GiftService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class DiningCarPointController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $diningCarService;
    protected $giftService;


    public function __construct(DiningCarPointService $diningCarPointService,GiftService $giftService)
    {
        $this->diningCarService = $diningCarPointService;
        $this->giftService = $giftService;
    }


    public function total(Request $request, $diningCarID)
    {
        try {
            $memberId = $request->memberId;
            $point = $this->diningCarService->total($diningCarID, $memberId);
            return $this->success(['point' => $point]);
        } catch (\Exception $e) {
            Logger::error('point total Error', $e->getMessage());
            return $this->failureCode('E0001');
        }
    }
    public function exchange(Request $request,$giftId)
    {
        $memberId = $request->memberId;

        //取得gift的兌換點數
        $gift = $this->giftService->getPoints($giftId);
        dd($gift);

        //檢查是否有足夠的點數可兌換

    }


}


