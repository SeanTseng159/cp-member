<?php
/**
 * User: lee
 * Date: 2019/02/27
 * Time: 上午 9:42
 */

namespace App\Http\Controllers\Api\V1;

use Exception;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\MemberService;
use App\Services\NewsletterService;
use App\Services\JWTTokenService;
use App\Services\Ticket\InvitationService;
use App\Parameter\MemberParameter;
use App\Traits\CryptHelper;
use App\Traits\ValidatorHelper;

use Hashids\Hashids;

class MemberController extends RestLaravelController
{
    use CryptHelper;
    use ValidatorHelper;

    protected $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * 餐車邀請 - 註冊會員
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerInvite(Request $request, NewsletterService $newsletterService)
    {
        try {
            $data = (new MemberParameter)->registerInvite($request);
            $type = $data['type'];

            $typeId = (new Hashids($type, 6))->decode($data['typeId']);
            unset($data['type']);
            unset($data['typeId']);

            $member = $this->memberService->createByInvite($data);

            if ($member) {
                $member = $this->memberService->generateToken($member, $data['device']);

                // 訂閱電子報
                $newsletter = $newsletterService->findByEmail($member->email);
                $newsletterData = [
                    'email' => $member->email,
                    'member_id' => $member->id
                ];

                ($newsletter) ? $newsletterService->update($newsletter->id, $newsletterData) : $newsletterService->create($newsletterData);

                // 發信
                $this->memberService->sendRegisterEmail($member);

                return $this->success([
                    'member' => [
                        'id' => $member->id,
                        'token' => $member->token,
                        'email' => $member->email,
                        'name' => $member->name,
                        'avatar' => $member->avatar,
                        'countryCode' => $member->countryCode,
                        'cellphone' => $member->cellphone,
                        'country' => $member->country,
                        'gender' => $member->gender,
                        'zipcode' => $member->zipcode,
                        'address' => $member->address,
                        'openPlateform' => $member->openPlateform
                    ],
                    'hashId' => $this->encryptHashId($type, $typeId[0])
                ]);
            }

            return $this->failureCode('E0012');
        } catch (Exception $e) {
            return $this->failureCode('E0012');
        }
    }

    /**
     *  檢查是否已註冊會員 [加密手機碼]
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerCheck(Request $request)
    {
        try {
            $params = (new MemberParameter)->registerCheck($request);

            $phoneNumber = (new Hashids('PhoneNumber', 20))->decode($params['mobile']);
            if (!$phoneNumber) return $this->failureCode('E0301');

            // 確認手機格式
            $phoneNumber = $this->VerifyPhoneNumber($params['country'], $phoneNumber[0], $phoneNumber[1]);
            if (!$phoneNumber) return $this->failureCode('E0301');

            // 確認手機是否使用
            $member = $this->memberService->checkHasPhoneAndisRegistered($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone']);
            if ($member) {
                $isRegistered = true;
                $memberToken = (new Hashids('Member', 12))->encode([$member->id, time()]);
            }
            else {
                $isRegistered = false;
                $memberToken = '';
            }

            return $this->success([
                'isRegistered' => $isRegistered,
                'token' => $memberToken
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     *  檢查是否已註冊會員 [手機明碼]
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerCheck2(Request $request)
    {
        try {
            $phoneNumber = $request->phoneNumber;

            // 確認手機是否使用
            $member = $this->memberService->checkHasPhoneAndisRegistered($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone']);
            if ($member) {
                $isRegistered = true;
                $memberToken = (new Hashids('Member', 12))->encode([$member->id, time()]);
            }
            else {
                $isRegistered = false;
                $memberToken = '';
            }

            $phoneEncode = (new Hashids('PhoneNumber', 20))->encode([$phoneNumber['countryCode'], $phoneNumber['cellphone']]);

            return $this->success([
                'isRegistered' => $isRegistered,
                'token' => $memberToken,
                'mobile' => $phoneEncode
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    public function invitationInput(Request $request,InvitationService $invitationService)
    {
        $memberId = $request->memberId;
        $invitation = $request->invitation;

        //查詢被邀請碼會員
        $passiveMember = $this->memberService->invitationFind($invitation);
        $passiveMemberId = $passiveMember->id;
        if($passiveMember)
        {   
            $member = $this->memberService->find($memberId);
            $gifts = $invitationService->allPromoteGift();
            if (count($gifts)==0) return $this->failureCode('E0078');
            //新增送禮紀錄
            $invitationService->addRecord($gifts,$memberId,$passiveMemberId);

            return $this->success();

            //寄信
            // $this->memberService->sendRegisterEmail($member);
            // $this->memberService->sendInvitationInput($passiveMember);
        }else
        {
            return $this->failureCode('E0090');
        }
    }
}
