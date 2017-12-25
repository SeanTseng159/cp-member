<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Services;

use App\Traits\JWTTokenHelper;
use Firebase\JWT\JWT;

class JWTTokenService
{
    use JWTTokenHelper;

    /**
     * 建立 token
     * @param $member
     * @param $platform
     * @return string
     */
    public function generateToken($member, $platform = 'web')
    {
        $iat = time();
        $exp = time() + env('JWT_EXP', 43200);

        $token = [
            'iss' => env('JWT_ISS', 'CityPass'),
            'iat' => $iat,
            'exp' => $exp,
            'id' => $member->id
        ];

        //來源為app, token無限制時間
        if ($platform === 'app') unset($token['exp']);

        if ($platform === 'oauth') {
            $token['exp'] = time() + 7200;
        }

        return $this->JWTencode($token);
    }

    /**
     * 建立 第三方暫時token
     * @param $member
     * @param $platform
     * @return string
     */
    public function generateOpenIdToken($member, $platform = 'web')
    {
        $iat = time();
        $exp = time() + 15;

        $token = [
            'iss' => env('JWT_ISS', 'CityPass'),
            'iat' => $iat,
            'exp' => $exp,
            'id' => $member->id
        ];

        return $this->JWTencode($token);
    }

    /**
     * 建立 token
     * @param $member
     * @param $platform
     * @return string
     */
    public function generateOAuthToken($id)
    {
        $iat = time();
        $exp = time() + 86400;

        $token = [
            'iss' => env('JWT_ISS', 'CityPass'),
            'iat' => $iat,
            'exp' => $exp,
            'id' => $id
        ];

        return $this->JWTencode($token);
    }

    /**
     * 刷新 token
     * @param $member
     * @param $platform
     * @return string
     */
     public function refreshToken($member, $platform = 'web')
     {
        $result = $this->checkToken($member->token);

        return ($result) ? $this->generateToken($member, $platform) : null;
     }

    /**
     * 檢查 token
     * @param $token
     * @return bool
     */
    public function checkToken($token)
    {
        return $this->JWTdecode($token);
    }
}
