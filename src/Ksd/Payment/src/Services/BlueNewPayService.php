<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\Payment\Services;

use Ksd\Payment\Repositories\BlueNewPayRepository;
use Ksd\Mediation\Repositories\OrderRepository;
use Ksd\Mediation\Config\ProjectConfig;

class BlueNewPayService
{
	protected $repository;
    protected $order_repository;

	public function __construct(BlueNewPayRepository $repository, OrderRepository $order_repository)
    {
    	$this->repository = $repository;
        $this->orderRepo = $order_repository;
    }

    /**
     * confirm
     * @param $parameters
     * @return mixed
     */
    public function confirm($parameters)
    {
        return $this->repository->confirm($parameters);
    }
}
