<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\Payment\Services;

use Ksd\Payment\Repositories\TspgRepository;

class TspgService
{
	protected $repository;

	public function __construct(TspgRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * confirm
     * @param $parameters
     * @return mixed
     */
    public function generateVirtualAccount($parameters)
    {
        return $this->repository->generateVirtualAccount($parameters);
    }
}
