<?php
/**
 * User: lee
 * Date: 2018/09/03
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Parameter\Ticket\Order\TicketParameter;
use App\Services\Ticket\TicketService;
use App\Services\MemberService;
use App\Result\Ticket\TicketResult;

use Exception;

class TicketController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $ticketService;
    protected $memberService;

    private $members;

    public function __construct(TicketService $ticketService, MemberService $memberService)
    {
        $this->ticketService = $ticketService;
        $this->memberService = $memberService;
    }

    /**
     * 取票券列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        try {
            $parameter = (new TicketParameter($request))->all();
            $data = $this->ticketService->all($this->lang, $parameter);
            if ($parameter->orderStatus === '4') {
                $result = (new TicketResult($parameter->orderStatus))->getAll($data);
                if ($result) {
                    foreach ($result as $k => $row) {
                        $member = $this->getMember($row['member']);
                        $result[$k]['member'] = ($member) ? (new TicketResult)->getMember($member) : null;
                    }
                }
            }
            else {
                $member = $this->memberService->find($parameter->memberId);
                $result = (new TicketResult($parameter->orderStatus))->getAll($data, $member);
            }

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }

    private function getMember($memberId)
    {
        if (!isset($this->members[$memberId])) {
            $this->members[$memberId] = $this->memberService->find($memberId);
        }

        return $this->members[$memberId];
    }


    public function forKRTTest(){
        $this->ticketService->forKRTTest();
        return $this->success();
    }
}
