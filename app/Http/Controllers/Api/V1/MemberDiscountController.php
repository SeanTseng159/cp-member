<?php

namespace App\Http\Controllers\Api\V1;


use App\Enum\MyGiftType;
use App\Models\PromoteGift;
use App\Result\AwardRecordResult;
use App\Result\MemberGiftItemResult;
use App\Result\PromoteGiftRecordResult;
use App\Services\AwardRecordService;
use App\Services\ImageService;
use App\Services\Ticket\InvitationService;
use App\Services\Ticket\MemberGiftItemService;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use stdClass;


class MemberDiscountController extends RestLaravelController
{
    const DelayVerifySecond = 90;
    protected $lang = 'zh-TW';
    protected $memberGiftItemService;
    protected $imageService;
    protected $awardRecordService;
    protected $invitationService;
    protected $diningCarDiscountService;
    protected $qrCodePrefix = 'gift_';


    public function __construct(
        MemberGiftItemService $memberGiftItemService,
        ImageService $imageService,
        AwardRecordService $awardRecordService,
        InvitationService $invitationService

    )
    {

        $this->memberGiftItemService = $memberGiftItemService;
        $this->imageService = $imageService;
        $this->awardRecordService = $awardRecordService;
        $this->invitationService = $invitationService;
    }

    /**
     * 我的禮物列表
     *
     * @param Request $request
     *
     * @return JsonResponseAlias
     */
    public function list(Request $request)
    {
        try {
            $memberId = $request->memberId;
            $type = Input::get('type', 0);

            //取得使用者的禮物清單
            $diningCarGift = $this->memberGiftItemService->list($type, $memberId);

            //獎品
            $award = $this->awardRecordService->list($type, $memberId);

            //邀請碼的獎品
            $promoteGifts = $this->invitationService->list($type, $memberId);

            //折價券

            $resultGift = (new MemberGiftItemResult())->list($diningCarGift, $type);
            $resultAward = (new AwardRecordResult())->list($award);
            $resultPromote = (new PromoteGiftRecordResult())->list($promoteGifts);

            $result = array_merge($resultGift, $resultAward, $resultPromote);
            $result = array_values(collect($result)->sortBy('duration')->toArray());

            return $this->success($result);

        } catch (\Exception $e) {
            if ($e->getMessage()) {
                return $this->failureCode($e->getMessage());
            }

            return $this->failureCode('E0007');
        }


    }


    /**
     * 我的禮物明細
     *
     * @param Request $request
     *
     * @param $id
     * @param $type
     * @return JsonResponseAlias
     * @throws \Exception
     */
    public function show(Request $request, $id)
    {
        try {
            $memberId = $request->memberId;
            $result = $this->memberGiftItemService->findByID($id, $memberId);
            if ($result) {
                $result = (new MemberGiftItemResult())->show($result);
            } else {
                throw  new \Exception('E0076');
            }
            return $this->success($result);
        } catch (\Exception $e) {
            if ($e->getMessage()) {
                return $this->failureCode($e->getMessage());
            }

            return $this->failureCode('E0007');
        }

    }


    /**
     * 格式: 編碼前 memberID.memberGiftItemID.$截止時間(timestamp)
     * ex.151.1.1551927111
     *
     * @param Request $request
     * @param $memberGiftId
     * @return string
     */
    public function getQrcode(Request $request, $memberGiftId)
    {

        try {
            $memberId = $request->memberId;

            //檢查禮物是否屬於該會員
            $memberGiftItem = $this->memberGiftItemService->findByID($memberGiftId, $memberId);
            if (!$memberGiftItem) {
                throw new \Exception('E0076');
            }

            //檢查qr code是否已經使用過
            if ($memberGiftItem->used_time) {
                throw new \Exception('E0078');
            }

            //90秒
            $duration = Carbon::now()->addSeconds($this::DelayVerifySecond)->timestamp;
            $code = $this->qrCodePrefix . base64_encode("$memberId.$memberGiftId.$duration");
            $result = new stdClass();
            $result->code = $code;

            return $this->success($result);

        } catch (\Exception $e) {
            if ($e->getMessage())
                return $this->failureCode($e->getMessage());
            return $this->failureCode('E0007');

        }
    }


}
