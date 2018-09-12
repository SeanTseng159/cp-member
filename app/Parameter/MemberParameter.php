<?php
/**
 * User: lee
 * Date: 2017/10/26
 * Time: 上午 9:42
 */

namespace App\Parameter;

class MemberParameter
{
    /**
     * laravel request 參數處理
     * @param $request
     */
    public function create($request)
    {
        $parameter = $request->only([
            'countryCode',
            'cellphone',
            'openPlateform',
            'openId',
            'country'
        ]);

        if ($request->phoneNumber) {
            $parameter['country'] = $request->phoneNumber['country'];
            $parameter['countryCode'] = $request->phoneNumber['countryCode'];
            $parameter['cellphone'] = $request->phoneNumber['cellphone'];
        }

        return $parameter;
    }

    /**
     * laravel request 參數處理
     * @param $request
     */
    public function update($request)
    {
        $parameter = $request->except([
            'id',
            'password',
            'email',
            'newsletter',
            'checkCellphone'
        ]);

        if ($request->phoneNumber) {
            $parameter['country'] = $request->phoneNumber['country'];
            $parameter['countryCode'] = $request->phoneNumber['countryCode'];
            $parameter['cellphone'] = $request->phoneNumber['cellphone'];
        }

        return $parameter;
    }
}