<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Http\Controllers\Api;

use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\MemberService;
use App\Services\NewsletterService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Crypt;
use Log;
use Illuminate\Contracts\Encryption\DecryptException;

class MemberController extends RestLaravelController
{
    protected $memberService;
    protected $newsletterService;

    public function __construct(MemberService $memberService, NewsletterService $newsletterService)
    {
        $this->memberService = $memberService;
        $this->newsletterService = $newsletterService;
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
            'openId',
            'country'
        ]);

        $validator = Validator::make($data, [
            'countryCode' => 'required|max:6',
            'cellphone' => 'required|alpha_num|max:12',
            'openPlateform' => 'required',
            'country' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->failure('E0001', '傳送參數錯誤');
        }

        $country = strtoupper($data['country']);

        try {
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneUtil->parse($data['countryCode'] . $data['cellphone'], $country);

            $countryCode = $data['countryCode'] = $phoneNumber->getCountryCode();
            $cellphone = $data['cellphone'] = $phoneNumber->getNationalNumber();
            $intlNumber = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);

            $isValid = $phoneUtil->isValidNumber($phoneNumber);
            $getNumberType = $phoneUtil->getNumberType($phoneNumber);

            if (!$phoneUtil->isValidNumber($phoneNumber) || $phoneUtil->getNumberType($phoneNumber) != 1) {
                Log::error('不是手機格式');
                return $this->failure('E0301', '手機格式錯誤');
            }
        } catch (\libphonenumber\NumberParseException $e) {
            Log::debug($e);
            return $this->failure('E0301', '手機格式錯誤');
        }

        //確認手機是否使用
        if ($this->memberService->checkPhoneIsUse($data['country'], $countryCode, $cellphone)) {
            return $this->failure('A0031', '該手機號碼已使用');
        }

        //驗證可否註冊
        if (!$this->memberService->canReRegister($data['country'], $countryCode, $cellphone)) {
            return $this->failure('A0030', '請15分鐘後再註冊');
        }

        $member = $this->memberService->checkHasPhoneAndNotRegistered($data['country'], $countryCode, $cellphone);

        $member = ($member) ? $this->memberService->update($member->id, $data) : $this->memberService->create($data);

        //傳送簡訊認證
        $this->memberService->sendSMS($member);
        return ($member) ? $this->success(['id' => $member->id, 'validPhoneCode' => $member->validPhoneCode]) : $this->failure('E0011', '建立會員失敗');
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

        // 檢查Email是否使用
        $result = $this->memberService->checkEmailIsUse($data['email']);
        if ($result) return $this->failure('A0032', '該Email已使用');

        $member = $this->memberService->update($id, $data);

        if ($member) {
            $member = $this->memberService->generateToken($member, $platform);

            // 訂閱電子報
            $newsletter = $this->newsletterService->findByEmail($member->email);
            $newsletterData = [
                'email' => $member->email,
                'member_id' => $member->id
            ];

            ($newsletter) ? $this->newsletterService->update($newsletter->id, $newsletterData) : $this->newsletterService->create($newsletterData);

            // 發信
            $this->memberService->sendRegisterEmail($member);

            return $this->success([
                'id' => $member->id,
                'token' => $member->token,
                'name' => $member->name,
                'avatar' => $member->avatar
            ]);
        }
        else {
            return $this->failure('E0012', '註冊失敗');
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
                    'email',
                    'newsletter'
                ]);

        $countryCode = $request->input('countryCode');
        $cellphone = $request->input('cellphone');
        $country = $request->input('country');

        if ($countryCode && $cellphone && $country) {
            try {
                $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                $phoneNumber = $phoneUtil->parse($countryCode . $cellphone, strtoupper($country));

                $countryCode = $data['countryCode'] = $phoneNumber->getCountryCode();
                $cellphone = $data['cellphone'] = $phoneNumber->getNationalNumber();
                $intlNumber = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);

                $isValid = $phoneUtil->isValidNumber($phoneNumber);
                $getNumberType = $phoneUtil->getNumberType($phoneNumber);

                if (!$phoneUtil->isValidNumber($phoneNumber) || $phoneUtil->getNumberType($phoneNumber) != 1) {
                    Log::error('不是手機格式');
                    return $this->failure('E0301', '手機格式錯誤');
                }
            } catch (\libphonenumber\NumberParseException $e) {
                Log::debug($e);
                return $this->failure('E0301', '手機格式錯誤');
            }

            //確認手機是否使用
            if ($this->memberService->checkPhoneIsUse($country, $countryCode, $cellphone)) {
                return $this->failure('A0031', '該手機號碼已使用');
            }
        }

        $member = $this->memberService->update($id, $data);
        if (!$member) return $this->failure('E0003', '更新失敗');

        $member->newsletter = $this->newsletterService->findByEmail($member->email);

        // 更新訂閱電子報
        $postNewsletter = $request->input('newsletter');

        if (isset($postNewsletter['status'])) {
            $newsletterData = [
                'member_id' => $member->id,
                'schedule' => (isset($postNewsletter['schedule'])) ? $postNewsletter['schedule'] : 0,
                'status' => $postNewsletter['status'],
                'memo' => (isset($postNewsletter['memo'])) ? $postNewsletter['memo'] : ''
            ];

            if ($member->newsletter) {
                $newsletter = $this->newsletterService->update($member->newsletter->id, $newsletterData);
            }
            else {
                $newsletterData['email'] = $member->email;
                $newsletter = $this->newsletterService->create($newsletterData);
            }

            $member->newsletter = $newsletter;
        }

        return $this->success($member);
     }

    /**
    * 刪除會員
    * @param Int $id
    * @return \Illuminate\Http\JsonResponse
    */
    public function deleteMember($id)
    {
        $member = $this->memberService->delete($id);

        return ($member) ? $this->success(['id' => $member]) : $this->failure('E0004', '刪除失敗');
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
    * 驗證-手機驗證碼
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');

        $result = $this->memberService->checkEmailIsUse($email);

        return (!$result) ? $this->success() : $this->failure('A0032', '該Email已使用');
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
    * 單一會員資料查詢
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function singleMember(Request $request, $id)
    {
        $member = $this->memberService->find($id);

        if ($member) {
            $member->newsletter = $this->newsletterService->findByEmail($member->email);
        }

        return $this->success($member);
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
        $data = $request->only([
            'oldpassword',
            'password'
        ]);

        $result = $this->memberService->changePassword($id, $data);

        return ($result) ? $this->success() : $this->failure('E0018', '密碼修改失敗，請確認舊密碼是否正確。');
    }

    /**
    * 發送忘記密碼信
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function sendForgetPassword(Request $request)
    {
        $email = $request->input('email');

        $result = $this->memberService->sendForgetPassword($email);

        return ($result) ? $this->success() : $this->failure('E0061', '會員不存在');
    }

    /**
    * 忘記密碼-修改密碼
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function resetPassword(Request $request)
    {
        $key = $request->input('key');
        $password = $request->input('password');

        try {
            $key = Crypt::decrypt($key);
            $keyAry = explode('_', $key);
            $email = $keyAry[0];
            $expires = $keyAry[1];
        } catch (DecryptException $e) {
            return $this->failure('E0001', '傳送參數錯誤');
        }

        $result = $this->memberService->validateResetPasswordKey($expires);

        if (!$result) return $this->failure('A0033', '超過可修改時間，請重新操作');

        $member = $this->memberService->findByEmail($email);

        if (!$member || $member->isRegistered == 0) return $this->failure('E0021', '會員驗證失敗');

        $result = $this->memberService->update($member->id, ['password' => $password]);

        return ($result) ? $this->success() : $this->failure('E0018', '密碼修改失敗');
    }

    /**
    * 發送手機驗證碼
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function sendValidPhoneCode(Request $request)
    {
        $id = $request->input('id');
        $countryCode = $request->input('countryCode');
        $cellphone = $request->input('cellphone');
        $country = $request->input('country');

        if ($countryCode && $cellphone && $country) {
            try {
                $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                $phoneNumber = $phoneUtil->parse($countryCode . $cellphone, strtoupper($country));

                $countryCode = $phoneNumber->getCountryCode();
                $cellphone = $phoneNumber->getNationalNumber();
                $intlNumber = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);

                $isValid = $phoneUtil->isValidNumber($phoneNumber);
                $getNumberType = $phoneUtil->getNumberType($phoneNumber);

                if (!$phoneUtil->isValidNumber($phoneNumber) || $phoneUtil->getNumberType($phoneNumber) != 1) {
                    Log::error('不是手機格式');
                    return $this->failure('E0301', '手機格式錯誤');
                }
            } catch (\libphonenumber\NumberParseException $e) {
                Log::debug($e);
                return $this->failure('E0301', '手機格式錯誤');
            }

            //確認手機是否使用
            if ($this->memberService->checkPhoneIsUse($country, $countryCode, $cellphone)) {
                return $this->failure('A0031', '該手機號碼已使用');
            }

            $member = $this->memberService->update($id, [
                    'countryCode' => $countryCode,
                    'cellphone' => $cellphone,
                    'country' => $country
                ]);
        }
        else {
            $member = $this->memberService->update($id, [
                    'validPhoneCode' => strval(mt_rand(100000, 999999))
                ]);
        }

        //傳送簡訊認證
        $this->memberService->sendSMS($member);
        return ($member) ? $this->success(['id' => $member->id, 'validPhoneCode' => $member->validPhoneCode]) : $this->failure('E0052', '簡訊發送失敗');
    }

    /**
    * 發送Email驗證信
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function sendValidateEmail(Request $request)
    {
        $id = $request->input('id');

        $result = $this->memberService->sendValidateEmail($id);

        return ($result) ? $this->success(['id' => $id]) : $this->failure('E0051', 'Email發送失敗');
    }

    /**
    * 驗證-Email驗證碼
    * @paramRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function validateEmail(Request $request)
    {
        $validEmailCode = $request->input('validEmailCode');

        $result = $this->memberService->validateEmail($validEmailCode);

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
            'email' => $member->email,
            'name' => $member->name,
            'avatar' => $member->avatar,
            'countryCode' => $member->countryCode,
            'cellphone' => $member->cellphone,
            'country' => $member->country,
            'gender' => $member->gender,
            'zipcode' => $member->zipcode,
            'address' => $member->address
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

    /**
     * 第三方登入
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function oauthLogin(Request $request)
    {
        $token = $request->input('token');
        $result = $this->memberService->checkOpenIdToken($token);

        if ($result) {
            try {
                $token = Crypt::decrypt($token);
                $tokenAry = explode('_', $token);
                $openId = $tokenAry[0];
                $openPlateform = $tokenAry[1];

                $member = $this->memberService->findByOpenId($openId, $openPlateform);

                if (!$member || $member->status == 0 || $member->isRegistered == 0) {
                    return $this->failure('E0021','會員驗證失效');
                }

                $member = $this->memberService->generateToken($member, 'web');
                if (!$member) {
                    return $this->failure('E0025','Token產生失敗');
                }

                return $this->success([
                    'id' => $member->id,
                    'token' => $member->token,
                    'email' => $member->openId,
                    'name' => $member->name,
                    'avatar' => $member->avatar,
                    'countryCode' => $member->countryCode,
                    'cellphone' => $member->cellphone,
                    'country' => $member->country,
                    'gender' => $member->gender,
                    'zipcode' => $member->zipcode,
                    'address' => $member->address
                ]);
            } catch (DecryptException $e) {
                return $this->failure('E0025','Token產生失敗');
            }
        }

        return $this->failure('E0025','Token產生失敗');
    }
}
