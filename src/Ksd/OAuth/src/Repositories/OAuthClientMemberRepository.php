<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\OAuth\Repositories;

use Illuminate\Database\QueryException;
use Ksd\OAuth\Models\OauthClientMember;

class OAuthClientMemberRepository
{
    protected $model;

    public function __construct(OauthClientMember $model)
    {
        $this->model = $model;
    }

    /**
     * 新增
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        try {
            $oauthClientMember = new OAuthClientMember();
            $oauthClientMember->fill($data);
            $oauthClientMember->save();
            return $oauthClientMember;
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 更新
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        try {
            $oauthClientMember = $this->model->find($id);

            if ($oauthClientMember) {
                $oauthClientMember->fill($data);
                $oauthClientMember->save();
                return $oauthClientMember;
            } else {
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }

    public function queryOne($data)
    {
        return $this->model->where($data)->first();
    }
}
