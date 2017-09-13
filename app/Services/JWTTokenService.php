<?php

namespace App\Services;

use App\Traits\JWTTokenHelper;
use Firebase\JWT\JWT;

class JWTTokenService
{
    use JWTTokenHelper;

    /**
     * 建立 token
     * @param $member
     * @return string
     */
    public function generateToken($member)
    {
        $iat = time();
        $exp = time() + env('JWT_EXP');
        
        return $this->JWTencode([
            'iss' => env('JWT_ISS'),
            'iat' => $iat,
            'exp' => $exp,
            'id' => $member->id
        ]);
    }

    /**
     * 刷新 token
     * @param $member
     * @return string
     */
     public function refreshToken($member)
     {
        $result = $this->checkToken($member->token);

        return ($result) ? $this->generateToken($member) : null;
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