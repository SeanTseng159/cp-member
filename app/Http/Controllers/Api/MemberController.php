<?php

namespace App\Http\Controllers\Api;

use App\Services\MemberService;
use App\Services\JWTTokenService;
use App\Traits\ApiResponseHelper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;

class MemberController extends Controller
{
    use ApiResponseHelper;

    private $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * 建立會員
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMember(Request $request)
    {
        $data = $request->all();
        $member = $this->memberService->create($data);

        return ($member) ? $this->apiRespSuccess([
            'id' => $member->id
        ]) : $this->apiRespFail('1111', 'SQL ERROR');
    }

    /**
     * 更新會員資料
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
     public function updateMember(Request $request, $id)
     {
         $data = $request->except([
                    'id',
                    'email',
                    'password'
                ]);
         $member = $this->memberService->update($id, $data);
 
         return ($member) ? $this->apiRespSuccess($member) : $this->apiRespFail('1111', 'SQL ERROR');
     }

     /**
     * 刪除會員
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
     public function deleteMember($id)
     {
         $member = $this->memberService->delete($id);
 
         return ($member) ? $this->apiRespSuccess([
            'id' => $id
         ]) : $this->apiRespFail('1111', 'SQL ERROR');
     }


    /**
     * 建立金鑰
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $member = $this->memberService->findOnly($email, $password);
        if (!$member) {
            return $this->apiRespFail('A01006','會員驗證失效');
        }

        $token = $this->memberService->generateToken($member);
        if (!$token) {
            return $this->apiRespFail('A01005','token產生失敗');
        }

        return $this->apiRespSuccess([
            'token' => $token
        ]);
    }

    /**
     * 刷新金鑰
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken();

        $member = $this->memberService->findByToken($token);
        if (!$member) {
            return $this->apiRespFail('A01004', '無法驗證金鑰');
        }

        $token = $this->memberService->refreshToken($member);
        if (!$token) {
            return $this->apiRespFail('A01002', '無法驗證token');
        }

        return $this->apiRespSuccess([
            'token' => $token
        ]);
    }
}
