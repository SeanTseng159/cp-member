<?php

namespace App\Http\Controllers\Api\V1;


use App\Enum\MyGiftType;
use App\Result\AwardRecordResult;
use App\Result\MemberGiftItemResult;
use App\Services\AwardRecordService;
use App\Services\ImageService;
use App\Services\Ticket\MemberGiftItemService;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use stdClass;


class MemberGiftController extends RestLaravelController
{
    const DelayVerifySecond = 90;
    protected $lang = 'zh-TW';
    protected $memberGiftItemService;
    protected $imageService;
    protected $awardRecordService;
    protected $qrCodePrefix = 'gift_';


    public function __construct(
        MemberGiftItemService $memberGiftItemService,
        ImageService $imageService,
        AwardRecordService $awardRecordService
    )
    {

        $this->memberGiftItemService = $memberGiftItemService;
        $this->imageService = $imageService;
        $this->awardRecordService = $awardRecordService;
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
            $type = Input::get('type', 'current');
            $client = Input::get('client', null);
            $clientId = intval(Input::get('uid', null));

            if (!$memberId || !$type) {
                throw new \Exception('E0007');
            }


            //current 未使用 1 used 已使用 2
            if ($type == 'current') {
                $type = 1;
            } else if ($type == 'used') {
                $type = 2;
            } else {
                throw  new \Exception('E0001');
            }

            //取得使用者的禮物清單
            $diningCarGift = $this->memberGiftItemService->list($type, $memberId, $client, $clientId);

            //取得活動的獎品清單
            $award = $this->awardRecordService->list($type, $memberId);


            $resultGift = (new MemberGiftItemResult())->list($diningCarGift, $type);
            $resultAward = (new AwardRecordResult())->list($award);
            $result = array_merge($resultGift, $resultAward);
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
