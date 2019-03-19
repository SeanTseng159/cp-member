<?php


namespace App\Http\Controllers\Api\V1;


use App\Enum\ClientType;
use App\Helpers\ImageHelper;
use App\Result\Ticket\GiftResult;
use App\Services\Ticket\MemberGiftItemService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\Ticket\GiftService;
use App\Traits\MemberHelper;

class GiftController extends RestLaravelController
{
    use MemberHelper;

    protected $lang = 'zh-TW';
    protected $giftService;
    protected $memberGiftItemService;

    /**
     *
     * @param GiftService $giftService
     * @param MemberGiftItemService $memberGiftItem
     */
    public function __construct(GiftService $giftService, MemberGiftItemService $memberGiftItem)
    {

        $this->giftService = $giftService;
        $this->memberGiftItemService = $memberGiftItem;
    }

    /**
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $memberId = $this->getMemberId();


        $client = $request->modelType;
        $clientId = $request->modelSpecId;

        if (!$client or !$clientId) {
            return $this->failureCode('E0007');
        }

        //禮物清單
        $gifts = $this->giftService->list($client, $clientId);

        foreach ($gifts as $item) {
            $item->photo = ImageHelper::getImageUrl(ClientType::gift, $item->id, 1);
        }


        //設定禮物狀態(可使用/額度已用完)
        $this->setGiftStatus($gifts, $memberId);

        $result = (new GiftResult())->list($gifts);
        return $this->success($result);

    }

    /**
     * @param $gifts
     * @param $memberId
     */
    public function setGiftStatus($gifts, $memberId)
    {

        $giftIds = $gifts->pluck('id')->toArray();
        $giftIds = array_unique($giftIds);
        $mememberGiftStatus = $this->memberGiftItemService->getUsedCount($giftIds);


        //禮物使用狀態
        foreach ($gifts as $item) {

            $giftID = $item->id;
            $qty = $item->qty;
            $limiQty = $item->limit_qty;


            $item->status = 0;

            //全部額度已用完
            if ($qty <= 0) {
                $item->status = 2;
            }

            //個人額度已用完
            $personal = $mememberGiftStatus
                ->where('member_id', $memberId)
                ->where('gift_id', $giftID)
                ->first();

            if ($personal) {
                $personalUsed = $personal->total;
                $item->qty = $limiQty - $personalUsed;
                if ($personalUsed >= $limiQty) {
                    $item->status = 1;
                }
            }
            else{
                $item->qty = $limiQty;
            }

        }
    }

}
