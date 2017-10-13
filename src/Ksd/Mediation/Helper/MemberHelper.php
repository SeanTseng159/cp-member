<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:18
 */

namespace Ksd\Mediation\Helper;


use Illuminate\Support\Facades\Request;

trait MemberHelper
{
    /**
     * 取得使用者 token
     * @return string
     */
    protected function userToken()
    {
        ////        $token = 'gw83u0093jo5g6n7cv22rdnx13n569e5';
//        $token = 'draeedfcm6nue7saxgj8pjxj2b9mqyji';
        $localToken = '8gokejggsu8j4h8e3gnu72rdcj6xgyls';
//        $localToken = 'uvyxdjeqfovjveeiuyqhu0uqq9ef6pnp';
        return $localToken;
    }

    protected function cityPassUserToken()
    {
        return Request::bearerToken();
    }
}