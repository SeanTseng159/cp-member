<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/5
 * Time: 下午 03:25
 */

namespace Ksd\Mediation\Repositories;

use Ksd\Mediation\CityPass\MyTicket;
use Ksd\Mediation\Services\MemberTokenService;

class MyTicketRepository extends BaseRepository
{
    private $result = false;
    private $memberTokenService;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->cityPass = new MyTicket();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
        $this->setToken($this->memberTokenService->cityPassUserToken());
    }

    /**
     * 票券物理主分類(目錄)
     * @param  $parameter
     * @return array
     */
    public function catalogIcon($parameter)
    {
        $cityPass = $this->cityPass->authorization($this->token)->catalogIcon($parameter->hash);
        return  $cityPass;
    }

    /**
     * 取得票券使用說明
     * @return array
     */
    public function help()
    {
        $cityPass = $this->cityPass->authorization($this->token)->help();
        return  $cityPass;
    }

    /**
     * 取得票券列表
     * @param  $parameter
     * @return array
     */
    public function info($parameter)
    {
        $statusId = $parameter->id;
        $cityPass = $this->cityPass->authorization($this->token)->info($statusId);
        return $cityPass ;
    }

    /**
     * 利用id取得細項資料
     * @param  $parameter
     * @return array
     */
    public function detail($parameter)
    {
        $id = $parameter->id;
            $cityPass = $this->cityPass->authorization($this->token)->detail($id);
            return $cityPass;
    }

    /**
     * 利用id取得使用紀錄
     * @param  $parameter
     * @return array
     */
    public function record($parameter)
    {
        $id = $parameter->id;
        $cityPass = $this->cityPass->authorization($this->token)->record($id);
        return $cityPass;
    }

    /**
     * 轉贈票券
     * @param $parameters
     * @param $id
     * @return  bool
     */
    public function gift($parameters,$id)
    {
        $this->result = $this->cityPass->authorization($this->token)->gift($parameters,$id);
        return $this->result;
    }

    /**
     *  轉贈票券退回
     * @param parameters
     * @return  bool
     */
    public function refund($parameters)
    {
        $this->result = $this->cityPass->authorization($this->token)->refund($parameters);
        return $this->result;
    }

    /**
     *  隱藏票券
     * @param parameters
     * @return  bool
     */
    public function hide($parameters)
    {
        $this->result = $this->cityPass->authorization($this->token)->hide($parameters);
        return $this->result;
    }
}