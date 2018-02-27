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
        $data = $this->repository->info($parameter);

        if ($data && $data !== 'nodata') {
            foreach ($data as $key => $value) {
                $member = $this->memberService->find($value['memberId']);

                if ($member) {
                    $memberData = new \stdClass;
                    $memberData->name = $member->name;
                    $memberData->phone = '+' . $member->countryCode . $member->cellphone;

                    $member = $memberData;
                }

                unset($data[$key]['memberId']);
                unset($data[$key]['memberName']);
                unset($data[$key]['memberPhone']);
                $data[$key]['member'] = $member;
            }
        }

        return $data;
    }

    /**
     * 利用票券id取得細項資料
     * @param  $parameter
     * @return array
     */
    public function detail($parameter)
    {
        $data = $this->repository->detail($parameter);

        if ($data && $data !== 'nodata') {
            if (isset($data['gift']) && $data['gift']) {
                $member = $this->memberService->find($data['gift']['memberId']);

                if ($member) {
                    $memberData = new \stdClass;
                    $memberData->name = $member->name;
                    $memberData->phone = '+' . $member->countryCode . $member->cellphone;

                    $member = $memberData;
                }

                unset($data['gift']['memberId']);
                $data['gift']['member'] = $member;
            }
        }

        return $data;
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
        if(isset($member)) {
            return $this->repository->gift($parameters, $member->id);
        }else{
            return false;
        }
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

    /**
     * 隱藏票券
     * @param  $parameters
     * @return  bool
     */
    public function hide($parameters)
    {
        return $this->repository->hide($parameters);
    }


}
