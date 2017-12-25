<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\Member;
use Crypt;
use Hash;
use Log;

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
            $member->validEmailCode = (isset($data['openId']) && $data['openId']) ? Crypt::encrypt($data['openId']) : '';
            $member->validPhoneCode = strval(mt_rand(100000, 999999));
            $member->save();
            return $member;
        } catch (QueryException $e) {
            Log::info('=== 會員註冊 ===');
            Log::debug(print_r($e, true));
            return false;
        } catch (\Exception $e) {
            Log::info('=== 會員註冊 ===');
            Log::debug(print_r($e, true));
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
                if ($member->email) $member->validEmailCode = Crypt::encrypt($member->email);
                if (isset($data['email'])) $member->validEmailCode = Crypt::encrypt($data['email']);
                $member->validPhoneCode = strval(mt_rand(100000, 999999));
                if (!isset($data['birthday']) || !$data['birthday']) unset($member->birthday);
                $member->save();
                return $member;
            } else {
                Log::error('找不到使用者，無法更新');
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

    /**
     * 依據手機,查詢使用者(增加國家代碼)
     * @param $country
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findByCountryPhone($country, $countryCode, $cellphone)
    {
        return $this->model->where(['country' => $country, 'countryCode' => $countryCode, 'cellphone' => $cellphone])->first();
    }

    /**
     * 依據OpenId,查詢使用者
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findByOpenId($openId, $openPlateform)
    {
        return $this->model->where(['openId' => $openId, 'openPlateform' => $openPlateform])->first();
    }


}
