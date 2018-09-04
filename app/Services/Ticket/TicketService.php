<?php
/**
 * User: lee
 * Date: 2018/09/03
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\OrderDetailRepository;

class TicketService extends BaseService
{
    protected $orderDetailRepository;

    public function __construct(OrderDetailRepository $orderDetailRepository)
    {
        $this->orderDetailRepository = $orderDetailRepository;
    }

    /**
     * 取票券列表
     * @param $lang
     * @param $parameter
     * @return mixed
     */
    public function all($lang = 'zh-TW', $parameter = null)
    {
        return $this->orderDetailRepository->all($lang, $parameter);
    }
}
