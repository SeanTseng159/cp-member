<?php

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\Member;
use Crypt;
use Hash;

class MemberRepository
{
    protected $model;

    public function __construct(Member $model)
    {
        $this->model = $model;
    }

    /**
     * 新增會員
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        try {
            $member = new Member();
            $member->fill($data);
            $member->validPhoneCode = mt_rand(100000, 999999);
            $member->validEmailCode = Crypt::encrypt($data['cellphone']);
            $member->save();
            return $member;
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 更新會員資料
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        try {
            $member = $this->model->find($id);

            if ($member) {
                $member->fill($data);
                if (isset($data['password'])) $member->password = Hash::make($member->password);
                $member->validPhoneCode = mt_rand(100000, 999999);
                $member->save();
                return $member;
            } else {
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 刪除會員
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        try {
            $member = $this->model->find($id);

            if ($member) {
                $member->delete();
                return $id;
            } else {
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 找所有會員
     * @param $data
     * @return mixed
     */
     public function query($data)
     {
         return $this->model->where($data)->get();
     }

     /**
     * 找所有會員
     * @param $email
     * @return mixed
     */
     public function all()
     {
         return $this->model->all();
     }

    /**
     * 依據帳號,查詢使用者認証
     * @param $email
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * 依據帳號,查詢使用者
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->model->whereEmail($email)->first();
    }

    /**
     * 依據Token,查詢使用者
     * @param $token
     * @return mixed
     */
    public function findByToken($token)
    {
        return $this->model->whereToken($token)->first();
    }

    /**
     * 依據手機,查詢使用者
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findByPhone($countryCode, $cellphone)
    {
        return $this->model->where(['countryCode' => $countryCode, 'cellphone' => $cellphone])->first();
    }
}