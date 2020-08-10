<?php

/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\Member;
use Hashids\Hashids;
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
            $member->validEmailCode = '';
            $member->validPhoneCode = strval(mt_rand(100000, 999999));
            $member->save();
            return $member;
        } catch (QueryException $e) {
            Log::info('=== 會員註冊 error ===');
            Log::debug(print_r($e->getMessage(), true));
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
                if (isset($data['socialId'])) $member->socialId = strtoupper($member->socialId);
                $member->save();

                if ($member->openPlateform != 'citypass') $member->email = $member->openId;
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
     * 新增會員 by 邀請
     * @param $data
     * @return mixed
     */
    public function createByInvite($data)
    {
        try {
            $member = new Member();
            $member->fill($data);
            $member->validEmailCode = '';
            $member->validPhoneCode = strval(mt_rand(100000, 999999));
            if (isset($data['password']) && $data['password']) $member->password = Hash::make($data['password']);
            if ($member->email) $member->validEmailCode = Crypt::encrypt($data['email']);

            $member->save();

            if ($member->openPlateform != 'citypass') $member->email = $member->openId;

            return $member;
        } catch (QueryException $e) {
            Log::info('=== 會員註冊 error ===');
            Log::debug(print_r($e->getMessage(), true));
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
        $query = $this->model->where($data);
        // 如果搜尋email也要連同第三方帳號一起搜尋
        if (isset($data['email'])) {
            $members = $query->orWhere('openId', $data['email'])->get();
        } else {
            $members = $query->get();
        }

        // 將第三方登入openId對到email
        if ($members) {
            foreach ($members as $key => $member) {
                if ($member['openPlateform'] != 'citypass') $members[$key]['email'] = $member['openId'];
            }
        }

        return $members;
    }

    /**
     * 找所有會員
     * @param $email
     * @return mixed
     */
    public function all()
    {
        $members = $this->model->all();

        // 將第三方登入openId對到email
        if ($members) {
            foreach ($members as $key => $member) {
                if ($member['openPlateform'] != 'citypass') $members[$key]['email'] = $member['openId'];
            }
        }

        return $members;
    }

    /**
     * 依據帳號,查詢使用者認証
     * @param $email
     * @return mixed
     */
    public function find($id)
    {
        $member = $this->model->find($id);

        return $this->memberEmailMapping($member);
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
        $member = $this->model->whereToken($token)->first();

        return $this->memberEmailMapping($member);
    }

    /**
     * 依據手機,查詢使用者
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findByPhone($countryCode, $cellphone)
    {
        $member = $this->model->where(['countryCode' => $countryCode, 'cellphone' => $cellphone])->first();

        return $this->memberEmailMapping($member);
    }

    /**
     * 依據邀請碼,查詢使用者
     * @param $invitation
     * @return mixed
     */
    public function findByInvitation($invitation)
    {
        $member = $this->model->where(['invited_code' => $invitation])->first();

        return $member;
    }

    /**
     * 依據邀請碼,查詢使用者
     * @param $country
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findByCountryPhone($country, $countryCode, $cellphone)
    {
        $member = $this->model->where(['country' => $country, 'countryCode' => $countryCode, 'cellphone' => $cellphone])->first();

        return $this->memberEmailMapping($member);
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
        return $this->model->where(['country' => $country, 'countryCode' => $countryCode, 'cellphone' => $cellphone, 'isValidPhone' => 1])->first();
    }

    /**
     * 依據身分證/護照,查詢使用者
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findBySocialId($socialId)
    {
        $member = $this->model->where(['socialId' => $socialId])->first();

        return $this->memberEmailMapping($member);
    }

    /**
     * 依據OpenId,查詢使用者
     * @param $countryCode
     * @param $cellphone
     * @return mixed
     */
    public function findByOpenId($openId, $openPlateform)
    {
        $member = $this->model->where(['openId' => $openId, 'openPlateform' => $openPlateform])->first();

        return $this->memberEmailMapping($member);
    }

    /**
     * 對應第三方登入使用者的Email
     * @param $member
     * @return mixed
     */
    private function memberEmailMapping($member)
    {
        if ($member && $member->openPlateform != 'citypass') {
            $member->email = $member->openId;
        }

        return $member;
    }

    //根據invitation,查詢使用者
    public function invitationFind($code)
    {
        $member = $this->model->where(['invited_code' => $code])->first();
        return $member;
    }



    //創建會員時，自動產生邀請碼，並一併上傳至資料庫
    public function createInviteCode($id)
    {
        //產生邀請碼
        $hashids = new Hashids('', 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'); // all lowercase
        //利用id產生邀請碼
        $inviteCode = $hashids->encode($id);

        //上傳邀請碼回至資料庫
        try {
            $member = $this->model->find($id);
            if ($member) {
                $member->invited_code = $inviteCode;
                $member->save();
            } else {
                Log::error('找不到使用者，無法更新');
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }

        return $inviteCode;
    }

    /**
     * 登出會員 by ID
     * @param $id
     * @param $data
     * @return mixed
     */
    public function logoutById($id, $data)
    {
        try {
            $member = $this->model->find($id);

            if ($member) {
                $member->fill($data);
                Log::error($member->id);
                if (isset($data['token'])) $member->token = null;
                $member->save();

                return true;
            } else {
                Log::error('找不到使用者，無法登出');
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }
}
