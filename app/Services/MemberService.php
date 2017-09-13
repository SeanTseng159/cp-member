<?php

namespace App\Services;

use App\Repositories\MemberRepository;
use App\Services\JWTTokenService;
use Illuminate\Support\Facades\Hash;

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
     * 依據email,查詢使用者認証
     * @param $uuid
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

        //檢查錯誤
        $errorList = $jwtTokenService->getErrorList();
        if (!in_array($token, $errorList)) {
            $result = $this->update($member->id, [
                'token' => $token
           ]);
    
           return ($result) ? $token : null;
        }
 
        return $token;
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
}