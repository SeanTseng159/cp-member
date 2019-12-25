<?php

namespace App\Http\Controllers\Api\V2;


use App\Enum\MyGiftType;
use App\Result\AwardRecordResult;
use App\Result\DiningCarDiscountResult;
use App\Result\MemberGiftItemResult;
use App\Result\PromoteGiftRecordResult;
use App\Result\Ticket\MemberDiningCarDiscountResult;
use App\Services\AwardRecordService;
use App\Services\ImageService;
use App\Services\Ticket\DiningCarDiscountService;
use App\Services\Ticket\InvitationService;
use App\Services\Ticket\MemberDiningCarDiscountService;
use App\Services\Ticket\MemberGiftItemService;


use Carbon\Carbon;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
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
    protected $invitationService;
    protected $diningCarDiscountService;
    protected $memberDiningCarDiscountService;
    

    public function __construct(
        MemberGiftItemService $memberGiftItemService,
        ImageService $imageService,
        AwardRecordService $awardRecordService,
        InvitationService $invitationService,
        DiningCarDiscountService $diningCarDiscountService,
        MemberDiningCarDiscountService $memberDiningCarDiscountService
    )
    {

        $this->memberGiftItemService = $memberGiftItemService;
        $this->imageService = $imageService;
        $this->awardRecordService = $awardRecordService;
        $this->invitationService = $invitationService;
        $this->diningCarDiscountService = $diningCarDiscountService;
        $this->memberDiningCarDiscountService = $memberDiningCarDiscountService;
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
    public function show(Request $request, $id, $type)
    {
        try {
            $memberId = $request->memberId;

            if (!$memberId || !$type) {
                throw new \Exception('E0007');
            }

            $result = null;
            if ($type == MyGiftType::gift) {
                $result = $this->memberGiftItemService->findByID($id, $memberId);
            } else if ($type == MyGiftType::award) {
                $result = $this->awardRecordService->find($id, $memberId);
            } else if ($type == MyGiftType::PROMOTE_GIFT) {
                $result = $this->invitationService->findPromoteGiftRecord($id, $memberId);
            } else if ($type == MyGiftType::DISCOUNT) {
                $result = $this->memberDiningCarDiscountService->find($id, $memberId);
            }

            if ($result) {
                if ($type == MyGiftType::gift) {
                    $result = (new MemberGiftItemResult())->show($result);
                } else if ($type == MyGiftType::award) {
                    $result = (new AwardRecordResult())->show($result);
                } else if ($type == MyGiftType::PROMOTE_GIFT) {
                    $result = (new PromoteGiftRecordResult())->show($result);
                } else if ($type == MyGiftType::DISCOUNT) {
                    $result = (new MemberDiningCarDiscountResult())->show($result);
                }
            } else {
                throw New \Exception('E0076');
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
     * @param $type : gift or award
     * @return string
     */
    public function getQrcode(Request $request, $memberGiftId, $type)
    {

        try {
            $memberId = $request->memberId;

            if (!$memberGiftId or !$type) {
                throw  new \Exception('E0007');
            }

            $result = new stdClass();
            if ($type == MyGiftType::gift) {
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

                $result->type = 'QrCode';
                $result->code = $code;
            } else if ($type == MyGiftType::award) {
                //檢查禮物是否屬於該會員
                $awardRecord = $this->awardRecordService->find($memberGiftId, $memberId);
                if (!$awardRecord) {
                    throw new \Exception('E0076');
                }

                //檢查qr code是否已經使用過
                if ($awardRecord->verified_at) {
                    throw new \Exception('E0078');
                }

                if ($awardRecord->barcode) {
                    $result->type = $awardRecord->barcode_type;
                    $result->code = $awardRecord->barcode;
                } else {
                    $result->type = 'QrCode';
                    $result->code = $awardRecord->qrcode;
                }
            }  else if ($type == MyGiftType::DISCOUNT) {
                //檢查禮物是否屬於該會員
                $memberDiscount = $this->memberDiningCarDiscountService->find($memberGiftId, $memberId);
                if (!$memberDiscount) {
                    throw new \Exception('E0076');
                }

                //檢查qr code是否已經使用過
                if ($memberDiscount->used_time) {
                    throw new \Exception('E0078');
                }

                $result->type = 'QrCode';
                $result->code = 'discount_'.$memberDiscount->qrcode;
                
            }
            return $this->success($result);
        } catch (\Exception $e) {
            if ($e->getMessage())
                return $this->failureCode($e->getMessage(), '');
            return $this->failureCode('E0007', '');

        }
    }


}
