<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\OAuth\Repositories;

use Illuminate\Database\QueryException;
use Ksd\OAuth\Models\OauthClient;
use Carbon\Carbon;

class OAuthClientRepository
{
    protected $model;

    public function __construct(OauthClient $model)
    {
        $this->model = $model;
    }

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

    /**
     * 更新應用程式授權資料
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        try {
            $oauthClient = $this->model->find($id);

            if ($oauthClient) {
                $oauthClient->fill($data);
                $oauthClient->code = hash('sha256', Carbon::now() . $oauthClient->uid);
           		$oauthClient->expires_at = Carbon::now()->addMinutes(10);
                $oauthClient->save();
                return $oauthClient;
            } else {
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function queryOne($data)
    {
        return $this->model->where($data)->first();
    }

    public function findByUidAndSecret($uid, $secret)
    {
        return $this->model->where(['uid' => $uid, 'secret' => $secret])->first();
    }
}
