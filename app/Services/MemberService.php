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
use App\Traits\CryptHelper;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendValidateEmail;
use App\Jobs\SendRegisterMail;
use App\Jobs\SendForgetPasswordMail;
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
    public function generateToken($member, $platform)
    {
        $jwtTokenService = new JWTTokenService;
        $token = $jwtTokenService->generateToken($member, $platform);
        $result = $this->update($member->id, ['token' => $token]);

        return ($result) ? $result : null;
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
        $member = $this->repository->findByCountryPhone($country, $countryCode, $cellphone);
        if ($member) {
            return ($member->isRegistered == 1);
        }
        return false;
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
        if ($member && $member->isRegistered == 1 && $member->isValidEmail == 0) {
            $job = (new SendRegisterMail($member))->delay(5);
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
    public function sendSMS($member)
    {
        if ($member) {
            //發送簡訊
            $easyGoService = new EasyGoService;
            $phoneNumber = $member->countryCode . $member->cellphone;
            $message = 'CityPass驗證碼： ' . $member->validPhoneCode;

            try {
                return (env('APP_ENV') === 'production') ? $easyGoService->send($phoneNumber, $message) : true;
                // return $easyGoService->send($phoneNumber, $message);
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
}
