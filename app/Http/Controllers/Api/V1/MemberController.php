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
use App\Parameter\MemberParameter;

class MemberController extends RestLaravelController
{
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
    public function registerByDiningCar(Request $request, NewsletterService $newsletterService)
    {
        try {
            $data = (new MemberParameter)->registerByDiningCar($request);

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
                ]);
            }

            return $this->failureCode('E0012');
        } catch (Exception $e) {
            return $this->failureCode('E0012');
        }
    }
}
