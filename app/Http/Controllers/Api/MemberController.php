<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Http\Controllers\Api;

use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\MemberService;
use App\Services\JWTTokenService;
use App\Services\NewsletterService;
use App\Parameter\MemberParameter;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Crypt;
use Log;
use App\Traits\CryptHelper;
use Illuminate\Contracts\Encryption\DecryptException;

class MemberController extends RestLaravelController
{
    use CryptHelper;

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
        $data = (new MemberParameter)->create($request);

        //驗證可否註冊
        if (!$this->memberService->canReRegister($data['country'], $data['countryCode'], $data['cellphone'])) {
            return $this->failureCode('A0030');
        }

        $member = $this->memberService->findByCountryPhone($data['country'], $data['countryCode'], $data['cellphone']);

        $member = ($member) ? $this->memberService->update($member->id, $data) : $this->memberService->create($data);

        //傳送簡訊認證
        $this->memberService->sendRegisterSMS($member);
        return ($member) ? $this->success([
                                'id' => $member->id,
                                'validPhoneCode' => $member->validPhoneCode
                            ]) : $this->failureCode('E0011');
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
        $data['device'] = $platform ?: 'web';

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
        $data = (new MemberParameter)->update($request);

        $member = $this->memberService->update($id, $data);
        if (!$member) return $this->failureCode('E0003');

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

        return ($result) ? $this->success(['id' => $id]) : $this->failureCode('E0013');
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
            // 檢查是否是第三方登入
            if ($member->openPlateform != 'citypass') $member->email = $member->openId;
            $member->newsletter = $this->newsletterService->findByEmail($member->email);


            // 加入uber
            $platform = $request->header('platform', 'web');
            $uber = new \stdClass;
            $uber->status = env('START_UBER', false);
            $uber->img = ($platform === 'app') ? asset('img/uber_app_banner_2.jpg') : asset('img/uber_web_banner.jpg');
            $uber->description = '限高屏地區上或下車使用,期限至 ' . env('UBER_LIMIT_DATE');
            $uber->link = 'http://bit.ly/UBERKSD';
            $member->uber = $uber;
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
        $phoneNumber = $request->phoneNumber;

        if ($phoneNumber) {
            $member = $this->memberService->update($id, [
                    'countryCode' => $phoneNumber['countryCode'],
                    'cellphone' => $phoneNumber['cellphone'],
                    'country' => $phoneNumber['country']
                ]);
        }
        else {
            $member = $this->memberService->update($id, [
                    'validPhoneCode' => strval(mt_rand(100000, 999999))
                ]);
        }

        //傳送簡訊認證
        //$this->memberService->sendRegisterSMS($member);
        return ($member) ? $this->success(['id' => $member->id, 'validPhoneCode' => $member->validPhoneCode]) : $this->failureCode('E0052');
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
        $member = $request->member;

        if (!$member) $this->failureCode('E0021');

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

    /**
     * 刷新金鑰
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken();
        $platform = $request->header('platform');

        $jwtTokenService = new JWTTokenService;
        $tokenData = $jwtTokenService->checkToken($token);

        $member = $this->memberService->find($tokenData->id);
        if (!$member || $member->status == 0 || $member->isRegistered == 0) {
            return $this->failure('E0021','會員驗證失效');
        }

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
        $token = $request->bearerToken();
        $platform = $request->header('platform');

        $jwtTokenService = new JWTTokenService;
        $tokenData = $jwtTokenService->checkToken($token);

        $member = $this->memberService->find($tokenData->id);
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
            'email' => $member->openId,
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
    
    public function thirdPartyLogin(Request $request)
    {
        $inputs = $request->only('openId', 'openPlateform', 'name');
        $member = $this->memberService->findByOpenId($inputs['openId'], $inputs['openPlateform']);
        $platform = $request->header('platform');
        $isFirstLogin = false;
        
        if ( ! in_array($inputs['openPlateform'], ['facebook', 'google'])) {
            return $this->failure('E0021','會員驗證失效');
        }
        
        if (empty($member)) {
            $data = [
                'isValidEmail' => 1,
                'status' => 1,
                'isRegistered' => 1,
            ];
            $inputs = array_merge($data, $inputs);
            $member = $this->memberService->create($inputs);
            $isFirstLogin = true;
        }
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
            'isFirstLogin' => $isFirstLogin,
            'openPlateform' => $inputs['openPlateform'],
        ]);
    }
}
