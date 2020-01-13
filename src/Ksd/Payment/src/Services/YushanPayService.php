<?php
/**
 * User: jerry
 * Date: 2020/01/06
 * Time: 上午 10:03
 */

namespace Ksd\Payment\Services;

use Ksd\Payment\Repositories\YushanPayRepository;
use Ksd\Mediation\Repositories\OrderRepository;


class YushanPayService
{
	protected $repository;
    protected $order_repository;

	public function __construct(YushanPayRepository $repository, OrderRepository $order_repository)
    {
    	$this->repository = $repository;
        $this->orderRepo = $order_repository;
    }

    /**
     * saveTransacctions
     */
    public function saveTransacctions($parameters)
    {
        return $this->repository->saveTransacctions($parameters);
    }
    /**
     * saveTransacctionsReturn
     */
    public function saveTransacctionsReturn($parameters)
    {
        return $this->repository->saveTransacctionsReturn($parameters);
    }

    /**
     * query yushan order  for check
     */
    public function checkYushanOrder($parameters)
    {
        return $this->repository->checkYushanOrder($parameters);
    }


}
