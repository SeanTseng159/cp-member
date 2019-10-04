<?php


namespace App\Http\Controllers\Api\V1;


use App\Enum\ClientType;
use App\Helpers\ImageHelper;
use App\Result\Ticket\GiftResult;
use App\Services\Ticket\DiningCarService;
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
    protected $diningCarService;

    /**
     *
     * @param GiftService $giftService
     * @param MemberGiftItemService $memberGiftItem
     * @param DiningCarService $diningCarService
     */
    public function __construct(GiftService $giftService,
                                MemberGiftItemService $memberGiftItem,
                                DiningCarService $diningCarService)
    {

        $this->giftService = $giftService;
        $this->memberGiftItemService = $memberGiftItem;
        $this->diningCarService = $diningCarService;
    }

    /**
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {

        try {
            $memberId = $this->getMemberId();


            $client = $request->modelType;
            $clientId = $request->modelSpecId;

            if (!$client or !$clientId) {
                return $this->failureCode('E0007');
            }

            //檢查餐車是否存在
            $diningCar = $this->diningCarService->find($clientId);
            if (!$diningCar) {
                return $this->failureCode('E0007');
            }

            //餐車付費狀態
            $isPaid = $this->diningCarService->isPaid($clientId);

            if ($isPaid) {
                //禮物清單
                $gifts = $this->giftService->list($client, $clientId);

                foreach ($gifts as $item) {
                    $item->photo = ImageHelper::getImageUrl(ClientType::gift, $item->id);
                }

                //設定禮物狀態(可使用/額度已用完)
                $this->setGiftStatus($gifts, $memberId);

                $result = (new GiftResult())->list($gifts, $isPaid);

                return $this->success($result);

            } else {
                //沒有付費則不顯示，使用者只能從我的禮物進入
                return $this->success([]);
            }

        } catch (\Exception $e) {
            dd($e);
            $this->failureCode('E0001');
        }


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

            //個人額度是否已用完
            $personal = $mememberGiftStatus
                ->where('member_id', $memberId)
                ->where('gift_id', $giftID)
                ->first();

            if ($personal) {
                $personalUsed = $personal->total;
                $remainingQty = $qty < ($limiQty - $personalUsed) ? $qty : ($limiQty - $personalUsed);
                if ($remainingQty <= 0) {
                    $item->status = 1;
                    $item->qty = 0;
                } else {
                    $item->qty = $remainingQty;
                }
            } else {
                //如果庫存量小於個人可使用的數量
                $item->qty = $qty < $limiQty ? $qty : $limiQty;
            }

        }
    }

}
