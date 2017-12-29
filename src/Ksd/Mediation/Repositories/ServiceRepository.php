<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:02
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\CityPass\Service;
use Ksd\Mediation\Services\MemberTokenService;

class ServiceRepository extends BaseRepository
{

    private $memberTokenService;
    private $result = false;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->cityPass = new Service();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
    }

    /**
     * 取得常用問題
     * @param $id
     * @return array
     */
    public function qa($id)
    {
        return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->qa($id);

    }

    /**
     * 問題與建議
     * @param $parameters
     * @return bool
     */
    public function suggestion($parameters)
    {
        return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->suggestion($parameters);
    }


}
