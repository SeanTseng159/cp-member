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
use App\Result\Ticket\TicketResult;

use Exception;

class TicketController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
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
            $result = (new TicketResult)->getAll($data);

            //var_dump($result);

            //return $this->success($result);
        } catch (Exception $e) {
            var_dump($e->getMessage());
            //return $this->success();
        }
    }
}
