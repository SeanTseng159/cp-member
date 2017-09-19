<?php

namespace App\Services;

use App\Repositories\MemberRepository;
use App\Services\JWTTokenService;
use Ksd\SMS\Services\EasyGoService;
use Illuminate\Support\Facades\Hash;
use Carbon;

class MemberService
{
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
        //$data = $request->all();
        return $this->repository->query($data);
    }


    /**
     * 依據email,查詢使用者認証
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
    public function checkPhoneIsUse($countryCode, $cellphone)
    {
        $member = $this->repository->findByPhone($countryCode, $cellphone);
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
    public function checkHasPhoneAndNotRegistered($countryCode, $cellphone)
    {
        return $this->repository->query([
            'countryCode' => $countryCode,
            'cellphone' => $cellphone,
            'isRegistered' => 0
        ]);
    }

    /**
     * 確認是否可重新註冊
     * @param $countryCode
     * @param $cellphone
     * @return bool
     */
    public function canReRegister($countryCode, $cellphone)
    {
        $member = $this->repository->findByPhone($countryCode, $cellphone);

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

            return ($minutes < 10 && $member->validPhoneCode == $validPhoneCode);
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
     * 寄送Email驗證信
     * @param $id
     * @param $data
     * @return bool
     */
    public function sendValidateEmail($id)
    {
        $member = $this->repository->find($id);

        if ($member && $member->isValidEmail == 0) {
            //未實作寄信
            //記得要做

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
    public function validateEmail($id, $validEmailCode)
    {
        $member = $this->repository->find($id);

        if ($member) {
            try {
                $cellphone = Crypt::decrypt($validEmailCode);
                if ($member->cellphone == $cellphone) {
                    $result = $this->update($member->id, [
                        'isValidEmail' => 1
                    ]);

                    return ($result);
                }

            } catch (DecryptException $e) {
                return false;
            }
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
        if ($member && env('APP_ENV') === 'production') {
            //發送簡訊
            $easyGoService = new EasyGoService;
            $phoneNumber = $member->countryCode . $member->cellphone;
            $message = 'CityPass驗證碼： ' . $member->validPhoneCode;

            return $easyGoService->send($phoneNumber, $message);
        }

        return false;
    }
}
