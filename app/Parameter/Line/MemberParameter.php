<?php

namespace App\Parameter\Line;

class MemberParameter
{
    /**
     * laravel request 參數處理
     * @param $request
     */
    public function member($member)
    {
        $parameter['openPlateform'] = 'line';
        $parameter['openId'] = $member->email;
        $parameter['isTw'] = $member->local;
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
