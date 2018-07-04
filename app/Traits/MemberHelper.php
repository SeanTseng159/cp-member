<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Traits;

use App\Traits\JWTTokenHelper;

trait MemberHelper
{
    use JWTTokenHelper;

    /**
     * 取得會員ID
     * @return int|null
     */
    public function getMemberId()
    {
        $tokenData = $this->JWTdecode();

        return ($tokenData) ? $tokenData->id : null;
    }
}
