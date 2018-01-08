<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:04
 */

namespace Ksd\Mediation\Services;



use Ksd\Mediation\Repositories\ServiceRepository;
use App\Services\MemberService;
use App\Traits\JWTTokenHelper;
use App\Traits\CryptHelper;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendServiceEmail;
use Illuminate\Contracts\Encryption\DecryptException;

class ServiceService
{
    use DispatchesJobs;
    use CryptHelper;

    use JWTTokenHelper;
    private $repository;
    private $memberService;

    public function __construct(MemberService $memberService,MemberTokenService $memberTokenService)
    {
        $this->memberService = $memberService;
        $this->repository = new ServiceRepository($memberTokenService);
    }

    /**
     * 取得常用問題
     * @return array
     */

    public function qa()
    {
        return $this->repository->qa();
    }


    /**
     * 問題與建議
     * @param $parameters
     * @return bool
     */
    public function suggestion($parameters)
    {
        if($parameters->loginStatus === "Y") {
            $data = $this->JWTdecode();
            if (empty($data)) {
                return false;
            }
            $member = $this->memberService->find($data->id);
            $job = (new SendServiceEmail($member,$parameters))->delay(5);
            $this->dispatch($job);
            return true;
        }else{
            $job = (new SendServiceEmail($member=null,$parameters))->delay(5);
            $this->dispatch($job);
            return true;

        }


    }

    /**
     * 問題與建議
     * @param $parameters
     * @return bool
     */
    public function getData($parameters)
    {
        return $parameters;
    }


}
