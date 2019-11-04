<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Traits;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Request;

trait JWTTokenHelper
{
    public function JWTencode($token, $key = null)
    {
        if (!$key) $key = env('JWT_KEY');

        return JWT::encode($token, $key);
    }

    public function JWTdecode($token = null)
    {
        
        
        if (!$token) $token = Request::bearerToken();

        if (!$token) return null;
        
        try {
            return JWT::decode($token, env('JWT_KEY', 'DEVKEY'), ['HS256']);
        } catch (\Firebase\JWT\ExpiredException $exception) {
            return null;
        } catch (\Firebase\JWT\SignatureInvalidException $exception) {
            return null;
        } catch (\Exception $exception) {
            return null;
        }

        return null;
    }

}
