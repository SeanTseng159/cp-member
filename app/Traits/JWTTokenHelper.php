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
    public function JWTencode($token)
    {
      return JWT::encode($token, env('JWT_KEY', '53890045'));
    }

    public function JWTdecode($token = null)
    {
        if (!$token) $token = Request::bearerToken();

        if (!$token) return null;

        try {
            return JWT::decode($token, env('JWT_KEY', '53890045'), ['HS256']);
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
