<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\PaymentMethodRepository;

class PaymentMethodService extends BaseService
{
    protected $repository;

    public function __construct(PaymentMethodRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取全部
     * @return mixed
     */
    public function all($lang)
    {
        return $this->model->all();
    }
}
