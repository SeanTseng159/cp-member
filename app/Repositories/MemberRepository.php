<?php

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\Member;
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
            $member->password = Hash::make($member->password);
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
                $member->save();
                return $member;
            }
            else {
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
             }
             else {
                 return false;
             }
         } catch (QueryException $e) {
             return false;
         }
     }

    /**
     * 依據帳號,查詢使用者認証
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->model->whereEmail($email)->first();
    }

    /**
     * 根據token取得使用者認證
     * @param $token
     * @return mixed
     */
    public function findByToken($token)
    {
        return $this->model->whereToken($token)->first();
    }
}