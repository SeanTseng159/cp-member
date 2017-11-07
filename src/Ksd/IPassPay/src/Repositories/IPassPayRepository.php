<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Repositories;

use Illuminate\Database\QueryException;
//use Ksd\OAuth\Models\OauthClient;
use Carbon\Carbon;

class IPassPayRepository
{
    /*protected $model;

    public function __construct(OauthClient $model)
    {
        $this->model = $model;
    }*/

    /**
     * 新增應用程式授權資料
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        try {
            $oauthClient = new OauthClient();
            $oauthClient->fill($data);
            $oauthClient->uid = uniqid();
            $oauthClient->secret = hash('sha256', mt_rand(1, 99999999));
            $oauthClient->code = hash('sha256', Carbon::now() . $oauthClient->uid);
            $oauthClient->expires_at = Carbon::now()->addMinutes(10);
            $oauthClient->save();
            return $oauthClient;
        } catch (QueryException $e) {
            return false;
        }
    }
}
