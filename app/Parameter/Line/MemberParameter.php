<?php

namespace App\Parameter\Line;

class MemberParameter
{
    /**
     * laravel request εζΈθη
     * @param $request
     */
    public function member($user_profile, $payload)
    {
        $parameter['openPlateform'] = 'line';
        $parameter['openId'] = $payload->email;
        $parameter['name'] = $payload->name;
        $parameter['lineUuid'] = $payload->sub;
        $parameter['isValidEmail'] = 1;
        $parameter['status'] = 1;
        $parameter['isRegistered'] = 1;

        return $parameter;
    }
}
