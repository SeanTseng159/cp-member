<?php

namespace App\Http\Controllers\Api;

use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\MemberService;
use App\Services\JWTTokenService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Log;

class MemberController extends RestLaravelController
{
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
        $data = $request->only([
            'countryCode',
            'cellphone',
            'openPlateform',
            'openId'
        ]);

        $validator = Validator::make($data, [
            'countryCode' => 'required|max:6',
            'cellphone' => 'required|alpha_num|max:12',
            'openPlateform' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failure('E0001', '傳送參數錯誤');
        }

        //驗證可否註冊
        if (!$this->memberService->canReRegister($data['countryCode'], $data['cellphone'])) {
            return $this->failure('A0030', '請15分鐘後再註冊');
        }

        if ($this->memberService->checkPhoneIsUse($data['countryCode'], $data['cellphone'])) {
            return $this->failure('A0031', '該手機號碼已使用');
        }

        $member = $this->memberService->create($data);

        if ($member && env('APP_ENV') === 'production') {
            return ($member) ? $this->success(['id' => $member->id]) : $this->failure('E0011', '建立會員失敗');
        }
        else {
            return ($member) ? $this->success(['id' => $member->id, 'validPhoneCode' => $member->validPhoneCode]) : $this->failure('E0011', '建立會員失敗');
        }
    }

    /**
     * 註冊-更新會員資料
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerMember(Request $request, $id)
    {
        $platform = $request->header('platform');
        $data = $request->except(['id']);
        $data['status'] = $data['isValidPhone'] = $data['isRegistered'] = 1;

        $member = $this->memberService->update($id, $data);

        $member = $this->memberService->generateToken($member, $platform);

        if ($member) {
            // 發信
            $this->memberService->sendValidateEmail($member->id);

            return $this->success([
                'id' => $member->id,
                'token' => $member->token,
                'name' => $member->name,
                'avatar' => $member->avatar
            ]);
        }
        else {
            $this->failure('E0012', '註冊失敗');
        }
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
                    'password',
                    '',
                    ''
                ]);
         $member = $this->memberService->update($id, $data);

         return ($member) ? $this->success($member) : $this->failure('E0003', '更新失敗');
     }

    /**
    * 刪除會員
    * @param Int $id
    * @return \Illuminate\Http\JsonResponse
    */
    public function deleteMember($id)
    {
        $member = $this->memberService->delete($id);

        return ($member) ? $this->success(['id' => $member->id]) : $this->failure('E0004', '刪除失敗');
    }

    /**
    * 驗證-手機驗證碼
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function validateCellphone(Request $request, $id)
    {
        $validPhoneCode = $request->input('validPhoneCode');

        $result = $this->memberService->validateCellphone($id, $validPhoneCode);

        return ($result) ? $this->success(['id' => $id]) : $this->failure('E0013', '電話驗證碼錯誤');
    }

    /**
    * 取所有會員
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function allMember(Request $request)
    {
        $members = $this->memberService->all();

        return $this->success($members);
    }

    /**
    * 會員資料查詢
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function queryMember(Request $request)
    {
        $data = $request->all();
        $member = $this->memberService->queryMember($data);

        return $this->success($member);
    }

    /**
    * 會員密碼修改
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function changePassword(Request $request, $id)
    {
        $data = $request->except([
            'oldpassword',
            'password'
        ]);

        $result = $this->memberService->changePassword($id, $data);

        return ($result) ? $this->success() : $this->failure('E0018', '密碼修改失敗');
    }

    /**
    * 會員密碼修改
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function sendValidateEmail(Request $request)
    {
        $id = $request->input('id');

        $result = $this->memberService->sendValidateEmail($id);

        return ($result) ? $this->success() : $this->failure('E0051', 'Email發送失敗');
    }

    /**
    * 驗證-Email驗證碼
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function validateEmail(Request $request, $id)
    {
        $validEmailCode = $request->input('validEmailCode');

        $result = $this->memberService->validateEmail($id, $validEmailCode);

        return ($result) ? $this->success() : $this->failure('E0014', 'Email驗證碼錯誤');
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
        $platform = $request->header('platform');

        $member = $this->memberService->findOnly($email, $password);
        if (!$member || $member->status == 0 || $member->isRegistered == 0) {
            return $this->failure('E0021','會員驗證失效');
        }

        $member = $this->memberService->generateToken($member, $platform);
        if (!$member) {
            return $this->failure('E0025','Token產生失敗');
        }

        return $this->success([
            'id' => $member->id,
            'token' => $member->token,
            'name' => $member->name,
            'avatar' => $member->avatar
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
        $platform = $request->header('platform');

        $token = $this->memberService->refreshToken($member, $platform);
        if (!$token) {
            return $this->apiRespFail('E0026', 'Token更新失敗');
        }

        return $this->success([
            'token' => $token
        ]);
    }
}
