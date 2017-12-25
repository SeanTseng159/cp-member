<?php
/**
 * User: lee
 * Date: 2017/10/26
 * Time: 上午 9:42
 */

namespace App\Parameter\Ipass;

class MemberParameter
{
    /**
     * laravel request 參數處理
     * @param $request
     */
    public function authorize()
    {
        $parameter = new \stdClass;
        $parameter->grant_type = 'auth_code';
        $parameter->client_id = env('IPASS_OAUTH_CLIENT_ID');
        $parameter->client_secret = env('IPASS_OAUTH_CLIENT_SECRET');
        // $parameter->scopes = '';
        // $parameter->redirect_url = '';

        return $parameter;
    }

    /**
     * laravel request 參數處理
     * @param $request
     */
    public function callback($request)
    {
        $parameter = new \stdClass;
        $parameter->access_token = $request->input('access_token');
        $parameter->member_id = $request->input('member_id');
        $parameter->email = $request->input('email');
        $parameter->expire_at = $request->input('expire_at');

        return $parameter;
    }

    /**
     * laravel request 參數處理
     * @param $request
     */
    public function member($member)
    {
        $parameter['openPlateform'] = 'ipass';
        $parameter['openId'] = $member->email;
        $parameter['socialId'] = $member->idn;
        $parameter['name'] = $member->name;
        $parameter['zipcode'] = $member->zipcode;
        $parameter['address'] = $member->addr;
        $parameter['isValidEmail'] = 1;
        $parameter['status'] = 1;
        $parameter['isRegistered'] = 1;

        return $parameter;
    }
}
