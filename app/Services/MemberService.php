<?php

/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Services;

use App\Repositories\MemberRepository;
use App\Services\JWTTokenService;
use Ksd\SMS\Services\EasyGoService;
use Illuminate\Support\Facades\Hash;
use Crypt;
use Carbon;
use Log;
use GuzzleHttp\Client;
use App\Traits\CryptHelper;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendValidateEmail;
use App\Jobs\SendRegisterMail;
use App\Jobs\SendRegisterCompleteMail;
use App\Jobs\SendForgetPasswordMail;
use App\Jobs\FindFriendInvitationMail;
use App\Jobs\InvitationInputMail;
use Illuminate\Contracts\Encryption\DecryptException;

class MemberService
{
    use DispatchesJobs;
    use CryptHelper;

    protected $repository;

    public function __construct(MemberRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 新增會員
     * @param $data
     * @return \App\Models\Member
     */
    public function create($data = [])
    {
        $member = $this->repository->create($data);

        return $member;
    }

    /**
     * 更新會員資料
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * 新增會員 by 邀請
     * @param $data
     * @return \App\Models\Member
     */
    public function createByInvite($data = [])
    {
        return $this->repository->createByInvite($data);
    }

    /**
     * 刪除會員
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    /**
     * 取得所有會員
     * @return mixed
     */
    public function all()
    {
        return $this->repository->all();
    }

    /**
     * 會員資料查詢
     * @param $data
     * @return mixed
     */
    public function queryMember($data)
    {
        return $this->repository->query($data);
    }

    /**
     * 依據id,查詢使用者
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $member = $this->repository->find($id);

        if ($member) {
            // 移除不必要的欄位
            unset($member->password);
            unset($member->validPhoneCode);
            unset($member->validEmailCode);
        }

        return $member;
    }

    /**
     * 依據email,查詢使用者
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * 建立token
     * @param $member
     * @param $platform
     * @return mixed
     */
    public function generateToken($member, $platform = 'web')
    {
        $jwtTokenService = new JWTTokenService;
        $token = $jwtTokenService->generateToken($member, $platform);
        if ($platform === 'app') {
            $member = $this->update($member->id, ['token' => $token]);
        } else {
            $member->token = $token;
        }

        return ($member) ? $member : null;
    }

    /**
     * 刷新 token
     * @param $member
     * @param $platform
     * @return mixed
     */
    public function refreshToken($member, $platform)
    {
        $jwtTokenService = new JWTTokenService;
        $token = $jwtTokenService->refreshToken($member, $platform);

        if ($token) {
            $result = $this->update($member->id, [
                'token' => $token
            ]);

            return ($result) ? $token : null;
        }

        return null;
    }

    /**
     * 依據帳號跟密碼,查詢唯一使用者認証
     * @param $email
     * @param $password
     * @return mixed
     */
    public function findOnly($email, $password)
    {
        $member = $this->findByEmail($email);
        if ($member && Hash::check($password, $member->password)) {
            return $member;
        }

        return null;
    }

    /**
     * 驗證密碼跟帳號
     * @param $email
     * @param $password
     * @return bool
     */
    public function valid($email, $password)
    {
        return ($this->findOnly($email, $password));
    }

    /**
     * 根據token取得使用者認證
     * @param $token
     * @return mixed
     */
    public function findByToken($token)
    {
        return $this->repository->findByToken($token);
    }

    /**
     * 確認Email是否被是否被使用
     * @param $email
     * @return bool
     */
    public function checkEmailIsUse($email)
    {
        $member = $this->repository->findByEmail($email);
        return ($member);
    }

    /**
     * 確認手機號碼是否被是否被使用
     * @param $countryCode
     * @param $cellphone
     * @return bool
     */
    public function checkPhoneIsUse($country, $countryCode, $cellphone)
    {
        $member = $this->repository->findValidByCountryPhone($country, $countryCode, $cellphone);
        return ($member);
    }

    /**
     * 確認手機號碼是否已驗證
     * @param $countryCode
     * @param $cellphone
     * @return bool
     */
    public function checkPhoneIsValid($country, $countryCode, $cellphone)
    {
        $member = $this->repository->findByCountryPhone($country, $countryCode, $cellphone);
        return ($member && $member->isValidPhone == 1);
    }

    /**
     * 確認手機號碼是否已驗證
     * @param $countryCode
     * @param $cellphone
     * @return bool
     */
    public function checkPhoneIsValidById($id)
    {
        $member = $this->repository->find($id);
        return ($member && $member->isValidPhone == 1);
    }

    /**
     * 確認身分證/護照是否被是否被使用
     * @param $countryCode
     * @param $cellphone
     * @return bool
     */
    public function checkSocialIdIsUse($socialId)
    {
        $member = $this->repository->findBySocialId($socialId);
        return ($member);
    }

    /**
     * 確認手機號碼是否在資料庫,但未註冊完成
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function checkHasPhoneAndNotRegistered($country, $countryCode, $cellphone)
    {
        $member = $this->repository->findByCountryPhone($country, $countryCode, $cellphone);
        return ($member && $member->isRegistered == 0) ? $member : null;
    }

    /**
     * 確認手機號碼是否在資料庫,但未註冊完成
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function checkHasPhoneAndisRegistered($country, $countryCode, $cellphone)
    {
        $member = $this->repository->findByCountryPhone($country, $countryCode, $cellphone);
        return ($member && $member->isRegistered == 1) ? $member : null;
    }

    /**
     * 確認是否可重新註冊
     * @param $countryCode
     * @param $cellphone
     * @return bool
     */
    public function canReRegister($country, $countryCode, $cellphone)
    {
        $member = $this->repository->findByCountryPhone($country, $countryCode, $cellphone);

        if ($member) {
            $now = Carbon\Carbon::now()->timestamp;
            $updated_at = strtotime($member->updated_at);
            $minutes = round(abs($updated_at - $now) / 60);

            return ($minutes > 15 && $member->isRegistered == 0);
        }

        return true;
    }

    /**
     * 驗證-手機驗證碼
     * @param $id
     * @param $validPhoneCode
     * @return bool
     */
    public function validateCellphone($id, $validPhoneCode)
    {
        $member = $this->repository->find($id);
        if ($member) {
            if ($member->isValidPhone === 1) return true;

            $now = Carbon\Carbon::now()->timestamp;
            $updated_at = strtotime($member->updated_at);
            $minutes = round(abs($updated_at - $now) / 60);

            $result = ($minutes < 10 && $member->validPhoneCode == $validPhoneCode);

            if ($result) {
                $this->update($member->id, [
                    'isValidPhone' => 1
                ]);
            }

            return $result;
        }

        return false;
    }

    /**
     * 會員密碼修改
     * @param $id
     * @param $data
     * @return bool
     */
    public function changePassword($id, $data)
    {
        $member = $this->repository->find($id);
        if ($member && Hash::check($data['oldpassword'], $member->password)) {
            $result = $this->update($member->id, [
                'password' => $data['password']
            ]);

            return ($result);
        }

        return false;
    }

    /**
     * 發送忘記密碼信
     * @param $email
     * @return bool
     */
    public function sendForgetPassword($email)
    {
        $member = $this->repository->findByEmail($email);

        if ($member && $member->isRegistered == 1) {
            $job = (new SendForgetPasswordMail($member))->delay(5);
            $this->dispatch($job);

            return true;
        }

        return false;
    }

    /**
     * 邀請好友後獲得的獲得禮物信件
     * @param $member
     * @param $parameter
     * @return bool
     */
    public function findFriendInvitation($member, $parameter)
    {
        if ($member && $member->isRegistered == 1) {
            $job = (new FindFriendInvitationMail($member, $parameter))->delay(5);
            $this->dispatch($job);

            return true;
        }

        return false;
    }

    /**
     * 填寫邀請碼後獲得的禮物信件
     * @param $member
     * @param $parameter
     * @return bool
     */
    public function invitationInput($member, $parameter)
    {
        if ($member && $member->isRegistered == 1) {
            $job = (new InvitationInputMail($member, $parameter))->delay(5);
            $this->dispatch($job);

            return true;
        }

        return false;
    }

    /**
     * 驗證-重設密碼key
     * @param $email
     * @param $expires
     * @return bool
     */
    public function validateResetPasswordKey($expires)
    {
        $now = Carbon\Carbon::now()->timestamp;

        return ($now < $expires);
    }

    /**
     * 寄送Email註冊成功
     * @param $id
     * @param $data
     * @return bool
     */
    public function sendRegisterEmail($member)
    {
        if (!$member) return false;

        if ($member->isRegistered == 1 && $member->isValidEmail == 0) {
            //寄送註冊信
            $job = (new SendRegisterMail($member))->delay(5);
            $this->dispatch($job);

            return true;
        } elseif ($member->isRegistered == 1 && $member->isValidEmail == 1) {
            //寄送優惠券
            $job = (new SendRegisterCompleteMail($member))->delay(5);
            $this->dispatch($job);

            return true;
        }

        return false;
    }

    /**
     * 寄送Email驗證信
     * @param $id
     * @param $data
     * @return bool
     */
    public function sendValidateEmail($id)
    {
        $member = $this->repository->find($id);

        if ($member && $member->isValidEmail == 0) {
            // 重新產生驗證碼
            $member->validEmailCode = Crypt::encrypt($member->email);
            $member->save();

            $job = (new SendValidateEmail($member))->delay(5);
            $this->dispatch($job);

            return true;
        }

        return false;
    }

    /**
     * 驗證-Email驗證碼
     * @param $id
     * @param $validEmailCode
     * @return bool
     */
    public function validateEmail($validEmailCode)
    {
        try {
            $email = Crypt::decrypt($validEmailCode);
        } catch (DecryptException $e) {
            return false;
        }

        $member = $this->repository->findByEmail($email);

        if ($member) {
            $result = $this->update($member->id, [
                'isValidEmail' => 1
            ]);

            return ($result);
        }

        return false;
    }

    /**
     * 發送手機驗證簡訊
     * @param $id
     * @param $member
     * @return mixed
     */
    public function sendRegisterSMS($member)
    {
        if ($member) {
            //發送簡訊
            $easyGoService = new EasyGoService;
            $phoneNumber = $member->countryCode . $member->cellphone;
            if ($member->countryCode !== '886') $phoneNumber = '+' . $phoneNumber;
            $message = '您的 「CityPass都會通」 手機驗證碼：' . $member->validPhoneCode . '。 (注意：此驗證碼10分鐘內有效)';

            try {
                return $easyGoService->send($phoneNumber, $message);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        return false;
    }

    /**
     * 檢查Token
     * @param $id
     * @param $member
     * @return mixed
     */
    public function checkToken($token = '', $platform = '')
    {
        if (empty($token)) return false;

        try {
            $tokenData = (new JWTTokenService)->checkToken($token);
            if (!$tokenData) return false;

            //來源為app, 需檢查DB裡的token
            $member = $this->repository->find($tokenData->id);
            if (!$member) return false;
            if (!$member->status || !$member->isRegistered) return false;
            if ($platform === 'app' && $member->token != $token) return false;
        } catch (Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * 依據手機,查詢使用者(增加國家代碼)
     * @param $country
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findByCountryPhone($country, $countryCode, $cellphone)
    {
        return $this->repository->findByCountryPhone($country, $countryCode, $cellphone);
    }

    /**
     * 依據邀請碼,查詢使用者
     * @param $invitation
     * @return mixed
     */
    public function findByInvitation($invitation)
    {
        return $this->repository->findByInvitation($invitation);
    }

    /**
     * 查詢已驗證手機的使用者
     * @param $country
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findValidByCountryPhone($country, $countryCode, $cellphone)
    {
        return $this->repository->findValidByCountryPhone($country, $countryCode, $cellphone);
    }

    /**
     * 產生第三方登入Token
     * @param $data
     * @return mixed
     */
    public function generateOpenIdToken($member, $platform)
    {
        $jwtTokenService = new JWTTokenService;
        $token = $jwtTokenService->generateOpenIdToken($member, $platform);
        $result = $this->update($member->id, ['token' => $token]);

        return ($result) ? $token : '';
    }

    /**
     * 用OpenId找會員
     * @param $data
     * @return mixed
     */
    public function findByOpenId($openId, $openPlateform)
    {
        $member = $this->repository->findByOpenId($openId, $openPlateform);
        if ($member) $member->email = $member->openId;

        return $member;
    }

    public function verifyThirdPartLoginToken($token, $inputs)
    {
        switch ($inputs['openPlateform']) {
            case 'google':
                return $this->verifyGoogleLogin($token, $inputs['openId']);
                break;
            case 'facebook':
                return $this->verifyFacebookLogin($token, $inputs['openId']);
                break;
            default:
                return false;
        }
    }

    private function verifyGoogleLogin($token, $openId)
    {
        try {
            $url = 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=%s';
            $client = new Client();
            $response = $client->get(sprintf($url, $token));

            $body = json_decode($response->getBody());
            return isset($body->aud) ? (in_array($body->aud, explode(',', config('social.google.web_client_id'))) && $openId == $body->email) : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function verifyFacebookLogin($input_token, $openId)
    {
        try {
            $url = 'https://graph.facebook.com/v2.6/debug_token?input_token=%s&access_token=%s';
            $access_token = config('social.facebook.app_id') . '|' . config('social.facebook.app_secert');
            $client = new Client();
            $response = $client->get(sprintf($url, $input_token, $access_token));
            $response_array = json_decode($response->getBody(), true);

            return isset($response_array['data']['app_id']) ? $response_array['data']['app_id'] == config('social.facebook.app_id') : false;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function getDiningCarGift()
    {
        return $this->repository->getDiningCarGift();
    }

    public function invitationFind($code = null)
    {
        return $this->repository->invitationFind($code);
    }


    //創建會員時，自動產生邀請碼
    public function createInviteCode($id)
    {
        $invite = $this->repository->createInviteCode($id);

        return $invite;
    }

    /**
     * 會員登出
     * @param $id
     * @param $data
     * @return mixed
     */
    public function logout($member)
    {
        return $this->repository->logoutById($member->id, [
            'token' => null
        ]);
    }
}
