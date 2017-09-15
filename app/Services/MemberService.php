<?php

namespace App\Services;

use App\Repositories\MemberRepository;
use App\Services\JWTTokenService;
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
        return $this->repository->create($data);
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
        $data = $request->all();
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
     * @return mixed
     */
    public function generateToken($member)
    {
        $jwtTokenService = new JWTTokenService;
        $token = $jwtTokenService->generateToken($member);
        $result = $this->update($member->id, [
            'token' => $token
        ]);

        return ($result) ? $token : null;
    }

    /**
     * 刷新 token
     * @param $member
     * @return mixed
     */
     public function refreshToken($member)
     {
        $jwtTokenService = new JWTTokenService;
        $token = $jwtTokenService->refreshToken($member);

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
     * 確認手機號碼是否被是否被使用
     * @param $countryCode
     * @param $cellphone
     * @return bool
     */
    public function checkPhoneIsUse($countryCode, $cellphone)
    {
        $member = $this->repository->findByPhone($countryCode, $cellphone);
        if ($member) {
            return ($member->is_registered == 1);
        }
        return false;
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

            return ($minutes > 15 && $member->is_registered == 0);
        }

        return true;
    }

    /**
     * 驗證-手機驗證碼
     * @param $id
     * @param $active_code
     * @return bool
     */
    public function validateCellphone($id, $active_code)
    {
        $member = $this->repository->find($id);
        if ($member) {
            $now = Carbon\Carbon::now()->timestamp;
            $updated_at = strtotime($member->updated_at);
            $minutes = round(abs($updated_at - $now) / 60);

            return ($minutes < 10 && $member->active_code == $active_code);
        }

        return false;
    }

    /**
     * 會員密碼修改
     * @param $data
     * @return bool
     */
     public function changePassword($data)
     {
        $member = $this->repository->find($data['id']);
        if ($member && Hash::check($data['oldpassword'], $member->password)) {
            $result = $this->update($member->id, [
                'password' => $data['password']
            ]);

            return ($result);
        }

        return false;
     }
}
