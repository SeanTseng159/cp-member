<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\Payment\Services;

use Ksd\Payment\Repositories\TaiwanPayRepository;
use Ksd\Mediation\Repositories\OrderRepository;


class TaiwanPayService
{
	protected $repository;
    protected $order_repository;

	public function __construct(TaiwanPayRepository $repository, OrderRepository $order_repository)
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




}
