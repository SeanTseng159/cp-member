<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/5
 * Time: 下午 02:52
 */

namespace Ksd\Mediation\Services;

use App\Services\MemberService;
use Ksd\Mediation\Repositories\MyTicketRepository;

class MyTicketService
{


    private $repository;
    private $memberService;

    public function __construct(MemberService $memberService,MemberTokenService $memberTokenService)
    {
        $this->memberService = $memberService;
        $this->repository = new MyTicketRepository($memberTokenService);
    }

    /**
     * 票券物理主分類(目錄)
     * @param  $parameter
     * @return array
     */
    public function catalogIcon($parameter)
    {
        return $this->repository->catalogIcon($parameter);
    }
    /**
     * 取得票券使用說明
     * @return array
     */
    public function help()
    {
        return $this->repository->help();
    }

    /**
     * 取得票券列表
     * @param  $parameter
     * @return array
     */
    public function info($parameter)
    {
        return $this->repository->info($parameter);
    }

    /**
     * 利用票券id取得細項資料
     * @param  $parameter
     * @return array
     */
    public function detail($parameter)
    {
        return $this->repository->detail($parameter);
    }

    /**
     * 利用票券id取得使用紀錄
     * @param  $parameter
     * @return array
     */
    public function record($parameter)
    {
        return $this->repository->record($parameter);
    }

    /**
     * 轉贈票券
     * @param  $parameters
     * @return  bool
     */
    public function gift($parameters)
    {
        $member = $this->memberService->findByCountryPhone($parameters->country,$parameters->countryCode,$parameters->memberPhone);
        return $this->repository->gift($parameters,$member->id);
    }

    /**
     * 轉贈票券退回
     * @param  $parameters
     * @return  bool
     */
    public function refund($parameters)
    {
        return $this->repository->refund($parameters);
    }


}