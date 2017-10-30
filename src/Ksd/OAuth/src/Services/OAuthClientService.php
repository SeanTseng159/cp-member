<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\OAuth\Services;

use Ksd\OAuth\Repositories\OAuthClientRepository;
use Carbon\Carbon;

class OAuthClientService
{
    protected $repository;

    public function __construct(OAuthClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create($data)
    {
        return $this->repository->create($data);
    }

    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function queryOne($data)
    {
        return $this->repository->queryOne($data);
    }

    public function authorize($uid, $secret)
    {
        $os = $this->repository->findByUidAndSecret($uid, $secret);

        if ($os) {
        	$os = $this->repository->update($os->id, ['code' => hash('sha256', Carbon::now() . $os->uid)]);
        }

        return $os;
    }
}
