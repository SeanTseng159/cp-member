<?php

namespace App\Http\Controllers\Api;

use App\Services\MemberService;
use App\Services\JWTTokenService;
use Ksd\SMS\Services\EasyGoService;
use App\Traits\ApiResponseHelper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
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
            return $this->apiRespFail('1111', '傳送資料錯誤');
        }

        //驗證可否註冊
        if (!$this->memberService->canReRegister($data['countryCode'], $data['cellphone'])) {
            return $this->apiRespFail('1111', '請15分鐘後再註冊');
        }

        if ($this->memberService->checkPhoneIsUse($data['countryCode'], $data['cellphone'])) {
            return $this->apiRespFail('1111', '該手機號碼已使用');
        }
        
        $member = $this->memberService->create($data);
        //新增成功
        if ($member) {
            $easyGoService = new EasyGoService;
            $phoneNumber = $data['countryCode'] . $data['cellphone'];
            $message = 'CityPass驗證碼： ' . $member->active_code;
            $easyGoService->send($phoneNumber, $message);

            return $this->apiRespSuccess([
                'id' => $member->id
            ]);
        }

        return $this->apiRespFail('1111', '新增失敗');
    }

    /**
     * 註冊-更新會員資料
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
     public function registerMember(Request $request, $id)
     {
         $data = $request->except([
                    'id'
                ]);
         $data['status'] = $data['is_registered'] = 1;
         $member = $this->memberService->update($id, $data);
 
         return ($member) ? $this->apiRespSuccess($member) : $this->apiRespFail('1111', '註冊失敗');
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
                    'password'
                ]);
         $member = $this->memberService->update($id, $data);
 
         return ($member) ? $this->apiRespSuccess($member) : $this->apiRespFail('1111', '更新失敗');
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
    * 驗證-手機驗證碼
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function validateCellphone(Request $request)
    {
        $id = $request->input('id');
        $active_code = $request->input('active_code');

        $result = $this->memberService->validateCellphone($id, $active_code);
 
        return ($result) ? $this->apiRespSuccess([
        'id' => $id
        ]) : $this->apiRespFail('1111', '驗證碼錯誤');
    }

    /**
    * 取所有會員
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function allMember(Request $request)
    {
        $members = $this->memberService->all();
 
        return $this->apiRespSuccess($members);
    }

    /**
    * 會員資料查詢
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function queryMember(Request $request)
    {
        $data = $request->all();
        $member = $this->memberService->query($data);
 
        return $this->apiRespSuccess($member);
    }

    /**
    * 會員密碼修改
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function changePassword(Request $request)
    {
        $data = $request->except([
            'id',
            'oldpassword',
            'password'
        ]);

        $result = $this->memberService->changePassword($data);
 
        return ($result) ? $this->apiRespSuccess([]) : $this->apiRespFail('A01005','密碼修改失敗');
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
