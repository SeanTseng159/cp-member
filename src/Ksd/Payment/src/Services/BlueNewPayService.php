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
use Log;

class BlueNewPayService
{
    protected $repository;
    protected $order_repository;

    public function __construct(BlueNewPayRepository $repository, OrderRepository $order_repository)
    {
        $this->repository = $repository;
        $this->order_repository = $order_repository;
    }


    public function merchant($url)
    {
        return $this->repository->merchantValidation(['url' => $url]);
    }

    /**
     * reserve
     * @param $mobleParams
     * @return mixed
     */
    public function newReserve($mobleParams)
    {
        if ($mobleParams['orderNo']) {
            // 導向路徑
            if ($mobleParams['type'] === 'google') {
                return $this->repository->reserve($mobleParams);
            } else if ($mobleParams['type'] === 'apple') {
                return $this->repository->reserve($mobleParams);
            }
        }

        return [
            'code' => 'E0101',
            'message' => '訂單不存在'
        ];
    }

    public function merchant($url)
    {
        return $this->repository->merchantValidation(['url' => $url]);
    }


     /* confirm
     * @param $parameters
     * @return mixed
     */
    public function confirm($parameters)
    {
        Log::debug('======= start sent bluenewpay  service=======');
        return $this->repository->confirm($parameters);
    }

}
