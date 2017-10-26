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
        $hasToken = empty($token);
        if ($hasToken) {
            $token = Request::bearerToken();
        }
        try {
            return JWT::decode($token, env('JWT_KEY', '53890045'), ['HS256']);
        } catch (\Firebase\JWT\ExpiredException $exception) {
            throw $exception;
        } catch (\Firebase\JWT\SignatureInvalidException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            if(!$hasToken) {
                throw $exception;
            }
        }

        return null;
    }

}
