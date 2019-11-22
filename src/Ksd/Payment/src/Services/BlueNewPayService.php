<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\Payment\Services;

use Ksd\Payment\Repositories\BlueNewPayRepository;

class BlueNewPayService
{
    protected $repository;
    protected $order_repository;

    public function __construct(BlueNewPayRepository $repository)
    {
        $this->repository = $repository;
    }

    public function merchant($url)
    {
        return $this->repository->merchantValidation(['url' => $url]);

    }

}
