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
use Ksd\Mediation\Services\MemberTokenService;
use App\Services\JWTTokenService;
use App\Repositories\Ticket\OrderDetailRepository;
use App\Traits\StringHelper;

class MyTicketService
{
    use StringHelper;

    private $repository;
    private $memberService;
    private $memberTokenService;
    private $orderDetailRepository;
    private $jwtTokenService;

    public function __construct(MyTicketRepository $myTicketRepository, 
                                 MemberService $memberService, 
                                 MemberTokenService $memberTokenService, 
                                 OrderDetailRepository $orderDetailRepository,
                                 JWTTokenService $jwtTokenService
    )
    {
        $this->repository = $myTicketRepository;
        $this->memberService = $memberService;
        $this->memberTokenService = $memberTokenService;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->jwtTokenService = $jwtTokenService;
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
                    $memberData->name = $this->hideName($member->name);
                    $memberData->phone = '+' . $member->countryCode . $this->hidePhoneNumber($member->cellphone);

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
                    $memberData->name = $this->hideName($member->name);
                    $memberData->phone = '+' . $member->countryCode . $this->hidePhoneNumber($member->cellphone);

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
        $memberId = $this->memberTokenService->getId();

        $myMemberIsValid = $this->memberService->checkPhoneIsValidById($memberId);
        if (!$myMemberIsValid) return 4;

        $member = $this->memberService->findValidByCountryPhone($parameters->country, $parameters->countryCode, $parameters->memberPhone);
        // 會員不存在
        if (!$member) return 3;
        // 會員手機未驗證
        // if ($member->isValidPhone != 1) return 3;
        
        $result = $this->repository->gift($parameters, $member->id);
        return ($result) ? 1 : 0;
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

    public function mrtCertificate($parameters)
    {
        $member_id = $this->jwtTokenService->JWTdecode()->id;
        $order_detail = $this->orderDetailRepository->getMrtCertificate($parameters->id, $member_id);
        
        if ( ! empty($order_detail)) {
            
            // 可移至transformer
            $order_detail = [
                'orderNo' => $order_detail->order_no,
                'name' => $order_detail->prod_name,
                'specName' => $order_detail->prod_spec_name . 
                              (empty($order_detail->prod_spec_price_name) ? '' : 
                              '/' . $order_detail->prod_spec_price_name),
                'qty' => $order_detail->price_company_qty,
                'price' => $order_detail->price_off,
                'print_at' => $order_detail->print_mrt_certificate_at,
                'buy_at' => $order_detail->created_at,
            ];
        }
        return $order_detail;
    }
    
    public function printMrtCertificate($parameters)
    {
        $member_id = $this->jwtTokenService->JWTdecode()->id;
        return $this->orderDetailRepository->printMrtCertificate($parameters->id, $member_id);
        
    }
}
